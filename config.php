<?php

define('APP_NAME', 'Perdido Pelo Campus');

define('QUERY_SEARCH_LIST', 'MATCH (n)
	WHERE NOT (n:Ignorable OR (exists(n.subtype) AND n.subtype STARTS WITH "banheiro"))
	RETURN n.id as id, n.names as names ORDER BY LOWER(n.names[0]) ASC');

define('NODE_UNIQUE_ID', 'id');
define('NODE_NAMES_ID', 'names');
define('NODE_PASSAGE_TYPE_ID', 'passagem');
define('NODE_PARENT_ID', 'parent');
define('EDGE_WEIGHT_ID', 'weight');
define('EDGE_DIRECTION_ID', 'dir');

define('DIR_FOWARD', 1);
define('DIR_BACKWARD', 5);
define('DIR_RIGHT', 3);
define('DIR_LEFT', 7);

define('PAGE_ID', 'id');

define('PROTOCOL', 'http://');
define('DIST_DIR', '/dist/');

define('PUBLIC_DEV', false);
if(PUBLIC_DEV)
{
	define('ROOT_DIR', PROTOCOL.'189.122.170.129:8080/findmypuc');
}
else
{
	define('ROOT_DIR', PROTOCOL.'localhost/findmypuc');
}

define('DEBUG', false);
define('MINIFY_HTML', false);

if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

require_once ABSPATH."lib/loader.php";

if(MINIFY_HTML)
{
	function sanitize_output($buffer) {

		$search = array(
	        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
	        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
	        '/(\s)+/s'       // shorten multiple whitespace sequences
	        );

		$replace = array(
			'>',
			'<',
			'\\1'
			);

		$buffer = preg_replace($search, $replace, $buffer);

		return $buffer;
	}

	ob_start("sanitize_output");
}