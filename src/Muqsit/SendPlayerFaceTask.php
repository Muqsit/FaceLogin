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
* Almost everything in this class belongs to Legoboy0215
* Twitter: http://twitter.com/Legoboy0215
* GitHub: http://github.com/legoboy0215
*
* Source: http://gist.github.com/legoboy0215/43282a636844bb0d1accbc91c3fc43f6
*
*/
namespace Muqsit;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\TextFormat;

class SendPlayerFaceTask extends AsyncTask {

    const HEX_SYMBOL = "e29688";

    const TEXTFORMAT_RGB = [
        [0, 0, 0],
        [0, 0, 170],
        [0, 170, 0],
        [0, 170, 170],
        [170, 0, 0],
        [170, 0, 170],
        [255, 170, 0],
        [170, 170, 170],
        [85, 85, 85],
        [85, 85, 255],
        [85, 255, 85],
        [85, 255, 255],
        [255, 85, 85],
        [255, 85, 255],
        [255, 255, 85],
        [255, 255, 255]
    ];

    const TEXTFORMAT_LIST = [
        TextFormat::BLACK,
        TextFormat::DARK_BLUE,
        TextFormat::DARK_GREEN,
        TextFormat::DARK_AQUA,
        TextFormat::DARK_RED,
        TextFormat::DARK_PURPLE,
        TextFormat::GOLD,
        TextFormat::GRAY,
        TextFormat::DARK_GRAY,
        TextFormat::BLUE,
        TextFormat::GREEN,
        TextFormat::AQUA,
        TextFormat::RED,
        TextFormat::LIGHT_PURPLE,
        TextFormat::YELLOW,
        TextFormat::WHITE
    ];

    private $messages;
    private $player;
    private $skindata;

    public function __construct(string $player, string $skindata, array $messages)
    {
        $this->messages = (array) $messages;
        $this->player = $player;
        $this->skindata = $skindata;
    }

    private function rgbToTextFormat($r, $g, $b)
    {
        $differenceList = [];
        foreach(self::TEXTFORMAT_RGB as $value){
            $difference = sqrt(pow($r - $value[0],2) + pow($g - $value[1],2) + pow($b - $value[2],2));
            $differenceList[] = $difference;
        }
        $smallest = min($differenceList);
        $key = array_search($smallest, $differenceList);
        return self::TEXTFORMAT_LIST[$key];
    }

    public function onRun()
    {
        $symbol = hex2bin(self::HEX_SYMBOL);
        $strArray = [];
        $skin = substr($this->skindata, ($pos = (64 * 8 * 4)) - 4, $pos);
        for($y = 0; $y < 8; ++$y){
            for($x = 1; $x < 9; ++$x){
                if(!isset($strArray[$y])){
                    $strArray[$y] = "";
                }
                $key = ((64 * $y) + 8 + $x) * 4;
                $r = ord($skin{$key});
                $g = ord($skin{$key + 1});
                $b = ord($skin{$key + 2});
                $format = $this->rgbToTextFormat($r, $g, $b);
                $strArray[$y] .= $format.$symbol;
            }
        }
        foreach($this->messages as $k => $v){
            $strArray[$k - 1] = $strArray[$k - 1]." ".str_replace("{NAME}", $this->player, $v);
        }
        $this->setResult(implode("\n", $strArray));
    }

    public function onCompletion(Server $server)
    {
        if(($player = $server->getPlayerExact($this->player)) !== null){
            $player->sendMessage($this->getResult());
        }
    }
}
