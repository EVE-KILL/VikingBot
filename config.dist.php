<?php

$config = array(
	'server'		=>	'ssl://', // ssl://ip for ssl tcp://ip for regular
	'port'			=>	0000,
	'channel'		=>	array("#chan1", "#chan2"), // array of channels
	'name'			=>	'botbot',
	'nick'			=>	'botbot',
	'pass'			=>	'', // password if the irc server needs it
	'waitTime'		=>	10,
	'adminPass'		=>	'mysuperadminpassword', // admin password for the bot
	'memoryLimit'	=>	'128',
    'memoryRestart' =>	'10',
	'trigger'		=>	'.',
	'maxPerTenMin'	=>	5000
);

// mySQL
$config["mysql"] = array(
	"host" => "",
	"username" => "",
	"password" => "",
	"database" => "",
);

// Oper
// The bot will auto identify with oper
$config["oper"] = array(
	"operUsername" => "",
	"operPassword" => ""
	);

// WolframAlpha
require_once("lib/wolframalpha/WolframAlphaEngine.php");
$config["wolframalpha"] = array(
	"appID" => ""
	);

// FileReader
$config["fileReader"] = array(
	"twitter" => "",
	"fleetops" => ""
	);

// Twitter
$config["twitter"] = array(
	"consumerKey" => "",
	"consumerSecret" => "",
	"accessToken" => "",
	"accessTokenSecret" => ""
	);

// Nickserv
$config["nickserv"] = array(
	"username" => "",
	"password" => ""
	);