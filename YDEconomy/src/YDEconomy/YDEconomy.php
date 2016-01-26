<?php
namespace YDEconomy;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;

class YDEconomy extends PluginBase implements Listener{
	public static $instance;
	public $clist = [];
	public $csave;

	public static function getInstance(){
		return self::$instance;
	}

	public function onLoad(){
		self::$instance = $this;
	}

	public function onEnable(){
		$this->getLogger()->info("YDEconomy 加载中!");

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if(!ZXDA_Connector::getAPIVersion()){
			$this->getLogger()->warning("无法连接至ZXDA服务器！已关闭插件！");
			$this->getServer()->disablePlugins();
		}
		$this->csave = new Config($this->getDataFolder() . "card.yml", Config::YAML, array());
		$this->clist = $this->csave->getAll();
		$this->getLogger()->info("YDEconomy加载完毕");
	}


	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$pmoney = $this->SeeMoney($name);
		$player->sendMessage("[梦灵]欢迎回来，您当前有 $pmoney 梦核。");
	}

	/**
	 * @param $PlayerName
	 * @return bool
	 */
	public function SeeMoney($PlayerName){
		return ZXDA_Connector::getCoupons($PlayerName);
	}

	/**
	 * @param $PlayerName
	 * @param $Count
	 * @return bool
	 */
	public function SetMoney($PlayerName, $Count){
		ZXDA_Connector::setCoupons($PlayerName, $Count);
		return $this->SeeMoney($PlayerName);
	}

	/**
	 * @param $PlayerName
	 * @param $Count
	 * @return bool
	 */
	public function AddMoney($PlayerName, $Count){
		ZXDA_Connector::addCoupons($PlayerName, $Count);
		return $this->SeeMoney($PlayerName);
	}

	/**
	 * @param $PlayerName
	 * @param $Count
	 * @return bool
	 */
	public function ReduceMoney($PlayerName, $Count){
		if($this->SeeMoney($PlayerName) < $Count){
			return false;
		}
		ZXDA_Connector::takeCoupons($PlayerName, $Count);
		return $this->SeeMoney($PlayerName);
	}


	/**
	 * @param $name
	 * @param $pass
	 * @return bool
	 */
	public function card($name, $pass){
		if(in_array($pass, $this->clist)){
			$this->AddMoney($name, $this->clist[$pass]);
			unset($this->clist[$pass]);
			$this->csave->save();
			return $this->clist[$pass];
		}
		return false;
	}


	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		$name = $sender->getName();
		switch($command->getName()){
			case "money":
				$money = $this->SeeMoney($name);
				$sender->sendMessage("你当前拥有梦核：$money");
				break;

			case "givemoney":
				if(isset($args[0]) and is_numeric($args[1]) and isset($args[1])){
					$result = $this->SeeMoney($args[0]);

					$this->AddMoney($args[0], $args[1]);
					$sender->sendMessage("已成功给予玩家 $args[0] $args[1]梦核");
					return true;
				}
				$sender->sendMessage("请输入正确的格式/givemoney <玩家名> <数量>");
				break;

			case "pay":
				if(isset($args[0]) and is_numeric($args[1]) and isset($args[1])){
					$result = $this->SeeMoney($args[0]);

					$old = $this->SeeMoney($name);
					if($args[1] > $old){
						$sender->sendMessage("你现在拥有的梦核数不足！");
						return false;
					}
					$this->ReduceMoney($name, $args[1]);
					$this->AddMoney($args[0], $args[1]);
					$sender->sendMessage("已成功给予玩家 $args[0] $args[1]梦核");
					return true;
				}
				$sender->sendMessage("请输入正确的格式/pay <玩家名> <数量>");
				break;

			case "setmoney":
				if(isset($args[0]) and is_numeric($args[1]) and isset($args[1])){
					$result = $this->SeeMoney($args[0]);

					$this->SetMoney($args[0], $args[1]);
					$sender->sendMessage("已成功设置玩家 $args[0]的梦核数量为$args[1]");
					return true;
				}
				$sender->sendMessage("请输入正确的格式/setmoney <玩家名> <数量>");
				break;

			case "card":
				if(isset($args[0]) and is_numeric($args[0])){
					$result = $this->card($name, $args[0]);
					if($result != false){
						$now = $this->SeeMoney($name);
						$sender->sendMessage("[梦灵]已成功兑换 $result 梦核，你现在拥有 $now 梦核");
						return true;
					}
					$sender->sendMessage("[梦灵]兑换失败，请检查梦核兑换码是否正确。");
				}
				break;

		}
	}


}
 
