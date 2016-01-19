<?php

/**
*
* 一个简单的领地生成器! By: MUedsa
*
**/
namespace SiteLand;

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

class SmallLandGenerator extends Generator{

	/** @var GenerationChunkManager */
	private $level;
	/** @var FullChunk */
	private $chunk1,$chunk2,$chunk3,$chunk4,$chunk5,$chunk6,$chunk7,$chunk8,$chunk9;
	/** @var Random */
	private $random;
	/** @var Populator[] */
	private $populators = [];

	public function getSettings(){
		return [];
	}

	public function getName(){
		return "land";
	}

	public function __construct(array $settings = []){
		$this->options = $settings;
	}

	public function init(ChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;
	}

	public function generateChunk($chunkX, $chunkZ){
		$CX = ($chunkX % 3) < 0 ? (($chunkX % 3) + 3) : ($chunkX % 3);
		$CZ = ($chunkZ % 3) < 0 ? (($chunkZ % 3) + 3) : ($chunkZ % 3);
		switch ($CX.":".$CZ) {
			case '0:0':
				/**
				* 16 # # # # @ & & & & & & & & & & &
				* 15 # # # # @ & & & & & & & & & & &
				* 14 # # # # @ & & & & & & & & & & &
				* 13 # # # # @ & & & & & & & & & & &
				* 12 # # # # @ & & & & & & & & & & &
				* 11 # # # # @ & & & & & & & & & & &
				* 10 # # # # @ & & & & & & & & & & &
				*  9 # # # # @ & & & & & & & & & & &
				*  8 # # # # @ & & & & & & & & & & &
				*  7 # # # # @ & & & & & & & & & & &
				*  6 # # # # @ S & & & & & & & & & &
				*  5 # # # # @ @ @ @ @ @ @ @ @ @ @ @
				*  4 # # # # # # # # # # # # # # # #
				*  3 # # # # # # # # # # # # # # # #
				*  2 # # # # # # # # # # # # # # # #
				*  1 # # # # # # # # # # # # # # # #
				*	 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6
    **-------------↑这上面的s那个地方设置牌子------------*/
				if ($this->chunk1 === null) {
					$this->chunk1 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 10; $y++) { 
								if ($x < 4 OR $z < 4) {
									$this->chunk1->setBlockId($x, $y, $z, Block::PLANK);//木方块
								}elseif($x > 4 AND $z > 4){
									$this->chunk1->setBlockId($x, $y, $z, Block::GRASS);//草方块
									$this->chunk1->setBlockId(4, 11, 4, Block::GLOWING_REDSTONE_ORE);
								}else{
									$this->chunk1->setBlockId($x, $y, $z, Block::DOUBLE_SLAB);
									if ($y == 9) {
										$this->chunk1->setBlockId($x, $y + 1, $z, Block::SLAB);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk1;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '0:1':
				/**            +
				* 16 # # # # @ & & & & & & & & & & &
				* 15 # # # # @ & & & & & & & & & & &
				* 14 # # # # @ & & & & & & & & & & &
				* 13 # # # # @ & & & & & & & & & & &
				* 12 # # # # @ & & & & & & & & & & &
				* 11 # # # # @ & & & & & & & & & & &
				* 10 # # # # @ & & & & & & & & & & &
				*  9 # # # # @ & & & & & & & & & & &
				*  8 # # # # @ & & & & & & & & & & &
				*  7 # # # # @ & & & & & & & & & & &
				*  6 # # # # @ & & & & & & & & & & &
				*  5 # # # # @ & & & & & & & & & & &
				*  4 # # # # @ & & & & & & & & & & &
				*  3 # # # # @ & & & & & & & & & & &
				*  2 # # # # @ & & & & & & & & & & &
				*  1 # # # # @ & & & & & & & & & & &
				*	 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6
				**/
				if ($this->chunk2 === null) {
					$this->chunk2 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 10; $y++) { 
								if ($x < 4) {
									$this->chunk2->setBlockId($x, $y, $z, Block::PLANK);//木方块
								}elseif($x > 4){
									$this->chunk2->setBlockId($x, $y, $z, Block::GRASS);//草方块
								}else{
									$this->chunk2->setBlockId($x, $y, $z, Block::DOUBLE_SLAB);
									if ($y == 9) {
										$this->chunk2->setBlockId($x, $y + 1, $z, Block::SLAB);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk2;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '0:2':
				/**
				* 16 # # # # # # # # # # # # # # # #
				* 15 # # # # # # # # # # # # # # # #
				* 14 # # # # # # # # # # # # # # # #
				* 13 # # # # # # # # # # # # # # # #
				* 12 # # # # @ @ @ @ @ @ @ @ @ @ @ @
				* 11 # # # # @ # # # # # # # # # # #
				* 10 # # # # @ # # # # # # # # # # #
				*  9 # # # # @ # # # # # # # # # # #
				*  8 # # # # @ # # # # # # # # # # #
				*  7 # # # # @ # # # # # # # # # # #
				*  6 # # # # @ # # # # # # # # # # #
				*  5 # # # # @ # # # # # # # # # # #
				*  4 # # # # @ # # # # # # # # # # #
				*  3 # # # # @ # # # # # # # # # # #
				*  2 # # # # @ # # # # # # # # # # #
				*  1 # # # # @ # # # # # # # # # # #
				*	 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 
				**/
				if ($this->chunk3 === null) {
					$this->chunk3 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 10; $y++) { 
								if ($x < 4 OR $z > 11) {
									$this->chunk3->setBlockId($x, $y, $z, Block::PLANK);//木方块
								}elseif($x > 4 AND $z <11){
									$this->chunk3->setBlockId($x, $y, $z, Block::GRASS);//草方块
								}else{
									$this->chunk3->setBlockId($x, $y, $z, Block::DOUBLE_SLAB);
									if ($y == 9) {
										$this->chunk3->setBlockId($x, $y + 1, $z, Block::SLAB);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk3;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '1:0':
				/**
				* 16 & & & & & & & & & & & & & & & &
				* 15 & & & & & & & & & & & & & & & &
				* 14 & & & & & & & & & & & & & & & &
				* 13 & & & & & & & & & & & & & & & &
				* 12 & & & & & & & & & & & & & & & &
				* 11 & & & & & & & & & & & & & & & &
				* 10 & & & & & & & & & & & & & & & &
				*  9 & & & & & & & & & & & & & & & &
				*  8 & & & & & & & & & & & & & & & &
				*  7 & & & & & & & & & & & & & & & &
				*  6 & & & & & & & & & & & & & & & &
				*  5 @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @
				*  4 # # # # # # # # # # # # # # # #
				*  3 # # # # # # # # # # # # # # # #
				*  2 # # # # # # # # # # # # # # # #
				*  1 # # # # # # # # # # # # # # # #
				*	 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 
				**/
				if ($this->chunk4 === null) {
					$this->chunk4 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 10; $y++) { 
								if ($z < 4) {
									$this->chunk4->setBlockId($x, $y, $z, Block::PLANK);//木方块
								}elseif($z > 4){
									$this->chunk4->setBlockId($x, $y, $z, Block::GRASS);//草方块
								}else{
									$this->chunk4->setBlockId($x, $y, $z, Block::DOUBLE_SLAB);
									if ($y == 9) {
										$this->chunk4->setBlockId($x, $y + 1, $z, Block::SLAB);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk4;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '2:0':
				/**
				* 16 & & & & & & & & & & & @ # # # #
				* 15 & & & & & & & & & & & @ # # # #
				* 14 & & & & & & & & & & & @ # # # #
				* 13 & & & & & & & & & & & @ # # # #
				* 12 & & & & & & & & & & & @ # # # #
				* 11 & & & & & & & & & & & @ # # # #
				* 10 & & & & & & & & & & & @ # # # #
				*  9 & & & & & & & & & & & @ # # # #
				*  8 & & & & & & & & & & & @ # # # #
				*  7 & & & & & & & & & & & @ # # # #
				*  6 & & & & & & & & & & & @ # # # #
				*  5 @ @ @ @ @ @ @ @ @ @ @ @ # # # #
				*  4 # # # # # # # # # # # # # # # #
				*  3 # # # # # # # # # # # # # # # #
				*  2 # # # # # # # # # # # # # # # #
				*  1 # # # # # # # # # # # # # # # #
				*	 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 
				**/
				if ($this->chunk5 === null) {
					$this->chunk5 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 10; $y++) { 
								if ($x > 11 OR $z < 4) {
									$this->chunk5->setBlockId($x, $y, $z, Block::PLANK);//木方块
								}elseif($x < 11 AND $z > 4){
									$this->chunk5->setBlockId($x, $y, $z, Block::GRASS);//草方块
								}else{
									$this->chunk5->setBlockId($x, $y, $z, Block::DOUBLE_SLAB);
									if ($y == 9) {
										$this->chunk5->setBlockId($x, $y + 1, $z, Block::SLAB);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk5;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '2:1':
				/**
				* 16 & & & & & & & & & & & @ # # # # 
				* 15 & & & & & & & & & & & @ # # # # 
				* 14 & & & & & & & & & & & @ # # # # 
				* 13 & & & & & & & & & & & @ # # # # 
				* 12 & & & & & & & & & & & @ # # # # 
				* 11 & & & & & & & & & & & @ # # # # 
				* 10 & & & & & & & & & & & @ # # # # 
				*  9 & & & & & & & & & & & @ # # # # 
				*  8 & & & & & & & & & & & @ # # # # 
				*  7 & & & & & & & & & & & @ # # # # 
				*  6 & & & & & & & & & & & @ # # # # 
				*  5 & & & & & & & & & & & @ # # # # 
				*  4 & & & & & & & & & & & @ # # # # 
				*  3 & & & & & & & & & & & @ # # # # 
				*  2 & & & & & & & & & & & @ # # # # 
				*  1 & & & & & & & & & & & @ # # # # 
				*	 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 
				**/
				if ($this->chunk6 === null) {
					$this->chunk6 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 10; $y++) { 
								if ($x > 11) {
									$this->chunk6->setBlockId($x, $y, $z, Block::PLANK);//木方块
								}elseif($x < 11){
									$this->chunk6->setBlockId($x, $y, $z, Block::GRASS);//草方块
								}else{
									$this->chunk6->setBlockId($x, $y, $z, Block::DOUBLE_SLAB);
									if ($y == 9) {
										$this->chunk6->setBlockId($x, $y + 1, $z, Block::SLAB);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk6;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '2:2':
				/**
				* 16 # # # # # # # # # # # # # # # #
				* 15 # # # # # # # # # # # # # # # #
				* 14 # # # # # # # # # # # # # # # #
				* 13 # # # # # # # # # # # # # # # #
				* 12 @ @ @ @ @ @ @ @ @ @ @ @ # # # #
				* 11 & & & & & & & & & & & @ # # # # 
				* 10 & & & & & & & & & & & @ # # # # 
				*  9 & & & & & & & & & & & @ # # # # 
				*  8 & & & & & & & & & & & @ # # # # 
				*  7 & & & & & & & & & & & @ # # # # 
				*  6 & & & & & & & & & & & @ # # # # 
				*  5 & & & & & & & & & & & @ # # # # 
				*  4 & & & & & & & & & & & @ # # # # 
				*  3 & & & & & & & & & & & @ # # # # 
				*  2 & & & & & & & & & & & @ # # # # 
				*  1 & & & & & & & & & & & @ # # # # 
				*	 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 
				**/
				if ($this->chunk7 === null) {
					$this->chunk7 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 10; $y++) { 
								if ($x > 11 OR $z > 11) {
									$this->chunk7->setBlockId($x, $y, $z, Block::PLANK);//木方块
								}elseif($x < 11 AND $z < 11){
									$this->chunk7->setBlockId($x, $y, $z, Block::GRASS);//草方块
								}else{
									$this->chunk7->setBlockId($x, $y, $z, Block::DOUBLE_SLAB);
									if ($y == 9) {
										$this->chunk7->setBlockId($x, $y + 1, $z, Block::SLAB);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk7;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '1:2':
				/**
				* 16 # # # # # # # # # # # # # # # #
				* 15 # # # # # # # # # # # # # # # #
				* 14 # # # # # # # # # # # # # # # #
				* 13 # # # # # # # # # # # # # # # #
				* 12 @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @
				* 11 & & & & & & & & & & & & & & & &
				* 10 & & & & & & & & & & & & & & & &
				*  9 & & & & & & & & & & & & & & & &
				*  8 & & & & & & & & & & & & & & & &
				*  7 & & & & & & & & & & & & & & & &
				*  6 & & & & & & & & & & & & & & & &
				*  5 & & & & & & & & & & & & & & & &
				*  4 & & & & & & & & & & & & & & & &
				*  3 & & & & & & & & & & & & & & & &
				*  2 & & & & & & & & & & & & & & & &
				*  1 & & & & & & & & & & & & & & & &
				*	 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 
				**/
				if ($this->chunk8 === null) {
					$this->chunk8 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 10; $y++) { 
								if ($z > 11) {
									$this->chunk8->setBlockId($x, $y, $z, Block::PLANK);//木方块
								}elseif($z < 11){
									$this->chunk8->setBlockId($x, $y, $z, Block::GRASS);//草方块
								}else{
									$this->chunk8->setBlockId($x, $y, $z, Block::DOUBLE_SLAB);
									if ($y == 9) {
										$this->chunk8->setBlockId($x, $y + 1, $z, Block::SLAB);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk8;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			default:
				/**
				* 16 & & & & & & & & & & & & & & & &
				* 15 & & & & & & & & & & & & & & & &
				* 14 & & & & & & & & & & & & & & & &
				* 13 & & & & & & & & & & & & & & & &
				* 12 & & & & & & & & & & & & & & & &
				* 11 & & & & & & & & & & & & & & & &
				* 10 & & & & & & & & & & & & & & & &
				*  9 & & & & & & & & & & & & & & & &
				*  8 & & & & & & & & & & & & & & & &
				*  7 & & & & & & & & & & & & & & & &
				*  6 & & & & & & & & & & & & & & & &
				*  5 & & & & & & & & & & & & & & & &
				*  4 & & & & & & & & & & & & & & & &
				*  3 & & & & & & & & & & & & & & & &
				*  2 & & & & & & & & & & & & & & & &
				*  1 & & & & & & & & & & & & & & & &
				*	 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 
				**/
				if ($this->chunk9 === null) {
					$this->chunk9 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 10; $y++) { 
								$this->chunk9->setBlockId($x, $y, $z, Block::GRASS);//草方块
							}
						}
					}
				}
				$chunk = clone $this->chunk9;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;
		}
	}

	public function populateChunk($chunkX, $chunkZ){

	}

	public function getSpawn(){
		return new Vector3(0, $this->floorLevel, 0);
	}

}