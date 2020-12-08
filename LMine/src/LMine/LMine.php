<?php

namespace LMine;

use pocketmine\plugin\PluginBase;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\math\Vector3;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\HappyVillagerParticle;

class LMine extends PluginBase implements Listener{
    
    public $setting = [];
    public $data = [];
    
    public function onEnable(){
        
        @mkdir($this->getDataFolder());
        $this->saveResource("settings.yml");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new LMining($this), 200);
        
        $this->settings = new Config($this->getDataFolder(). 'setting.yml', Config::YAML, [
            
            "WorldName" => [ "후원자용광산" ],
            
            "Ores" => [
                
                Block::GOLD_ORE => [
                
                    "GenerateTime" => 10,
                    "ItemId" => Item::GOLD_INGOT,
                    "ItemDamage" => 0
                
            ],
                
                Block::IRON_ORE => [
                    
                    "GenerateTime" => 10,
                    "ItemId" => Item::IRON_INGOT,
                    "ItemDamage" => 0
                    
                    
            ]
            
            ]
            
        ]);
        
        $this->list = new Config($this->getDataFolder(). 'data.yml', Config::YAML, [ "Blocks" => [] ]);
        
        $this->setting = $this->settings->getAll();
        $this->data = $this->list->getAll();
        
    }
    
    public function onDisable(){
        
        $this->list->setAll($this->data);
        $this->list->save();
        
    }
	

    
    public function onBreak(BlockBreakEvent $event){
        
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $WorldName = $this->setting["WorldName"];
        $Ores = $this->setting["Ores"];
        $Blocks = $this->data["Blocks"];
        $x = $block->getX();
        $y = $block->getY();
        $z = $block->getZ();
        $Ore = $block->getId();
        $level = $block->getLevel();
        $Name = $level->getFolderName();
        
        if (in_array($Name, $WorldName)){
            
            if (in_array($Ore, array_keys($Ores))){
                
                $event->setDrops([]);
                
                if ($player->isOp()){
                    
                    return true;
                    
                }
                
                $inf = "{$Name}:{$block->getX()}:{$block->getY()}:{$block->getZ()}:{$Ore}";
                
                if (!in_array($inf, $Blocks)){
                    
                    $this->getScheduler()->scheduleDelayedTask(new LRegen($this, $inf), $Ores[(string)$Ore]["GenerateTime"]);
                    
                }
                
                $event->getPlayer()->getInventory()->addItem(Item::get($Ores[(string)$Ore]["ItemId"], $Ores[(string)$Ore]["ItemDamage"], 1));
                $level->addParticle(new FlameParticle(new Vector3($x, $y + 1, $z)));
                $level->addParticle(new FlameParticle(new Vector3($x + 1, $y + 1, $z)));
                $level->addParticle(new FlameParticle(new Vector3($x, $y + 1, $z + 1)));
                $level->addParticle(new FlameParticle(new Vector3($x + 1, $y + 1, $z + 1)));
                $level->addParticle(new FlameParticle(new Vector3($x, $y, $z)));
                $level->addParticle(new FlameParticle(new Vector3($x + 1, $y, $z)));
                $level->addParticle(new FlameParticle(new Vector3($x, $y, $z + 1)));
                $level->addParticle(new FlameParticle(new Vector3($x + 1, $y, $z + 1)));
                $this->getScheduler()->scheduleTask(new LTask($this, $inf));
                
            }else{
                
                if (!$player->isOp()){
                    
                    $event->setCancelled();
                    
                }
                
            }
            
        }
        
    }
    
    public function LMine(){//유저가 부순 블럭 리젠
        
        $s = $this->data["Blocks"];
        
        $arr = [];
        
        foreach ($s as $block){
            
            $pos = explode(":", $block);
                
            $vec = new Vector3($pos[1], $pos[2], $pos[3]);
            $level = $this->getServer()->getLevelByName($pos[0]);
            
            if ($level !== null){
                
                $level->setBlock($vec, Block::get($pos[4]));
                
            }else{
                
                array_push($arr, implode(":", $pos));
                
            }
            
            array_shift($s);
            
        }
        
        $this->data["Blocks"] = $arr;
        
    }
        
}

class LMining extends Task{
    
    private $owner;
    
    public function __construct(LMine $owner){
        
        $this->owner = $owner;
        
    }
    
    public function onRun($currentTick){
        
        $this->owner->LMine();
        
    }
    
}

class LTask extends Task{
    
    private $owner;
    
    public function __construct(LMine $owner, $info){
        
        $this->owner = $owner;
        $this->info = $info;
        
    }
    
    public function onRun($currentTick){
        
        $info = explode(":", $this->info);
        
        $this->owner->getServer()->getLevelByName($info[0])->setBlock(new Vector3($info[1], $info[2], $info[3]), Block::get(Block::AIR));
        
    }
    
}

class LRegen extends Task{
    
    private $owner;
    
    public function __construct(LMine $owner, $info){
        
        $this->owner = $owner;
        $this->info = $info;
        
    }
    
    public function onRun($currentTick){
        
        $info = explode(":", $this->info);
        
        $pos = new Vector3($info[1], $info[2], $info[3]);
        $level = $this->owner->getServer()->getLevelByName($info[0]);
        
        if ($level->getBlock($pos)->getId() == Block::AIR){
            
            $level->setBlock($pos, Block::get($info[4]));
            $level->addParticle(new HappyVillagerParticle($pos));
            
        }else{
            
            array_push($this->owner->data["Blocks"], implode(":", $info));
            
        }
        
    }
    
}