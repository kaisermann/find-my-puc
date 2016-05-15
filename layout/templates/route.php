<?php global $route; ?>

<div class="route-wrapper">
	<div class="route">
		<?php 
		$n_steps = count($route->steps); 
		$curStep = -1;
		$routeSteps = [];
		?>
		<?php for($i = 0; $i < $n_steps; $i++): ?>
			<?php
			$step = $route->steps[$i];
			$prev = ($i > 0) ? $route->steps[$i-1] : NULL;
			$dir = NULL;
			$isPassage = false;
			$curEdge = $step['edge'];

			$isPassage = ($curEdge->getType()=='STAIR_CONNECTION' || $curEdge->getType()=='LIFT_CONNECTION' || $curEdge->getType()=='PARENT_OF');

			if(!$isPassage)
			{
				if($i>0)
					$dir = Route::getDirection($curEdge->getDirection(),$prev['edge']->getDirection());		
				else
					$dir = $curEdge->getDirection();
			}

			if($curStep == -1 || $routeSteps[$curStep]['dir']!=$dir)
			{
				$routeSteps[++$curStep] = [];
				$routeSteps[$curStep]['nodes'] = [];
				$routeSteps[$curStep]['weight'] = 0;
			}
			$routeSteps[$curStep]['dir'] = $dir;

			$routeSteps[$curStep]['nodes'][$step['source']->getFullName()] = 1;//$step['source'];
			$routeSteps[$curStep]['nodes'][$step['target']->getFullName()] = 1;//$step['target'];

			if($curEdge->getType()=='REGULAR_CONNECTION' || $curEdge->getType()=='STAIR_CONNECTION')
				$routeSteps[$curStep]['weight'] += $curEdge->getAttr("weight");
			else
				$routeSteps[$curStep]['weight'] = NULL;

			echo $step["source"]->getFullName()."<br />";
			echo " [".$curEdge->getAttr("type")."] [".$curEdge->getAttr("weight")."]<br />";
			echo $step["target"]->getFullName()."<br />";
			if(!$isPassage)
			{
				echo 'sentido ('.$dir.'): '. Route::translateDirection($dir);
			}
			echo "<br /><br /><br /><br />"; ?>
		<?php endfor; ?>
		<?php 
		p_dump($routeSteps);
		echo "size: ".$route->length." passos<br /><br /> "; 
		?>
		<div class="route__item">
		</div>
	</div>
</div>		
















