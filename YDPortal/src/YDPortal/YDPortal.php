<?php
namespace YDPortal;
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
use pocketmine\event\player\PlayerMoveEvent;

class YDPortal extends PluginBase implements Listener{
	
	
	public function onEnable()
	{
		@mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array());
		
	}
	
	
	public function onPlayerMove(PlayerMoveEvent $event){
		$name = $event->getPlayer()->getName();
		if(isset($this->players[$name])){
			$ppp = &$this->players[$name];
			if($ppp["IsLogin"] == 0){
				$event->getPlayer()->sendTip("请先登录！");
				$event->setCancelled();
			}
		}
	}

}
 
