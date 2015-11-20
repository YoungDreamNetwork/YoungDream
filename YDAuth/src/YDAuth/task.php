<?php

namespace YDAuth;

use pocketmine\scheduler\PluginTask;

class task extends PluginTask{
	
	private $plugin;
	public function __construct(YDAuth $plugin,\mysqli $db)
	{
		$this->plugin=$plugin;
		$this->db=$db;
		parent::__construct($plugin);
	}
	
	public function onRun($ck)
	{
		$this->db->ping();
	}	
}