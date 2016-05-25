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

	public static function translateDirection($dir, $tolower = false)
	{
		switch($dir)
		{
			case 1: $str = 'Em frente'; break;

			case 2: $str = 'Em frente à direita'; break;
			case 3: $str = 'À direita'; break;

			case 4: $str = 'Para trás à direita'; break;
			case 5: $str = 'Para trás'; break;
			case 6: $str = 'Para trás à esquerda'; break;

			case 7: $str = 'À esquerda'; break;
			case 8: $str = 'Em frente à esquerda'; break;
			default:
				$str = NULL;
				break;
		}
		return $tolower ? @mb_strtolower($str) : $str;

	}
}
