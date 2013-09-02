<?php

class pingChannels implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "pingChannels";
        $channels = array("#fleetcommanders", "leadership", "capitalcommanders");
        $command = "ping";
        $description = "Pings all users in a channel. Example: |g|.ping #asdf IM ON FIRE|n|";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->socket = $socket;
        $this->config = $config;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
        // initiate a who
        if(stringStartsWith($msg, "{$this->config['trigger']}ping"))
        {
            if(in_array($channel, array("#fleetcommanders", "#leadership", "#capitalcommanders")))
            {
                $data = explode(" ", $msg);
                $pingChan = $data[1];
                unset($data[0]);
                unset($data[1]);
                $message = implode(" ", $data);

                $date = date("Y-m-d H:i:s");
                $message = $message . " [Sent by {$from} ({$channel}) at {$date}]";

                setTemp("SENDPINGTOCHANNEL", $pingChan);
                setTemp("ORIGINCHANNEL", $channel);
                setTemp("PINGMESSAGE", $message);
                sendData($this->socket, "WHO {$pingChan}");
            }
            else
                sendMessage($this->socket, $channel, "|r|Error!|n| Command cannot be used in this channel..");
        }
    }

    function destroy() {
    }

    function onData($data) {
        // The end, now post to the origin channel how many was pinged
        if(stristr($data, ".net 315"))
        {
            $count = getTemp("PINGCOUNT");
            $originChannel = getTemp("ORIGINCHANNEL");
            $pingChannel = getTemp("SENDPINGTOCHANNEL");

            sendMessage($this->socket, $originChannel, "Sent message to |g|{$count} people|n| in |g|{$pingChannel}|n|");

            setTemp("PINGCOUNT", 0);
            setTemp("SENDPINGTOCHANNEL", null);
            setTemp("ORIGINCHANNEL", null);
            setTemp("PINGMESSAGE", null);
        }
        // notice that a who has been made for a channel, and start pinging everyone with the message set in temporary storage called "PINGMESSAGE"
        if(stristr($data, ".net 352"))
        {
            // count
            $num = getTemp("PINGCOUNT");
            if($num == NULL) setTemp("PINGCOUNT", 0, 300);

            memIncrement("PINGCOUNT", 1);
            $newData = explode(" ", $data);
            $name = $newData[7];
            $message = getTemp("PINGMESSAGE");

            sendMessage($this->socket, $name, $message);
        }
    }
}