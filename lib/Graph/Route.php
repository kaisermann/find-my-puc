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

	/* Parseia a rota crua, organizando-a por direções */

	public static function parseRoute($rawRoute)
	{
		$n_steps = count($rawRoute->steps); 
		$curStep = -1;
		$routeSteps = [];

		for($i = 0; $i < $n_steps; $i++)
		{
			$step = $rawRoute->steps[$i];
			$prev = ($i > 0) ? $rawRoute->steps[$i-1] : NULL;
			$dir = NULL;
			$isPassage = false;
			$curEdge = $step['edge'];
			$dirType = 'soft';	

			$isPassage = ($curEdge->getType()=='STAIR_CONNECTION' || $curEdge->getType()=='LIFT_CONNECTION' || $curEdge->getType()=='PARENT_OF');

			if(!$isPassage)
			{
				if($i>0 && $prev['edge']->getDirection()!=NULL)
				{
					$dir = Route::getDirection($curEdge->getDirection(),$prev['edge']->getDirection());
				}
				else
				{
					$exitDir = $step['source']->getAttr('exit_dir');
					if(isset($exitDir))
					{
						$dir = Route::getDirection($curEdge->getDirection(), $exitDir);
					}
					else
					{
						$dir = $curEdge->getDirection();
						$dirType = 'hard';
					}
				}
			}

			// <= 0 para nao considerar direção hard e soft iguais em uma mesma etapa
			if($curStep <= 0 || $routeSteps[$curStep]['dir']!=$dir)
			{
				$routeSteps[++$curStep] = [];
				$routeSteps[$curStep]['nodes'] = [];
				$routeSteps[$curStep]['weight'] = 0;
			}

			$routeSteps[$curStep]['dir-type'] = $dirType;
			$routeSteps[$curStep]['dir'] = $dir;

			$routeSteps[$curStep]['nodes'][$step['source']->id()] = $step['source'];//$step['source'];
			$routeSteps[$curStep]['nodes'][$step['target']->id()] = $step['target'];//$step['target'];

			if($curEdge->getType()=='REGULAR_CONNECTION' || $curEdge->getType()=='STAIR_CONNECTION')
				$routeSteps[$curStep]['weight'] += $curEdge->getAttr("weight");
			else
				$routeSteps[$curStep]['weight'] = NULL;

			if($curEdge->getType()=='STAIR_CONNECTION' || $curEdge->getType()=='LIFT_CONNECTION')
				$routeSteps[$curStep]['type'] = 'FLOOR_CHANGE';
			else
				$routeSteps[$curStep]['type'] = 'REGULAR_CONNECTION';

			if(DEBUG)
			{
				echo $step["source"]->getFullName()."<br />";
				echo " [".$curEdge->getAttr("type")."] [".$curEdge->getAttr("weight")."]<br />";
				echo $step["target"]->getFullName()."<br />";
				if(!$isPassage)
				{
					echo 'sentido ('.$dir.'): '. Route::translateDirection($dir);
				}
				echo "<br /><br /><br /><br />"; 
			}
		}
		return $routeSteps;
	}
}
