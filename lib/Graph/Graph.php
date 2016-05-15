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

	public function __CONSTRUCT() {}
	
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
		if(DEBUG)
			p_dump($params);

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
				$route = new Route();
				$route->nodes = [];
				$route->length = 0;

				while ($previous[$curNode])
				{
					$nextID = $curNode;
					$nextNode = $this->getNode($nextID);
					$curEdge = $previous[$curNode]["edge"];

					$route->nodes[] = $this->getNode($nextID);
					$route->nodes[] = $curEdge;
					// Se for uma conexão valida, adicionar ao peso
					$curEdgeType = $curEdge->getAttr("type");
					if($curEdgeType == 'REGULAR_CONNECTION' || $curEdgeType == 'STAIR_CONNECTION')
					{
						$route->length += $curEdge->getAttr("weight");
					}

					$curNode = $previous[ $curNode ]["node"];
					$prevID = $curNode;
					$prevNode = $this->getNode($prevID);

					$route->steps[] = ["source" => $prevNode, "target" => $nextNode, "edge" => $curEdge];
				}
				$route->nodes[] =  $this->getNode($startID);
				$route->nodes = array_reverse($route->nodes);
				$route->steps = array_reverse($route->steps);
				return $route;
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

	public function __debugInfo() { $this->printMe(); }
	
	public function printMe()
	{
		echo "-[GRAPH]<br />";
		foreach($this->nodes as $node)
			$node->printMe();
		return $this;
	}
}
