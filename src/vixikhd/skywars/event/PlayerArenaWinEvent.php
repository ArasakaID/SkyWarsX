<?php

namespace vixikhd\skywars\event;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;
use vixikhd\skywars\arena\Arena;
use vixikhd\skywars\SkyWars;

class PlayerArenaWinEvent extends PluginEvent
{

    /** @var null $handlerList */
    public static $handlerList = null;

    protected Player $player;

    protected Arena $arena;

    public function __construct(SkyWars $plugin, Player $player, Arena $arena)
    {
        $this->player = $player;
        $this->arena = $arena;
        parent::__construct($plugin);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getArena(): Arena
    {
        return $this->arena;
    }
}