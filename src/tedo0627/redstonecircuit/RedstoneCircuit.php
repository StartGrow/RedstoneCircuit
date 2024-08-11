<?php

namespace tedo0627\redstonecircuit;

use Closure;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\BlockToolType;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ToolTier;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockFactory;
use ReflectionMethod;
use tedo0627\redstonecircuit\block\BlockTable;
use tedo0627\redstonecircuit\block\entity\BlockEntityChest;
use tedo0627\redstonecircuit\block\entity\BlockEntityCommand;
use tedo0627\redstonecircuit\block\entity\BlockEntityDispenser;
use tedo0627\redstonecircuit\block\entity\BlockEntityDropper;
use tedo0627\redstonecircuit\block\entity\BlockEntityHopper;
use tedo0627\redstonecircuit\block\entity\BlockEntityMoving;
use tedo0627\redstonecircuit\block\entity\BlockEntityNote;
use tedo0627\redstonecircuit\block\entity\BlockEntityObserver;
use tedo0627\redstonecircuit\block\entity\BlockEntityPistonArm;
use tedo0627\redstonecircuit\block\entity\BlockEntitySkull;
use tedo0627\redstonecircuit\block\entity\BlockEntityTarget;
use tedo0627\redstonecircuit\block\mechanism\BlockActivatorRail;
use tedo0627\redstonecircuit\block\mechanism\BlockCommand;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;
use tedo0627\redstonecircuit\block\mechanism\BlockDropper;
use tedo0627\redstonecircuit\block\mechanism\BlockFenceGate;
use tedo0627\redstonecircuit\block\mechanism\BlockHopper;
use tedo0627\redstonecircuit\block\mechanism\BlockIronDoor;
use tedo0627\redstonecircuit\block\mechanism\BlockIronTrapdoor;
use tedo0627\redstonecircuit\block\mechanism\BlockMoving;
use tedo0627\redstonecircuit\block\mechanism\BlockNote;
use tedo0627\redstonecircuit\block\mechanism\BlockPiston;
use tedo0627\redstonecircuit\block\mechanism\BlockPistonArmCollision;
use tedo0627\redstonecircuit\block\mechanism\BlockPoweredRail;
use tedo0627\redstonecircuit\block\mechanism\BlockRedstoneLamp;
use tedo0627\redstonecircuit\block\mechanism\BlockSkull;
use tedo0627\redstonecircuit\block\mechanism\BlockStickyPiston;
use tedo0627\redstonecircuit\block\mechanism\BlockStickyPistonArmCollision;
use tedo0627\redstonecircuit\block\mechanism\BlockTNT;
use tedo0627\redstonecircuit\block\mechanism\BlockWoodenDoor;
use tedo0627\redstonecircuit\block\mechanism\BlockWoodenTrapdoor;
use tedo0627\redstonecircuit\block\power\BlockDaylightSensor;
use tedo0627\redstonecircuit\block\power\BlockJukeBox;
use tedo0627\redstonecircuit\block\power\BlockLever;
use tedo0627\redstonecircuit\block\power\BlockObserver;
use tedo0627\redstonecircuit\block\power\BlockRedstone;
use tedo0627\redstonecircuit\block\power\BlockRedstoneTorch;
use tedo0627\redstonecircuit\block\power\BlockStoneButton;
use tedo0627\redstonecircuit\block\power\BlockStonePressurePlate;
use tedo0627\redstonecircuit\block\power\BlockTarget;
use tedo0627\redstonecircuit\block\power\BlockTrappedChest;
use tedo0627\redstonecircuit\block\power\BlockTripwire;
use tedo0627\redstonecircuit\block\power\BlockTripwireHook;
use tedo0627\redstonecircuit\block\power\BlockWeightedPressurePlateHeavy;
use tedo0627\redstonecircuit\block\power\BlockWeightedPressurePlateLight;
use tedo0627\redstonecircuit\block\power\BlockWoodenButton;
use tedo0627\redstonecircuit\block\power\BlockWoodenPressurePlate;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneComparator;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneRepeater;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneWire;
use tedo0627\redstonecircuit\listener\CommandBlockListener;
use tedo0627\redstonecircuit\listener\InventoryListener;
use tedo0627\redstonecircuit\listener\TargetBlockListener;
use tedo0627\redstonecircuit\loader\BlockEntityLoader;
use tedo0627\redstonecircuit\loader\BlockLoader;
use tedo0627\redstonecircuit\loader\ItemBlockLoader;
use tedo0627\redstonecircuit\loader\Loader;

class RedstoneCircuit extends PluginBase {

    private static bool $callEvent = false;

    /** @var Loader[] */
    private array $loader = [];

    public function onLoad(): void {
        // mechanism
        $this->addBlock("command_block", new BlockCommand(new BlockIdentifier(BlockTypeIds::COMMAND_BLOCK, BlockEntityCommand::class), "Command Block", BlockBreakInfo::indestructible()));
        $this->addBlockEntity("command_block", BlockEntityCommand::class, ["CommandBlock", "minecraft:command_block"]);
        $info = new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
        $this->addBlock("dispenser", new BlockDispenser(new BlockIdentifier(BlockTypeIds::DISPENSER, BlockEntityDispenser::class), "Dispenser", $info));
        $this->addBlockEntity("dispenser", BlockEntityDispenser::class, ["Dispenser", "minecraft:dispenser"]);
        $this->overrideBlock("door", BlockTypeIds::IRON_DOOR, fn($id, $name, $info) => new BlockIronDoor($id, $name, $info));
        $this->overrideBlocks("door", [
            BlockTypeIds::OAK_DOOR, BlockTypeIds::SPRUCE_DOOR, BlockTypeIds::BIRCH_DOOR,
            BlockTypeIds::JUNGLE_DOOR, BlockTypeIds::ACACIA_DOOR, BlockTypeIds::DARK_OAK_DOOR
        ], fn($id, $name, $info) => new BlockWoodenDoor($id, $name, $info));
        $info = new BlockBreakInfo(3, BlockToolType::AXE, 0, 15);
        $this->addBlock("door", new BlockWoodenDoor(new BlockIdentifier(BlockTypeIds::CRIMSON_DOOR), "Crimson Door", $info));
        $this->addItemBlock("door", BlockTypeIds::CRIMSON_DOOR, new ItemIdentifier(ItemIds::CRIMSON_DOOR, 0));
        $this->addBlock("door", new BlockWoodenDoor(new BlockIdentifier(BlockTypeIds::WARPED_DOOR), "Warped Door", $info));
        $this->addItemBlock("door", BlockTypeIds::WARPED_DOOR, new ItemIdentifier(ItemIds::WARPED_DOOR, 0));
        $this->addBlock("dropper", new BlockDropper(new BlockIdentifier(BlockTypeIds::DROPPER, BlockEntityDropper::class), "Dropper", $info));
        $this->addBlockEntity("dropper", BlockEntityDropper::class, ["Dropper", "minecraft:dropper"]);
        $this->overrideBlocks("fence_gate", [
            BlockTypeIds::OAK_FENCE_GATE, BlockTypeIds::SPRUCE_FENCE_GATE, BlockTypeIds::BIRCH_FENCE_GATE,
            BlockTypeIds::JUNGLE_FENCE_GATE, BlockTypeIds::DARK_OAK_FENCE_GATE, BlockTypeIds::ACACIA_FENCE_GATE
        ], fn($id, $name, $info) => new BlockFenceGate($id, $name, $info));
        $info = new BlockBreakInfo(2, BlockToolType::AXE, 0, 15);
        $this->addBlock("fence_gate", new BlockFenceGate(new BlockIdentifier(BlockTypeIds::CRIMSON
        $blockLoader = new BlockLoader("Fence Gate", _FENCE_GATE, "Crimson Fence Gate", $info);

        $this->addItemBlock("fence_gate", BlockTypeIds::CRIMSON_FENCE_GATE, new ItemIdentifier(ItemIds::CRIMSON_FENCE_GATE, 0));
        $this->addBlock("fence_gate", new BlockFenceGate(new BlockIdentifier(BlockTypeIds::WARPED_FENCE_GATE), "Warped Fence Gate", $info));
        $this->addItemBlock("fence_gate", BlockTypeIds::WARPED_FENCE_GATE, new ItemIdentifier(ItemIds::WARPED_FENCE_GATE, 0));
        $this->overrideBlock("iron_trapdoor", BlockTypeIds::IRON_TRAPDOOR, fn($id, $name, $info) => new BlockIronTrapdoor($id, $name, $info));
        $this->overrideBlock("moving", BlockTypeIds::PISTON_ARM_COLLISION, fn($id, $name, $info) => new BlockPistonArmCollision($id, $name, $info));
        $this->overrideBlock("moving", BlockTypeIds::STICKY_PISTON_ARM_COLLISION, fn($id, $name, $info) => new BlockStickyPistonArmCollision($id, $name, $info));
        $this->addBlock("moving", new BlockMoving(new BlockIdentifier(BlockTypeIds::MOVING_BLOCK, BlockEntityMoving::class), "Moving Block", BlockBreakInfo::indestructible()));
        $this->addBlockEntity("moving", BlockEntityMoving::class, ["MovingBlock", "minecraft:moving_block"]);
        $info = new BlockBreakInfo(0.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 2);
        $this->addBlock("note_block", new BlockNote(new BlockIdentifier(BlockTypeIds::NOTE_BLOCK, BlockEntityNote::class), "Note Block", $info));
        $this->addBlockEntity("note_block", BlockEntityNote::class, ["Music", "minecraft:music"]);
        $this->overrideBlock("piston", BlockTypeIds::PISTON, fn($id, $name, $info) => new BlockPiston($id, $name, $info));
        $this->overrideBlock("piston", BlockTypeIds::STICKY_PISTON, fn($id, $name, $info) => new BlockStickyPiston($id, $name, $info));
        $this->addBlock("skull", new BlockSkull(new BlockIdentifier(BlockTypeIds::SKULL_BLOCK, BlockEntitySkull::class), "Skull", BlockBreakInfo::indestructible()));
        $this->addBlockEntity("skull", BlockEntitySkull::class, ["Skull", "minecraft:skull"]);
        $this->overrideBlock("tnt", BlockTypeIds::TNT, fn($id, $name, $info) => new BlockTNT($id, $name, $info));
        $this->overrideBlock("trapped_chest", BlockTypeIds::TRAPPED_CHEST, fn($id, $name, $info) => new BlockTrappedChest($id, $name, $info));
        $this->overrideBlock("trip_wire", BlockTypeIds::TRIPWIRE, fn($id, $name, $info) => new BlockTripwire($id, $name, $info));
        $this->overrideBlock("trip_wire_hook", BlockTypeIds::TRIPWIRE_HOOK, fn($id, $name, $info) => new BlockTripwireHook($id, $name, $info));
        $this->overrideBlock("wooden_trapdoor", BlockTypeIds::WOODEN_TRAPDOOR, fn($id, $name, $info) => new BlockWoodenTrapdoor($id, $name, $info));
        $this->overrideBlocks("trapdoor", [
            BlockTypeIds::OAK_TRAPDOOR, BlockTypeIds::SPRUCE_TRAPDOOR, BlockTypeIds::BIRCH_TRAPDOOR,
            BlockTypeIds::JUNGLE_TRAPDOOR, BlockTypeIds::ACACIA_TRAPDOOR, BlockTypeIds::DARK_OAK_TRAPDOOR
        ], fn($id, $name, $info) => new BlockWoodenTrapdoor($id, $name, $info));
        $info = new BlockBreakInfo(3, BlockToolType::AXE, 0, 15);
        $this->addBlock("trapdoor", new BlockWoodenTrapdoor(new BlockIdentifier(BlockTypeIds::CRIMSON_TRAPDOOR), "Crimson Trapdoor", $info));
        $this->addItemBlock("trapdoor", BlockTypeIds::CRIMSON_TRAPDOOR, new ItemIdentifier(ItemIds::CRIMSON_TRAPDOOR, 0));
        $this->addBlock("trapdoor", new BlockWoodenTrapdoor(new BlockIdentifier(BlockTypeIds::WARPED_TRAPDOOR), "Warped Trapdoor", $info));
        $this->addItemBlock("trapdoor", BlockTypeIds::WARPED_TRAPDOOR, new ItemIdentifier(ItemIds::WARPED_TRAPDOOR, 0));
        // power
        $this->overrideBlock("daylight_detector", BlockTypeIds::DAYLIGHT_SENSOR, fn($id, $name, $info) => new BlockDaylightSensor($id, $name, $info));
        $this->overrideBlock("juke_box", BlockTypeIds::JUKEBOX, fn($id, $name, $info) => new BlockJukeBox($id, $name, $info));
        $this->overrideBlock("lever", BlockTypeIds::LEVER, fn($id, $name, $info) => new BlockLever($id, $name, $info));
        $this->overrideBlock("redstone", BlockTypeIds::REDSTONE_TORCH, fn($id, $name, $info) => new BlockRedstoneTorch($id, $name, $info));
        $this->overrideBlock("redstone", BlockTypeIds::REDSTONE, fn($id, $name, $info) => new BlockRedstone($id, $name, $info));
        $this->overrideBlock("redstone", BlockTypeIds::REDSTONE_WIRE, fn($id, $name, $info) => new BlockRedstoneWire($id, $name, $info));
        $this->overrideBlock("stone_button", BlockTypeIds::STONE_BUTTON, fn($id, $name, $info) => new BlockStoneButton($id, $name, $info));
        $this->overrideBlock("wooden_button", BlockTypeIds::WOODEN_BUTTON, fn($id, $name, $info) => new BlockWoodenButton($id, $name, $info));
        $this->overrideBlock("pressure_plate", BlockTypeIds::STONE_PRESSURE_PLATE, fn($id, $name, $info) => new BlockStonePressurePlate($id, $name, $info));
        $this->overrideBlock("pressure_plate", BlockTypeIds::WOODEN_PRESSURE_PLATE, fn($id, $name, $info) => new BlockWoodenPressurePlate($id, $name, $info));
        $this->overrideBlock("weighted_pressure_plate_light", BlockTypeIds::LIGHT_WEIGHTED_PRESSURE_PLATE, fn($id, $name, $info) => new BlockWeightedPressurePlateLight($id, $name, $info));
        $this->overrideBlock("weighted_pressure_plate_heavy", BlockTypeIds::HEAVY_WEIGHTED_PRESSURE_PLATE, fn($id, $name, $info) => new BlockWeightedPressurePlateHeavy($id, $name, $info));
        // transmission
        $this->overrideBlock("redstone", BlockTypeIds::REDSTONE_REPEATER, fn($id, $name, $info) => new BlockRedstoneRepeater($id, $name, $info));
        $this->overrideBlock("redstone", BlockTypeIds::REDSTONE_COMPARATOR, fn($id, $name, $info) => new BlockRedstoneComparator($id, $name, $info));
        // mechanism
        $this->overrideBlock("iron_trapdoor", BlockTypeIds::IRON_TRAPDOOR, fn($id, $name, $info) => new BlockIronTrapdoor($id, $name, $info));
        $this->overrideBlock("redstone_lamp", BlockTypeIds::REDSTONE_LAMP, fn($id, $name, $info) => new BlockRedstoneLamp($id, $name, $info));
        $this->overrideBlock("powered_rail", BlockTypeIds::POWERED_RAIL, fn($id, $name, $info) => new BlockPoweredRail($id, $name, $info));
        $this->overrideBlock("activator_rail", BlockTypeIds::ACTIVATOR_RAIL, fn($id, $name, $info) => new BlockActivatorRail($id, $name, $info));
        $this->addLoader(new BlockEntityLoader());
        $this->addLoader(new BlockLoader());
        $this->addLoader(new ItemBlockLoader());
    }

    private function addLoader(Loader $loader): void {
        $this->loader[] = $loader;
    }

    public function onEnable(): void {
        foreach ($this->loader as $loader) {
            $loader->load($this);
        }
    }

    public function overrideBlock(string $name, string $blockId, Closure $closure): void {
        $reflection

 = new \ReflectionClass(BlockTypeIds::class);
        $reflection = $reflection->getConstant($blockId);
        $block = $reflection;
        if ($block !== null) {
            $this->overrideBlockInstance($name, $block, $closure($blockId, $block, $block->getBreakInfo()));
        }
    }

    public function overrideBlockInstance(string $name, Block $block, Block $replacement): void {
        $name = strtolower($name);
        $block = $this->blocks[$name] ?? null;
        if ($block === null) {
            $this->blocks[$name] = $replacement;
        }
    }

    private function addBlock(string $name, Block $block): void {
        $name = strtolower($name);
        $this->blocks[$name] = $block;
    }

    public function overrideBlocks(string $name, array $blockIds, Closure $closure): void {
        foreach ($blockIds as $blockId) {
            $this->overrideBlock($name, $blockId, $closure);
        }
    }

    private function addItemBlock(string $name, string $blockId, Item $item): void {
        $this->items[$name] = $item;
    }

    public function addBlockEntity(string $name, string $className, array $names): void {
        $this->blockEntities[strtolower($name)] = $className;
        foreach ($names as $blockName) {
            $this->blockEntities[strtolower($blockName)] = $className;
        }
    }
}
