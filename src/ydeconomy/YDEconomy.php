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

class YDEconomy extends PluginBase implements Listener{
	private $MysqlHost;
	private $MysqlUser;
	private $MysqlPass;
	private $MysqlDB;
	private $MysqlTable;
	private $DB;
	
	public function onEnable(){
		$this->getLogger()->info("YDEconomy 加载中!");
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array());
		if($this->config->exists("MysqlHost") AND $this->config->get("MysqlHost") !== array()){
			$this->MysqlHost =  $this->config->get("MysqlHost");
			$this->MysqlUser =  $this->config->get("MysqlUser");
			$this->MysqlPass =  $this->config->get("MysqlPass");
			$this->MysqlDB =  $this->config->get("MysqlDB");
			$this->MysqlTable =  $this->config->get("MysqlTable");
			
			$this->getLogger()->info(TextFormat::GREEN."MySql_Host:".$this->MysqlHost);
			$this->getLogger()->info(TextFormat::GREEN."MySql_User:".$this->MysqlUser);
			$this->getLogger()->info(TextFormat::GREEN."MySql_Password:".$this->MysqlPass);
			$this->getLogger()->info(TextFormat::GREEN."MySql_Database:".$this->MysqlDB);
			$this->getLogger()->info(TextFormat::GREEN."MySql_Table:".$this->MysqlTable);
			$this->getLogger()->info(TextFormat::GREEN."Mysql信息 检查完毕");
		}else{
			$this->getLogger()->info(TextFormat::RED."未设置Mysql信息");
			$this->config->set("MysqlHost","127.0.0.1");
			$this->config->set("MysqlUser","root");
			$this->config->set("MysqlPass","password");
			$this->config->set("MysqlDB","user");
			$this->config->set("MysqlTable","Players");
			$this->config->save();
		}
		$this->DB = new \mysqli($this->MysqlHost,$this->MysqlUser,$this->MysqlPass,$this->MysqlDB);
		if ($this->DB->connect_error){
			$result = TextFormat::RED."Mysql连接失败！";
		}else{
			$result = TextFormat::GREEN."Mysql连接成功！";
		}
		$this->getLogger()->info($result);
		$this->getLogger()->info("YDEconomy加载完毕");	
	}
	
	
	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$result = $this->QueryPlayer($name);
		if($result == false){
			$this->CreatePlayer($name,233);
		}
	}
	
	public function QueryPlayer($name){
		$sql = "SELECT * FROM $this->MysqlTable where ID = '$name'";
		$result = $this->DB->query($sql);
		if($result instanceof \mysqli_result){
			$Userdata = $result->fetch_assoc();
			$result->free();
			if(isset($Userdata["ID"]) and $Userdata["ID"] == $name){
				return $Userdata;
			}
		}
		return false;
	}
	
	public function UpdateMoney($name,$money){
		$table = $this->MysqlTable;
		$sql = "UPDATE $table SET Money ='$money' where ID = '$name' ";
		$result=$this->DB->query($sql);
		if($result instanceof \mysqli_result){
			$Userdata = $result->fetch_assoc();
			$result->free();
			if(isset($Userdata["ID"]) and $Userdata["ID"] == $name){
				return $Userdata["Money"];
			}
		}
		else
		{
			return false;
		}
	}
	
	public function CreatePlayer($name,$money){
		$table = $this->MysqlTable;
		$sql = "insert into $table(ID,Money) values('$name','$money')";
		$this->DB->query($sql);
	}
	
	
	

}
 
