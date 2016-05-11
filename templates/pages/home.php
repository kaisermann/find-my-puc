
	<?php
	$client = Neo::getClient();
	global $Request;
// Test connection to server
	if(isset($Request['from'])
		&& !empty($Request['from'])
		&& isset($Request['to'])
		&& !empty($Request['to'])
		&& $Request['from'] != $Request['to'])
	{
		$fromNode = $Request['from'];
		$toNode = $Request['to'];
		elapsetime("SUBGRAPH_TOTAL");
		elapsetime("SUBGRAPH_generation");
		$g = Neo::getSubgraph($fromNode,$toNode);
		elapsetime("SUBGRAPH_generation");

		elapsetime("SUBGRAPH_pathFinding");
		$n1 = $g->getNode($fromNode);
		$n2 = $g->getNode($toNode);
		$path = $g->getPathBetween($n1, $n2);
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
		$n1 = $g->getNode($fromNode);
		$n2 = $g->getNode($toNode);
		$path = $g->getPathBetween($n1, $n2);
		elapsetime("GRAPH_pathFinding");
		elapsetime("GRAPH_TOTAL");
		$g->printPath($path);
	}
	else {
		$Request['to'] = $Request['from'] = '';
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
	?>