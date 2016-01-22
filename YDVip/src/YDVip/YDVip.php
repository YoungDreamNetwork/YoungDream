<?php
namespace YDVip;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\level\Level;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use YDAuth\task;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\CallbackTask;

class YDVip extends PluginBase implements Listener
{

    public $instance;
    private $vips = array();

    public static function getInstance()
    {
        return self::$instance;
    }

    public function onLoad()
    {
        self::$instance = $this;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->vips = $this->readconfig();
    }

    public function onDisable()
    {
        $this->readconfig(true);
    }

    public function readconfig($save)
    {
        @mkdir($this->getDataPath());
        $config = new Config($this->getDataFolder() . "vips.yml", Config::YAML, array());
        if ($save == true) {
            $config->setAll($this->vips);
            return true;
        }
        return $config->getAll();
    }

    public function isvip($name)
    {
        if (in_array($name, $this->vips)) {
            $ndate = strtotime("now");
            $sday = $this->vips[$name];
            $day = round(($sday - $ndate) / 86400);
            if ($day < 0) {
                unset($this->vips[$name]);
                return true;
            }
            return $day;
        }
        return false;
        //true为已过期。false为未开通，未到期返回剩余日期
    }

    public function addVip($name, $day)
    {
        if (isset($this->vips[$name])) {
            $eday = $day * 86400;
            return $this->vips[$name] = $this->vips[$name] + eday;
        }
        return $this->vips[$name] = time() + $day * 86400;
    }

    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $vipday = $this->isvip($name);
        if ($vipday == false) {
            $player->sendMessage(TextFormat::GREEN . "[梦灵]赞助轻梦，让我们走的更远！");
            return true;
        } elseif ($vipday == true) {
            $player->sendMessage(TextFormat::RED . "[梦灵]您的VIP已过期，请及时续费。");
            return true;
        }
        $player->sendMessage(TextFormat::GREEN . "[梦灵]您的VIP还剩余 $vipday 天");
        $player->setHealth(30);
        return true;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
        $name = $sender->getName();
        switch ($command->getName()) {
            case "fly":
                if ($this->isvip($name) == true or false) {
                    $sender->sendMessage(TextFormat::RED . "[梦灵]你无权使用此指令，请先充值VIP");
                    return true;
                }
                if (!$sender->getAllowFlight()) {
                    $sender->setAllowFlight(true);
                    $sender->sendMessage(TextFormat::GREEN . "[梦灵]已开启生存飞行。");
                    return true;
                }
                $sender->setAllowFilght(false);
                $sender->sendMessage(TextFormat::GREEN . "[梦灵]已关闭生存飞行。");
                return true;
                break;

            case "h":
                if ($this->isvip($name) == true or false) {
                    $sender->sendMessage(TextFormat::RED . "[梦灵]你无权使用此指令，请先充值VIP");
                    return true;
                }
                $sender->setHealth(30);
                $sender->sendMessage(TextFormat::GREEN . "[梦灵]你的血量已经回满 .");
                return true;
                break;

            case "tp":
                if ($this->isvip($name) == true or false) {
                    $sender->sendMessage(TextFormat::RED . "[梦灵]你无权使用此指令，请先充值VIP");
                    return true;
                }
                if (!isset($args[0])) {
                    $sender->sendMessage(TextFormat::RED . "[梦灵]请输入要强制传送到的玩家ID");
                    return true;
                }
                $endpos = $this->getServer()->getPlayer($args[0])->getPosition();
                $sender->teleport($endpos);
                $sender->sendMessage(TextFormat::GREEN . "[梦灵]传送完毕。");
                return true;
                break;
        }
    }


}
 
