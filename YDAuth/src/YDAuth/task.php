<?php
namespace YDAuth;
use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;
class task extends AsyncTask{
	
	private $plugin;
	
	public function __construct($pluginname,$db,$mode,$k1,$k2,$v,$cb)
	{
	    $this->pluginname=$pluginname;//插件名称
		$this->db=$db;//mysql数据库
		$this->mode=$mode;//查询模式
		$this->k1=$k1;//键值1：通指玩家名
		$this->k2=$k2;//键值2：赋值2
		$this->v=$v;//表名
		$this->cb=$v;//回传function名称
	}
	
	public function onRun()
	{
	    switch($this->mode){
	        case "query";
	        break;
	        case "set";
	        break;
	        case "create";
	        break;
	    }
	}
	public function onCompletion(Server server){
	    $爆炸=$this->cb;
	   Server::getInstance()->getPlugin($this->pluginname)->$爆炸($k1,$this->getResult());//回传function请给出两个参数
	}
	
}