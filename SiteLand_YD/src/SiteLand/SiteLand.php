<?php

namespace SiteLand;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use SiteLand\MainClass;
use YDEconomy\YDEconomy;

class SiteLand extends PluginBase implements Listener
{

    protected $conf;


    public function onEnable()
    {
        $this->path = $this->getDataFolder();
        @mkdir($this->path);
        $this->conf = (new Config($this->path . "SiteLand.yml", Config::YAML, array()))->getAll();
        $this->confp = (new Config($this->path . "landinfo.yml", Config::YAML, array("landname" => "land", "price" => "500000", "landlimit" => "2")))->getAll();
        $this->level = $this->confp["landname"];
        $this->price = $this->confp["price"];
        $this->limit = $this->confp["landlimit"];

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        new MainClass();


    }


    public function onPlayerInteract(PlayerInteractEvent $event)
    {
        $b = $event->getBlock();
        if ($b->getLevel()->getFolderName() == $this->level) {
            $player = $event->getPlayer();
            $this->checkPermission($player, $b, $event);
            $n = ($b->x + 44) / 48;
            $k = ($b->z + 44) / 48;
            if (is_int($n) and is_int($k)) {
                $player = $event->getPlayer();
                $data = YDEconomy::getInstance()->SeeMoney($player->getName());
                $bf = $b->x . "-" . $b->z;
                if (isset($this->conf[$bf])) {
                    $player->sendMessage("§c[SiteLand] 此领地已经有主人");
                } else {
                    if ($data < $this->price) {
                        $player->sendMessage("§a[SiteLand] 你的游戏币不够买此领地,需要 [ " . $this->price . " ] 游戏币");
                    } else {
                        $n = 0;
                        if (!$player->isOp()) {
                            foreach ($this->conf as $lands) {
                                if (strtolower($player->getName()) == $lands["owner"]) {
                                    $n++;
                                }
                            }
                            if ($n >= $this->limit) {
                                $player->sendMessage("§c[SiteLand] 你已经购买 [ $n ] 块地皮，不能再购买");
                                return;
                            }
                        }
                        $this->conf[$bf] = array(
                            "owner" => strtolower($player->getName()),
                            "p1x" => $b->x,
                            "p1z" => $b->z,
                            "p2x" => $b->x + 39,
                            "p2z" => $b->z + 39,
                            "guest" => array()
                        );
                        YDEconomy::getInstance()->ReduceMoney($player->getName(), $this->price);
                        $player->sendMessage("§b[SiteLand] 已经花费 [ " . $this->price . " ] 游戏币购买领地");
                        $this->save();
                    }//设置文件
                }//存在文件结束
            }//判断红石
        }
        unset($b, $player, $n, $k, $data, $bf, $event);
    }//function结束

    public function onBlockBreak(BlockBreakEvent $event)
    {
        $b = $event->getBlock();
        if ($b->getLevel()->getFolderName() == $this->level) {
            $player = $event->getPlayer();
            $this->checkPermission($player, $b, $event);
            $n = ($b->x + 44) / 48;
            $k = ($b->z + 44) / 48;
            if (is_int($n) and is_int($k)) {
                $event->setCancelled();
                $bf = $b->x . "-" . $b->z;
                if (isset($this->conf[$bf])) {
                    $info = $this->conf[$bf];
                    if (strtolower($player->getName()) == $info["owner"]) {
                        $player->sendMessage("§b[SiteLand] 已经卖出领地获得 [ " . ($this->price / 2) . " ] 游戏币");
                        YDEconomy::getInstance()->addMoney($player->getName(), $this->price / 2);
                        unset($this->conf[$bf]);
                        $this->save();
                    } else {
                        if ($player->isOp()) {
                            unset($this->conf[$bf]);
                            $this->save();
                            $player->sendMessage("§d[SiteLand] 已经删除领地");
                            return;
                        }
                        $player->sendMessage("§b[SiteLand] 不是你的领地");
                    }
                }
            }
        }
        unset($b, $player, $n, $k, $info, $bf, $event);
    }

    public function save()
    {
        $cfg = new Config($this->path . "SiteLand.yml", Config::YAML, array());
        $cfg->setAll($this->conf);
        $cfg->save();
        $this->conf = (new Config($this->path . "SiteLand.yml", Config::YAML, array()))->getAll();
        unset($cfg);
    }

    public function checkPermission($player, $b, $event)
    {
        if ($this->conf == \Null and !$player->isOp()) {
            $event->setCancelled();
            return;
        } else {
            foreach ($this->conf as $lands) {
                if ($b->x > $lands["p1x"] And $b->x < $lands["p2x"] And $b->z > $lands["p1z"] And $b->z < $lands["p2z"]) {
                    $check = 0;
                    $this->checkLand($lands, $player, $event);
                }//判断为领地
            }
        }//foreach
        if (!isset($check)) {
            if (!$player->isOp()) {
                $event->setCancelled();
            }
        }
        unset($player, $b, $event, $lands, $check);
    }//function结束

    public function checkLand($lands, $player, $event)
    {
        if (strtolower($player->getName()) !== $lands["owner"] and !in_array(strtolower($player->getName()), $lands["guest"]) and !$player->isOp()) {
            $event->setCancelled();
            $player->sendMessage("§5[SiteLand] 此领地主人 [ " . $lands["owner"] . " ] 你不能互动");
        }//判断主人客人结束
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
    {
        switch ($cmd->getName()) {
            case "giveland":
                if (isset($args[0])) {
                    if ($sender instanceof Player) {
                        if ($sender->getLevel()->getFolderName() !== $this->level) {
                            $sender->sendMessage("此地图不是领地地图");
                            return;
                        }
                        $x = $sender->x;
                        $z = $sender->z;
                        if (!$this->conf == null) {
                            foreach ($this->conf as $lands) {
                                if ($x > $lands["p1x"] And $x < $lands["p2x"] And $z > $lands["p1z"] And $z < $lands["p2z"]) {
                                    $check = 0;
                                    $bf = $lands["p1x"] . "-" . $lands["p1z"];
                                    if (strtolower($sender->getName()) == $lands["owner"]) {
                                        $this->conf[$bf]["owner"] = strtolower($args[0]);
                                        $this->save();
                                        $sender->sendMessage("§a[SiteLand] 成功把领地送给 [ " . $args[0] . " ]");
                                    } else {
                                        if ($sender->isOp()) {
                                            $this->conf[$bf]["owner"] = strtolower($args[0]);
                                            $this->save();
                                            $sender->sendMessage("§a[SiteLand] 成功强行把领地送给 [ " . $args[0] . " ]");
                                            return true;
                                            return;
                                        }
                                        $sender->sendMessage("§c[SiteLand] 你不是领地主人");
                                    }
//判断领地主人
                                }
                            }//foreach
                            if (!isset($check)) {
                                $sender->sendMessage("§e[SiteLand] 附近没有领地");
                            }
                        } else {
                            $sender->sendMessage("§e[SiteLand] 附近没有领地");
                        }
                    } else {
                        $sender->sendMessage("后台给nmb");
                    }
                }//存在args0
                else {
                    $sender->sendMessage("§c[SiteLand] /giveland [ 名字 ]");
                }
                return true;
                break;
            case "guest":
                if (isset($args[0])) {
                    if ($sender instanceof Player) {
                        if ($sender->getLevel()->getFolderName() !== $this->level) {
                            $sender->sendMessage("此地图不是领地地图");
                            return true;
                            return;
                        }
                        $x = $sender->x;
                        $z = $sender->z;
                        if (!$this->conf == null) {
                            foreach ($this->conf as $lands) {
                                if ($x > $lands["p1x"] And $x < $lands["p2x"] And $z > $lands["p1z"] And $z < $lands["p2z"]) {
                                    $check = 0;
                                    $bf = $lands["p1x"] . "-" . $lands["p1z"];
                                    if (strtolower($sender->getName()) == $lands["owner"]) {
                                        if (in_array(strtolower($args[0]), $lands["guest"])) {
                                            $founded = array_search(strtolower($args[0]), $this->conf[$bf]["guest"]);
                                            array_splice($this->conf[$bf]["guest"], $founded, 1);
                                            $sender->sendMessage("§a[SiteLand] 成功把 [ " . $args[0] . " ] 移出领地访客列表");
                                        } else {
                                            $this->conf[$bf]["guest"][] = strtolower($args[0]);
                                            $sender->sendMessage("§a[SiteLand] 成功把 [ " . $args[0] . " ] 添加进领地访客列表");
                                        }
                                        $this->save();
                                    } else {
                                        $sender->sendMessage("§c[SiteLand] 你不是领地主人");
                                    }
//判断领地主人
                                }
                            }//foreach
                            if (!isset($check)) {
                                $sender->sendMessage("§e[SiteLand] 附近没有领地");
                            }
                        } else {
                            $sender->sendMessage("§e[SiteLand] 附近没有领地");
                        }
                    } else {
                        $sender->sendMessage("后台给nmb");
                    }
                }//存在args0
                else {
                    $sender->sendMessage("§c[SiteLand] /guest [ 名字 ]");
                }
                return true;
                break;
            case "myguest":
                if ($sender instanceof Player) {
                    if ($sender->getLevel()->getFolderName() !== $this->level) {
                        $sender->sendMessage("此地图不是领地地图");
                        return true;
                        return;
                    }
                    $x = $sender->x;
                    $z = $sender->z;
                    if (!$this->conf == null) {
                        foreach ($this->conf as $lands) {
                            if ($x > $lands["p1x"] And $x < $lands["p2x"] And $z > $lands["p1z"] And $z < $lands["p2z"]) {
                                $check = 0;
                                if (strtolower($sender->getName()) == $lands["owner"]) {
                                    $sender->sendMessage("§b[SiteLand] ______访客列表______
§5" . implode("\n§5", $lands["guest"]));
                                } else {
                                    if ($sender->isOp()) {
                                        $sender->sendMessage("§b[SiteLand] ______访客列表______
§5" . implode("\n§5", $lands["guest"]));
                                        return true;
                                        return;
                                    }
                                    $sender->sendMessage("§c[SiteLand] 你不是领地主人");
                                }
//判断领地主人
                            }
                        }//foreach
                        if (!isset($check)) {
                            $sender->sendMessage("§e[SiteLand] 附近没有领地");
                        }
                    } else {
                        $sender->sendMessage("§e[SiteLand] 附近没有领地");
                    }
                } else {
                    $sender->sendMessage("后台看nmb");
                }
                return true;
                break;
            case "landinfo":
                if ($sender instanceof Player) {
                    if ($sender->getLevel()->getFolderName() !== $this->level) {
                        $sender->sendMessage("此地图不是领地地图");
                        return true;
                        return;
                    }
                    $x = $sender->x;
                    $z = $sender->z;
                    if (!$this->conf == null) {
                        foreach ($this->conf as $lands) {
                            if ($x > $lands["p1x"] And $x < $lands["p2x"] And $z > $lands["p1z"] And $z < $lands["p2z"]) {
                                $check = 0;
                                $sender->sendMessage("§a[SiteLand] 领地主人 [ " . $lands["owner"] . " ] 领地价格 [ " . $this->price . " ] 游戏币");
                            }
                        }//foreach
                        if (!isset($check)) {
                            $sender->sendMessage("§e[SiteLand] 附近没有领地");
                        }
                    } else {
                        $sender->sendMessage("§e[SiteLand] 附近没有领地");
                    }
                } else {
                    $sender->sendMessage("后台看nmb");
                }
                return true;
                break;
            case "mylands":
                if ($sender instanceof Player) {
                    if (!$this->conf == null) {
                        $tags = [];
                        foreach ($this->conf as $tag => $lands) {
                            if (strtolower($sender->getName()) == $lands["owner"]) {
                                $tag = explode("-", $tag);
                                $tag = $tag[0] . $tag[1];
                                $tags[] = $tag;
                            }
                        }//foreach
                        $sender->sendMessage("§b[SiteLand] ______我的地皮列表______
§e" . implode("\n§a", $tags));
                    } else {
                        $sender->sendMessage("§c[SiteLand] 你没有地皮");
                    }
                } else {
                    $sender->sendMessage("后台看nmb");
                }
                return true;
                break;
            case "seelands":
                if (isset($args[0])) {
                    if (!$this->conf == null) {
                        $tags = [];
                        foreach ($this->conf as $tag => $lands) {
                            if (strtolower($args[0]) == $lands["owner"]) {
                                $per = "§c你没有此地皮的建筑权限";
                                if ($sender instanceof Player) {
                                    $pn = strtolower($sender->getName());
                                    if ($pn == $lands["owner"]) {
                                        $per = "§b地皮可以建筑 ( 地皮主人 )";
                                    } else
                                        if (in_array($pn, $lands["guest"])) {
                                            $per = "§b地皮可以建筑 ( 地皮客人 )";
                                        } else
                                            if ($sender->isOp()) {
                                                $per = "§b地皮可以建筑 ( OP )";
                                            }
                                } else {
                                    $per = "后台权限";
                                }
                                $tag = explode("-", $tag);
                                $tag = "[ " . $tag[0] . $tag[1] . " ] §e权限 : $per";
                                $tags[] = $tag;
                            }
                        }//foreach
                        $sender->sendMessage("§b[SiteLand] ______[§e $args[0] §b] 的地皮编号列表______
§a" . implode("\n§a", $tags));
                    } else {
                        $sender->sendMessage("§c[SiteLand] [ $args[0] ] 没有地皮");
                    }
                } else {
                    $sender->sendMessage("§b[SiteLand] /seelands [ 名字 ]");
                }
                return true;
                break;
            case "tpland":
                if ($sender instanceof Player) {
                    if (isset($args[0])) {
                        if (!$this->conf == null) {
                            foreach ($this->conf as $tag => $lands) {
                                $tag = explode("-", $tag);
                                $tag = $tag[0] . $tag[1];
                                if ($args[0] == $tag) {
                                    $check = 0;
                                    $pn = strtolower($sender->getName());
                                    if (($pn == $lands["owner"] or in_array($pn, $lands["guest"])) or $sender->isOp()) {
                                        $host = $lands["owner"];
                                        $pos = new \pocketmine\level\Position($lands["p1x"], 15, $lands["p1z"], $this->getServer()->getLevelByName($this->level));
                                        $sender->teleport($pos);
                                        $sender->sendMessage("§b[SiteLand] 已经传送到编号为 [ $args[0] ] 主人 [ $host ] 的地皮");
                                    } else {
                                        $sender->sendMessage("§b[SiteLand] 你没有编号为 [ $args[0] ] 的地皮的编辑权限");
                                    }
                                }
                            }//foreach
                            if (!isset($check)) {
                                $sender->sendMessage("§b[SiteLand] 没有找到编号 [ $args[0] ] 的地皮");
                            }
                        } else {
                            $sender->sendMessage("§c[SiteLand] 没有编号为 [ $args[0] ] 的地皮");
                        }
                    } else {
                        $sender->sendMessage("§b[SiteLand] /tpland [ 领地编号 ]");
                    }
                } else {
                    $sender->sendMessage("后台去nmb");
                }
                return true;
                break;
            case "checkhost":
                if (isset($args[0])) {
                    if (!$this->conf == null) {
                        foreach ($this->conf as $tag => $lands) {
                            $tag = explode("-", $tag);
                            $tag = $tag[0] . $tag[1];
                            if ($tag == $args[0]) {
                                $check = 0;
                                $guest = "";
                                if ($sender->isOp()) {
                                    $guest = "§b[UniteLand] ______[§e $args[0] §b] 地皮客人列表______
§a" . implode("\n§a", $lands["guest"]);
                                }
                                $host = $lands["owner"];
                                $sender->sendMessage("§b[UniteLand] 编号为 [ $args[0] ] 的地皮主人是 [ $host ]");
                                $sender->sendMessage($guest);
                            }
                        }//foreach
                        if (!isset($check)) {
                            $sender->sendMessage("§c[UniteLand] 没有找到编号为 [ $args[0] ] 的地皮信息");
                        }
                    } else {
                        $sender->sendMessage("§c[UniteLand] 没有编号为 [ $args[0] ] 和地皮");
                    }
                } else {
                    $sender->sendMessage("§c[UniteLand] /checkhost [ 领地编号 ]");
                }
                return true;
                break;
        }//switch结束
        unset($args, $sender, $x, $z, $check);
    }//function结束


}




