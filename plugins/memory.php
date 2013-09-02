<?php

/**
 * Plugin that responds with bot memory usage information
 */
class memory implements pluginInterface {

	var $socket;
	var $config;

    function getDescription()
    {
    	$pluginName = "memory";
        $channels = array("all");
        $command = "memory";
        $description = "Lists the amount of memory the bot is using.";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
	function init($config, $socket)
	{
		$this->config = $config;
		$this->socket = $socket;
	}

	function tick()
	{
	}

	function onData($data)
	{
    }

    function onMessage($from, $channel, $msg)
    {
		if(stringEndsWith($msg, "{$this->config['trigger']}memory"))
		{
			$usedMem = round(((memory_get_usage() / 1024) / 1024),2);
			$freeMem = round(($this->config['memoryLimit'] - $usedMem),2);
			sendMessage($this->socket, $channel, "|g|Memory status:|n| {$usedMem} |g|MB used|n| / {$freeMem} |g|MB free.|n|");
			$usedMem = null;
			$freeMem = null;
		}
	}

	function destroy()
	{
		$this->socket = null;
		$this->config = null;
	}
}
