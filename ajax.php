<?php 
require_once "config.php";

class AjaxCall
{
	function __construct()
	{
		$r = $_GET;
		if(!isset($r['action']))
			die('0');

		@call_user_func([$this,$r['action']]);
	}

	function findRoute()
	{
		global $Request;
		if(isset($Request['origem'])
			&& !empty($Request['origem'])
			&& isset($Request['destino'])
			&& !empty($Request['destino'])
			&& $Request['origem'] != $Request['destino'])
		{
			$originNode = $Request['origem'];
			$targetNode = $Request['destino'];

			$params = [];
			if(isset($Request['desconsiderar']))
			{
				$params["ignore"]["edge"] = ["type" => []];
				foreach($Request['desconsiderar'] as $filter)
				{
					$params["ignore"]["edge"]["type"][] = $filter;
				}
			}

			elapsetime("SUBGRAPH_TOTAL");
			elapsetime("SUBGRAPH_generation");
			$g = Neo::getSubgraph($originNode,$targetNode);
			elapsetime("SUBGRAPH_generation");

			elapsetime("SUBGRAPH_pathFinding");
			$n1 = $g->getNode($originNode);
			$n2 = $g->getNode($targetNode);
			$path = $g->getPathBetween($n1, $n2, $params);
			//p_dump($path);
			elapsetime("SUBGRAPH_pathFinding");
			elapsetime("SUBGRAPH_TOTAL");
			$g->printPath($path);

			if(DEBUG)
			{
				echo "subgraph size:".objectSize($g)."<br /><br />";

				elapsetime("GRAPH_TOTAL");
				$g = new Graph();
				elapsetime("GRAPH_generation");
				$neoNodesSet = Neo::populateGraph($g);
				elapsetime("GRAPH_generation");

				echo "graph size:".objectSize($g)."<br /><br />";

				elapsetime("GRAPH_pathFinding");
				$n1 = $g->getNode($originNode);
				$n2 = $g->getNode($targetNode);
				$path = $g->getPathBetween($n1, $n2, $params);
				elapsetime("GRAPH_pathFinding");
				elapsetime("GRAPH_TOTAL");
				$g->printPath($path);


				printElapsedTimes(["SUBGRAPH_ALLPATHS","SUBGRAPH_QUERY_GENERIC","SUBGRAPH_QUERY_LOCATIONS"]);
				printElapsedTimes(["SUBGRAPH_pathFinding","GRAPH_pathFinding"]);
				printElapsedTimes(["SUBGRAPH_generation","GRAPH_generation"]);
				printElapsedTimes(["SUBGRAPH_QUERY_LOCATIONS","GRAPH_QUERY"]);
				printElapsedTimes(["SUBGRAPH_TOTAL","GRAPH_TOTAL"]);
				printElapsedTimes();
			}
		}
		else
		{
			die('0');
		}
	}

	function getPlacesList()
	{
		header('Content-Type: application/json');
		$places = [];

		$query = QUERY_SEARCH_LIST;
		$result = Neo::$client->run($query);
		$records = $result->records();

		foreach ($records as $record)
		{
			$places[] = [
			"names" => $record->value("names")
			, "id" => $record->value("id")
			];
		}
		$places = json_encode($places);
		print_r($places);
	}
}
new AjaxCall();
?>
