<?php

use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\Neo4jClientEvents;

class Neo
{	

	public static $client = NULL;

	public static function getClient() { return self::$client; }

	public static function init()
	{
		if(self::$client!=NULL)
			return self::$client;

		$GLOBALS['neo4jOn'] = true;

		self::$client = ClientBuilder::create()
		->addConnection('default', 'http://neo4j:hunter155@localhost:7474')
		->build();

		return self::$client;
	}

	public static function executeQuery($query, $limit = 400, $skip = 0)
	{
		$query = $query.' SKIP '.$skip.' '.'LIMIT '.$limit;
		try
		{
			$result = @self::$client->run($query);
		}
		catch(Exception $e)
		{
			$GLOBALS['neo4jOn'] = false;
		}

		if(DEBUG)
			p_dump($query);

		return $result;
	}

	public static function getSubgraph($nodeA, $nodeB)
	{
		$q = QUERY_EVERYTHING;

		$parentA = explode("n", $nodeA);
		$parentB = explode("n", $nodeB);
		
		$q = 'MATCH (src{id:"'.$nodeA.'"}),(tgt{id:"'.$nodeB.'"})
		WITH 
		(CASE WHEN src:Location THEN src.parent ELSE src.id END) AS genericSrc
		,(CASE WHEN tgt:Location THEN tgt.parent ELSE tgt.id END) AS genericTgt
		MATCH (ptgt:Generic{id:genericTgt}), (psrc:Generic{id:genericSrc})
		OPTIONAL MATCH p = allShortestPaths((psrc)-[:CONNECTED_TO*]-(ptgt))
		WITH (CASE WHEN count(p) = 0 AND genericSrc = genericTgt THEN psrc ELSE nodes(p) END) as parents
		UNWIND parents as relevantParent
		WITH DISTINCT relevantParent
		MATCH (n)<-[:PARENT_OF]-(relevantParent)
		WITH DISTINCT n
		MATCH (n)<-[e:REGULAR_CONNECTION|STAIR_CONNECTION|LIFT_CONNECTION|PARENT_OF]-(m) 
		RETURN n, e, m';

		elapsetime("SUBGRAPH_QUERY_LOCATIONS");
		$limit = 400;
		$counter = 0;
		$graph = new Graph();
		do {
			$set = self::executeQuery($q, $limit, $counter);
			self::loadGraphSet($graph, $set->records());
			$counter += $limit;
		} while ($set->size() == $limit);

		elapsetime("SUBGRAPH_QUERY_LOCATIONS");
		return $graph;
	}

	public static function populateGraph($graph)
	{
		elapsetime("GRAPH_QUERY");
		$q = QUERY_EVERYTHING;

		$limit = 400;
		$counter = 0;
		do {
			$set = self::executeQuery($q, $limit, $counter);
			self::loadGraphSet($graph, $set->records());
			$counter += $limit;
		} while ($set->size() == $limit);

		elapsetime("GRAPH_QUERY");

		return $graph;
	}

	public static function loadGraphSet($graph, $set)
	{
		foreach($set as $nodeRecord)
		{
			$nodes = [];
			foreach(['n','m'] as $nodeLetter)
			{
				$curNode = $nodeRecord->value($nodeLetter);
				$properties = $curNode->values();
				$preNode = $graph->getNode($properties[NODE_UNIQUE_ID]);

				// O nó já existe? Se sim, vamos ligar a edge até ele
				if($preNode != NULL)
				{
					$nodes[$nodeLetter] = $preNode;
					continue;
				}

				// Se não, vamos criar o nó
				$properties["labels"] = $curNode->labels();

				$node = (new Node())->setAttr($properties);
				$nodes[$nodeLetter] = $node;
				$graph->addNode($node);
			}
			$edgeEntity = $nodeRecord->value("e");
			$edgeProps = $edgeEntity->values();
			$edgeProps['type'] = $edgeEntity->type();
			
			$graph->createEdgeBetween($nodes['m'], $nodes['n'], $edgeProps);
		}
		return $graph;
	}

}
Neo::init();