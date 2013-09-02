<?php

interface pluginInterface {
	function getDescription();
	function init($config, $socket);
	function tick();
	function onMessage($from, $channel, $msg);
	function destroy();
}