<?php
namespace MJob;

use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\item\Item;

use pocketmine\entity\Arrow;

use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;


use pocketmine\network\mcpe\protocol\ModalFormRequestPacket; 
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use pocketmine\level\Position;
use pocketmine\level\Explosion;
use pocketmine\level\particle\HeartParticle;

class MJob extends PluginBase implements Listener {

private $Cool1= [];
private $Cool2= [];
private $Cool3= [];

public $times = [], $time = [];

public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents ($this, $this);
		@mkdir ( $this->getDataFolder () );
      $this->job = new Config($this->getDataFolder() . "job.yml", Config::YAML);
      $this->data = $this->job->getAll();
      $this->level = new Config($this->getDataFolder() . "level.yml", Config::YAML);
      $this->lv = $this->level->getAll();
      		$this->setting = new Config($this->getDataFolder()."setting.yml", Config::YAML, [
			"skill-enable-world" => "world",
			"leaving-item" => "370:0",
			"skill-1-item" => "369:0",
			"skill-2-item" => "369:1",
			"skill-3-item" => "369:2"
		]);
		$this->db ["setting"] = $this->setting->getAll();
}

public function onJoin(PlayerJoinEvent $event){
	$player = $event->getPlayer();
	$name = $player->getName();
	if(!isset($this->data[$name])){
		$this->data[$name] = "직업없음";
		$this->save();
		}
		
	if(!isset($this->lv[$name])){
		$this->lv[$name] = 1;
		$this->save();
		}
	}




public function onTouch(PlayerInteractEvent $event){
	$player = $event->getPlayer();
$name = $player->getName();
$item = $event->getItem();
$lev = $this->lv[$name];
$jn = $this->data[$name];
$pf="§e[ §f직업 §e] §f";
$code = $item->getId().":".$item->getDamage();

if ($player->getLevel()->getFolderName() == $this->db ["setting"] ["skill-enable-world"])
{
	if ($code == $this->db ["setting"] ["skill-1-item"]) {
		
		
		if($jn=="전사"){
			
			if( ! isset( $this->Cool1 [$name] ) ){
					$this->Cool1 [$name] = $this->getTime();
				}
				else if( $this->getTime() - $this->Cool1 [$name] > 10 ){
    $this->Cool1 [$name] = $this->getTime();	
    $player->sendMessage($pf."'의지' 를 사용하셨습니다");
    $this->addEffect($player, 5, $lev-1, 5);//힘
	$this->addEffect($player, 11, $lev-1, 5);//저항
	break;
    }
    else{
					$time = 10 - ($this->getTime() - $this->Cool1 [$name]);			
					$player->addTitle("쿨타임 : ".$time."초 남음");
					return;
				}
    
    	}else if($jn=="궁수"){
    	
    
    	if( ! isset( $this->Cool1 [$name] ) ){
					$this->Cool1 [$name] = $this->getTime();
				}
				else if( $this->getTime() - $this->Cool1 [$name] > 15 ){
    $this->Cool1 [$name] = $this->getTime();	
    $player->sendMessage($pf."'화살 폭탄' 을 사용하셨습니다");
    
    
	break;
    }
    else{
					$time = 15 - ($this->getTime() - $this->Cool1 [$name]);			
					$player->addTitle("쿨타임 : ".$time."초 남음");
					return;
				}
    	}else if($jn=="도적"){
    	
    if( ! isset( $this->Cool1 [$name] ) ){
					$this->Cool1 [$name] = $this->getTime();
				}
				else if( $this->getTime() - $this->Cool1 [$name] > 10 ){
    $this->Cool1 [$name] = $this->getTime();	
    $player->sendMessage($pf."'유체화' 를 사용하셨습니다");
    $this->addEffect($player, 14, $lev-1, 5);
	$this->addEffect($player, 1, $lev-1, 5);
	break;
    }
    else{
					$time = 10 - ($this->getTime() - $this->Cool1 [$name]);			
					$player->addTitle("쿨타임 : ".$time."초 남음");
					return;
				}
    
    
    	}else if($jn=="마법사"){
    
if( ! isset( $this->Cool1 [$name] ) ){
					$this->Cool1 [$name] = $this->getTime();
				}
				else if( $this->getTime() - $this->Cool1 [$name] > 20 ){
    $this->Cool1 [$name] = $this->getTime();	
    $player->sendMessage($pf."'벼락치기' 를 사용하셨습니다");
    
    $count = 0;
		foreach ($player->getLevel()->getPlayers() as $players){
		if($player->distance($players) <= 5 && $players !== $player){
		$this->addLighting($players, $players->x, $players->z);
		$count++;
		if($count === $lev){
		return true;
							}
						}
					}
					break;
    
	break;
    }
    else{
					$time = 20 - ($this->getTime() - $this->Cool1 [$name]);			
					$player->addTitle("쿨타임 : ".$time."초 남음");
					return;
				}	
				}
			}
		}
		
	if ($code == $this->db ["setting"] ["skill-2-item"]) {
		
		if($lev>=15){
			
			if($jn=="전사"){
			if( ! isset( $this->Cool2 [$name] ) ){
					$this->Cool2 [$name] = $this->getTime();
				}
				else if( $this->getTime() - $this->Cool2 [$name] > 15 ){
    $this->Cool2 [$name] = $this->getTime();	
    $player->sendMessage($pf."'번지점프' 를 사용하셨습니다");
    
    foreach ($player->getLevel()->getPlayers() as $Players){
				if($player->distance($Players) <= 5){
					$this->addEffect($player, 11, $lev, 5);
				$Players->attack(new EntityDamageEvent($event->getPlayer(), EntityDamageEvent::CAUSE_MAGIC, 10));
				$Players->setMotion (new Vector3(0,2, 0));
				}
				}
				
    
	break;
    }
    else{
					$time = 15 - ($this->getTime() - $this->Cool2 [$name]);			
					$player->addTitle("쿨타임 : ".$time."초 남음");
					return;
				}
			}
			else if($jn=="궁수"){
			if( ! isset( $this->Cool2 [$name] ) ){
					$this->Cool2 [$name] = $this->getTime();
				}
				else if( $this->getTime() - $this->Cool2 [$name] > 20 ){
    $this->Cool2 [$name] = $this->getTime();

	
    $player->sendMessage($pf."'화살디펜서' 를 사용하셨습니다");
    
	
				
    
	break;
    }
    else{
					$time = 15 - ($this->getTime() - $this->Cool2 [$name]);			
					$player->addTitle("쿨타임 : ".$time."초 남음");
					return;
				}
			}else if($jn=="도적"){
			if( ! isset( $this->Cool2 [$name] ) ){
					$this->Cool2 [$name] = $this->getTime();
				}
				else if( $this->getTime() - $this->Cool2 [$name] > 10 ){
    $this->Cool2 [$name] = $this->getTime();	
    $player->sendMessage($pf."'바람타기' 를 사용하셨습니다");
    
	$a = $player->getDirectionVector()->multiply(4);
        $x = $a->getX();
        $y = $a->getY();
        $z = $a->getZ();
        $player->setMotion(new Vector3($x, $y, $z));
				
    
	break;
    }
    else{
					$time = 10 - ($this->getTime() - $this->Cool2 [$name]);			
					$player->addTitle("쿨타임 : ".$time."초 남음");
					return;
				}
			}else if($jn=="마법사"){
			if( ! isset( $this->Cool2 [$name] ) ){
					$this->Cool2 [$name] = $this->getTime();
				}
				else if( $this->getTime() - $this->Cool2 [$name] > 15 ){
    $this->Cool2 [$name] = $this->getTime();	
    $player->sendMessage($pf."'자힐' 을 사용하셨습니다");
    
	$this->addEffect($player, 10, 10, 10);
				
    
	break;
    }
    else{
					$time = 15 - ($this->getTime() - $this->Cool2 [$name]);			
					$player->addTitle("쿨타임 : ".$time."초 남음");
					return;
				}
			}
			
			}
		
	
	
	}else{
		$player->sendMessage($pf."§c이 월드에선 사용하실 수 없습니다 §r§o§7/직업월드 설정 [월드이름]");
		}
	}


public function onCommand(Commandsender $sender, Command $command, string $label, array $args) : bool{
		if ($command->getName() === "직업") {
			$name = $sender->getName();
			if(!$sender instanceof Player) {
        $sender->sendMessage ("§c§l콘솔에서는 실행이 불가능합니다." );
        return true;
        }
        $this->sendUI($sender, 2000, $this->OpenUiJM($name));
        return true;
    }
    
    
    
    
        if ($command == "직업월드") { 
      if (! isset($args[0])) {
        $sender->sendMessage("사용법 : /직업월드 설정 (월드이름)");
        return true;
      }
      switch ($args[0]) {
        case "설정":
        if (! $sender->isOp()){
           $sender->sendMessage("§4권한이 없습니다");
          return true;
          }
        if (! isset ( $args [1] )) {
          $sender->sendMessage("사용법 : §f/직업월드 설정 (월드이름)");
          return true;
        }
        switch ($args[1]) {
          case $args[1]:
          
          $this->db ["setting"] ["skill-enable-world"] = $args[1];
          $this->save();
          $sender->sendMessage("§l§o§b[§f ! §b] §7사용 가능월드가§e ". $args[1] . " §7로 바뀌었습니다");
          
          }
          }
          }
		  if ($command == "스킬템") { 
      if (! isset($args[0])) {
        $sender->sendMessage("사용법 : /스킬템 1 (코드)");
		$sender->sendMessage("사용법 : /스킬템 2 (코드)");
        return true;
      }
      switch ($args[0]) {
        case "1":
        if (! $sender->isOp()){
           $sender->sendMessage("§4권한이 없습니다");
          return true;
          }
        if (! isset ( $args [1] )) {
          $sender->sendMessage("사용법 : /스킬템 1 (코드)");
          return true;
        }
        switch ($args[1]) {
          case $args[1]:
          
          $this->db ["setting"] ["skill-1-item"] = $args[1];
          $this->save();
          $sender->sendMessage("§l§o§b[§f ! §b] §7첫번째스킬템이§e ". $args[1] . " §7로 바뀌었습니다");
          
          }
		  case "2":
        if (! $sender->isOp()){
           $sender->sendMessage("§4권한이 없습니다");
          return true;
          }
        if (! isset ( $args [1] )) {
          $sender->sendMessage("사용법 : /스킬템 2 (코드)");
          return true;
        }
        switch ($args[1]) {
          case $args[1]:
          
          $this->db ["setting"] ["skill-2-item"] = $args[1];
          $this->save();
          $sender->sendMessage("§l§o§b[§f ! §b] §7두번째스킬템이§e ". $args[1] . " §7로 바뀌었습니다");
          
          }
		  
          }
          }
          }

public function sendUI(Player $p, $c, $d) {
		$pack = new ModalFormRequestPacket();
		$pack->formId = $c;
		$pack->formData = $d;
		$p->dataPacket($pack);
	}

public function OpenUiJM($name) {
	$jn = $this->data[$name];
	$lev = $this->lv[$name];
			$encode = [
		"type" => "form",
		"title" => "§l§a직업",
		"content" => "§l<< §f직업 메뉴입니다 >>\n\nVersion 1.1.25.3\n\n직업: ".$jn."\n\n전직 레벨: ".$lev,
		"buttons" => [
		[
		"text" => "§l§a[ §f직업 선택하기§a ]",
		],
		[
		"text" => "§l§a[ §f전직 메뉴 열기§a ]",
		],
		[
		"text" => "§l§a[ §f직업 정보 보기§a ]",
		],
		[
		"text" => "§l나가기",
		]
		]
		];
		return json_encode($encode);
			
	}
	
public function OpenUi2() {
		
         $encode = [
		"type" => "form",
		"title" => "§l§a직업 선택",
		"content" => "§l<< 직업 선택 >>",
		"buttons" => [
		[
		"text" => "§l§b[ §f전사 §b]",
		],
		[
		"text" => "§l§b[ §f궁수§b ]",
		],
		[
		"text" => "§l§b[ §f마법사§b ]",
		],
		[
		"text" => "§l§b[ §f도적§b ]",
		],
		[
		"text" => "§l돌아가기",
		],
		[
		"text" => "§l나가기",
		]
		]
		];
		return json_encode($encode);
	}

public function OpenUi3($name) {
		$jn = $this->data[$name];
		$lev = $this->lv[$name];
		$pe = $lev;
		$ja = $pe*3;
         $encode = [
		"type" => "form",
		"title" => "§l§a전직",
		"content" => "§l<< 직업 전직 >>\n\n§c※ 직업 전직에는 전직권 ".$ja."개가 있어야 합니다.\n\n§f나의 직업: ".$jn."\n\n전직 레벨: ".$lev."\n\n",
		"buttons" => [
		[
		"text" => "§l§b[ §f전직하기 §b]",
		],
		[
		"text" => "§l돌아가기",
		],
		[
		"text" => "§l나가기",
		]
		]
		];
		return json_encode($encode);
	}
	
public function OpenUi4($name) {
		 $job = $this->data[$name];
		 $lev = $this->lv[$name];
         $encode = [
		"type" => "form",
		"title" => "§l§a정보",
		"content" => "§l<< 직업 정보 >>\n\n§b당신의 직업: ".$job,
		"buttons" => [
		[
		"text" => "§l§b[ §f스킬보기 §b]",
		],
		[
		"text" => "§l나가기",
		]
		]
		];
		return json_encode($encode);
	}
	
public function OpenUiM($c) {
		
         $encode = [
		"type" => "form",
		"title" => "§l§c메세지",
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

public function onDataPacketRecieve(DataPacketReceiveEvent $event) {
		$packet = $event->getPacket();
		$player = $event->getPlayer();
		$name = $player->getName();
		if ($packet instanceof ModalFormResponsePacket) {
			$id = $packet->formId;
			$a = json_decode($packet->formData, true);
			if ($id === 2000) {
				if($a === 3){
					$player->sendMessage("나왔습니다");
                   }
                 else if($a === 0){//직업 선택
                 	$this->sendUI($player, 2001, $this->OpenUi2());
                 }
                 else if($a === 1){//직업 전직
                 	$this->sendUI($player, 2003, $this->OpenUi3($name));
                 }
                 else if($a === 2){//직업 정보
                 	$this->sendUI($player, 2004, $this->OpenUi4($name));
                 }
                else {
              	$this->sendUI($player, 2005, $this->OpenUiM("§l§c오피만 가능합니다"));
               }
          }
				}//1끝	
			     else if ($id === 2001) {
				  if($a === 4){
                 	$this->sendUI($player, 2000, $this->OpenUiJM($name));
                 }
                 else if($a === 5){
					$player->sendMessage("나왔습니다");
                   }
                 else if($a === 0){
                 	if($this->data[$name] == "직업없음"){
                    $this->addJob($name,"전사");
				    $this->heart($player);
                    $this->sendUI($player, 2005, $this->OpenUiM("§l§a성공적으로 직업을 선택했습니다."));
                    return;
                    }
                    else {
                    	$this->sendUI($player, 2005, $this->OpenUiM("§l§c당신은 이미 직업이 있습니다"));
                    }
                 }
                 else if($a === 1){
                 	if($this->data[$name] == "직업없음"){
				 $this->heart($player);
                 $this->sendUI($player, 2005, $this->OpenUiM("§l§a성공적으로 직업을 선택했습니다."));
                 return;
                 }
                 else{
                 	$this->sendUI($player, 2005, $this->OpenUiM("§l§c당신은 이미 직업이 있습니다"));
                    }
                 }
                 else if($a === 2){
                 	if($this->data[$name] == "직업없음"){
                 	$this->addJob($name,"마법사");
                 $this->sendUI($player, 2005, $this->OpenUiM("§l§a성공적으로 직업을 선택했습니다."));
                 return;
                 }
                 else{
                 	$this->sendUI($player, 2005, $this->OpenUiM("§l§c당신은 이미 직업이 있습니다"));
                     }
                 }
                 else if($a === 3){
                 	if($this->data[$name] == "직업없음"){
                 	$this->addJob($name,"도적");
					$this->sendUI($player, 2005, $this->OpenUiM("§l§a성공적으로 직업을 선택했습니다."));
                 return;
                 }
                 else{
                        $this->sendUI($player, 2005, $this->OpenUiM("§l§c당신은 이미 직업이 있습니다"));
                    }
                 }
				}//2끝
				
			   else if ($id === 2003) {
				if($a === 2){
					$player->sendMessage("나왔습니다");
                   }
                else if($a === 1){
                 	$this->sendUI($player, 2000, $this->OpenUiJM($name));
                 return;
                 }                	 
                 else if($a === 0){
                 	$lev = $this->lv[$name];
                     $pe = $lev;
					 $leaving = explode (":", $this->db ["setting"] ["leaving-item"]);
                     if($lev<254){
                     $ja = $pe*3;
                 	if($player->getInventory()->contains(Item::get($leaving[0], $leaving[1], $ja))){
                    $this->deleteJob($name);
                    $player->getInventory()->removeItem(Item::get(370, 0, $ja));
                    $this->lv[$name]++;
                    $this->save();
                    $this->sendUI($player, 2005, $this->OpenUiM("§l§a성공적으로 직업이 삭제되었습니다. 직업을 새로 선택해주시기 바랍니다."));
                    return;
                    }else{
                    	$this->sendUI($player, 2005, $this->OpenUiM("§l§c전직권이 부족합니다. 다시 시도해주시기 바랍니다"));
                 return;
				 }
                    }else{
                    $this->sendUI($player, 2005, $this->OpenUiM("§l§c※255레벨이 최대입니다※"));
                    return true;
                 }
                 }                                                              
				 }//3끝
				
				else if ($id === 2004) {
                $job = $this->data[$name];
                $lev = $this->lv[$name];
                if ($a === 0){
                if ($job == "전사") {
                $ex = "- '의지'\n- 힘과 저항을 받는다\n- '번지점프'\n- 상대방에게 폭딜을 주면서 위로 날아간다";
                }
                else if ($job == "궁수") {
                $ex = "- '화살폭탄'\n- 자신을 기준으로 8방향으로 화살을 날린다\n- '화살디펜서'\n- 바라보는 방향으로 화살을 60번 날린다.";
                }
                else if ($job == "마법사") {
                $ex = "- '벼락치기'\n- 주위 5칸내에 있는 플레이어에게 번개를 날린다\n- '자힐'\n- 재생을 받는다";
                }
                else if ($job == "도적") {
                $ex = "- '유체화'\n- 신속과 투명을 받는다\n- '바람타기'\n- 바라보는 방향으로 대쉬한다";
                }
                else {
                	$ex = "당신은 직업이 없습니다";
                }
                $this->sendUI($player, 2005, $this->OpenUiM("§l§a《 직업 정보 》 \n\n전직 레벨: ".$lev."\n\n나의 직업: ".$job."\n\n직업 설명: \n".$ex));
                return;
                }
                else if($a === 1){
                      $player->sendMessage("나왔습니다");
                   }
                }//4끝
                
				else if ($id === 2005) {
				if($a === 0){
					$this->sendUI($player, 2000, $this->OpenUiJM($name));
					return;
                   }
                else if($a === 1){
                 	$player->sendMessage("나왔습니다");
                 }
                 }//5끝        
				}//packet 끝
	    }	
public function addEffect(\pocketmine\entity\Creature $player, $id, $amplifier, $seconds){
		$effectType = \pocketmine\entity\Effect::getEffect($id);
		$player->addEffect(new \pocketmine\entity\EffectInstance($effectType, 20*$seconds, $amplifier, false));
	}
public function addExplosion($player, $x, $y, $z, $size){
		$level = $player->getLevel();
		$size = round($size);
		$position = new Position($x, $y, $z, $level);
		$explosion = new Explosion($position, $size);
		$explosion->explodeB();
	}
public function addLighting($player, $x, $z){
		$packet = new \pocketmine\network\mcpe\protocol\AddActorPacket();
		$packet->entityRuntimeId = \pocketmine\entity\Entity::$entityCount++;
		$packet->type = 93;
		$packet->position = new \pocketmine\math\Vector3($x, $player->y, $z);
		$packet->metadata = array();
		$this->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $packet);
		$this->addExplosion($player, $x, $player->y, $z, 1);
	}
public function addJob($name , $jn){
	$this->data[$name] = $jn;
	$this->save();
	}
public function deleteJob($name){
	    $this->data[$name] = "직업없음";
		$this->save();
}
public function save(){
    $this->job->setAll($this->data);
    $this->job->save();
    $this->level->setAll($this->lv);
    $this->level->save();
}
public function heart (Player $player) : void{
		$px = $player->getX();
		$py = $player->getY();
		$pz = $player->getZ();
		for ($i = 0; $i < 180; $i ++) {
			$sin = sin($i / 90 * M_PI);
			$cos = cos($i / 90 * M_PI);
			$particle = new HeartParticle(new Vector3($px + $sin * 3, $py + 1, $pz + $cos * 3));
			$player->getLevel()->addParticle($particle);
		}
	}
}
