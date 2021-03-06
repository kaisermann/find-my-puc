<?php

/* Defines */
define('APP_NAME', 'Perdido No Campus');

define('QUERY_SEARCH_LIST', 'MATCH (n)
	WHERE NOT (n:Ignorable OR (exists(n.subtype) AND n.subtype STARTS WITH "banheiro"))
	RETURN n.id as id, n.names as names, labels(n) as labels ORDER BY LOWER(n.names[0]) ASC');

define('QUERY_EVERYTHING', 'match (n)-[e]->(m) RETURN n, m, e');

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
define('DIST_DIR', 'dist/');

define('PUBLIC_DEV', false);
if(PUBLIC_DEV)
{
	define('ROOT_DIR', PROTOCOL.'179.218.92.42:8080/findmypuc/');
}
else
{
	define('ROOT_DIR', PROTOCOL.'localhost/findmypuc/');
}

define('IMG_DIR', ABSPATH.DIST_DIR.'images/nodes/');
define('IMG_URL', ROOT_DIR.DIST_DIR.'images/nodes/');
define('MEDIA_URL', ROOT_DIR.DIST_DIR.'images/media/');

define('DEBUG', false);
define('MINIFY_HTML', false);

/* Globals */

$alphabet = [
	'[áãà]' => 'a'
	, '[éê]' => 'e'
	, '[íî]' => 'i'
	, '[óô]' => 'o'
	, '[úüû]' => 'u'
	, 'ç' => 'c'
	, '(^|\s)?dr(a?)\.?' => 'doutor$2'
	, 'professora?' => 'prof'
	, ' - ' => ' '
	, '\\.' => ''
	];
