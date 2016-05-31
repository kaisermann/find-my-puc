<?php 
require_once "config.php";

class AjaxCall
{
	function __construct()
	{
		$r = $_GET;
		if(!isset($r['action']))
			die('0');

		call_user_func([$this,$r['action']]);
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

			if(DEBUG)
			{
				elapsetime("SUBGRAPH_TOTAL");
				elapsetime("SUBGRAPH_generation");
			}
			
			$g = Neo::getSubgraph($originNode,$targetNode);
			
			if(DEBUG)
			{
				elapsetime("SUBGRAPH_generation");
				elapsetime("SUBGRAPH_pathFinding");
			}

			$n1 = $g->getNode($originNode);
			$n2 = $g->getNode($targetNode);
			
			$GLOBALS['graph'] = $g;
			$GLOBALS['route'] = Route::parseRoute($g->getPathBetween($n1, $n2, $params));

			if(DEBUG)
			{
				elapsetime("SUBGRAPH_pathFinding");
				elapsetime("SUBGRAPH_TOTAL");
			}

			getTemplatePart('layout/templates/route');

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

				printElapsedTimes(["SUBGRAPH_pathFinding","GRAPH_pathFinding"]);
				echo '<br><br>';
				printElapsedTimes(["SUBGRAPH_generation","GRAPH_generation"]);
				echo '<br><br>';
				printElapsedTimes(["SUBGRAPH_QUERY_LOCATIONS","GRAPH_QUERY"]);
				echo '<br><br>';
				printElapsedTimes(["SUBGRAPH_TOTAL","GRAPH_TOTAL"]);
				echo '<br><br>';
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
			, "labels" => $record->value("labels")
			];
		}
		$places = json_encode($places);
		print_r($places);
	}
}
new AjaxCall();
?>
