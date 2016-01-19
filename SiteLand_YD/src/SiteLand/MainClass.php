<?php

namespace SiteLand;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;

class MainClass extends PluginBase{
	
public function __construct(){
		Generator::addGenerator(SmallLandGenerator::class, "land");
	}
}