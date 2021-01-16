<?php

/**
 * Copyright 2018-2020 GamakCZ
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace vixikhd\skywars\arena;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\ClickSound;
use pocketmine\scheduler\Task;
use pocketmine\tile\Sign;
use vixikhd\skywars\math\Time;
use vixikhd\skywars\math\Vector3;

/**
 * Class ArenaScheduler
 * @package skywars\arena
 */
class ArenaScheduler extends Task
{

    protected Arena $plugin;

    public int $startTime = 40;

    public $gameTime = 20 * 60;

    public int $restartTime = 10;

    public function __construct(Arena $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick)
    {
        $this->reloadSign();

        if ($this->plugin->setup) return;

        switch ($this->plugin->phase) {
            case Arena::PHASE_LOBBY:
                if (count($this->plugin->players) >= 2) {
                    $this->plugin->broadcastMessage("§a> Starting in " . Time::calculateTime($this->startTime) . " sec.", Arena::MSG_TIP);
                    $this->startTime--;
                    if ($this->startTime == 0) {
                        $this->plugin->startGame();
                        foreach ($this->plugin->players as $player) {
                            $this->plugin->level->addSound(new AnvilUseSound($player->asVector3()));
                        }
                    } else {
                        foreach ($this->plugin->players as $player) {
                            $this->plugin->level->addSound(new ClickSound($player->asVector3()));
                        }
                    }
                } else {
                    $this->plugin->broadcastMessage("§c> You need more players to start a game!", Arena::MSG_TIP);
                    $this->startTime = 40;
                }
                break;
            case Arena::PHASE_GAME:
                $this->plugin->broadcastMessage("§a> There are " . count($this->plugin->players) . " players, time to end: " . Time::calculateTime($this->gameTime) . "", Arena::MSG_TIP);
                switch ($this->gameTime) {
                    case 15 * 60:
                        $this->plugin->broadcastMessage("§a> All chests will be refilled in 5 min.");
                        break;
                    case 11 * 60:
                        $this->plugin->broadcastMessage("§a> All chest will be refilled in 1 min.");
                        break;
                    case 10 * 60:
                        $this->plugin->broadcastMessage("§a> All chests are refilled.");
                        break;
                }
                if ($this->plugin->checkEnd()) $this->plugin->startRestart();
                $this->gameTime--;
                break;
            case Arena::PHASE_RESTART:
                $this->plugin->broadcastMessage("§a> Restarting in {$this->restartTime} sec.", Arena::MSG_TIP);
                $this->restartTime--;

                switch ($this->restartTime) {
                    case 0:
                        foreach ($this->plugin->getPlayers() as $player) {
                            $this->plugin->disconnectPlayer($player);
                        }
                        $this->plugin->loadArena(true);
                        $this->reloadTimer();
                        break;
                }
                break;
        }
    }

    public function reloadSign()
    {
        if (!is_array($this->plugin->data["joinsign"]) || empty($this->plugin->data["joinsign"])) return;

        $signPos = Position::fromObject(Vector3::fromString($this->plugin->data["joinsign"][0]), $this->plugin->plugin->getServer()->getLevelByName($this->plugin->data["joinsign"][1]));

        if (!$signPos->getLevel() instanceof Level || is_null($this->plugin->level)) return;

        $signText = [
            "§e§lSkyWars",
            "§9[ §b? / ? §9]",
            "§6Setup",
            "§6Wait few sec..."
        ];

        if ($signPos->getLevel()->getTile($signPos) === null) return;

        if ($this->plugin->setup || $this->plugin->level === null) {
            /** @var Sign $sign */
            $sign = $signPos->getLevel()->getTile($signPos);
            $sign->setText($signText[0], $signText[1], $signText[2], $signText[3]);
            return;
        }

        $signText[1] = "§9[ §b" . count($this->plugin->players) . " / " . $this->plugin->data["slots"] . " §9]";

        switch ($this->plugin->phase) {
            case Arena::PHASE_LOBBY:
                if (count($this->plugin->players) >= $this->plugin->data["slots"]) {
                    $signText[2] = "§6Full";
                    $signText[3] = "§8Map: §7{$this->plugin->level->getFolderName()}";
                } else {
                    $signText[2] = "§aJoin";
                    $signText[3] = "§8Map: §7{$this->plugin->level->getFolderName()}";
                }
                break;
            case Arena::PHASE_GAME:
                $signText[2] = "§5InGame";
                $signText[3] = "§8Map: §7{$this->plugin->level->getFolderName()}";
                break;
            case Arena::PHASE_RESTART:
                $signText[2] = "§cRestarting...";
                $signText[3] = "§8Map: §7{$this->plugin->level->getFolderName()}";
                break;
        }

        /** @var Sign $sign */
        $sign = $signPos->getLevel()->getTile($signPos);
        if ($sign instanceof Sign)
            $sign->setText($signText[0], $signText[1], $signText[2], $signText[3]);
    }

    public function reloadTimer()
    {
        $this->startTime = 30;
        $this->gameTime = 20 * 60;
        $this->restartTime = 10;
    }
}
