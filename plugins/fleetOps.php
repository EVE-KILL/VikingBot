<?php

class fleetOps implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "fleetOps";
        $channels = array("#insidiousempire", "#allies");
        $command = "ops";
        $description = "Lists all upcoming ops for the next 10 hours.";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->socket = $socket;
        $this->config = $config;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
        if(stringEndsWith($msg, "{$this->config['trigger']}ops")) {
            if($channel == "#insidiousempire" || $channel == "#allies")
            {
                $currentDate = date("Y-m-d H:i:s", strtotime("-1 hours"));
                $maxDate = date("Y-m-d H:i:s", strtotime("+24 hours"));
                $dateSort = array();
                $ops = dbQuery("SELECT * FROM smf_messages WHERE id_board = '8' ORDER BY poster_time", array());
                foreach($ops as $op)
                {
                    $time = explode("//", $op["subject"]);
                    if(isset($time[0]))
                    {
                        $time = $time[0];
                        $time = str_replace("eve time", "", strtolower($time));
                        $time = trim($time);
                        $time = date("Y-m-d H:i:s", strtotime($time));

                        if($time <= $currentDate) continue;
                        if($time >= $maxDate) continue;
                        
                        $dateSort[$time] = $op;
                    }
                }

                ksort($dateSort);
                if(empty($dateSort))
                    sendMessage($this->socket, $channel, "|g|No ops coming up|n| in the next 24 hours");
                else
                {
                    foreach($dateSort as $op)
                    {
                        $subject = trim($op["subject"]);
                        $linkID = $op["id_topic"];
                        $url = "https://forum.insidiousempire.net/index.php?topic={$linkID}.0";

                        $message = "Op: |g|{$subject}|n| / URL: |g|{$url}|n|";
                        sendMessage($this->socket, $channel, $message);
                    }
                }
            }
            else
                sendMessage($this->socket, $channel, "|r|Error!|n| Command cannot be used in this channel..");
        }
    }

    function destroy() {
    }

    function onData($data) {
    }
}