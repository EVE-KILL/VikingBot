<?php
chdir("/opt/VikingBot/cron/");
date_default_timezone_set("UTC");
require_once( "../config.php" );
require_once( "../lib/functions.php" );
require_once("../lib/pluginInterface.php");
require_once("../vendor/autoload.php");

// autoload the pheal stuff
spl_autoload_register("Pheal::classload");

// temporary api key stuff, should load from either db or config once finished
$keyID = "2347241";
$vCode = "1E4zV68QFY5AwH8w9fPSyKXOmKFWPlujQZnFUC2YbVVXxHR55cUeVBXwywdUVNyx";
$charID = "268946627";

// api constants
$apiServer = "https://api.zkillboard.com";

// load the notifications
$pheal = new Pheal($keyID, $vCode, "char");
$result = $pheal->notifications(array("characterID" => $charID));

var_dump($result);