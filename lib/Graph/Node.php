<?php
//namespace Graph;

class Node extends Entity
{
	private $edges = [];

	public function __CONSTRUCT()
	{
	}

	public function addEdge($edge) { $this->edges[] = $edge; }
	public function getEdge($id=NULL) { return getKeyValue($this->edges, $id); }
	public function getEdges() { return $this->edges; }

	public function id() { return $this->getAttr(NODE_UNIQUE_ID); }
	public function getLabels() { return $this->getAttr("labels"); }

	public function getType() { return $this->getAttr("type"); }
	public function getSubType() { return $this->getAttr("subtype"); }
	public function getNames() { return $this->getAttr("names"); }

	public function getFullName() { return $this->getAttr(NODE_NAMES_ID)[0]." - ".$this->getAttr(NODE_UNIQUE_ID); }
	
	public function printMe($identation = 0)
	{
		echo $this->getFullName();
		return $this;
		echo str_repeat("--", $identation + 1). "[NODE]<br />";
		$this->printAttrs($identation + 2);
		echo str_repeat("--", $identation + 3). count($this->edges). " Edges<br />";
		foreach($this->edges as $edge)
			$edge->printMe($identation + 2);
		return $this;
	}
}
