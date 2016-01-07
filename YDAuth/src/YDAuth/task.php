<?php
namespace YDAuth;
use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;
class task extends AsyncTask{
	
	private $plugin;
	
	public function __construct($pluginname,$db,$table,$mode,$k1,$k2,$v,$cb)
	{
	    $this->pluginname=$pluginname;//插件名称
		$this->db=$db;//mysql数据库
		$this->table = $table;//table名
		$this->mode=$mode;//查询模式
		$this->k1=$k1;//键值1：通指玩家名
		$this->k2=$k2;//键值2：赋值2
		$this->v=$v;//值
		$this->cb=$v;//回传function名称
	}
	
	public function onRun()
	{
	    switch($this->mode){
	        case "query";
	        $this->setResult=($this->db->query("SELECT * FROM ".$this->table." where ID = '".$this->k1."'"));
	        break;
	        case "set"://支持：单变量与数组
	        break;
	        case "create";
	   foreach($this->v as $k=>$v){
	       if(isset($表名)){
	           $表名=$表名.",".$k;
	       }else{
	           $表名=$k;
	       }
	       if(isset($值)){
	           $值=$值.",'".$k."'";
	       }else{
	           $表名="'".$k."'";
	       }
	   }
	   $this->db->query = "insert into ".$this->table."(".$表名.") values(".$值.")";
		/*
		传入$this->v格式：
		$this->v = array(
		"a"=>"va",
		"b"=>"vb",
		"c"=>"vb\c",
		"d"=>"vd"
				)；
				相当于：
		$sql = "insert into ".$this->table."(a,b,c,d) values('va','vb','vc','vd')";
		*/
		$this->Setresult(true);
	        break;
	    }
	}
	public function onCompletion(Server server){
	    $爆炸=$this->cb;
	   Server::getInstance()->getPlugin($this->pluginname)->$爆炸($k1,$this->getResult());//回传function请给出两个参数
	}
	
}