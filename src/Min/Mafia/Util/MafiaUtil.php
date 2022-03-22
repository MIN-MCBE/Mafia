<?php

declare(strict_types=1);

namespace MIN\Mafia\Util;

use Min\Mafia\Mafia;
use pocketmine\player\Player;

final class MafiaUtil
{
    public static function msg(Player $player, $msg) : void{
        $player->sendMessage(Mafia::$prefix . $msg);
    }

    public static function makeButton(string $title, string $subtitle) : array{
        return ['text' => "§l$title\n§r§8▶ $subtitle §r§8◀"];
    }
}