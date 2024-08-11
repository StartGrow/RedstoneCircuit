<?php

namespace tedo0627\redstonecircuit\loader;

use pocketmine\tile\TileFactory;
use pocketmine\tile\TileType;

class BlockEntityLoader extends Loader {

    private string $className;
    private array $saveNames;

    /**
     * BlockEntityLoader constructor.
     *
     * @param string $name The name of the loader.
     * @param string $className The class name of the tile entity.
     * @param array $saveNames The names used to save the tile entity.
     */
    public function __construct(string $name, string $className, array $saveNames) {
        parent::__construct($name);

        $this->className = $className;
        $this->saveNames = $saveNames;
    }

    /**
     * Registers the block entity (tile entity) with the TileFactory.
     */
    public function load(): void {
        TileFactory::getInstance()->registerTile($this->className, $this->saveNames);
    }
}
