<?php

/* Array Related */

function arrayPrint($array, $identation = 0)
{
	foreach($array as $key => $value)
	{
		echo @sprintf(str_repeat("--", $identation)."[%s] %s<br />", $key, (isArray($value) ? implode(",", $value) : $value));
	}
}
function isArray($array) { return !((array) $array !== $array); }

function arrayContainskey($array, $key) { return isset($array[$key]) || array_key_exists($key,$array); }

function getKeyValue($array, $id) { return (arrayContainskey($array, $id))? $array[$id] : NULL; }

function objectSize($var) { return ((mb_strlen(serialize($var), '8bit'))/1024)."kbs"; }

$elapsedtimes = [];

function elapsetime($timeKey)
{
	global $elapsedtimes;
	if(!arrayContainskey($elapsedtimes, $timeKey))
		return ($elapsedtimes[$timeKey] = microtime(true));
	else
		return ($elapsedtimes[$timeKey] = microtime(true) - $elapsedtimes[$timeKey]);
}

function printElapsedTimes($keys = NULL)
{
	global $elapsedtimes;
	if(isset($keys))
	{
		foreach ($keys as $key)
		{
			if(isset($elapsedtimes[$key]))
				echo $elapsedtimes[$key]." [TIME=".$key."]".'<br />';
		}
	}
	else
	{
		foreach ($elapsedtimes as $key => $value)
			echo $value." [TIME=".$key."]".'<br />';
	}
	echo "<br /><br />";
}
