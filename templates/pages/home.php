
	<?php
	$client = Neo::getClient();
	global $Request;
// Test connection to server
	if(isset($Request['origin'])
		&& !empty($Request['origin'])
		&& isset($Request['target'])
		&& !empty($Request['target'])
		&& $Request['origin'] != $Request['target'])
	{
		$originNode = explode('|', urldecode($Request['origin']));
		$targetNode = explode('|', urldecode($Request['target']));

		$params = [];
		if(isset($Request['filter-pathway']))
		{
			$params["ignore"]["edge"] = ["type" => []];
			foreach($Request['filter-pathway'] as $filter)
			{
				$params["ignore"]["edge"]["type"][] = $filter;
			}
		}

		elapsetime("SUBGRAPH_TOTAL");
		elapsetime("SUBGRAPH_generation");
		$g = Neo::getSubgraph($originNode,$targetNode);
		elapsetime("SUBGRAPH_generation");

		elapsetime("SUBGRAPH_pathFinding");
		$n1 = $g->getNode($originNode[0]);
		$n2 = $g->getNode($targetNode[0]);
		$path = $g->getPathBetween($n1, $n2, $params);
		elapsetime("SUBGRAPH_pathFinding");
		elapsetime("SUBGRAPH_TOTAL");
		$g->printPath($path);

		echo "subgraph size:".objectSize($g)."<br /><br />";

		elapsetime("GRAPH_TOTAL");
		$g = new Graph();
		elapsetime("GRAPH_generation");
		$neoNodesSet = Neo::populateGraph($g);
		elapsetime("GRAPH_generation");

		echo "graph size:".objectSize($g)."<br /><br />";

		elapsetime("GRAPH_pathFinding");
		$n1 = $g->getNode($originNode[0]);
		$n2 = $g->getNode($targetNode[0]);
		$path = $g->getPathBetween($n1, $n2, $params);
		elapsetime("GRAPH_pathFinding");
		elapsetime("GRAPH_TOTAL");
		$g->printPath($path);
	}
	else {
		$Request['target'] = $Request['origin'] = '';
	}
	?>

	OIE
	<br><br>

	<?php printElapsedTimes(["SUBGRAPH_ALLPATHS","SUBGRAPH_QUERY_GENERIC","SUBGRAPH_QUERY_LOCATIONS"]); ?>
	<br>
	<br>
	<?php printElapsedTimes(["SUBGRAPH_pathFinding","GRAPH_pathFinding"]); ?>
	<br>
	<br>
	<?php printElapsedTimes(["SUBGRAPH_generation","GRAPH_generation"]); ?>
	<br>
	<br>
	<?php printElapsedTimes(["SUBGRAPH_QUERY_LOCATIONS","GRAPH_QUERY"]); ?>
	<br>
	<br>
	<?php printElapsedTimes(["SUBGRAPH_TOTAL","GRAPH_TOTAL"]); ?>
	<br>
	<br>
	<?php printElapsedTimes(); ?>