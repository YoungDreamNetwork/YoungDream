<?php
namespace AuthorPE;
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
class Main extends PluginBase implements Listener{
    private static $instance;
    private $DB = 
        array(
        "HOST"=>"127.0.0.1",
        "USER"=>"root",
        "PASWD"=>"passwd",
        "DB"=>"AuthorPE",
        "DATA-TABLE"=>"Auth",
        "BANID-TABLE"=>"BANID",
        "BANIP-TABLE"=>"BANIP",
        "BANCID-TABLE"=>"BANCID"
        );
    
    
    public function onEnable(){
        
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
    }
}