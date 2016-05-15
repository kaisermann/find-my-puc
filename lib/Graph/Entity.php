<?php
//namespace Graph;

class Entity
{
	private $attributes = [];

	public function getAttributes() { return $this->attributes; }
	public function getAttr($id)
	{
		return getKeyValue($this->attributes, $id);
	}

	public function setAttr($keyOrArray, $value = NULL)
	{
		if(isArray($keyOrArray))
		{
			foreach($keyOrArray as $key => $value)
				$this->attributes[$key] = $value;
		}
		else {
			$this->attributes[$keyOrArray] = $value;
		}
		return $this;
	}

	public function printAttrs($identation = 0) { arrayPrint($this->attributes, $identation); return $this; }
	
	public function __debugInfo() { $this->printMe(); }
}
