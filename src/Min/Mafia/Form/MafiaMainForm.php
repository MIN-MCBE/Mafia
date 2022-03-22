<?php

declare(strict_types=1);

namespace MIN\Mafia\Form;

use pocketmine\form\Form;
use pocketmine\player\Player;

final class MafiaMainForm implements Form
{
    public function jsonSerialize(): array
    {
        return [
            'type' => 'form',
            'title' => '§lMAFIA',
            'content' => "§c〔 §f마피아 UI입니다 §c〕\n§f선택해주세요 \n",
            'buttons' => [
                [
                    'text' => '§c〔 §0마피아 참가하기 §c〕',
                ],
                [
                    'text' => '§c〔 §0마피아 룰 §c〕',
                ],
            ]
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        // TODO: Implement handleResponse() method.
    }
}