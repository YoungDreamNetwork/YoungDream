<?php
namespace YDEconomy;

use pocketmine\scheduler\PluginTask;

class task extends PluginTask{
    
    private $plugin;
    
    public function __construct(YDEconomy $plugin,\mysqli $db)
    {
        $this->plugin=$plugin;
        $this->db=$db;
        parent::__construct($plugin);
    }
    
    public function onRun($ck)
    {
        $status=$this->db->ping();
        $this-plugin->status=$stauts;
        if($status == false)
        {
            $this->plugin->MysqlConnect();
        }
    }
    
}