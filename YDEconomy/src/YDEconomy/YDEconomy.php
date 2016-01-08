<?php
namespace YDEconomy;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\scheduler\PluginTask;

class YDEconomy extends PluginBase implements Listener{
	
	
	public static function getInstance(){
		return self::$instance;
	}
	public function onLoad(){
		self::$instance = $this;
	}
	
	
	public function onEnable(){
		$this->getLogger()->info("YDEconomy 加载中!");
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		
		$this->money = new Config($this->getDataFolder() . "money.yml", Config::YAML, array());
		$this->plist=$money->getAll();
		$this->getLogger()->info("YDEconomy加载完毕");	
	}
	
	
	public function save()
	{
		if(!isset($n))
		{
			$n=0;
			return;
		}
		$n=$n+1;
		if($n>50)
		{
			$this->money->setAll($this->plist);
			return true;
		}
		return true;
	}
	
	
	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(!in_array($name,$this->plist)
		{
			$this->CreatePlayer($name,0);
			$player->sendMessage("[梦灵]欢迎来到轻梦，您当前有 0 梦核。");
			return true;			
		}
		$pmoney=$this->SeeMoney($name);
		$player->sendMessage("[梦灵]欢迎回来，您当前有 $pmoney 梦核。");
	}
	
	public function SeeMoney($name){
		$this->save();
		if(!in_array($name,$this->plist)
		{
			$this->CreatePlayer($name,0);
			return 0;
		}
		return $this->plist[$name];
	}
	
	public function SetMoney($name,$money){
		$this->save();
		if(!in_array($name,$this->plist)
		{
			$this->CreatePlayer($name,0);
			return 0;
		}
		return $this->plist[$name]=$money;
	}
	
	public function CreatePlayer($name,$money){
		$this->save();
		if(!in_array($name,$this->plist)
		{
			return false;
		}
		return $this->plist[$name]=$money;
	}
	
	public function AddMoney($name,$money)
	{
		$this->save();
		if(!in_array($name,$this->plist)
		{
			$this->CreatePlayer($name,0);
			return false;
		}
		return $this->plist[$name]=$this->plist[$name]+$money;
	}
	
	public function ReduceMoney($name,$money)
	{
		$this->save();
		if(!in_array($name,$this->plist)
		{
			$this->CreatePlayer($name,0);
			return false;
		}
		return $this->plist[$name]=$this->plist[$name]-$money;
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
		{
			$name = $sender->getName();
			switch($command->getName())
			{
				case "money":
					$money=$this->SeeMoney($name);
					$sender->sendMessage("你当前拥有梦核：$money");
				break;
				
				case "givemoney":
					if(isset($args[0]) and is_numeric($args[1]) and isset($args[1]))
					{
						$result = $this->SeeMoney($args[0]);
							
							$this->AddMoney($args[0],$args[1]);
							$sender->sendMessage("已成功给予玩家 $args[0] $args[1]梦核");
							return true;	
					}
					$sender->sendMessage("请输入正确的格式/givemoney <玩家名> <数量>");
				break;	
				
				case "pay":
					if(isset($args[0]) and is_numeric($args[1]) and isset($args[1]))
					{
						$result = $this->SeeMoney($args[0]);
							
						$old = $this->SeeMoney($name);
						if($args[1] > $old)
						{
							$sender->sendMessage("你现在拥有的梦核数不足！");
							return false;
						}
							$this->ReduceMoney($name,$args[1]);
							$this->AddMoney($args[0],$args[1]);
							$sender->sendMessage("已成功给予玩家 $args[0] $args[1]梦核");
							return true;	
					}
					$sender->sendMessage("请输入正确的格式/pay <玩家名> <数量>");
				break;	
					
				case "setmoney":
					if(isset($args[0]) and is_numeric($args[1]) and isset($args[1]))
					{
						$result = $this->SeeMoney($args[0]);
							
							$this->SetMoney($args[0],$args[1]);
							$sender->sendMessage("已成功设置玩家 $args[0]的梦核数量为$args[1]");
							return true;	
					}
					$sender->sendMessage("请输入正确的格式/setmoney <玩家名> <数量>");
				break;

			}
		}

	
	

}
 
