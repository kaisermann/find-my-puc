<?php
//namespace Graph;

class Route
{
	public $nodes = [];
	public $steps = [];
	public $length = -1;

	private static $m = [1,2,3,4,5,6,7,8];

	public function __CONSTRUCT() {}

	public static function getDirection($current, $prev)
	{
		/*
		echo 'dir prev: '. $prev;
		echo '<br>dir atual: '. $current;
		echo '<br><br>';
		var_dump(Route::$m);
		*/

		if($current == $prev)
			return 1;

		for($i = 0; Route::$m[0] != $prev; $i++)
		{
			array_push(Route::$m, array_shift(Route::$m));

			// Anti-loop
			if($i>8)
				break;
		}
		/*
		echo '<br>';
		var_dump(Route::$m);
		*/
		Route::$m = array_flip(Route::$m);
		$val = Route::$m[$current]+1;
		Route::$m = array_flip(Route::$m);
		return $val;

	}

	public static function translateDirection($dir)
	{
		switch($dir)
		{
			case 1: return 'Em frente';

			case 2: return 'Em frente à direita';
			case 3: return 'À direita';

			case 4: return 'Para à direita';
			case 5: return 'Para trás';
			case 6: return 'Para trás à esquerda';

			case 7: return 'À esquerda';
			case 8: return 'Em frente à esquerda';
			default:
				return NULL;
		}

	}
}
