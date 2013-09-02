<?php

class userGroups implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "userGroups";
        $channels = array("#leadership", "#developers");
        $command = "group";
        $description = "Lists the various channels that various groups can enter. Can also add and remove channels from groups. Commands available: |g|.group list <group name>|n| / |g|.group delete <channel> <group name>|n| / |g|.group add <channel> <group name>|n|";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->socket = $socket;
        $this->config = $config;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
        if(stringStartsWith($msg, "{$this->config['trigger']}group"))
        {
            if(in_array($channel, array("#leadership", "#developers")))
            {

            }
        }
    }

    function destroy() {
    }

    function onData($data) {
    }
}