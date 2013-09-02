<?php
chdir("/opt/VikingBot/cron/");
date_default_timezone_set("UTC");
require_once( "../config.php" );
require_once( "../lib/functions.php" );
require_once("../lib/pluginInterface.php");
require_once("../vendor/autoload.php");

$latest = getPermCache("fleetOps");
if($latest == null) $latest = 0;

$ops = dbQuery("SELECT * FROM smf_messages WHERE id_board = '8' GROUP BY id_topic ORDER BY poster_time");
$maxID = $latest;

foreach($ops as $op)
{
	$id = $op["poster_time"];
	$topicID = $op["id_topic"];
	$subject = $op["subject"];
	$by = $op["poster_name"];

	if($id <= $latest) continue;

	$maxID = max($id, $maxID);

	$date = date("Y-m-d H:i:s", $id);
	$message = "Fleet OP Posted: |g|{$by}|n| / Posted At: |g|{$date}|n| / Subject: |g|{$subject}|n| / URL: |g|https://forum.insidiousempire.net/index.php?topic={$topicID}.0|n|\n";
	file_put_contents("../db/fileReaderFleetOps.db", $message, FILE_APPEND);
	setPermCache("fleetOps", $maxID);
}