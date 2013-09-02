<?php

class youtube implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "youtube";
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
        $matches = null;
        if(preg_match("/(https?...(www\.)?youtu\.?be[^ ]*)/", $msg, $matches))
        {
            parse_str( parse_url( $matches[0], PHP_URL_QUERY ), $id );
            $id = $id["v"];
            $data = getData("https://gdata.youtube.com/feeds/api/videos/".$id);
            $title = new SimpleXMLElement($data);
            sendMessage($this->socket, $channel, "|g|Youtube:|n| {$title->author->name} |g|/|n| {$title->title}");
        }
    }

    function destroy() {
    }

    function onData($data) {
    }
}