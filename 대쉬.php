<?php
/**
 * @name VisionDashplugin
 * @main skill\skill
 * @author 민
 * @version 못생긴버전
 * @api 3.0.0
 */
namespace skill;

    use pocketmine\event\Listener;
    use pocketmine\event\player\PlayerInteractEvent;
    use pocketmine\math\Vector3;
    use pocketmine\plugin\PluginBase;
    use skymin\particle\ParticleAPI;
    use pocketmine\level\sound\EndermanTeleportSound;

    class skill extends PluginBase implements Listener
    {
        public function onEnable()
        {
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
        }

        public function onInteract(PlayerInteractEvent $ev)
        {
            $p = $ev->getPlayer();
            if ($ev->getItem()->getId() == 398) {
                $level = $p->getLevel();
                $pos = $p->getPosition()->add(0,1);
                $a = $p->getDirectionVector()->multiply(3);
                $x = $a->getX();
                $y = $a->getY();
                $z = $a->getZ();
                $p->setMotion(new Vector3($x, $y, $z));
                ParticleAPI::getInstance()->colorCircle($pos, 2, 15, 0, $level, 255, 255, 000, 1, $this->getServer()->getOnlinePlayers(),0.6);
                $sound = new EndermanTeleportSound($pos);
                $p->getLevel()->addSound($sound);

            }
        }

    }
