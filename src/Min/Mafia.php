<?php
namespace Min;

use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket; 
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\level\Position;
use pocketmine\level\Explosion;
use pocketmine\scheduler\Task;

class Mafia extends PluginBase implements Listener
{

    //public $JobList = array("의사");
    public $JobList = array("경찰", "마피아", "마피아", "의사", "군인", "기자", "시민", "시민");
    public $pp = array("플레이어1", "플레이어2", "플레이어3", "플레이어4", "플레이어5", "플레이어6", "플레이어7", "플레이어8");
    public $plist;
    public $dlist;
    public $mlist;
    public $rlist;
	public $vlist;

    public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->data = (new Config($this->getDataFolder() . "Config.yml", Config::YAML, [
            "상태" => "중지",//중지 실행 참가
            "시간" => "없음", //투표 아침 저녁 반론
			"반론자" => [
			"사람" => []
			],
            "인원" => 0,
			"투표" => [],
			"최후" => [
			"찬성" => 0,
			"불찬성" => 0
			],
            "마피아팀" => [
                "마피아" => [],
                "선택한사람" => "없음"
            ],
            "시민팀" => [],
            "사람" => [],
            "의사선택" => [],
            "기자선택" => [],
            "기자갯수" => 1,
            "군인갯수" => 1,
			"경찰사용" => "없음",
            "대기실" => [
                "x" => 0,
                "y" => 0,
                "z" => 0,
                "world" => "Lobby"
            ],
			"스폰" => [
                "x" => 0,
                "y" => 0,
                "z" => 0,
                "world" => "Lobby"
            ],
            "단두대" => [
                "x" => 0,
                "y" => 0,
                "z" => 0,
                "world" => "Lobby"
            ],
            "관중석" => [
                "x" => 0,
                "y" => 0,
                "z" => 0,
                "world" => "Lobby"
            ],
			"회의" => [
                "x" => 0,
                "y" => 0,
                "z" => 0,
                "world" => "Lobby"
            ],
            "플레이어" => [
                "플레이어1" => [
                    "x" => 0,
                    "y" => 0,
                    "z" => 0,
                    "world" => "Lobby",
                    "이름" => []
                ],
                "플레이어2" => [
                    "x" => 0,
                    "y" => 0,
                    "z" => 0,
                    "world" => "Lobby",
                    "이름" => []
                ],
                "플레이어3" => [
                    "x" => 0,
                    "y" => 0,
                    "z" => 0,
                    "world" => "Lobby",
                    "이름" => []
                ],
                "플레이어4" => [
                    "x" => 0,
                    "y" => 0,
                    "z" => 0,
                    "world" => "Lobby",
                    "이름" => []
                ],
                "플레이어5" => [
                    "x" => 0,
                    "y" => 0,
                    "z" => 0,
                    "world" => "Lobby",
                    "이름" => []
                ],
                "플레이어6" => [
                    "x" => 0,
                    "y" => 0,
                    "z" => 0,
                    "world" => "Lobby",
                    "이름" => []
                ],
                "플레이어7" => [
                    "x" => 0,
                    "y" => 0,
                    "z" => 0,
                    "world" => "Lobby",
                    "이름" => []
                ],
                "플레이어8" => [
                    "x" => 0,
                    "y" => 0,
                    "z" => 0,
                    "world" => "Lobby",
                    "이름" => []
                ]
            ]
        ]));
        $this->db = $this->data->getAll();
        $this->player = new Config($this->getDataFolder() . "player.yml", Config::YAML);
        $this->p = $this->player->getAll();
    }

    public function onDisable()
    {
        $this->off();
    }

    public function leave($player)
    {
        $name = $player->getName();
		if ($this->db["상태"] == "실행") {
			if ($this->p[$name]["참가"] == "참가"){
			$this->db["플레이어"][$this->p[$name]["번호"]]["이름"] = [];
			}
        }
        $this->p[$name]["참가"] = "불참가";
        $this->p[$name]["직업"] = "없음";

        if (isset($this->db["마피아팀"]["마피아"][$name])) {
            unset($this->db["마피아팀"]["마피아"][$name]);
        }
        if (isset($this->db["시민팀"]["시민들"][$name])) {
            unset($this->db["시민팀"]["시민들"][$name]);
        }
		if (isset($this->db["투표"]["사람"][$name])) {
					unset ($this->db["투표"]["사람"][$name]);
        }
		if (isset($this->db["투표"]["횟수"])) {
					unset ($this->db["투표"]["횟수"]);
        }
        $this->p[$name]["번호"] = "없음";
		$this->p[$name]["투표"] = "안함";
		$this->p[$name]["최후"] = "안함";
        $this->save();
    }

    public function skillend()
    {
        $this->db["마피아팀"]["선택한사람"] = "없음";
        $this->db["기자선택"]["사람"] = "없음";
        $this->db["의사선택"] = "없음";
        $this->save();
    }

    public function skill()
    {
        if ($this->db["기자선택"]["사람"] != "없음") {
            if ($this->db["기자갯수"] == 1) {
                foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 기자가 §c{$this->db["기자선택"]["사람"]}§f님이§c {$this->p[$this->db["기자선택"]["사람"]]["직업"]}§f이라는걸 발표했습니다");
					}
                
                $this->db["기자갯수"] = 0;
                $this->save();
            }
        }
        if ($this->db["마피아팀"]["선택한사람"] === $this->db["의사선택"] and $this->db["마피아팀"]["선택한사람"] != "없음" and $this->db["의사선택"] != "없음") {
            foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 의사가 " . $this->db["마피아팀"]["선택한사람"] ."님을 살렸습니다!");
					}
            
			return true;
        }
        if ($this->db["마피아팀"]["선택한사람"] == "없음") {
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 마피아가 아무도 죽이지 않았습니다");
					}
			return true;
			}else if ($this->p[$this->db["마피아팀"]["선택한사람"]]["직업"] == "군인") {
				if($this->db["군인갯수"] == 0){
					foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 이미 군인이 마피아의 공격을 방어해서 이번 공격을 방어하지 못했습니다.");
					}
				}else{
					foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 마피아의 공격을 군인 " . $this->db["마피아팀"]["선택한사람"] . "님이 방어했습니다");
					}
				$this->db["군인갯수"] = 0;
				$this->save();
                return true;
				}
		}

			$this->db["플레이어"][$this->p[$this->db["마피아팀"]["선택한사람"]]["번호"]]["이름"] = [];
			$this->p[$this->db["마피아팀"]["선택한사람"]]["참가"] = "불참가";
			$this->p[$this->db["마피아팀"]["선택한사람"]]["직업"] = "없음";
			$this->p[$this->db["마피아팀"]["선택한사람"]]["번호"] = "없음";
			$this->p[$this->db["마피아팀"]["선택한사람"]]["최후"] = "없음";
			$ppp = $this->getServer()->getPlayer($this->db["마피아팀"]["선택한사람"]);
				$ppp->sendMessage("§l§f마피아의의해 사망하셨습니다");
				$ppp->addtitle("마피아의 의해", "사망하셨습니다");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				$this->db["인원"]--;
				if (isset($this->db["마피아팀"]["마피아"][$ppp->getName()])) {
					unset ($this->db["마피아팀"]["마피아"][$ppp->getName()]);
            $this->save();
        }
				if (isset($this->db["시민팀"]["시민들"][$ppp->getName()])) {
					unset ($this->db["시민팀"]["시민들"][$ppp->getName()]);
            $this->save();
        }
				if (isset($this->db["사람"][$ppp->getName()])) {
					unset ($this->db["사람"][$ppp->getName()]);
            $this->save();
        }
		foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f " . $this->db["마피아팀"]["선택한사람"] . "님이 마피아의 의해 사망했습니다.");
					}
			$this->save();
            return true;
    }
    public function off()
    {
		if (isset($this->db['사람'])) {
			foreach ($this->db['사람'] as $key => $type) {
			$ppp = $this->getServer()->getPlayer($key);
			$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
        }
        }
		
        $this->db["상태"] = "중지";
        $this->db["인원"] = "0";
        $this->db["사람"] = [];
        $this->db["시간"] = "없음";
		$this->db["기자갯수"] = 1;
		$this->db["군인갯수"] = 1;
        if (isset($this->db["마피아팀"]["마피아"])) {
            unset($this->db["마피아팀"]["마피아"]);
        }
        if (isset($this->db["시민팀"]["시민들"])) {
            unset($this->db["시민팀"]["시민들"]);
        }
		if (isset($this->db["투표"]["사람"])) {
					unset ($this->db["투표"]["사람"]);
        }
		if (isset($this->db["투표"]["횟수"])) {
					unset ($this->db["투표"]["횟수"]);
        }
		$this->db["반론자"]["사람"] = [];
        unset($this->db["플레이어"]["플레이어1"]["이름"]);
        unset($this->db["플레이어"]["플레이어2"]["이름"]);
        unset($this->db["플레이어"]["플레이어3"]["이름"]);
        unset($this->db["플레이어"]["플레이어4"]["이름"]);
        unset($this->db["플레이어"]["플레이어5"]["이름"]);
        unset($this->db["플레이어"]["플레이어6"]["이름"]);
        unset($this->db["플레이어"]["플레이어7"]["이름"]);
        unset($this->db["플레이어"]["플레이어8"]["이름"]);
        foreach ($this->getServer()->getOnlinePlayers() as $p) {
            $this->p[$p->getName()]["참가"] = "불참가";
            $this->p[$p->getName()]["직업"] = "없음";
            $this->p[$p->getName()]["번호"] = "없음";
        }
        $this->JobList = array("경찰", "마피아", "마피아", "의사", "군인", "기자", "시민", "시민");
        $this->pp = array("플레이어1", "플레이어2", "플레이어3", "플레이어4", "플레이어5", "플레이어6", "플레이어7", "플레이어8");
        $this->skillend();
        $this->save();
		$this->getServer()->broadcastMessage("§c〔 §f마피아 §c〕§f 마피아가 끝났습니다");
    }

    public function save()
    {
        $this->data->setAll($this->db);
        $this->data->save();
        $this->player->setAll($this->p);
        $this->player->save();
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $name = $event->getPlayer()->getName();
        $player = $event->getPlayer();
        if (!isset($this->p[$name])) {
            $this->p[$name] = [
                "참가" => "불참가",//참가 불참가
                "직업" => "없음",	//경찰1,마피아2,의사1,군인1,기자1,시민1,시민1  ,유령,없음
				"투표" => "안함",
				"최후" => "안함",
                "번호" => []
            ];
            $this->save();
        }
    }
	public function onChat(PlayerChatEvent $ev){
		$player = $ev->getPlayer();
		$name = $player->getName();
		$msg = $ev->getMessage();
		$number = $this->p[$name]["번호"];
		if($this->db["상태"] == "실행"){
			if (isset($this->db["사람"][$name])) {
			$ev->setCancelled();
			if($this->db["시간"] == "저녁"){
				if($this->p[$name]["직업"] == "마피아"){
					foreach ($this->db["마피아팀"]["마피아"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§c {$number}. {$name} §f: §c{$msg}");
					}
					return true;
				}else{
					$player->sendMessage("§c〔 §f마피아 §c〕§c 현재 밤이여서 채팅을 칠수가 없습니다");
					return true;
				}
			}
			if($this->db["시간"] == "아침" or $this->db["시간"] == "투표"){
					foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f {$number}. {$name} §f: §f{$msg}");
					}
					return true;
			}
			if($this->db["시간"] == "반론투표"){
				if($this->db["반론자"]["사람"] == $name){
					foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§b 반론자 {$name} §f: §f{$msg}");
					}
					return true;
				}
					foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f {$number}. {$name} §f: §f{$msg}");
					}
					return true;
			}
			if($this->db["시간"] == "반론"){
				if($this->db["반론자"]["사람"] != $name){
					$player->sendMessage("§c〔 §f마피아 §c〕§f 현재 반론자의 시간이여서 채팅을 칠수가 없습니다");
					return true;
				}
					foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§b 반론자 {$name} §f: §f{$msg}");
					}
					return true;
			}
			
			
			}
		}
	}
    public function onQuit(PlayerQuitEvent $event)
    {
        $name = $event->getPlayer()->getName();
        if (isset($this->db["사람"][$name])) {
            $this->db["인원"]--;
            unset($this->db["사람"][$name]);
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f " . $name . "님이 마피아 게임에서 나가버렸습니다. §c" . $this->db["인원"] . "/§c8");
				}
        }
        $this->leave($event->getPlayer());
		return true;
    }

    public function Coolt()
    {
        $this->getScheduler()->scheduleDelayedTask(new class($this) extends Task {
            public function __construct($th)
            {
                $this->this = $th;
            }

            public function onRun(int $currentTick)
            {
                $this->this->gameSt();
            }
        }, 20 * 10);
        $this->getServer()->broadcastMessage("§c〔 §f마피아 §c〕§f 10초후 마피아가 시작됩니다");
    }

    public function Night()
    {
        if ($this->db["상태"] == "실행") {
            $this->getScheduler()->scheduleDelayedTask(new class($this) extends Task {
                public function __construct($th)
                {
                    $this->this = $th;
                }

                public function onRun(int $currentTick)
                {
                    if ($this->this->db["상태"] == "실행") {
                        $this->this->Mor();
                    }
                }
            }, 20 * 60);
			
			if(count($this->db["마피아팀"]["마피아"]) >= count($this->db["시민팀"]["시민들"])){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "마피아팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			if(count($this->db["마피아팀"]["마피아"]) == 0){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "시민팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			if(isset($this->db["투표"]["사람"])){
			unset($this->db["투표"]["사람"]);
			}
			if(isset($this->db["투표"]["횟수"])){
			unset($this->db["투표"]["횟수"]);
			}
			$this->db["최후"]["찬성"] = 0;
			$this->db["최후"]["불찬성"] = 0;
			$this->db["반론자"]["사람"] = [];
			foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
			$this->warp($players);
			
			$this->db["투표"]["사람"][$players->getName()]["투표"] = 0;
			$this->p[$players->getName()]["투표"] = "안함";
			$this->p[$players->getName()]["최후"] = "안함";
			}
            $this->skillend();
            $this->db["시간"] = "저녁";
            $this->save();
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 밤이 되었습니다 §c60초 후에 아침이됩니다");
					$players->sendMessage("§c〔 §f마피아 §c〕§f 능력은 터치로 사용해주세요!");
					
			}
			return true;
        }
    }

    public function onTouch(PlayerInteractEvent $event)
    {
        $name = $event->getPlayer()->getName();
        $player = $event->getPlayer();
        if ($this->db["상태"] == "실행") {
            if ($this->db["시간"] == "저녁") {
                if ($this->p[$name]["직업"] == "경찰") {
                    $this->sendUI($player, 51234, $this->police($name));
                }
                if ($this->p[$name]["직업"] == "기자") {
                    $this->sendUI($player, 51235, $this->report($name));
                }
                if ($this->p[$name]["직업"] == "마피아") {
                    $this->sendUI($player, 51236, $this->mafiasun($name));
                }
                if ($this->p[$name]["직업"] == "의사") {
                    $this->sendUI($player, 51237, $this->doctor($name));
                }
            }
			if ($this->db["시간"] == "투표") {
				if(isset($this->db["사람"][$name])){
					$this->sendUI($player, 4123, $this->vote($name));
				}
            }
			if ($this->db["시간"] == "반론투표") {
				if(isset($this->db["사람"][$name])){
					$this->sendUI($player, 44344, $this->bvote($name));
				}
            }
        }
    }
	public function votetime()
    {
        if ($this->db["상태"] == "실행") {
            $this->getScheduler()->scheduleDelayedTask(new class($this) extends Task {
                public function __construct($th)
                {
                    $this->this = $th;
                }

                public function onRun(int $currentTick)
                {
                    if ($this->this->db["상태"] == "실행") {
						//foreach ($this->this->db['사람'] as $key => $type) {
						//$players = $this->this->getServer()->getPlayer($key);
						$this->this->ifvote();
						//}
                    }
                }
            }, 20 * 15);
			if(count($this->db["마피아팀"]["마피아"]) >= count($this->db["시민팀"]["시민들"])){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "마피아팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			if(count($this->db["마피아팀"]["마피아"]) == 0){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "시민팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 표시간이 되었습니다 §c15초 후에 결과가 발표됩니다");
					$players->sendMessage("§c〔 §f마피아 §c〕§f 터치로 투표해주세요");
					
			}
			$this->db["시간"] = "투표";
			$this->save();
			return true;
        }
    }
	public function ifvote(){
		$p1 = [];
		$p2 = [];
		$p3 = [];
		$p4 = [];
		$p5 = [];
		$p6 = [];
		$p7 = [];
		$p8 = [];
        foreach ($this->db["사람"] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
			if($p1 == []){
				$p1 = $this->db["투표"]["사람"][$players->getName()]["투표"];
			}else if($p2 == []){
				$p2 = $this->db["투표"]["사람"][$players->getName()]["투표"];
			}else if($p3 == []){
				$p3 = $this->db["투표"]["사람"][$players->getName()]["투표"];
			}else if($p4 == []){
				$p4 = $this->db["투표"]["사람"][$players->getName()]["투표"];
			}else if($p5 == []){
				$p5 = $this->db["투표"]["사람"][$players->getName()]["투표"];
			}else if($p6 == []){
				$p6 = $this->db["투표"]["사람"][$players->getName()]["투표"];
			}else if($p7 == []){
				$p7 = $this->db["투표"]["사람"][$players->getName()]["투표"];
			}else if($p8 == []){
				$p8 = $this->db["투표"]["사람"][$players->getName()]["투표"];
			}
        }
		$arr = [];
		if($p8 != []){
			$arr = array($p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8);
		}else if($p7 != []){
			$arr = array($p1,$p2,$p3,$p4,$p5,$p6,$p7);
		}else if($p6 != []){
			$arr = array($p1,$p2,$p3,$p4,$p5,$p6);
		}else if($p5 != []){
			$arr = array($p1,$p2,$p3,$p4,$p5);
		}else if($p4 != []){
			$arr = array($p1,$p2,$p3,$p4);
		}else if($p3 != []){
			$arr = array($p1,$p2,$p3);
		}
		else if($p2 != []){
			$arr = array($p1,$p2);
		}else if($p1!= []){
			$arr = array($p1);
		}
        $max = max($arr);
		if($max == 0){
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 투표가 무효처리되었습니다");
					
			}
				$this->night();
				return true;
		}
		foreach ($this->db["사람"] as $key => $type) {//////////////
			$players = $this->getServer()->getPlayer($key);
		foreach($this->db["투표"]["사람"][$players->getName()] as $tu => $value){///////////
		$this->db["투표"]["횟수"][$value][] = $players->getName();
		$this->save();
		}
		}
		if(count($this->db["투표"]["횟수"][$max]) >= 2){
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 투표가 무효처리되었습니다");
					
			}
			$this->night();
			return true;
			}
			$this->db["반론자"]["사람"] = $this->db["투표"]["횟수"][$max][0];
		$this->save();
		$this->banron();
		return true;
    }
    public function Mor()
    {
        if ($this->db["상태"] == "실행") {
            $this->getScheduler()->scheduleDelayedTask(new class($this) extends Task {
                public function __construct($th)
                {
                    $this->this = $th;
                }

                public function onRun(int $currentTick)
                {
                    if ($this->this->db["상태"] == "실행") {
                        $this->this->votetime();
                    }
                }
            }, 20 * 60);
			
			if(count($this->db["마피아팀"]["마피아"]) >= count($this->db["시민팀"]["시민들"])){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "마피아팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			if(count($this->db["마피아팀"]["마피아"]) == 0){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "시민팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
			$this->warph($players);
			}
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 아침이 되었습니다 §c60초 후에 투표가 시작됩니다");
					
			}
			$this->skill();
			$this->db["경찰사용"] = "없음";
            $this->db["시간"] = "아침";
            $this->save();
			return true;
        }
    }
	public function banron()
    {
        if ($this->db["상태"] == "실행") {
            $this->getScheduler()->scheduleDelayedTask(new class($this) extends Task {
                public function __construct($th)
                {
                    $this->this = $th;
                }

                public function onRun(int $currentTick)
                {
                    if ($this->this->db["상태"] == "실행") {
                        $this->this->bantop();
                    }
                }
            }, 20 * 15);
			if(count($this->db["마피아팀"]["마피아"]) >= count($this->db["시민팀"]["시민들"])){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "마피아팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			if(count($this->db["마피아팀"]["마피아"]) == 0){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "시민팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			$this->db["시간"] = "반론";
			foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
			$this->warpb($players);
			}
			$banronza = $this->getServer()->getPlayer($this->db["반론자"]["사람"]);
			$banronza->teleport(new Vector3($this->db["단두대"]["x"], $this->db["단두대"]["y"], $this->db["단두대"]["z"], $this->db["단두대"]["world"]));
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f {$this->db["반론자"]["사람"]}님의 최후의 반론! §c15초 후에 투표가 시작됩니다");
					
			}
            $this->save();
        }
    }
	public function bantop()
    {
        if ($this->db["상태"] == "실행") {
            $this->getScheduler()->scheduleDelayedTask(new class($this) extends Task {
                public function __construct($th)
                {
                    $this->this = $th;
                }

                public function onRun(int $currentTick)
                {
                    if ($this->this->db["상태"] == "실행") {
                        $this->this->ifbanron();
                    }
                }
            }, 20 * 15);
			if(count($this->db["마피아팀"]["마피아"]) >= count($this->db["시민팀"]["시민들"])){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "마피아팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			if(count($this->db["마피아팀"]["마피아"]) == 0){
				foreach ($this->db['사람'] as $key => $type) {
				$ppp = $this->getServer()->getPlayer($key);
				$ppp->addtitle("게임 끝", "시민팀의 승리");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				}
			$this->off();
			return true;
			}
			$this->db["시간"] = "반론투표";
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 반론자 투표 시간입니다 §c터치를 해서 투표를 진행해주세요");
					$players->sendMessage("§c〔 §f마피아 §c〕§c 15초뒤에 결과가 발표됩니다");
					
			}
			return true;
        }
    }
	public function ifbanron(){
		if($this->db["최후"]["찬성"] == $this->db["최후"]["불찬성"]){
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 무효처리되었습니다 §c찬성:{$this->db["최후"]["찬성"]} 불찬성:{$this->db["최후"]["불찬성"]}");
					
			}
			$this->night();
			return true;
		}else if($this->db["최후"]["찬성"] < $this->db["최후"]["불찬성"]){
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f 무효처리되었습니다 §c찬성:{$this->db["최후"]["찬성"]} 불찬성:{$this->db["최후"]["불찬성"]}");
					
			}
			$this->night();
			return true;
		}else if($this->db["최후"]["찬성"] > $this->db["최후"]["불찬성"]){
			foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f {$this->db["반론자"]["사람"]}님이 사형 당했습니다");
					
			}
			$ppp = $this->getServer()->getPlayer($this->db["반론자"]["사람"]);
			$this->db["플레이어"][$this->p[$ppp->getName()]["번호"]]["이름"] = [];
			$this->p[$ppp->getName()]["참가"] = "불참가";
			$this->p[$ppp->getName()]["직업"] = "없음";
			$this->p[$ppp->getName()]["번호"] = "없음";
			$this->p[$ppp->getName()]["최후"] = "없음";
				$ppp->sendMessage("§l§f사형당했습니다");
				$ppp->addtitle("사형", "당했습니다");
				$ppp->teleport(new Vector3($this->db["스폰"]["x"], $this->db["스폰"]["y"], $this->db["스폰"]["z"], $this->db["스폰"]["world"]));
				$this->db["인원"]--;
				if (isset($this->db["마피아팀"]["마피아"][$ppp->getName()])) {
					unset ($this->db["마피아팀"]["마피아"][$ppp->getName()]);
        }
				if (isset($this->db["시민팀"]["시민들"][$ppp->getName()])) {
					unset ($this->db["시민팀"]["시민들"][$ppp->getName()]);
        }
				if (isset($this->db["사람"][$ppp->getName()])) {
					unset ($this->db["사람"][$ppp->getName()]);
        }
		$this->night();
		//
		return true;
		}
		$this->save();
	}
    public function gameSt()
    {
        $this->db["상태"] = "실행";
        $this->save();
        $this->getServer()->broadcastMessage("§c〔 §f마피아 §c〕§f 마피아가 시작되었습니다.");
        foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
            $this->addJob($players);
            $this->addMember($players);
            $players->sendMessage("§l§f당신의 직업은" . $this->p[$players->getName()]["직업"] . "입니다");
            $players->addtitle("당신의 직업은", $this->p[$players->getName()]["직업"] . " 입니다");
        }
        $this->Night();
    }

    public function warp($player)
    {
        $member = $this->p[$player->getName()]["번호"];
        $player->teleport(new Vector3($this->db["플레이어"][$member]["x"], $this->db["플레이어"][$member]["y"], $this->db["플레이어"][$member]["z"], $this->db["플레이어"][$member]["world"]));
    }
	public function warph($player)
    {
        $player->teleport(new Vector3($this->db["회의"]["x"], $this->db["회의"]["y"], $this->db["회의"]["z"], $this->db["회의"]["world"]));
    }
	public function warpb($player)
    {
        $player->teleport(new Vector3($this->db["관중석"]["x"], $this->db["관중석"]["y"], $this->db["관중석"]["z"], $this->db["관중석"]["world"]));
    }

    public function addJob($player)
    {
        $name = $player->getName();
        $rand = mt_rand(0, count($this->JobList) - 1);
        $this->p[$name]["직업"] = $this->JobList[$rand];
        if ($this->JobList[$rand] == "마피아") {
            $this->db["마피아팀"]["마피아"][$player->getName()] = [];
            $this->save();
        } else {
            $this->db["시민팀"]["시민들"][$player->getName()] = [];
            $this->save();
        }
        unset($this->JobList[$rand]);
        $this->JobList = array_values($this->JobList);
        return true;

    }

    public function addMember($player)
    {
        $name = $player->getName();
        $rand1 = mt_rand(0, count($this->pp) - 1);
        $this->p[$name]["번호"] = $this->pp[$rand1];
        $this->db["플레이어"][$this->pp[$rand1]]["이름"] = $player->getName();
        $this->save();
        unset($this->pp[$rand1]);
        $this->pp = array_values($this->pp);
        return true;

    }

    public function onCommand(Commandsender $sender, Command $command, string $label, array $args): bool
    {
        if ($command->getName() === "마피아") {
            $name = $sender->getName();
            if (!$sender instanceof Player) {
                $sender->sendMessage("§c〔 §0마피아 §c〕§f 콘솔에서는 실행이 불가능합니다.");
                return true;
            }
            $this->sendUI($sender, 3411, $this->Mafia($name));
            return true;
        }
        if ($command->getName() === "마피아설정") {
            $name = $sender->getName();
            if (!$sender instanceof Player) {
                $sender->sendMessage("§c〔 §0마피아 §c〕§f 콘솔에서는 실행이 불가능합니다.");
                return true;
            }
            $this->sendUI($sender, 3322, $this->option($name));
            return true;
        }
    }

    public function sendUI(Player $p, $c, $d)
    {
        $pack = new ModalFormRequestPacket();
        $pack->formId = $c;
        $pack->formData = $d;
        $p->dataPacket($pack);
    }

    public function Mafia($name)
    {
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0마피아 §c〕§f ",
            "content" => "§c〔 §f마피아 UI입니다 §c〕\n§f선택해주세요 \n",
            "buttons" => [
                [
                    "text" => "§c〔 §0마피아 참가하기 §c〕",
                ],
                [
                    "text" => "§c〔 §0마피아 룰 §c〕",
                ],
            ]
        ];
        return json_encode($encode);

    }

    public function option($name)
    {
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0마피아OP §c〕§f ",
            "content" => "§c〔 §f마피아설정 OP UI입니다 §c〕\n§f선택해주세요 \n",
            "buttons" => [
                [
                    "text" => "§c〔 §0마피아 시작하기 §c〕",
                ],
                [
                    "text" => "§c〔 §0마피아 설정하기 §c〕",
                ],
                [
                    "text" => "§c〔 §0마피아 강제중지 §c〕",
                ],
            ]
        ];
        return json_encode($encode);

    }

    public function llist()
    {
        $arr = [];
        foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
            array_push($arr, array("text" => "- {$players->getName()} -\n- 마피아 플레이중 -"));
        }
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0마피아 플레이어 §c〕§f ",
            "content" => "§c〔 §f마피아설정 플레이어 UI입니다 §c〕\n§f플레이중인 사람들입니다\n",
            "buttons" => $arr
        ];
        return json_encode($encode);

    }

    public function police($name)
    {
		$this->plist = [];
        $arr = [];
        $index = 0;
        foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
            array_push($arr, array("text" => "- {$players->getName()} -\n- 수색 하기 -"));
            $this->plist[$name][$index] = $players->getName();
            $index++;
        }
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0수색 하기 §c〕§f ",
            "content" => "§c〔 §f수색 UI입니다 §c〕\n§f선택해주세요\n",
            "buttons" => $arr
        ];
        return json_encode($encode);

    }

    public function doctor($name)
    {
		$this->dlist = [];
        $arr = [];
        $index = 0;
        foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
            array_push($arr, array("text" => "- {$players->getName()} -\n- 치료 하기 -"));
            $this->dlist[$name][$index] = $players->getName();
            $index++;
        }
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0치료 하기 §c〕§f ",
            "content" => "§c〔 §f치료 UI입니다 §c〕\n§f선택해주세요\n",
            "buttons" => $arr
        ];
        return json_encode($encode);

    }

    public function mafiasun($name)
    {
		$this->mlist = [];
        $mafia = [];
        foreach ($this->db["마피아팀"]["마피아"] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
            $mafia = $players->getName();
        }
        $arr = [];
        $index = 0;

        foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
            array_push($arr, array("text" => "- {$players->getName()} -\n- 살해 하기 -"));
            $this->mlist[$name][$index] = $players->getName();
            $index++;
        }
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0살해 하기 §c〕§f ",
            "content" => "§c〔 §f살해 UI입니다 §c〕\n§f선택해주세요\n§c마피아: " . $mafia,
            "buttons" => $arr
        ];
        return json_encode($encode);

    }

    public function report($name)
    {
		$this->rlist = [];
        $arr = [];
        $index = 0;
        foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
            array_push($arr, array("text" => "- {$players->getName()} -\n- 직업 알리기 -"));
            $this->rlist[$name][$index] = $players->getName();
            $index++;
        }
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0직업 알리기 §c〕§f ",
            "content" => "§c〔 §f직업 보는 UI입니다 §c〕\n§f선택해주세요\n",
            "buttons" => $arr
        ];
        return json_encode($encode);

    }

    public function setting1($name)
    {
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0마피아설정 §c〕§f ",
            "content" => "§c〔 §f마피아설정 UI입니다 §c〕\n§f선택해주세요 \n",
            "buttons" => [
                [
                    "text" => "§c〔 §0대기실 위치 설정 §c〕",
                ],
                [
                    "text" => "§c〔 §0플레이어 집 위치 설정 §c〕",
                ],
                [
                    "text" => "§c〔 §0단두대 위치 설정 §c〕",
                ],
                [
                    "text" => "§c〔 §0관중석 설정 §c〕",
                ],
				[
                    "text" => "§c〔 §0스폰 설정 §c〕",
                ],
				[
                    "text" => "§c〔 §0회의 설정 §c〕",
                ],
            ]
        ];
        return json_encode($encode);

    }

    public function setting2()
    {
        $encode = [
            "type" => "custom_form",
            "title" => "§c〔 §0마피아 플레이어 집 설정 §c〕§f ",
            "content" => [
                [
                    "type" => "dropdown",
                    "text" => "§c〔 §f마피아 플레이어 집 설정 UI입니다 §c〕\n§f선택해주세요 \n\n\n\n\n.",
                    "options" => array("플레이어1", "플레이어2", "플레이어3", "플레이어4", "플레이어5", "플레이어6", "플레이어7", "플레이어8")
                ]
            ]
        ];
        return json_encode($encode);

    }

    public function ruel($c)
    {

        $encode = [
            "type" => "form",
            "title" => "§c〔 §0룰 §c〕",
            "content" => $c,
            "buttons" => [
                [
                    "text" => "§l메인",
                ],
                [
                    "text" => "§l나가기",
                ]
            ]
        ];
        return json_encode($encode);
    }

    public function msg($c)
    {

        $encode = [
            "type" => "form",
            "title" => "§c〔 §0메세지 §c〕",
            "content" => $c,
            "buttons" => [
                [
                    "text" => "§l나가기",
                ]
            ]
        ];
        return json_encode($encode);
    }
	
	public function vote($name)
    {
        $arr = [];
        $index = 0;
        foreach ($this->db['사람'] as $key => $type) {
            $players = $this->getServer()->getPlayer($key);
            array_push($arr, array("text" => "- {$players->getName()} -\n- 투표 하기 -"));
            $this->vlist[$name][$index] = $players->getName();
            $index++;
        }
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0투표 §c〕§f ",
            "content" => "§c〔 §f투표 UI입니다 §c〕\n§f선택해주세요\n",
            "buttons" => $arr
        ];
        return json_encode($encode);

    }
	
	public function bvote($name)
    {
        $encode = [
            "type" => "form",
            "title" => "§c〔 §0마피아 §c〕§f ",
            "content" => "§c〔 §f최후의 투표 UI입니다 §c〕\n§f선택해주세요 \n",
            "buttons" => [
                [
                    "text" => "§c〔 §0죽인다 §c〕",
                ],
                [
                    "text" => "§c〔 §0죽이지 않는다 §c〕",
                ],
            ]
        ];
        return json_encode($encode);

    }

    public function onDataPacketRecieve(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        $name = $player->getName();
        if ($packet instanceof ModalFormResponsePacket) {
            $id = $packet->formId;
            $a = json_decode($packet->formData, true);
            if ($id === 3411) {//마피아메인
                if ($a === 0) {
                    $option = $this->db["상태"];
                    if ($option == "실행") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 이미 게임이 진행중 입니다");
                        return true;
                    }
                    if ($this->db["인원"] == 8) {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 인원이 많습니다");
                        return true;
                    }
                    if ($option == "중지") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 게임이 진행중이지 않습니다");
                        return true;
                    }
                    if ($option == "참가") {
                        if ($this->p[$name]["참가"] == "참가") {
                            $player->sendMessage("§c〔 §f마피아 §c〕§f 이미 참여해 있습니다");
                            return true;
                        }
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 참가되었습니다");
                        $this->p[$name]["참가"] = "참가";
                        $this->db["인원"]++;
                        $this->db["사람"][$name] = [];
                        $player->teleport(new Vector3($this->db["대기실"]["x"], $this->db["대기실"]["y"], $this->db["대기실"]["z"], $this->db["대기실"]["world"]));
                        $this->save();
                        $this->getServer()->broadcastMessage("§c〔 §f마피아 §c〕§f " . $name . "님이 마피아 게임에 참가하셨습니다. §c" . $this->db["인원"] . "/§c8");

                        if ($this->db["인원"] == /**/8) {
                            $this->Coolt();
                        }

                        return true;
                    }
                }
                if ($a === 1) {
                    $this->sendUI($player, 3999, $this->ruel("1. 마피아 게임이 시작되면 경찰,마피아,의사,군인,기자,시민,시민 직업이 랜덤으로 뽑힙니다 \n2. 게임의 ‘밤’ 턴에 마피아들은 사람 한명을 골라 살해한다. \n3. 게임의 ‘밤’ 턴에 의사는 사람 한명을 골라 치료한다.\n4. 게임의 ‘밤’ 턴에 경찰은 사람 한명을 골라 조사한다.\n5. 게임의 ‘밤’ 턴에 군인은 마피아의 공격을 방어하고 마피아를 알수있다. \n6. 기자는 밤에 한 명을 선택하여 취재해 다음 날 그 사람의 직업을 모두에게 공개한다. (1회용) \n7. 시민은 아무것도못한다. \n8. 마피아팀과 시민팀의 수가 같아질때 마피아팀이 승리한다 \n\n다같이 매너를 지켜요^^"));

                }
            }
            if ($id === 3322) { //시작
                if ($a === 0) {
                    if ($this->db["대기실"]["x"] == "0" and $this->db["대기실"]["y"] == "0" and $this->db["대기실"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 대기실 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["단두대"]["x"] == "0" and $this->db["단두대"]["y"] == "0" and $this->db["단두대"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 단두대 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["관중석"]["x"] == "0" and $this->db["관중석"]["y"] == "0" and $this->db["관중석"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 관중석 구경대 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["플레이어"]["플레이어1"]["x"] == "0" and $this->db["플레이어"]["플레이어1"]["y"] == "0" and $this->db["플레이어"]["플레이어1"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 플레이어1 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["플레이어"]["플레이어2"]["x"] == "0" and $this->db["플레이어"]["플레이어2"]["y"] == "0" and $this->db["플레이어"]["플레이어2"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 플레이어2 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["플레이어"]["플레이어3"]["x"] == "0" and $this->db["플레이어"]["플레이어3"]["y"] == "0" and $this->db["플레이어"]["플레이어3"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 플레이어3 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["플레이어"]["플레이어4"]["x"] == "0" and $this->db["플레이어"]["플레이어4"]["y"] == "0" and $this->db["플레이어"]["플레이어4"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 플레이어4 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["플레이어"]["플레이어5"]["x"] == "0" and $this->db["플레이어"]["플레이어5"]["y"] == "0" and $this->db["플레이어"]["플레이어5"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 플레이어5 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["플레이어"]["플레이어6"]["x"] == "0" and $this->db["플레이어"]["플레이어6"]["y"] == "0" and $this->db["플레이어"]["플레이어6"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 플레이어6 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["플레이어"]["플레이어7"]["x"] == "0" and $this->db["플레이어"]["플레이어7"]["y"] == "0" and $this->db["플레이어"]["플레이어7"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 플레이어7 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    if ($this->db["플레이어"]["플레이어8"]["x"] == "0" and $this->db["플레이어"]["플레이어8"]["y"] == "0" and $this->db["플레이어"]["플레이어8"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 플레이어8 위치가 설정되지 않았습니다.");
                        return true;
                    }
					if ($this->db["회의"]["x"] == "0" and $this->db["회의"]["y"] == "0" and $this->db["회의"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 회의 위치가 설정되지 않았습니다.");
                        return true;
                    }
					if ($this->db["스폰"]["x"] == "0" and $this->db["스폰"]["y"] == "0" and $this->db["스폰"]["z"] == "0") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 스폰 위치가 설정되지 않았습니다.");
                        return true;
                    }
                    //
                    if ($this->db["상태"] == "실행" or $this->db["상태"] == "참가") {
                        $player->sendMessage("§c〔 §f마피아 §c〕§f 이미 실행중이거나 대기중 입니다");
                        return true;
                    }


                    $this->getServer()->broadcastMessage("§c〔 §f마피아 §c〕§f 마피아가 시작되었습니다 §c/마피아§f명령어로 참가하세요! §c8명이 모이면 자동으로 시작됩니다");
                    $this->db["상태"] = "참가";
                    return true;
                }
                if ($a === 1) {
                    $this->sendUI($player, 314, $this->setting1($name));
                }
                if ($a === 2) {
                    $this->getServer()->broadcastMessage("§c〔 §f마피아 §c〕§f 강제중지 하였습니다");
                    $this->off();
                    return true;
                }
            }
            if ($id === 3999) {//룰
                if ($a === 0) {
                    $this->sendUI($player, 3411, $this->Mafia($name));
                }
            }
            if ($id === 314) { //설정메인
                if ($a === 0) {
                    $x = $player->x;
                    $y = $player->y;
                    $z = $player->z;
					$level = $player->getLevel ()->getFolderName ();
					$this->db["대기실"]["world"] = $level;
                    $this->db["대기실"]["x"] = $x;
                    $this->db["대기실"]["y"] = $y;
                    $this->db["대기실"]["z"] = $z;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 대기실 위치가 설정되었습니다 §c" . $this->db["대기실"]["x"] . " " . $this->db["대기실"]["y"] . " " . $this->db["대기실"]["z"]);
                    return true;
                }
                if ($a === 1) {
                    $this->sendUI($player, 134134, $this->setting2());
                }
                if ($a === 2) {
                    $x = $player->x;
                    $y = $player->y;
                    $z = $player->z;
					$level = $player->getLevel ()->getFolderName ();
					$this->db["단두대"]["world"] = $level;
                    $this->db["단두대"]["x"] = $x;
                    $this->db["단두대"]["y"] = $y;
                    $this->db["단두대"]["z"] = $z;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 단두대 위치가 설정되었습니다 §c" . $this->db["단두대"]["x"] . " " . $this->db["단두대"]["y"] . " " . $this->db["단두대"]["z"]);
                    return true;
                }
                if ($a === 3) {
                    $x = $player->x;
                    $y = $player->y;
                    $z = $player->z;
					$level = $player->getLevel ()->getFolderName ();
					$this->db["관중석"]["world"] = $level;
                    $this->db["관중석"]["x"] = $x;
                    $this->db["관중석"]["y"] = $y;
                    $this->db["관중석"]["z"] = $z;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 관중석 위치가 설정되었습니다 §c" . $this->db["관중석"]["x"] . " " . $this->db["관중석"]["y"] . " " . $this->db["관중석"]["z"]);
                    return true;
                }
				if ($a === 4) {
                    $x = $player->x;
                    $y = $player->y;
                    $z = $player->z;
					$level = $player->getLevel ()->getFolderName ();
					$this->db["스폰"]["world"] = $level;
                    $this->db["스폰"]["x"] = $x;
                    $this->db["스폰"]["y"] = $y;
                    $this->db["스폰"]["z"] = $z;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 스폰 위치가 설정되었습니다 §c" . $this->db["관중석"]["x"] . " " . $this->db["관중석"]["y"] . " " . $this->db["관중석"]["z"]);
                    return true;
                }
				if ($a === 5) {
                    $x = $player->x;
                    $y = $player->y;
                    $z = $player->z;
					$level = $player->getLevel ()->getFolderName ();
					$this->db["회의"]["world"] = $level;
                    $this->db["회의"]["x"] = $x;
                    $this->db["회의"]["y"] = $y;
                    $this->db["회의"]["z"] = $z;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 회의 위치가 설정되었습니다 §c" . $this->db["관중석"]["x"] . " " . $this->db["관중석"]["y"] . " " . $this->db["관중석"]["z"]);
                    return true;
                }
            }
            if ($id === 134134) { //집설정
                $x = $player->x;
                $y = $player->y;
                $z = $player->z;
				$level = $player->getLevel ()->getFolderName ();
                if ($a[0] === 0) {
                    $this->db["플레이어"]["플레이어1"]["x"] = $x;
                    $this->db["플레이어"]["플레이어1"]["y"] = $y;
                    $this->db["플레이어"]["플레이어1"]["z"] = $z;
					$this->db["플레이어"]["플레이어1"]["world"] = $level;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 집 위치가 설정되었습니다 §c" . $x . " " . $y . " " . $z);
                    return true;
                }
                if ($a[0] === 1) {
                    $this->db["플레이어"]["플레이어2"]["x"] = $x;
                    $this->db["플레이어"]["플레이어2"]["y"] = $y;
                    $this->db["플레이어"]["플레이어2"]["z"] = $z;
					$this->db["플레이어"]["플레이어2"]["world"] = $level;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 집 위치가 설정되었습니다 §c" . $x . " " . $y . " " . $z);
                    return true;
                }
                if ($a[0] === 2) {
                    $this->db["플레이어"]["플레이어3"]["x"] = $x;
                    $this->db["플레이어"]["플레이어3"]["y"] = $y;
                    $this->db["플레이어"]["플레이어3"]["z"] = $z;
					$this->db["플레이어"]["플레이어3"]["world"] = $level;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 집 위치가 설정되었습니다 §c" . $x . " " . $y . " " . $z);
                    return true;
                }
                if ($a[0] === 3) {
                    $this->db["플레이어"]["플레이어4"]["x"] = $x;
                    $this->db["플레이어"]["플레이어4"]["y"] = $y;
                    $this->db["플레이어"]["플레이어4"]["z"] = $z;
					$this->db["플레이어"]["플레이어4"]["world"] = $level;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 집 위치가 설정되었습니다 §c" . $x . " " . $y . " " . $z);
                    return true;
                }
                if ($a[0] === 4) {
                    $this->db["플레이어"]["플레이어5"]["x"] = $x;
                    $this->db["플레이어"]["플레이어5"]["y"] = $y;
                    $this->db["플레이어"]["플레이어5"]["z"] = $z;
					$this->db["플레이어"]["플레이어5"]["world"] = $level;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 집 위치가 설정되었습니다 §c" . $x . " " . $y . " " . $z);
                    return true;
                }
                if ($a[0] === 5) {
                    $this->db["플레이어"]["플레이어6"]["x"] = $x;
                    $this->db["플레이어"]["플레이어6"]["y"] = $y;
                    $this->db["플레이어"]["플레이어6"]["z"] = $z;
					$this->db["플레이어"]["플레이어6"]["world"] = $level;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 집 위치가 설정되었습니다 §c" . $x . " " . $y . " " . $z);
                    return true;
                }
                if ($a[0] === 6) {
                    $this->db["플레이어"]["플레이어7"]["x"] = $x;
                    $this->db["플레이어"]["플레이어7"]["y"] = $y;
                    $this->db["플레이어"]["플레이어7"]["z"] = $z;
					$this->db["플레이어"]["플레이어7"]["world"] = $level;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 집 위치가 설정되었습니다 §c" . $x . " " . $y . " " . $z);
                    return true;
                }
                if ($a[0] === 7) {
                    $this->db["플레이어"]["플레이어8"]["x"] = $x;
                    $this->db["플레이어"]["플레이어8"]["y"] = $y;
                    $this->db["플레이어"]["플레이어8"]["z"] = $z;
					$this->db["플레이어"]["플레이어8"]["world"] = $level;
                    $this->save();
                    $player->sendMessage("§c〔 §f마피아 §c〕§f 집 위치가 설정되었습니다 §c" . $x . " " . $y . " " . $z);
                    return true;
                }
            }
            if ($id === 51237) {//의사
                if (is_null($a)) return true;
                if (isset($this->dlist[$name][$a])) {
                    $this->db["의사선택"] = $this->dlist[$name][$a];
                    $this->save();
                }
            }
			if ($id === 51235) {//기자
                if (is_null($a)) return true;
				if($this->db["기자갯수"] == 0){
					$player->sendMessage("§c〔 §f마피아 §c〕§f 이미 한번 사용했습니다");
					return true;
				}
                if (isset($this->rlist[$name][$a])) {
                    $this->db["기자선택"]["사람"] = $this->rlist[$name][$a];
					$player->sendMessage("§c〔 §f마피아 §c〕§f " . $this->rlist[$name][$a] . "님을 선택했습니다");
                    $this->save();
                }
            }
            if ($id === 51236) {//마피아
                if (is_null($a)) return true;
                if (isset($this->mlist[$name][$a])) {
                    $this->db["마피아팀"]["선택한사람"] = $this->mlist[$name][$a];
					$player->sendMessage("§c〔 §f마피아 §c〕§f " . $this->mlist[$name][$a] . "님을 선택했습니다");
                    $this->save();
                }
            }
            if ($id === 51234) {//경찰
                if (is_null($a)) return true;
				if($this->db["경찰사용"] == "사용"){
					$player->sendMessage("§c〔 §f마피아 §c〕§f 이미 조사를 했습니다");
					return true;
				}
				$this->db["경찰사용"] = "사용";
				$this->save();
                if ($this->p[$this->plist[$name][$a]]["직업"] == "마피아") {
                    $this->sendUI($player, 1341341, $this->msg("조사결과 : 마피아입니다"));
                } else {
                    $this->sendUI($player, 1341341, $this->msg("조사결과 : 마피아가 아닙니다"));
                }
            }
			if ($id === 4123) {//투표
                if (is_null($a)) return true;
				if ($this->p[$name]["투표"] == "함"){
					$player->sendMessage("§c〔 §f마피아 §c〕§f 이미 투표를 했습니다");
					return true;
				}
                if (isset($this->vlist[$name][$a])) {
					$this->p[$name]["투표"] = "함";
                    $this->db["투표"]["사람"][$this->vlist[$name][$a]]["투표"] += 1;
					$player->sendMessage("§c〔 §f마피아 §c〕§f {$this->vlist[$name][$a]} 님을 선택했습니다");
					foreach ($this->db["사람"] as $key => $type) {
					$players = $this->getServer()->getPlayer($key);
					$players->sendMessage("§c〔 §f마피아 §c〕§f {$this->vlist[$name][$a]}님 한표!");
					}
                    $this->save();
					return true;
                }
            }
			if($id === 44344){
				if($this->p[$name]["최후"] == "함"){
					$player->sendMessage("§c〔 §f마피아 §c〕§f 이미 선택했습니다");
					return true;
				}
				$this->p[$name]["최후"] = "함";
				if($a === 0){
					$player->sendMessage("§c〔 §f마피아 §c〕§f 죽이기를 선택하셨습니다");
					$this->db["최후"]["찬성"]++;
					$this->save();
					return true;
				}else if($a===1){
					$player->sendMessage("§c〔 §f마피아 §c〕§f 죽이기를 선택하셨습니다");
					$this->db["최후"]["불찬성"]++;
					return true;
				}
			}
        }//packet 끝
    }
}
