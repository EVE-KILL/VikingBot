<?php

class wolframAlpha implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "wolframAlpha";
        $channels = array("all");
        $command = "wolf";
        $description = "Takes your question, and pokes WolframAlpha for it. Example: |g|.wolf 2+2|n|";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->socket = $socket;
        $this->config = $config;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
        if(stringStartsWith($msg, "{$this->config['trigger']}wolf"))
        {
            $wolframAlpha = new WolframAlphaEngine($this->config["wolframalpha"]["appID"]);
            $text = null;
            $data = explode(" ", $msg);
            unset($data[0]);
            $question = implode(" ", $data);
            $response = $wolframAlpha->getResults($question);

            $guess = $response->getPods();
            if(isset($guess[1]))
            {
                $guess = $guess[1]->getSubpods();
                $text = $guess[0]->plaintext;
                if(stristr($text, "\n"))
                    $text = str_replace("\n", " | ", $text);
   
                sendMessage($this->socket, $channel, "|g|WolframAlpha:|n| {$text}");
            }
            else
                sendMessage($this->socket, $channel, "|r|Sorry, WolframAlpha doesn't have an answer..|n|");
        }
    }

    function destroy() {
    }

    function onData($data) {
    }
}