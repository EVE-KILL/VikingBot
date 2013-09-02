<?php

/**
 * Checks if a string ends with substring
 * @param  string $whole
 * @param  string $end
 * @return bool
 */
function stringEndsWith($whole, $end) {
    return @(strpos($whole, $end, strlen($whole) - strlen($end)) !== false);
}

/**
 * Checks if a string starts with substring
 * @param  string $whole
 * @param  string $end
 * @return bool
 */
function stringStartsWith($whole, $end) {
	if(substr($whole, 0, strlen($end)) == $end) {
		return true;
	}
	return false;
}

/**
 * Gets the nick name from the ident
 * @param  string $in
 * @return string
 */
function getNick($in) {
	$in = str_replace(":", '', $in);
	$bits = explode("!", $in);
	return $bits[0];
}

/**
 * Adds colors to a message
 * @param string $msg
 */
function addIRCcolors($msg)
{
    $colors = array(
        "|r|" => "\x0305", // red
        "|g|" => "\x0303", // green
        "|w|" => "\x0315", // white
        "|b|" => "\x0302", // blue
        "|c|" => "\x0310", // cyan
        "|y|" => "\x0307", // yellow
        "|n|" => "\x03", // reset
        );

    foreach($colors as $color => $value)
        $msg = str_replace($color, $value, $msg);

    return $msg;
}

/**
 * Sends message to channel or user
 * @param  socket $socket
 * @param  string $channel
 * @param  string $msg
 */
function sendMessage($socket, $channel, $msg) {
	if(strlen($msg) > 2) { //Avoid sending empty lines to server, since all data should contain a line break, 2 chars is minimum
        // Add colors!
        $msg = addIRCcolors($msg);

        // Send message
		sendData($socket, "PRIVMSG {$channel} :{$msg}");
	}
}

/**
 * Sends data to server
 * @param  socket $socket
 * @param  string $msg
 */
function sendData($socket, $msg) {
	$res = fwrite($socket, "{$msg}\r\n") or trigger_error("Broken pipe on write, restarting the bot.");
	if($res) {
		logMsg("<Bot to server> {$msg}");
	}
}

/**
 * Error handler
 * @param  $errno
 * @param  $errstr
 * @param  $errfile
 * @param  $errline
 * @return bool
 */
function errorHandler($errno, $errstr, $errfile, $errline) {

	switch ($errno) {

		//Serious error, like server disconnection. Take a little break before restarting
		case E_USER_WARNING:
			logMsg("Error detected, restarting the bot.");
			sleep(10);
			doRestart();
		break;

		//PHP Warnings, like SSL errors
		case E_WARNING:
			if(strstr($errstr, "OpenSSL Error messages") !== false) {
				logMsg("SSL error detected, restarting the bot. ({$errstr})");
				sleep(10);
				doRestart();
			}		
		break;

                //PHP Notice, ignore it
                case E_NOTICE:
                break;

		//Default error handling, just log it
                default:
                        logMsg("errorHandler: unhandled PHP error {$errno}, {$errstr} from {$errfile}:{$errline}");
                break;
	}
	return false;
}

/**
 * Sends logdata to console
 * @param  string $msg
 */
function logMsg($msg) {
	if(!stringEndsWith($msg, "\n")) {
		$msg .= "\n";
	}		
	echo "[".date("t.M.y H:i:s")."] {$msg}";
}

/**
 * Performs a restart of the bot
 */
function doRestart() {
	die(exec('sh start.sh > /dev/null &'));
}

/**
 * Opens a database connection
 * @return a pdo connection to the database
 */
function openDatabase()
{
    global $config;

    $db = $config["mysql"];
    $dbname = $db["database"];
    $dbuser = $db["username"];
    $dbpass = $db["password"];
    $dbhost = $db["host"];

    $dsn = "mysql:dbname=$dbname;host=$dbhost";

    try
    {
        $pdo = new PDO($dsn, $dbuser, $dbpass, array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            )
        );
    }
    catch (Exception $e)
    {
        throw $e;
    }

    return $pdo;
}

/**
 * Queries the database and returns a single field
 * @param  string $query
 * @param  array  $params
 * @param  string $field
 * @return string
 */
function dbQueryField($query, $params = array(), $field)
{
    $pdo = openDatabase();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    $pdo = null;

    if(sizeof($result) == 0) return null;

    $resultRow = $result[0];
    return $resultRow[$field];
}

/**
 * Queries the database and returns a single row
 * @param  string $query
 * @param  array  $params
 * @return array
 */
function dbQueryRow($query, $params = array())
{
    $pdo = openDatabase();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    $pdo = null;

    if(sizeof($result) >= 1) return $result[0];
    return null;
}

/**
 * Queries the database and returns all results
 * @param  [type] $query
 * @param  array  $params
 * @return array
 */
function dbQuery($query, $params = array())
{
    $pdo = openDatabase();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    $pdo = null;

    return $result;
}

/**
 * Executes a query to the database
 * @param  string $query
 * @param  array  $params
 */
function dbExecute($query, $params = array())
{
    $pdo = openDatabase();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $stmt->closeCursor();
    $pdo = null;
}

/**
 * Opens a connection to memcached
 * @return memcached connection
 */
function memCached()
{
    $mc = new Memcached();
    $mc->addServer("127.0.0.1", "11211");
    return $mc;
}

/**
 * Sets temporary to memcached
 * @param string  $key
 * @param string  $value
 * @param integer $timeout
 */
function setTemp($key, $value, $timeout = 300)
{
    $mc = memCached();
    return $mc->set($key, $value, $timeout);
}

/**
 * Gets temporary data from memcached
 * @param  string $key
 * @return string
 */
function getTemp($key)
{
    $mc = memCached();
    return $mc->get($key);
}

/**
 * Increments a number in memcached
 * @param  string $key
 * @param  integer $step
 * @return bool
 */
function memIncrement($key, $step)
{
    $mc = memCached();
    if(!$step)
        return $mc->increment($key);
    else
        return $mc->increment($key, $step);
}

/**
 * Decrements a number in memcached
 * @param  string $key
 * @param  integer $step
 * @return bool
 */
function memDecrement($key, $step)
{
    $mc = memCached();
    if(!$step)
        return $mc->decrement($key);
    else
        return $mc->decrement($key, $step);
}

/**
 * Sets data to the permanent database cache
 * @param string $key
 * @param string $value
 */
function setPermCache($key, $value)
{
    dbExecute("INSERT INTO emp_ircCache (locker, value) VALUES (:locker, :value) ON DUPLICATE KEY UPDATE value = :value", array(":locker" => $key, ":value" => $value));
}

/**
 * Gets data from the permanent database cache
 * @param  string $key
 * @return string
 */
function getPermCache($key)
{
    return dbQueryField("SELECT value FROM emp_ircCache WHERE locker = :locker", array(":locker" => $key), "value");
}

/**
 * Opens a connection with cURL to a URL, and gets the data.
 * @param  string $url
 * @return cURL result
 */
function getData($url)
{
    $hash = sha1($url);
    if(getTemp($hash))
    {
        return getTemp($hash);
    }
    else
    {
        $userAgent = "Sovereign IRC Bot / InsidiousEmpire.net / An EVE-Online Alliance";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        $headers = array();
        $headers[] = "Connection: keep-alive";
        $headers[] = "Keep-Alive: timeout=10, max=1000";
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);

        setTemp($hash, $result, 3600);
        return $result;
    }
}