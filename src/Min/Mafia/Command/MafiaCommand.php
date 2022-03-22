<?php
declare(strict_types = 1);

namespace MIN\Mafia\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class MafiaCommand extends Command{

    public function __construct(){
        $this->setPermission('mafia.op');
        parent::__construct('마피아' , '마피아 UI를 엽니다' , '/마피아', ['mafia']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
        if(!$sender instanceof Player) return;
        $sender->sendForm(new FindTheRealMainForm());
    }

}
