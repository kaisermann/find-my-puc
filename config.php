<?php
use GraphAware\Neo4j\Client\ClientBuilder;

define('NODE_UNIQUE_ID', "id");
define('NODE_NAMES_ID', "names");
define('NODE_PASSAGE_TYPE_ID', "passagem");
define('NODE_PARENT_ID', "parent");
define('EDGE_WEIGHT_ID', "weight");
define('EDGE_DIRECTION_ID', "dir");
define('EDGE_TYPE_LINK', "LINKED_TO");
define('EDGE_TYPE_PARENT', "PARENT_OF");


$client = ClientBuilder::create()
    ->addConnection('default', 'http://neo4j:hunter155@localhost:7474')
    ->build();

Neo::setClient($client);
