<?php

class priceChecks implements pluginInterface
{
    var $socket;
    var $config;

    function getDescription()
    {
        $pluginName = "priceChecks";
        $channels = array("all");
        $command = "pc";
        $description = "PC can be used for checking the price of an item, on the global market. It can also be used to look up prices in the major market hubs. Jita, Amarr, Rens, Dodixie and Hek. Example: |g|.jita raven|n|";
        return array("pluginName" => $pluginName, "channels" => $channels, "command" => $command, "description" => $description);
    }
    
    function init($config, $socket) {
        $this->socket = $socket;
        $this->config = $config;
    }

    function tick() {
    }

    function onMessage($from, $channel, $msg) {
        if(stringStartsWith($msg, "{$this->config['trigger']}pc") || stringStartsWith($msg, "{$this->config['trigger']}jita") || stringStartsWith($msg, "{$this->config['trigger']}amarr") || stringStartsWith($msg, "{$this->config['trigger']}rens") || stringStartsWith($msg, "{$this->config['trigger']}dodixie") || stringStartsWith($msg, "{$this->config['trigger']}hek")) {

            $continue = false;
            $typeID = null;
            $typeName = null;
            $data = explode(" ", $msg);
            $systemName = $data[0];
            unset($data[0]);
            $systemName = str_replace(".", "", $systemName);
            $itemName = implode(" ", $data);

            if($itemName == null)
            {
                sendMessage($this->socket, $channel, "|r|Error!|n| Please type an item name..");
            }
            else
            {
                $data = dbQueryRow("SELECT typeID, typeName FROM ccp_invTypes WHERE typeName = :item", array(":item" => $itemName));
                if(count($data) != NULL)
                {
                    $typeID = $data["typeID"];
                    $typeName = $data["typeName"];
                    $continue = true;
                }

                if($typeID == NULL)
                {
                    $itemNames = dbQuery("SELECT typeName FROM ccp_invTypes WHERE typeName LIKE :item LIMIT 5", array(":item" => "%".$itemName."%"));
                    if(count($itemNames) == 0)
                        sendMessage($this->socket, $channel, "|r|No results found|n|");
                    elseif(count($itemNames) == 1)
                    {
                        $data = dbQueryRow("SELECT typeID, typeName FROM ccp_invTypes WHERE typeName = :item", array(":item" => $itemNames[0]["typeName"]));
                        $typeID = $data["typeID"];
                        $typeName = $data["typeName"];
                        $continue = true;
                    }
                    else
                    {
                        $items = array();
                        foreach($itemNames as $itm)
                            $items[] = $itm["typeName"];

                        $items = implode(", ", $items);
                        sendMessage($this->socket, $channel, "|g|Multiple results found:|n| {$items}");
                    }
                }

                switch($systemName)
                {
                    case "jita":
                        $systemID = "30000142";
                    break;
                    case "amarr":
                        $systemID = "30002187";
                    break;
                    case "rens":
                        $systemID = "30002510";
                    break;
                    case "dodixie":
                        $systemID = "30002659";
                    break;
                    case "hek":
                        $systemID = "30002053";
                    break;
                    default:
                        $systemID = NULL;
                    break;
                }
            }
            // get the price data and spit it out
            if($continue == true)
            {
                // this should _REALLY_ be executed with async
                if($systemID == NULL)
                    $url = "http://api.eve-central.com/api/marketstat?typeid=".$typeID;
                else
                    $url = "http://api.eve-central.com/api/marketstat?usesystem=".$systemID."&typeid=".$typeID;
                    $xml = getData($url);
                $data = new SimpleXMLElement($xml);
                $buyPrice = number_format((float)$data->marketstat->type->buy->max, 2, ".", ",");
                $sellPrice = number_format((float)$data->marketstat->type->sell->min, 2, ".", ",");
                $place = "Global";
                if($systemName != "pc")
                    $place = ucfirst($systemName);
               sendMessage($this->socket, $channel, "{$typeName} (|g|{$place}|n|) - |g|Buy:|n| {$buyPrice} ISK / |g|Sell:|n| {$sellPrice} ISK");
            }
        }
    }

    function destroy() {
    }

    function onData($data) {
    }
}