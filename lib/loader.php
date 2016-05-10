<?php
$dir = dirname(__FILE__);
$files = [
	'../vendor/autoload',
	'Graph/Graph',
	'Helpers/Assets',
	'Helpers/Helpers',
	'Neo',
	'Main',
];

for($i=0,$count=count($files);$i<$count;$i++)
	require_once $dir.'/'.$files[$i].'.php';
