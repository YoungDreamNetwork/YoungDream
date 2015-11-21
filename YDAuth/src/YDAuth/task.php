<?php

namespace YDAuth;

use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;


class YDAuth extends AsyncTask{
	
	
	private $db;
	
	public function ping($db,$main){
		$this->db = $db;
		$this->main=$main;
	}
	
	public function onRun($ck)
	{
		$status=$this->db->ping();
		$this->getstatus=$stauts;
		if($status == false)
		{
			$this->main->MysqlConnect();
		}
	}

	
}