<?php
/*
*
* Copyright (C) 2017 Muqsit Rayyan
*
*    ___                __             _
*   / __\_ _  ___ ___  / /  ___   __ _(_)_ __
*  / _\/ _` |/ __/ _ \/ /  / _ \ / _` | | '_ \
* / / | (_| | (_|  __/ /__| (_) | (_| | | | | |
* \/   \__,_|\___\___\____/\___/ \__, |_|_| |_|
*                                |___/
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
*
* @author Muqsit Rayyan
* Twiter: http://twitter.com/muqsitrayyan
* GitHub: http://github.com/Muqsit
*
*/
namespace Muqsit;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;

class FaceLogin extends PluginBase implements Listener {

    private $messages = [];

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if(!is_dir($dir = $this->getDataFolder())){
            mkdir($dir);
        }
        if(!is_file($dir."messages.yml")){
            file_put_contents($dir."messages.yml", $this->getResource("messages.yml"));
        }

        $messages = [];
        $data = yaml_parse_file($dir."messages.yml");
        $switchToDefault = false;
        if(isset($data["messages"])){
            $messages = $data["messages"];
            foreach($messages as $k => $v){
                if(!is_numeric($k)){
                    $this->getLogger()->critical("Line number in messages.yml must be an integer, $k given. Switching to default messages.");
                    $switchToDefault = true;
                    break;
                }elseif($k < 1 || $k > 8){
                    $this->getLogger()->critical("Line number in messages.yml must be an greater than 0 and less than 9, $k given. Switching to default messages.");
                    $switchToDefault = true;
                    break;
                }
            }
        }
        if($switchToDefault){
            $data = yaml_parse_file($this->getFile()."messages.yml");
            $messages = $data["messages"];
        }
        $this->messages = $messages;
    }

    public function sendFace(Player $player, array $messages = null)
    {
        $this->getServer()->getAsyncPool()->submitTask(new SendPlayerFaceTask($player->getName(), $player->getSkin()->getSkinData(), $messages ?? $this->messages));
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        if($event->getPlayer()->hasPermission("facelogin.show")){
            $this->sendFace($event->getPlayer());
        }
    }
}
