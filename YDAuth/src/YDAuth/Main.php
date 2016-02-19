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

use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\CallbackTask;
class Main extends PluginBase implements Listener{
    private static $instance;
    private $IS_LOGINED;
    private $IS_REGISTER;
    private $DB = 
        array(
        "HOST"=>"127.0.0.1",
        "PORT"=>"3389",
        "USER"=>"root",
        "PASWD"=>"passwd",
        "DB"=>"AuthorPE",
        "DATA_TABLE"=>"AUTH_DATA",
        "BANID_TABLE"=>"BANID",
        "BANIP_TABLE"=>"BANIP",
        "BANCID_TABLE"=>"BANCID",
        "SERVER_NAME"=>"YOUNGDREAM",
        );
    private $LOGIN_SET = array(
        "AUTO_LOGIN"=>true,
        "TIME_EFFECTIVE_HOUR"=>3,
        "FORCE_CANNOT_MOVE"=>true,
        "FORCE_MULTISERVER"=>false
        );
    
/*
PM-API LISTENER
*/
    public function onEnable(){
        
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
    }
    public function onPreJoin(\pocketmine\event\player\PlayerPreLoginEvent $e){
        $p=$e->getPlayer();
        $pn=$p->getName();
        $result=false;
        /*
        不太确定是否需要这个
        foreach($this->getServer()->getOnlinePlayers() as $k){
            if($k->getName()==$pn and $this->isLogined($pn)){
                $result=true;
            }
        } 
        */
        if(!$result){
            $data=array(
                "NAME"=>$pn,
                "IP"=>$p->getAddress(),
                "CID"=>$p->getClientId(),
                );
            if($this->LOGIN_SET["FORCE_CANNOT_MOVE"]){
                $p->setMovementSpeed(0);
            }
            $task=new \AuthorPE\firstVirfyTask($this->DB,$this->LOGIN_SET,$data);//首次验证
        }else{
            $p->close("There is a player already in this server.\n重名登录.\nTry to change the name.尝试更换名字");
        }
    }
    public function onQuit(\pocketmine\event\player\PlayerQuitEvent $e){
        $pn=$e->getPlayer()->getName();
        if($this->isLogin($pn)){//如果登陆过
            if($this->LOGIN_SET["FORCE_MULTISERVER"]){//如果有多服务器限制
            $task = new \AuthorPE\logOutTask($this->DB,$pn);//登出清除服务器信息
            }
        }
        if(isset($this->IS_LOGINED[$pn])){//清除数组
            unset($this->IS_LOGINED[$pn]);
        }
        if(isset($this->IS_REGISTER[$pn])){
            unset($this->IS_REGISTER[$pn]);
        }
    }
/*
AuthorPE-API
*/

/*
UTILS
*/
    public function isLogin($pn){//仅用于判断是否登录状态
        if(isset($this->IS_LOGINED[$pn])){
            return $this->IS_LOGINED[$pn];
        }else{
            return false;
        }
    }
    public function isRegistered($pn){//仅用于判断是否注册,null为未获取
        if(isset($this->IS_REGISTER[$pn])){
            return $this->IS_REGISTER[$pn];
        }else{
            return null;
        }
    }
}