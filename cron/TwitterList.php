<?php
chdir("/opt/VikingBot/cron/");
date_default_timezone_set("UTC");
require( "../lib/twitter/twitter.class.php" );
require( "../config.php" );
require( "../lib/functions.php" );
require("../lib/pluginInterface.php");

$cfg = $config["twitter"];

$twitter = new Twitter($cfg["consumerKey"], $cfg["consumerSecret"], $cfg["accessToken"], $cfg["accessTokenSecret"]);

$latest = getPermCache("twitterLatestSearchID");
if($latest == null) $latest = 0;

$replies = $twitter->load(Twitter::ME_AND_FRIENDS, 25);
$maxID = $latest;

foreach($replies as $reply)
{
	$text = $reply->text;
	$createdAt = $reply->created_at;
	$postedBy = $reply->user->name;
	$screenName = $reply->user->screen_name;
	$id = $reply->id;

	if($screenName[0] == "eve_kill") continue;
	if($id <= $latest) continue;

	$maxID = max($id, $maxID);

	if(strpos($text[0], "@eve_kill") !== false) continue;

	$date = date("Y-m-d H:i:s", strtotime($createdAt));
	$message = "|g|Twitter: {$postedBy} |n|(|g|@{$screenName}|n|) / |g|{$date} Message:|n| {$text}\n";
	file_put_contents("../db/fileReaderTwitter.db", $message, FILE_APPEND);
	setPermCache("twitterLatestSearchID", $maxID);
}