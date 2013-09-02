<?php

class fleetHash implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "fleetHash";
        $channels = array("#fleetcommanders");
        $command = "fleethash";
        $description = "FleetHash can be used to create / delete and list fleets that users, NOT registered on the EMP forum, can use. Commands available: |g|create <hash> <timeout>|n|, |g|delete <hash>|n|, |g|listfleets|n|, |g|list <hash>|n|";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->socket = $socket;
        $this->config = $config;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
        if(stringStartsWith($msg, "{$this->config['trigger']}fleet")) {
            if($channel == "#fleetcommanders")
            {
                $data = explode(" ", $msg);
                $cmd = trim($data[1]);
                $func = null;
                $extra = null;

                if(isset($data[2]))
                    $func = trim($data[2]);

                if(isset($data[3]))
                    $extra = trim($data[3]);

                switch($cmd)
                {
                    case "create":
                        if(!is_numeric($extra))
                            sendMessage($this->socket, $channel, "|r|Invalid numbering, please try again|n|");
                        else
                        {
                            $mtime = 60 * 60 * $extra;
                            $date = date("Y-m-d H:i:s", time() + $mtime);
                            dbExecute("INSERT INTO emp_fleetHash (fleetHash, dateEnding) VALUES (:hash, :end) ON DUPLICATE KEY UPDATE dateEnding = :end", array(":hash" => $func, ":end" => $date));
                            sendMessage($this->socket, $channel, "Fleet created with hash: |g|{$func}|n| ending at: |g|{$date}|n| / URL: |g|http://auth.insidiousempire.net/fleet/{$func}/|n|");
                        }
                    break;

                    case "delete":
                        $count = dbQueryField("SELECT count(*) AS cnt FROM emp_fleetUsers WHERE fleetHash = :hash", array(":hash" => $func), "cnt");
                        sendMessage($this->socket, $channel, "Deleting fleet with hash: |g|{$func}|n| with |g|{$count}|n| users registered");
                        dbExecute("DELETE FROM emp_fleetHash WHERE fleetHash = :hash", array(":hash" => $func));
                        dbExecute("DELETE FROM emp_fleetUsers WHERE fleetHash = :hash", array(":hash" => $func));
                    break;

                    case "listfleets":
                        $fleets = dbQuery("SELECT * FROM emp_fleetHash");
                        foreach($fleets as $fleet)
                        {
                            $hash = $fleet["fleetHash"];
                            $startDate = $fleet["dateStarted"];
                            $endDate = $fleet["dateEnding"];
                            $count = dbQueryField("SELECT count(*) AS cnt FROM emp_fleetUsers WHERE fleetHash = :hash", array(":hash" => $hash), "cnt");
                            sendMessage($this->socket, $channel, "Fleet |g|{$hash}|n| has |g|{$count}|n| members registered. It was started |g|{$startDate}|n| with ending set to |g|{$endDate}|n|");
                        }
                    break;

                    case "list":
                        $count = dbQueryField("SELECT count(*) AS cnt FROM emp_fleetUsers WHERE fleetHash = :hash", array(":hash" => $func), "cnt");
                        sendMessage($this->socket, $channel, "There are currently |g|{$count}|n| users registered for fleet |g|{$func}|n|");
                    break;
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