<?php
require_once "lib/loader.php";

// Test connection to server
if(isset($_GET['from'])
&& !empty($_GET['from'])
&& isset($_GET['to'])
&& !empty($_GET['to'])
&& $_GET['from'] != $_GET['to'])
{
	$fromNode = explode('|', $_GET['from']);
	$toNode = explode('|', $_GET['to']);
	elapsetime("SUBGRAPH_TOTAL");
	elapsetime("SUBGRAPH_generation");
	$g = Neo::getSubgraph($fromNode,$toNode);
	elapsetime("SUBGRAPH_generation");

	elapsetime("SUBGRAPH_pathFinding");
	$n1 = $g->getNode($fromNode[0]);
	$n2 = $g->getNode($toNode[0]);
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
	$n1 = $g->getNode($fromNode[0]);
	$n2 = $g->getNode($toNode[0]);
	$path = $g->getPathBetween($n1, $n2);
	elapsetime("GRAPH_pathFinding");
	elapsetime("GRAPH_TOTAL");
	$g->printPath($path);
}
else {
	$_GET['to'] = $_GET['from'] = '';
}
?>

<form method="get" action="./">
	<?php

	$query = "MATCH (n) WHERE (NOT n:Ignorable) RETURN n.id as id, n.names as names, labels(n) as labels";
	$result = $client->run($query);
	 ?>
	<?php foreach (["from","to"] as $inputName): ?>
		<select name="<?php echo $inputName; ?>">
			<?php
			foreach ($result->records() as $record)
			{
				echo '<option value="'.$record->value("id").'|'.implode(",", $record->value("labels")).'">';
				echo $record->value("names")[0];
				echo '</option>';
			}
			?>
		</select>
	<?php endforeach; ?>
	<input type="submit" value="Enviar"/>
</form>

<br>
<br>
<br>
<br>
<br>

<?php
printElapsedTimes(["SUBGRAPH_ALLPATHS","SUBGRAPH_QUERY_GENERIC","SUBGRAPH_QUERY_LOCATIONS"]);
printElapsedTimes(["SUBGRAPH_pathFinding","GRAPH_pathFinding"]);
printElapsedTimes(["SUBGRAPH_generation","GRAPH_generation"]);
printElapsedTimes(["SUBGRAPH_QUERY_LOCATIONS","GRAPH_QUERY"]);
printElapsedTimes(["SUBGRAPH_TOTAL","GRAPH_TOTAL"]);
printElapsedTimes();
?>
