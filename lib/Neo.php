<?php

use GraphAware\Neo4j\Client\ClientBuilder;

class Neo
{

	public static $client = NULL;

	public static function getClient() { return self::$client; }

	public static function init()
	{
		if(self::$client!=NULL)
			return self::$client;

		self::$client = ClientBuilder::create()
		->addConnection('default', 'http://neo4j:hunter155@localhost:7474')
		->build();

		return self::$client;
	}

	/*
	public static function getCommonParents($nodeA_ID, $nodeB_ID)
	{
		$q = 'MATCH (n{id:"'.$nodeA_ID.'"})<-[r:PARENT_OF*]-(p)
		RETURN p as parents
		UNION
		MATCH (m{id:"'.$nodeB_ID.'"})<-[r:PARENT_OF*]-(q)
		RETURN q as parents';
		$parentRecords = self::$client->run($q);
		return array_map(function($o){return $o->value("parents")->value("id");}, $parentRecords->records());
	}

	public static function getSubgraphOfParents($parents)
	{
		$q = "MATCH (n)-[e]->(m)
		WITH "."['".implode("','", $parents)."']"." AS parents, n, e, m
		WHERE (n:Location AND (n.parent IN parents OR m.parent IN parents)) or (n:Generic AND n.id IN parents AND m.id IN parents)
		RETURN n, m, e";
		$subgraphRecord = self::$client->run($q);
		return self::loadGraphSet(new Graph(), $subgraphRecord);
	}
	*/

	public static function executeQuery($query, $limit = 400, $skip = 0)
	{
		$query = $query.' SKIP '.$skip.' '.'LIMIT '.$limit;
		$result = self::$client->run($query);
		echo $query;
		echo '<br /><br />';
		return $result;
	}

	public static function getSubgraph($nodeA, $nodeB)
	{
		$q = 'match (n)-[e]->(m) RETURN n, m, e';

		$nodeA_ID = $nodeA[0];
		$nodeA_Type = $nodeA[1];

		$nodeB_ID = $nodeB[0];
		$nodeB_Type = $nodeB[1];


		$parentA = explode("n", $nodeA_ID);
		$parentB = explode("n", $nodeB_ID);

		array_shift($parentA);array_shift($parentB);
		array_pop($parentA);array_pop($parentB);

		if(count($parentA)<1 && count($parentB)<1)
		{
			echo "TA QUERENDO ESTRAGAR O SITE, NÉ??";
			exit;
		}

		// Determina o id dos pais do no A e B
		$parentA = 'n'.implode("n",$parentA);
		$parentB = 'n'.implode("n",$parentB);
		
		$q = '
		MATCH (src{id:"'.$nodeA_ID.'"}),(tgt{id:"'.$nodeB_ID.'"})
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
		MATCH (n)<-[e:LINKED_TO|PARENT_OF]-(m) 
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

	public static function getGenericGraph()
	{
		$q = 'match (n:Generic)-[e:CONNECTED_TO]->(m:Generic) RETURN n, m, e';
		$limit = 400;
		$counter = 0;
		$parentGraph = new Graph();
		do {
			$set = self::executeQuery($q, $limit, $counter);
			self::loadGraphSet($parentGraph, $set->records());
			$counter += $limit;
		} while ($set->size() == $limit);

		return $parentGraph;
	}

	public static function populateGraph($graph)
	{
		elapsetime("GRAPH_QUERY");
		$q = 'match (n)-[e]->(m) RETURN n, m, e';

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
			$graph->createEdgeBetween($nodes['n'], $nodes['m'], $edgeProps);
		}
		return $graph;
	}

}
Neo::init();