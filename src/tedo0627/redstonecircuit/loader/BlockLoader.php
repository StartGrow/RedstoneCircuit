<?php

namespace tedo0627\redstonecircuit\loader;

use Closure;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemFactory;

/**
 * Manages the loading and registration of custom blocks.
 */
class BlockLoader extends Loader {

    private Block $block;
    private bool $addCreative;

    /**
     * Creates a new BlockLoader instance.
     *
     * @param string $name The name of the loader.
     * @param int $id The block ID to base the new block on.
     * @param Closure $callback Callback to create the block.
     * @param string|null $class Optional class name for the block identifier.
     * @return self
     * @throws \InvalidArgumentException if the block ID is invalid.
     */
    public static function createBlock(string $name, int $id, Closure $callback, ?string $class = null): self {
        $factory = BlockFactory::getInstance();
        $oldBlock = $factory->get($id);
        if ($oldBlock === null) {
            throw new \InvalidArgumentException("Invalid block ID: $id");
        }
        $bid = $oldBlock->getIdInfo();
        if ($class !== null) {
            $bid = new BlockIdentifier($bid->getBlockId(), $bid->getVariant(), $bid->getItemId(), $class);
        }
        $block = $callback($bid, $oldBlock->getName(), $oldBlock->getBreakInfo());

        return new self($name, $block);
    }

    /**
     * BlockLoader constructor.
     *
     * @param string $name The name of the loader.
     * @param Block $block The block to register.
     * @param bool $addCreative Whether to add the block to the Creative inventory.
     */
    public function __construct(string $name, Block $block, bool $addCreative = false) {
        parent::__construct($name);

        $this->block = $block;
        $this->addCreative = $addCreative;
    }

    /**
     * Registers the block and optionally adds it to the Creative inventory.
     */
    public function load(): void {
        BlockFactory::getInstance()->register($this->block, true);
        if ($this->addCreative) {
            CreativeInventory::getInstance()->add($this->block->asItem());
        }
    }
}
