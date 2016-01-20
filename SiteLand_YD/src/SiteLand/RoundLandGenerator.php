<?php
/**
 * Created by PhpStorm.
 * User: labi
 * Date: 2016/1/19
 * Time: 21:56
 */
use pocketmine\level\generator\Generator;
use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\item\Item;
use pocketmine\tile\Tile;
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Compound;
use pocketmine\level\format\FullChunk;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\Server;

class RoundLandGenerator extends Generator
{

    /** @var GenerationChunkManager */
    private $level;
    /** @var FullChunk */
    private $chunk1, $chunk2, $chunk3, $chunk4, $chunk5, $chunk6, $chunk7, $chunk8, $chunk9;
    /** @var Random */
    private $random;
    /** @var Populator[] */
    private $populators = [];

    public function getSettings()
    {
        return [];
    }

    public function getName()
    {
        return "roundland";
    }

    public function __construct(array $settings = [])
    {
        $this->options = $settings;
    }

    public function init(ChunkManager $level, Random $random)
    {
        $this->level = $level;
        $this->random = $random;
    }

    public function generateChunk($chunkX, $chunkZ)
    {
        $CX = ($chunkX % 3) < 0 ? (($chunkX % 3) + 3) : ($chunkX % 3);
        $CZ = ($chunkZ % 3) < 0 ? (($chunkZ % 3) + 3) : ($chunkZ % 3);
        if ($this->chunk1 === null) {
            $this->chunk1 = clone $this->level->getChunk($chunkX, $chunkZ);
            for ($x = 8; $x < 16; $x++) {
                for ($y = 0; $y < 10; $y++) {
                    $za = sqrt(49 - $x * $x);
                    $zb = -$za;
                    for ($zb; $zb < $za; $zb++) {
                        $this->chunk1->setBlockId($x, $y, $zb, Block::GRASS);//草
                    }


                }
            }
        }

    }

    public function populateChunk($chunkX, $chunkZ)
    {

    }

    public function getSpawn()
    {
        return new Vector3(0, $this->floorLevel, 0);
    }
}