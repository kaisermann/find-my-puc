<?php
//namespace Graph;
require_once "Edge.php";
require_once "Node.php";
require_once "GraphQueue.php";

class Graph
{
	private $nodes = [];
	private $nodeCount = 0;
	private $edgeCount = 0;

	public function __CONSTRUCT()
	{
	}

	public function getNode($id)
	{
		return getKeyValue($this->nodes,$id);
	}

	public function addNode($node)
	{
		$nodeID = $node->id();
		if(arrayContainskey($this->nodes, $nodeID))
			throw new Exception("Trying to add same node twice", 1);
		$this->nodes[$nodeID] = $node;
		$this->nodeCount++;
		return $this;
	}

	public function createEdgeBetween($n1, $n2, $properties)
	{
		$e1 = new Edge($n1, $n2, $properties);
		if(arrayContainskey($properties, EDGE_DIRECTION_ID))
		{
			$dir = intval($properties[EDGE_DIRECTION_ID]);
			$dir += ($dir <= 4) ? 4 : -4;
			$properties[EDGE_DIRECTION_ID] = $dir;
		}
		$e2 = new Edge($n2, $n1, $properties);

		$n1->addEdge($e1);
		$n2->addEdge($e2);
		$this->edgeCount += 2;
	}

	public function getRelevantParentsBetween($start, $end)
	{
		$genericPaths = [];
		$relevantParents = [];
		$minsize = PHP_INT_MAX;
		//self::printMe();
		self::GetAllPathsBetween(
			$end
			, [$start => self::getNode($start)]
			, $genericPaths, $minsize);
		$maxsize = $minsize;
		echo "<".$minsize.">";
		echo "<".$maxsize."><br>";
		foreach($genericPaths as $path)
		{
			if(count($path)<=$maxsize)
			{
				foreach($path as $nodeID => $val)
					$relevantParents[$nodeID] = 1;
			}
		}
		return $relevantParents;
	}

	public function getAllPathsBetween($end, $visited, &$genericPaths, &$minsize)
	{
		$edges = end($visited)->getEdges();

		foreach ($edges as $e)
		{
			$node = $e->getNodeB();
			if(array_key_exists($node->id(), $visited))
				continue;

			if($node->id()==$end)
			{
				$visited[$end] = $node;

				$n_visited = count($visited);
					$genericPaths[] = $visited;
				if($n_visited <= $minsize)
				{
					$minsize = $n_visited;
				}

				$value = end($visited);
				$key = key($visited);
				unset($visited[$key]);

				break;
			}
		}

		foreach($edges as $e)
		{
			$node = $e->getNodeB();

			if(array_key_exists($node->id(), $visited) || $node->id() == $end)
				continue;

			$visited[$node->id()] = $node;
			self::getAllPathsBetween($end, $visited, $genericPaths, $minsize);

			// remove ultimo visitado
			$value = end($visited);
			$key = key($visited);
			unset($visited[$key]);
		}
	}

	private function printAllPaths($visited)
	{
		foreach($visited as $node)
		{
			print_r($node->id()." ");
		}
		echo "<br/>";
	}

	public function getPathBetween($start, $finish, $params = [])
	{
		$queue = new GraphQueue();

		$startID = $start->id();
		$startLabels = $start->getLabels();

		$finishID = $finish->id();
		$finishLabels = $finish->getLabels();

		$params = array_merge_recursive([
			"ignore" =>
			[
			"edge" => [
			"type" => ["PARENT_OF","CONNECTED_TO"]
			],
			"node" => [
			"labels" => "Untraversable"
			]
			]
			], $params);

		// Se a origem ou o destino são genéricos,
		// devemos poder percorrer as relações de parentesco
		if(in_array("Generic", $startLabels) || in_array("Generic", $finishLabels))
		{
			$key = array_search('PARENT_OF', $params["ignore"]["edge"]["type"]);
			unset($params["ignore"]["edge"]["type"][$key]);
		}
		$ignorableEntities = isset($params["ignore"]) ? $params["ignore"] : [];
		$ignorableAttributes = [];
		$distances = [];
		$previous = [];

		$distances[$startID] = 0;
		$queue->insert($startID, 0);

		foreach ( $this->nodes AS $nodeID => $value )
		{
			$previous[$nodeID] = [];
			if($nodeID===$startID)
				continue;
			$distances[$nodeID] = PHP_INT_MAX;
			$queue->insert($nodeID, PHP_INT_MAX);
		}
		$queue->top();

		while ($queue->valid())
		{
			$curNode = $queue->current();

			// Chegou no destino?
			if ($curNode === $finishID)
			{
				// Sim!
				$path["steps"] = [];
				$path["raw"] = [];
				$path["weight"] = 0;
				while ($previous[$curNode])
				{
					$nextID = $curNode;
					$nextNode = $this->getNode($nextID);
					$curEdge = $previous[ $curNode ]["edge"];

					$path["raw"][] = $this->getNode($nextID);
					$path["raw"][] = $curEdge;
					if($curEdge->getAttr("type")=="LINKED_TO")
					{
						$path["weight"] += $curEdge->getAttr("weight");
					}

					$curNode = $previous[ $curNode ]["node"];
					$prevID = $curNode;
					$prevNode = $this->getNode($prevID);

					$path["steps"][] = ["source" => $prevNode, "target" => $nextNode, "edge" => $curEdge];
				}
				$path["raw"][] =  $this->getNode($startID);
				$path["raw"] = array_reverse($path["raw"]);
				$path["steps"] = array_reverse($path["steps"]);
				return $path;
				// a-b-c-d-e
			}

			// Não encontrou caminho algum
			if ($curNode === null || $distances[$curNode] === PHP_INT_MAX)
				break;

			// Vamos percorrer as arestas que saem do nó em questão

			foreach ($this->nodes[$curNode]->getEdges() as $edge)
			{
				$jump = false;

				$nodeA = $edge->getNodeA();
				foreach($ignorableEntities as $ignorableEntity => $ignorableAttrs)
				{
					$entity = $ignorableEntity === "edge" ? $edge : $nodeA;
					$entityAttributes = $entity->getAttributes();
					foreach ($ignorableAttrs as $ignorableAttr => $value)
					{
						$entityAttr = getKeyValue($entityAttributes, $ignorableAttr);
						if($entityAttr==NULL)
							continue;
						// O atributo que queremos comparar é um array?
						// Se sim, o valor buscado está contido nele?
						// Se não, compara a string do atributo com o valor

						// "", ""
						// "", []
						// [], ""
						// [], []

						if(
							(
								isArray($entityAttr) &&
								((isArray($value) && count(array_intersect($entityAttr, $value))>0)
									||
									(in_array($value, $entityAttr)))
								)
							||
							(isArray($value) && in_array($entityAttr, $value))
							||
							($entityAttr === $value))
						{
							$jump = true;
						}
					}
				}

				if($jump)
					continue;

				$nodeB = $edge->getNodeB();
				$nodeB_ID = $nodeB->id();
				$edgeType = $edge->getAttr("type");
				$edgeWeight = $edge->getAttr(EDGE_WEIGHT_ID);

				// Colocando um peso muito alto para ligações de parentesco, 
				// o algoritmo faz com que só atravessemos uma ligação do tipo se for
				// extremamente necessário.
				if($edgeType == "PARENT_OF" || $edgeType == "CONNECTED_TO")
					$edgeWeight = 9999;

				$alt = $distances[ $curNode ] + $edgeWeight;
				if ( $alt < $distances[$nodeB_ID] )
				{
					$distances[ $nodeB_ID ] = $alt;
					$previous[ $nodeB_ID ] = ["node" => $curNode, "edge" => $edge];
					$queue->insert( $nodeB_ID, $alt );
				}
			}
			$queue->next();
		}
		return NULL;
	}

	public function printPath($path)
	{
		$countpath = count($path["raw"]);
		for($i = 0; $i < $countpath; $i+=2)
		{
			echo $path["raw"][$i]->getFullName();
			//echo "<br />";
			//if($i+1<$countpath)
			//	echo $path["raw"][$i+1]->getEdgeUniqueID();
			echo "<br /><br />";
		}

		echo "steps:<br /><br />";
		foreach($path["steps"] as $step)
		{
			continue;
			echo $step["source"]->getFullName()."<br />";
			echo $step["edge"]->getEdgeUniqueID()." [".$step["edge"]->getAttr("type")."] [".$step["edge"]->getAttr("weight")."]<br />";
			echo $step["target"]->getFullName()."<br />";
			echo "<br /><br />";
		}
		echo "size: ".$path["weight"]." passos<br /><br /> ";
	}

	public function printMe()
	{
		echo "-[GRAPH]<br />";
		foreach($this->nodes as $node)
			$node->printMe();
		return $this;
	}
}
