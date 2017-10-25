<?php

/* Template Related */

function body_class()
{
	$classes = [];

	if(Main::is('home'))
		$classes[] = 'home';

	echo implode(' ', $classes);
}

function getTemplatePart($url)
{
	$absUrl = ABSPATH.$url.'.php';

	if(file_exists($absUrl))
		require_once($absUrl);
	else
		throw new Exception("Invalid file url: ". $absUrl);
}

/* String Related */

function slugify($text)
{
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);
  $text = strtolower($text);

  if (empty($text))
    return 'n-a';

  return $text;
}

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


/* Random */
function p_dump($o)
{
	echo '<pre>';var_dump($o);echo '</pre>';
}
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
}
