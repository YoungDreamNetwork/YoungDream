<?php


namespace onebone\economyapi;

use onebone\economyapi\provider\MySQLProvider;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use onebone\economyapi\event\money\AddMoneyEvent;
use onebone\economyapi\event\money\ReduceMoneyEvent;
use onebone\economyapi\event\money\SetMoneyEvent;
use onebone\economyapi\event\account\CreateAccountEvent;
use onebone\economyapi\event\money\MoneyChangedEvent;
use onebone\economyapi\provider\YamlProvider;
use onebone\economyapi\provider\Provider;
use onebone\economyapi\task\SaveTask;


class EconomyAPI extends PluginBase implements Listener{
	/**
	 * @var int
	 */
	const API_VERSION = 2;

	/**
	 * @var string
	 */
	const PACKAGE_VERSION = "5.7";

	/**
	 * @var EconomyAPI
	 */
	private static $instance = null;

	/**
	 * @var Config
	 */
	private $config = null, $command = null, $mysql = null;

	/**
	 * @var Provider
	 */
	private $provider = null;

	/**
	 * @var array
	 */
	private $langRes = [];

	/**
	 * @var array
	 */
	private $playerLang = []; // language system related

	/**
	 * @var string
	 */
	private $monetaryUnit = "$";

	/**
	 * @var int RET_ERROR_1 Unknown error 1
	*/
	const RET_ERROR_1 = -4;

	/**
	 * @var int RET_ERROR_2 Unknown error 2
	*/
	const RET_ERROR_2 = -3;

	/**
	 * @var int RET_CANCELLED Task cancelled by event
	*/
	const RET_CANCELLED = -2;

	/**
	 * @var int RET_NOT_FOUND Unable to process task due to not found data
	*/
	const RET_NOT_FOUND = -1;

	/**
	 * @var int RET_INVALID Invalid amount of data
	*/
	const RET_INVALID = 0;

	/**
	 * @var int RET_SUCCESS The task was successful
	*/
	const RET_SUCCESS = 1;

	/**
	 * @var array
	 */
	private $langList = [
		"def" => "Default",
		"user-define" => "User Defined",
		"ch" => "简体中文",
	];

	/**
	 * @return EconomyAPI
	 */
	public static function getInstance(){
		return self::$instance;
	}

	public function onLoad(){
		self::$instance = $this;
	}

	public function onEnable(){
		@mkdir($this->getDataFolder());

		$this->createConfig();

		$this->convertData();
		switch(strtolower($this->config->get("provider"))){
			case "yaml":
				$this->provider = new YamlProvider($this->getDataFolder()."Money.yml");
				break;
			case "mysql":
				$this->provider = new MySQLProvider($this->mysql->getAll());
				break;
			default:
				$this->getLogger()->critical("Invalid provider was given. Aborting...");
				return;
		}
		$this->getLogger()->notice("存储方式被设置为: ".$this->provider->getName());

		$this->scanResources();

		if(!is_file($this->getDataFolder() . "PlayerLang.dat")){
			file_put_contents($this->getDataFolder() . "PlayerLang.dat", serialize([]));
		}

		$this->playerLang = unserialize(file_get_contents($this->getDataFolder() . "PlayerLang.dat"));

		if(!isset($this->playerLang["console"])){
			$this->getLangFile();
		}
		$commands = [
			"setmoney" => "onebone\\economyapi\\commands\\SetMoneyCommand",
			"seemoney" => "onebone\\economyapi\\commands\\SeeMoneyCommand",
			"mymoney" => "onebone\\economyapi\\commands\\MyMoneyCommand",
			"pay" => "onebone\\economyapi\\commands\\PayCommand",
			"givemoney" => "onebone\\economyapi\\commands\\GiveMoneyCommand",
			"topmoney" => "onebone\\economyapi\\commands\\TopMoneyCommand",
			"setlang" => "onebone\\economyapi\\commands\\SetLangCommand",
			"takemoney" => "onebone\\economyapi\\commands\\TakeMoneyCommand",
			"mystatus" => "onebone\\economyapi\\commands\\MyStatusCommand"
		];
		$commandMap = $this->getServer()->getCommandMap();
		foreach($commands as $key => $command){
			foreach($this->command->get($key) as $cmd){
				$commandMap->register("economyapi", new $command($this, $cmd));
			}
		}

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->monetaryUnit = $this->config->get("monetary-unit");

		$time = $this->config->get("auto-save-interval");
		if(is_numeric($time)){
			$interval = $time * 1200;
			if($interval>0){
				$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new SaveTask($this), $interval, $interval);
				$this->getLogger()->notice("自动存储时间 : ".$time." min(s)");
			}
		}
	}

	private function convertData(){
		$cnt = 0;
		if(is_file($this->getDataFolder() . "MoneyData.yml")){
			$data = (new Config($this->getDataFolder() . "MoneyData.yml", Config::YAML))->getAll();
			$saveData = [];
			foreach($data as $player => $money){
				$saveData["money"][$player] = round($money["money"], 2);
				++$cnt;
			}
			@unlink($this->getDataFolder() . "MoneyData.yml");
			$moneyConfig = new Config($this->getDataFolder() . "Money.yml", Config::YAML);
			$moneyConfig->setAll($saveData);
			$moneyConfig->save();
		}

		if($cnt > 0){
			$this->getLogger()->info(TextFormat::AQUA."Converted $cnt data(m) into new format");
		}
	}

	private function createConfig(){
		$this->config = new Config($this->getDataFolder() . "economy.properties", Config::PROPERTIES, yaml_parse($this->readResource("config.yml")));
		$this->command = new Config($this->getDataFolder() . "command.yml", Config::YAML, yaml_parse($this->readResource("command.yml")));
		$this->mysql = new Config($this->getDataFolder()."mysql_host.yml", Config::YAML, [
			"host" => "127.0.0.1",
			"port" => 3306,
			"user" => "onebone",
			"password" => "secret",
			"db" => "economys"
		]);
	}

	private function scanResources(){
		foreach($this->getResources() as $resource){
			$s = explode(\DIRECTORY_SEPARATOR, $resource);
			$res = $s[count($s) - 1];
			if(substr($res, 0, 5) === "lang_"){
				$this->langRes[substr($res, 5, -5)] = get_object_vars(json_decode($this->readResource($res)));
			}
		}
		$this->langRes["user-define"] = (new Config($this->getDataFolder() . "language.properties", Config::PROPERTIES, $this->langRes["def"]))->getAll();
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function getConfigurationValue($key, $default = false){
		if($this->config->exists($key)){
			return $this->config->get($key);
		}
		return $default;
	}

	/**
	 * @param string $res
	 *
	 * @return bool|string
	 */
	private function readResource($res){
		$resource = $this->getResource($res);
		if($resource !== null){
			return stream_get_contents($resource);
		}
		return false;
	}

	private function getLangFile(){
		$lang = $this->config->get("default-lang");
		if(isset($this->langRes[$lang])){
			$this->playerLang["console"] = $lang;
			$this->playerLang["rcon"] = $lang;
			$this->getLogger()->info(TextFormat::GREEN.$this->getMessage("language-set", "console", [$this->langList[$lang], "%2", "%3", "%4"]));
		}else{
			$this->playerLang["console"] = "def";
			$this->playerLang["rcon"] = "def";
			$this->getLogger()->info(TextFormat::GREEN.$this->getMessage("language-set", "console", [$this->langList[$lang], "%2", "%3", "%4"]));
		}
	}

	/**
	 * @param string $lang
	 * @param string $target
	 *
	 * @return bool
	 */
	public function setLang($lang, $target = "console"){
		if(isset($this->langRes[$lang])){
			$this->playerLang[strtolower($target)] = $lang;
			return $lang;
		}else{
			$lower = strtolower($lang);
			foreach($this->langList as $key => $l){
				if($lower === strtolower($l)){
					$this->playerLang[strtolower($target)] = $key;
					return $l;
				}
			}
		}
		return false;
	}

	/**
	 * @return array
	*/
	public function getLangList(){
		return $this->langList;
	}

	/**
	 * @return array
	*/
	public function getLangResource(){
		return $this->langRes;
	}

	/**
	 * @param string|Player $player
	 *
	 * @return string|boolean
	*/
	public function getPlayerLang($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		if(isset($this->playerLang[$player])){
			return $this->playerLang[$player];
		}else{
			return false;
		}
	}

	/**
	 * @return array
	*/
	public function getAllMoney(){
		return $this->provider->getAll();
	}

	/**
	  * @return string
	  */
	 public function getMonetaryUnit(){
		return $this->monetaryUnit;
	 }

	/**
	 * @param string $key
	 * @param Player|string $player
	 * @param array $value
	 *
	 * @return string
	*/
	public function getMessage($key, $player = "console", array $value = ["%1", "%2", "%3", "%4"]){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		if(isset($this->playerLang[$player]) and isset($this->langRes[$this->playerLang[$player]][$key])){
			return str_replace(["%MONETARY_UNIT%", "%1", "%2", "%3", "%4"], [$this->monetaryUnit, $value[0], $value[1], $value[2], $value[3]], $this->langRes[$this->playerLang[$player]][$key]);
		}elseif(isset($this->langRes["def"][$key])){
			return str_replace(["%MONETARY_UNIT%", "%1", "%2", "%3", "%4"], [$this->monetaryUnit, $value[0], $value[1], $value[2], $value[3]], $this->langRes["def"][$key]);
		}else{
			return "不能找到语言文件";
		}
	}

	/**
	 * @param Player|string $player
	 *
	 * @return bool
	*/
	public function accountExists($player){
		return $this->provider->accountExists($player);
	}

	/**
	 * @param Player|string $player
	 * @param bool|float $default_money
	 * @param bool $force
	 *
	 * @return boolean
	 */
	public function createAccount($player, $default_money = false, $force = false){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		if(!$this->provider->accountExists($player)){
			$this->getServer()->getPluginManager()->callEvent(($ev = new CreateAccountEvent($this, $player, $default_money === false ? $this->config->get("default-money") : $default_money, "EconomyAPI")));
			if(!$ev->isCancelled() and $force === false){
				$this->provider->createAccount($player, $ev->getDefaultMoney());
				return true;
			}
		}
		return false;
	}

	/**
	 * @param Player|string $player
	 *
	 * @return boolean
	*/
	public function removeAccount($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		if($this->provider->accountExists($player)){
			if($this->provider->removeAccount($player)){
				$p = $this->getServer()->getPlayerExact($player);
				if($p instanceof Player){
					$p->kick("Your account have been removed.");
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * @param Player|string $player
	 *
	 * @return boolean|float
	*/
	public function myMoney($player){ // To identify the result, use '===' operator
		return $this->provider->getMoney($player);
	}

	/**
	 * @param Player|string $player
	 * @param float $amount
	 * @param bool $force
	 * @param string $issuer
	 *
	 * @return int
	 */
	public function addMoney($player, $amount, $force = false, $issuer = "external"){
		if($amount <= 0 or !is_numeric($amount)){
			return self::RET_INVALID;
		}

		if(($money = $this->provider->getMoney($player)) !== false){
			$amount = min($this->config->get("max-money"), $amount);
			$event = new AddMoneyEvent($this, $player, $amount, $issuer);
			$this->getServer()->getPluginManager()->callEvent($event);
			if($force === false and $event->isCancelled()){
				return self::RET_CANCELLED;
			}
			$this->provider->addMoney($player, $amount);
			$this->getServer()->getPluginManager()->callEvent(new MoneyChangedEvent($this, $player, $money + $amount, $issuer));
			return self::RET_SUCCESS;
		}else{
			return self::RET_NOT_FOUND;
		}
	}

	/**
	 * @param Player|string $player
	 * @param float $amount
	 * @param bool $force
	 * @param string $issuer
	 *
	 * @return int
	 */
	public function reduceMoney($player, $amount, $force = false, $issuer = "external"){
		if($amount <= 0 or !is_numeric($amount)){
			return self::RET_INVALID;
		}
		if(($money = $this->provider->getMoney($player)) !== false){
			if($money - $amount < 0){
				return self::RET_INVALID;
			}
			$event = new ReduceMoneyEvent($this, $player, $amount, $issuer);
			$this->getServer()->getPluginManager()->callEvent($event);
			if($force === false and $event->isCancelled()){
				return self::RET_CANCELLED;
			}
			$this->provider->reduceMoney($player, $amount);
			$this->getServer()->getPluginManager()->callEvent(new MoneyChangedEvent($this, $player, $money - $amount, $issuer));
			return self::RET_SUCCESS;
		}else{
			return self::RET_NOT_FOUND;
		}
	}

	/**
	 * @param Player|string $player
	 * @param float $money
	 * @param bool $force
	 * @param string $issuer
	 *
	 * @return int
	 */
	public function setMoney($player, $amount, $force = false, $issuer = "external"){
		if($amount < 0 or !is_numeric($amount)){
			return self::RET_INVALID;
		}

		if(($money = $this->provider->getMoney($player)) !== false){
			$money = min($this->config->get("max-money"), $amount);
			$ev = new SetMoneyEvent($this, $player, $money, $issuer);
			$this->getServer()->getPluginManager()->callEvent($ev);
			if($force === false and $ev->isCancelled()){
				return self::RET_CANCELLED;
			}
			$this->provider->setMoney($player, $amount);
			$this->getServer()->getPluginManager()->callEvent(new MoneyChangedEvent($this, $player, $amount, $issuer));
			return self::RET_SUCCESS;
		}else{
			return self::RET_NOT_FOUND;
		}
	}

	public function onDisable(){
		$this->save();
		if($this->provider instanceof Provider){
			$this->provider->close();
		}
	}

	public function save(){
		if($this->provider instanceof Provider){
			$this->provider->save();
		}
		file_put_contents($this->getDataFolder() . "PlayerLang.dat", serialize($this->playerLang));
	}

	/**
	 * @param PlayerLoginEvent $event
	 */
	public function onLoginEvent(PlayerLoginEvent $event){
		$username = strtolower($event->getPlayer()->getName());
		if(!$this->provider->accountExists($username)){
			$this->createAccount($username);
		}
		if(!isset($this->playerLang[$username])){
			$this->setLang($this->config->get("default-lang"), $username);
		}
	}

	/**
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
		$command = strtolower(substr($event->getMessage(), 0, 9));
		if($command === "/save-all"){
			$this->onCommandProcess($event->getPlayer());
		}
	}

	/**
	 * @param ServerCommandEvent $event
	 */
	public function onServerCommandProcess(ServerCommandEvent $event){
		$command = strtolower(substr($event->getCommand(), 0, 8));
		if($command === "save-all"){
			$this->onCommandProcess($event->getSender());
		}
	}

	public function onCommandProcess(CommandSender $sender){
		$command = $this->getServer()->getCommandMap()->getCommand("save-all");
		if($command instanceof Command){
			if($command->testPermissionSilent($sender)){
				$this->save();
				$sender->sendMessage("[EconomyAPI] Saved money data.");
			}
		}
	}

	/**
	 * @return string
	*/
	public function __toString(){
		return "EconomyAPI";
	}
}
