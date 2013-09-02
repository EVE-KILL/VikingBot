<?php
class userAutoIdentify implements pluginInterface {

    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "userAutoIdentify";
        $channels = array();
        $command = "";
        $description = "";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->config = $config;
        $this->socket = $socket;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
    }

    function destroy() {
    }

    function onData($data) {
        if(stristr($data, ".net 340"))
        {
            $newData = explode(":", $data);
            $ipString = $newData[2];
            $n = explode("=+", $ipString);
            $n = $n[0];
            $nick = str_replace(":", "", $n);
            $nick = str_replace("*", "", $nick);
            $username = str_replace("_", " ", $nick);
            $botnick = $this->config["nick"];

            $p = explode("@", $ipString);
            $ip = trim($p[1]);

            // Find the ip of the user
            $ips = dbQueryRow("SELECT member_ip, member_ip2 FROM smf_members WHERE member_name = :nick", array(":nick" => $username));

            $ip_one = trim($ips["member_ip"]);
            $ip_two = trim($ips["member_ip2"]);

            if(in_array($ip, array($ip_one, $ip_two)))
            {
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
                            sendData($this->socket, "SAJOIN {$nick} {$chan}");
                        }
                    }
                }
            }
            else
                sendMessage($this->socket, $nick, "Hello |g|{$nick}|n|. You are connecting from a place, or with a user that isn't known by the EMP forum. To identify, reply to me with |g|.identify <password> <username>|n|, or you can use: |g|/msg {$botnick} .identify <password> <username>|n| . Remember to replace <username> and <password> with your username and password for the forum.");
        }

        if(stristr($data, "JOIN :#"))
        {
            $newData = explode(":", $data);
            $userString = explode(" ", $newData[1]);
            $u = explode("!", $userString[0]);
            $username = $u[0];
            $hostname = $u[1];
            $channel = trim($newData[2]);

            if($channel == "#liquidlounge")
                sendData($this->socket, "USERIP {$username}");
        }
    }
}
