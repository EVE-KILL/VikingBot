<?php

class botGhost implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "botGhost";
        $channels = array();
        $command = "";
        $description = "";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->socket = $socket;
        $this->config = $config;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
    }

    function destroy() {
    }

    function onData($data) {
        if(stristr($data, ".net 433"))
        {
            sendData($this->socket, "NICK Sovereign_");
            sendData($this->socket, "ns ghost {$nickserv["username"]} {$nickserv["password"]}");
            sendData($this->socket, "NICK Sovereign");
            sendData($this->socket, "ns identify {$nickserv["username"]} {$nickserv["password"]}");
        }
    }
}