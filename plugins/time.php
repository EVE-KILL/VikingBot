<?php

class time implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "time";
        $channels = array("all");
        $command = "time";
        $description = "Shows the current time ingame, and in various timezones.";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->socket = $socket;
        $this->config = $config;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
        if(stringStartsWith($msg, "{$this->config['trigger']}time"))
        {
            $time = date("H:i:s");
            $date = date("d-m-Y");
            $fullDate = date("Y-m-d H:i:s");
            $datetime = new DateTime($fullDate);
            $edt = $datetime->setTimezone(new DateTimeZone("America/New_York"));
            $edt = $edt->format("H:i:s");
            $pdt = $datetime->setTimezone(new DateTimeZone("America/Los_Angeles"));
            $pdt = $pdt->format("H:i:s");
            $cet = $datetime->setTimezone(new DateTimeZone("Europe/Copenhagen"));
            $cet = $cet->format("H:i:s");
            $msk = $datetime->setTimezone(new DateTimeZone("Europe/Moscow"));
            $msk = $msk->format("H:i:s");
            $aest = $datetime->setTimezone(new DateTimeZone("Australia/Sydney"));
            $aest = $aest->format("H:i:s");
            sendMessage($this->socket, $channel, "|g|EVE Time:|n| {$time} / |g|EVE Date:|n| {$date} / PDT: |g|{$pdt}|n| EDT: |g|{$edt}|n| CET: |g|{$cet}|n| MSK: |g|{$msk}|n| AEST: |g|{$aest}|n|");
        }
    }

    function destroy() {
    }

    function onData($data) {
    }
}