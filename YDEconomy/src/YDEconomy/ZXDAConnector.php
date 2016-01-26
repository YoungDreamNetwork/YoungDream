<?php
/*
ZXDA Connector 对接SDK
Version: 1.0.0.0

Functions:
1.检查是否已安装ZXDA Connector:
	ZXDA_Connector::isInstalled();
2.获取ZXDA Connector的API版本:
	ZXDA_Connector::getAPIVersion();
3.给某玩家添加点券:
	ZXDA_Connector::addCoupons($PlayerName,$Count);
4.获取某玩家的点券数量:
	ZXDA_Connector::getCoupons($PlayerName);
5.设置某玩家的点券数量:
	ZXDA_Connector::setCoupons($PlayerName,$Count);
6.扣除某玩家的点券:
	ZXDA_Connector::takeCoupons($PlayerName,$Count);
7.获取所有玩家的点券数量:
	ZXDA_Connector::getAllCoupons();

Copyright © 2015 ZXDA Development Group.All rights reserved.
*/

namespace YDEconomy;

class ZXDA_Connector{
	private static $_SDK_VERSION = 1000;
	private static $className = '\\ZXDAConnector\\Main';

	public static function isInstalled(){
		return class_exists(self::$className, false);
	}

	public static function getAPIVersion(){
		if(!self::isInstalled()){
			return 0;
		}
		$className = self::$className;
		return $className::getAPIVersion();
	}

	public static function addCoupons($player, $count){
		if(self::getAPIVersion() < self::$_SDK_VERSION){
			return false;
		}
		$className = self::$className;
		return $className::addCoupons($player, $count);
	}

	public static function getCoupons($player){
		if(self::getAPIVersion() < self::$_SDK_VERSION){
			return false;
		}
		$className = self::$className;
		return $className::getCoupons($player);
	}

	public static function setCoupons($player, $count){
		if(self::getAPIVersion() < self::$_SDK_VERSION){
			return false;
		}
		$className = self::$className;
		return $className::setCoupons($player, $count);
	}

	public static function takeCoupons($player, $count){
		if(self::getAPIVersion() < self::$_SDK_VERSION){
			return false;
		}
		$className = self::$className;
		return $className::takeCoupons($player, $count);
	}

	public static function getAllCoupons(){
		if(self::getAPIVersion() < self::$_SDK_VERSION){
			return false;
		}
		$className = self::$className;
		return $className::getAllCoupons();
	}
}
