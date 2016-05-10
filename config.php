<?php

define('APP_NAME', 'Perdido Pelo Campus');

define('QUERY_SEARCH_LIST', 'MATCH (n)
WHERE NOT (n:Ignorable OR n.subtype STARTS WITH "banheiro")
RETURN n.id as id, n.names as names ORDER BY LOWER(n.names[0]) ASC');

define('NODE_UNIQUE_ID', 'id');
define('NODE_NAMES_ID', 'names');
define('NODE_PASSAGE_TYPE_ID', 'passagem');
define('NODE_PARENT_ID', 'parent');
define('EDGE_WEIGHT_ID', 'weight');
define('EDGE_DIRECTION_ID', 'dir');
define('EDGE_TYPE_LINK', 'LINKED_TO');
define('EDGE_TYPE_PARENT', 'PARENT_OF');

define('PAGE_ID', 'id');

define('ROOT_DIR', 'http://localhost/findmypuc');
define('DIST_DIR', '/dist/');

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