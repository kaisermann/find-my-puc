<?php
//namespace Graph;

require_once "Entity.php";

class Edge extends Entity
{
	public $nodeA = NULL, $nodeB = NULL;

	public function __CONSTRUCT($n1, $n2, $data = NULL)
	{
		$this->nodeA = $n1;
		$this->nodeB = $n2;

		if(isset($data))
			$this->setAttr($data);
	}

	public function getNodeA() { return $this->nodeA; }
	public function getNodeB() { return $this->nodeB; }

	public function getType() { return $this->getAttr("type"); }
	public function getDirection() { return $this->getAttr("dir"); }

	public function getEdgeUniqueID() { return $this->nodeA->getAttr(NODE_UNIQUE_ID)."::".$this->nodeB->getAttr(NODE_UNIQUE_ID); }

	public function printMe($identation = 0)
	{
		echo str_repeat("--", $identation+1). "[EDGE]<br />";
		echo sprintf("%sNODE A: %s - %s<br />", str_repeat("--", $identation + 2), $this->nodeA->getAttr(NODE_UNIQUE_ID), $this->nodeA->getAttr(NODE_NAMES_ID)[0]);
		echo sprintf("%sNODE B: %s - %s <br />", str_repeat("--", $identation + 2), $this->nodeB->getAttr(NODE_UNIQUE_ID), $this->nodeB->getAttr(NODE_NAMES_ID)[0]);
		return $this->printAttrs($identation + 3);
	}
}
