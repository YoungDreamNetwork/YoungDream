<?php
namespace YDAuth;
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

//妈个鸡终于完善了……zzm写的简直了……兼容simpleauth数据
class YDAuth extends PluginBase implements Listener{
	private $MysqlHost;
	private $MysqlUser;
	private $MysqlPass;
	private $MysqlDB;
	private $MysqlTable;
	private $DB;
	private $players = [];
	private static $instance;
	
	
	public function onEnable(){
		if(!is_dir($this->getPluginDir())){
			@mkdir($this->getServer()->getDataPath()."plugins/YDAuth");
			
		}
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
			$this->getLogger()->info(TextFormat::GREEN."Mysql检查完毕!!!");
		}else{
			$this->getLogger()->info(TextFormat::RED."尚未设置Mysql !!!");
			$this->config->set("MysqlHost","127.0.0.1");
			$this->config->set("MysqlUser","root");
			$this->config->set("MysqlPass","password");
			$this->config->set("MysqlDB","user");
			$this->config->set("MysqlTable","Players");
			$this->config->save();
		}
		$testresult = $this->MysqlConnect();
		$this->getLogger()->info($testresult);
		$this->getLogger()->info("YDAuth Loaded !!!!");	
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new task($this,$this->DB),1800);
	}
	
	public static function getInstance(){
        return self::$instance;
    }
	

	
	public function MysqlConnect(){
		$this->DB = new \mysqli($this->MysqlHost,$this->MysqlUser,$this->MysqlPass,$this->MysqlDB);
		if ($this->DB->connect_error){
			$result = TextFormat::RED."ERROR, Connection Failed";
		}else{
			$result = TextFormat::GREEN."Mysql Connect Success!";
		}
		return $result;
	}
	
	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$result = $this->QueryPlayer($name);
		if($result == "UnFound"){
			$player->sendMessage(TextFormat::GREEN."欢迎来到轻梦群组服");
			$player->sendMessage(TextFormat::GREEN."你还没有注册，请先注册");
			$result = $this->NewPlayer($player);
			$player->sendMessage(TextFormat::RED."请输入验证码来验证:".$result);
		}else{
			if(intval((time() - $result["LastTime"])/10800) <= 3 and $result["LastIP"] == $player->getAddress()){
				$player->sendMessage(TextFormat::GREEN."欢迎回来~3小时内免登录~玩的开心~");
				$this->LoginPlayer($player);
				$this->UpdatePlayer($name,$player->getAddress());
			}else{
				$player->sendMessage(TextFormat::GREEN."请输入密码登陆~");
				$this->UnLoginPlayer($player,$result["Password"]);
			}
		}
	}
	
	public function QueryPlayer($name){
		if($this->atask->getstatus == false)
		{
			$this->DB=$this->MysqlConnect();
		}
		$sql = "SELECT * FROM $this->MysqlTable where ID = '$name'";
		$result = $this->DB->query($sql);
		if($result instanceof \mysqli_result){
			$Userdata = $result->fetch_assoc();
			$result->free();
			if(isset($Userdata["ID"]) and $Userdata["ID"] == $name){
				return $Userdata;
			}
		}
		
		return "UnFound";
	}
	
	public function NewPlayer($player){
		$vc = mt_rand(0,9). mt_rand(0,9). mt_rand(0,9). mt_rand(0,9);
		$config = array(
			"ID" => $player->getName(),
			"IP" => $player->getAddress(),
			"Time" => time(),
			"IsNew" => 1,
			"IsLogin" => 0,
			"IsVerify" => 0,
			"Check" => $vc,
        	);
		$this->players[$player->getName()] = $config;
		return $vc;
	}
	
	public function UnLoginPlayer($player,$password){
		$config = array(
			"ID" => $player->getName(),
			"IP" => $player->getAddress(),
			"Time" => time(),
			"IsNew" => 0,
			"IsLogin" => 0,
			"Password" => $password,
        	);
		$this->players[$player->getName()] = $config;
	}
	
	public function LoginPlayer($player){
		$config = array(
			"ID" => $player->getName(),
			"IP" => $player->getAddress(),
			"Time" => time(),
			"IsNew" => 0,
			"IsLogin" => 1,
        	);
		$this->players[$player->getName()] = $config;
	}
	
	public function UpdatePlayer($name,$ip){
		$time = time();
		$table = $this->MysqlTable;
		$sql = "UPDATE $table SET LastIP ='$ip' where ID = '$name' ";
		$sql1 = "UPDATE $table SET LastTime ='$time' where ID = '$name' ";
		$this->DB->query($sql);
		$this->DB->query($sql1);
	}
	
	public function CreatePlayer($name,$ip,$password){
		$time = time();
		$pass = $this->hash(strtolower($name),$password);
		$table = $this->MysqlTable;
		$sql = "insert into $table(ID,Password,LastIP,LastTime) values('$name','$pass','$ip','$time')";
		return $this->DB->query($sql);
		
	}
	
	public function onPlayerInteract(PlayerInteractEvent $event){
		$name = $event->getPlayer()->getName();
		if(isset($this->players[$name])){
			$ppp = &$this->players[$name];
			if($ppp["IsLogin"] == 0){
				$event->getPlayer()->sendTip("请先登录！");
				$event->setCancelled();
			}
		}
	}
	
	public function onBlockBreak(BlockBreakEvent $event){
		$name = $event->getPlayer()->getName();
		if(isset($this->players[$name])){
			$ppp = &$this->players[$name];
			if($ppp["IsLogin"] == 0){
				$event->getPlayer()->sendTip("请先登录！");
				$event->setCancelled();
			}
		}
	}
	
	public function onEntityDamage(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent){
			$p = $event->getDamager();
			$zo = $event->getEntity();
			if ($p instanceof Player and $zo instanceof Player) {
				$name = $p->getName();
				if(isset($this->players[$name])){
					$ppp = &$this->players[$name];
					if($ppp["IsLogin"] == 0 ){
						$p->sendTip("请先登录！");
						$event->setCancelled();
					}
				}
			}
		}
	}
	
	public function onBlockPlace(BlockPlaceEvent $event){
		$name = $event->getPlayer()->getName();
		if(isset($this->players[$name])){
			$ppp = &$this->players[$name];
			if($ppp["IsLogin"] == 0){
				$event->getPlayer()->sendTip("请先登录！");
				$event->setCancelled();
			}
		}
	}
	
	public function onPlayerDrop(PlayerDropItemEvent $event){
		$name = $event->getPlayer()->getName();
		if(isset($this->players[$name])){
			$ppp = &$this->players[$name];
			if($ppp["IsLogin"] == 0){
				$event->getPlayer()->sendTip("请先登录！");
				$event->setCancelled();
			}
		}
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
	
	public function onPlayerQuit(PlayerQuitEvent $event){
		//var_dump("211");
		$player = $event->getPlayer();
		$name = $event->getPlayer()->getName();
		$name = $event->getPlayer()->getName();
		if(isset($this->players[$name])){
			$ppp = &$this->players[$name];
			if($ppp["IsLogin"] == 1 ){
				$this->UpdatePlayer($name,$player->getAddress());
			}
			unset($this->players[$name]);
		}
	}

	public function onPlayerChat(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(isset($this->players[$name])){
			$ppp = &$this->players[$name];
			if($ppp["IsNew"] == 1){
				if($ppp["IsLogin"] == 0 and $ppp["IsVerify"] == 2){
					if($ppp["Check"] == $event->getMessage()){
						if($this->CreatePlayer($name,$player->getAddress(),$event->getMessage())){
							$event->getPlayer()->sendMessage(TextFormat::GREEN."注册成功~");
						    	$ppp["IsLogin"] = 1;
							$event->setCancelled();
						}else{
							$event->getPlayer()->sendMessage(TextFormat::RED."注册失败！请重试！");
						    	$ppp["IsLogin"] = 0;
							 $event->setCancelled();
							return;
						}
					}else{
						$event->getPlayer()->sendMessage(TextFormat::RED."密码错误，请重新输入！");
						$event->setCancelled();
					}
				}
				if($ppp["IsLogin"] == 0 and $ppp["IsVerify"] == 1){
					$ppp["Check"] = $event->getMessage();
					$ppp["IsVerify"] = 2;
					$event->getPlayer()->sendMessage(TextFormat::GREEN."请再次输入你的密码！");
					$event->setCancelled();
				}
				if($ppp["IsLogin"] == 0 and $ppp["IsVerify"] == 0){
					if($event->getMessage() != $ppp["Check"]){
						$event->getPlayer()->sendMessage(TextFormat::RED."验证码错误！");
						$event->getPlayer()->sendMessage(TextFormat::RED."请重新输入验证码 ".TextFormat::YELLOW.$ppp["Check"]);
						$event->setCancelled();
					}else{
						$ppp["IsVerify"] = 1;
						$event->getPlayer()->sendMessage(TextFormat::GREEN."请输入你的密码！");
						$event->setCancelled();
					}
				}
			}else{
				if($ppp["IsLogin"] == 0){
					if($this->hash(strtolower($name),$event->getMessage()) != $ppp["Password"]){
						$event->getPlayer()->sendMessage(TextFormat::RED."密码错误，请重新输入！");
						$event->setCancelled();
					}else{
						$ppp["IsLogin"] = 1;
						$event->getPlayer()->sendMessage(TextFormat::GREEN."登陆成功~开始享受吧~");
						$event->setCancelled();
					}
				}
			}
		}
	}
	
	private function hash($salt, $password){
		return bin2hex(hash("sha512", $password . $salt, true) ^ hash("whirlpool", $salt . $password, true));
	}

}
 
