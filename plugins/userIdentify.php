<?php

class userIdentify implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "userIdentify";
        $channels = array("all");
        $command = "identify";
        $description = "Identifies you with the bot. Remember to use this in a PM. |r|REQUIRES USERNAME AND PASSWORD FROM THE FORUM|n|. Example: |g|.identify <password> <username>|n|";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->socket = $socket;
        $this->config = $config;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
        if(stringStartsWith($msg, "{$this->config['trigger']}identify")) {
            $data = explode(" ", $msg);
            $password = trim($data[1]);
            unset($data[0]);
            unset($data[1]);
            $username = trim(implode(" ", $data));

            $hashedPw = sha1(strtolower($username) . $password);
            $check = dbQueryRow("SELECT member_name FROM smf_members WHERE member_name = :username AND passwd = :password", array(":username" => $username, ":password" => $hashedPw));

            if($check["member_name"])
            {
                sendMessage($this->socket, $from, "You have been identified, you will now be pushed into all the various channels you are to be in.");
                $group = dbQueryRow("SELECT id_group FROM smf_members WHERE member_name = :nick", array(":nick" => $username));
                $subGroup = dbQueryRow("SELECT additional_groups FROM smf_members WHERE member_name = :nick", array("nick" => $username));

                $groups = $group["id_group"] . "," . $subGroup["additional_groups"];
                $groups = explode(",", $groups);

                $groupNames = array();

                foreach($groups as $groupID)
                {
                    $groupName = dbQueryField("SELECT group_name FROM smf_membergroups WHERE id_group = :group", array(":group" => $groupID), "group_name");
                    $channels = dbQuery("SELECT groupName, channel FROM emp_ircChannels");
                    foreach($channels as $channel)
                    {
                        if($channel["groupName"] == $groupName)
                        {
                            $chan = $channel["channel"];
                            sendData($this->socket, "SAJOIN {$from} {$chan}");
                        }
                    }
                }
            }
            else
                sendMessage($this->socket, $from, "|r|Sorry, a user with that username / password does not exist.|n|");
        }
    }

    function destroy() {
    }

    function onData($data) {
    }
}