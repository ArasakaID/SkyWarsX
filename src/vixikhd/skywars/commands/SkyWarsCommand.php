<?php

namespace vixikhd\skywars\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use vixikhd\skywars\arena\Arena;
use vixikhd\skywars\SkyWars;

class SkyWarsCommand extends Command implements PluginIdentifiableCommand
{

    protected SkyWars $plugin;

    public function __construct(SkyWars $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("skywars", "SkyWars commands", null, ["sw"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender->hasPermission("sw.cmd")) {
            $sender->sendMessage("§cYou have not permissions to use this command!");
            return;
        }
        if (!isset($args[0])) {
            $sender->sendMessage("§cUsage: §7/sw help");
            return;
        }

        switch ($args[0]) {
            case "help":
                if (!$sender->hasPermission("sw.cmd.help")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                $sender->sendMessage("§a> SkyWars commands:\n" .
                    "§7/sw help : Displays list of SkyWars commands\n" .
                    "§7/sw create : Create SkyWars arena\n" .
                    "§7/sw remove : Remove SkyWars arena\n" .
                    "§7/sw set : Set SkyWars arena\n" .
                    "§7/sw arenas : Displays list of arenas");

                break;
            case "create":
                if (!$sender->hasPermission("sw.cmd.create")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/sw create <arenaName>");
                    break;
                }
                if (isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("§c> Arena $args[1] already exists!");
                    break;
                }
                $this->plugin->arenas[$args[1]] = new Arena($this->plugin, []);
                $sender->sendMessage("§a> Arena $args[1] created!");
                break;
            case "remove":
                if (!$sender->hasPermission("sw.cmd.remove")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/sw remove <arenaName>");
                    break;
                }
                if (!isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("§c> Arena $args[1] was not found!");
                    break;
                }

                $arena = $this->plugin->arenas[$args[1]];

                foreach ($arena->players as $player) {
                    $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
                }

                if (is_file($file = $this->plugin->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $args[1] . ".yml")) unlink($file);
                unset($this->plugin->arenas[$args[1]]);

                $sender->sendMessage("§a> Arena removed!");
                break;
            case "set":
                if (!$sender->hasPermission("sw.cmd.set")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if (!$sender instanceof Player) {
                    $sender->sendMessage("§c> This command can be used only in-game!");
                    break;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/sw set <arenaName>");
                    break;
                }
                if (isset($this->plugin->setters[$sender->getName()])) {
                    $sender->sendMessage("§c> You are already in setup mode!");
                    break;
                }
                if (!isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("§c> Arena $args[1] does not found!");
                    break;
                }
                $sender->sendMessage("§a> You are joined setup mode.\n" .
                    "§7- use §lhelp §r§7to display available commands\n" .
                    "§7- or §ldone §r§7to leave setup mode");
                $this->plugin->setters[$sender->getName()] = $this->plugin->arenas[$args[1]];
                break;
            case "arenas":
                if (!$sender->hasPermission("sw.cmd.arenas")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if (count($this->plugin->arenas) === 0) {
                    $sender->sendMessage("§6> There are 0 arenas.");
                    break;
                }
                $list = "§7> Arenas:\n";
                foreach ($this->plugin->arenas as $name => $arena) {
                    if ($arena->setup) {
                        $list .= "§7- $name : §cdisabled\n";
                    } else {
                        $list .= "§7- $name : §aenabled\n";
                    }
                }
                $sender->sendMessage($list);
                break;
            default:
                if (!$sender->hasPermission("sw.cmd.help")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                $sender->sendMessage("§cUsage: §7/sw help");
                break;
        }

    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

}
