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
			?>
		<?php endfor; ?>
		<?php $tmpList = []; ?>
		<?php foreach($routeSteps as $step): ?>
			<?php 
			$step['nodes'] = array_values($step['nodes']);
			$lastIndex = count($step['nodes'])-1; 
			$origin = $step['nodes'][0];
			$target = $step['nodes'][$lastIndex];
			$inBetweenList = array_slice($step['nodes'], 1, $lastIndex-1);

			?>
			<?php if($step['type'] == 'FLOOR_CHANGE'): ?>
				<div class="route__step">
					<div class="route__step-label route__step-label--big h-bg--green">
						<?php echo '<strong>Troca de andar</strong>';?>
					</div>
				</div>
			<?php else: ?>
				<div class="route__step" dir="<?php echo $step['dir']; ?>" dir-type="<?php echo $step['dir-type']; ?>">
					<div class="route__step-left">
						<span class="route__step-direction"></span>
						<span class="route__step-steps"><?php echo $step['weight']; ?></span>
					</div>
					<div class="route__step-right">
						<?php if($step['dir']): ?>
							<div class="route__step-label route__step-verbose-direction">
								<?php if($step['dir-type']==='hard')
								echo 'Olhando para Norte siga ';
								else 
									echo 'Siga ';
								echo Route::translateDirection($step['dir'], true); 
								?>
							</div>
						<?php endif; ?>
						<div class="route__step-nodes">
							<?php if($origin->getType()!='waypoint'): ?>
								<div class="route__step-label route__step-row route__step-row--origin">
									<?php echo '<strong>De:</strong> '. $origin->getNames()[0]; ?>
								</div>
							<?php endif; ?>
							<?php 
							$inBetweenList = array_filter($inBetweenList, function($var)
							{
								return $var->getType()!='waypoint';
							});
							$n_inBetween = count($inBetweenList); 
							?>
							<?php if($n_inBetween>0): ?>
								<div class="route__step-row route__step-row--between">
									<span class="route__step-label"><strong>Passando por:</strong></span>
									<div class="route__step-between__nodes">
										<div class="route__step-between__node route__step-between__node--first">
											<?php echo $inBetweenList[0]->getNames()[0]; ?>
										</div>
										<?php $tmpInBetweenList = array_slice($inBetweenList, 1, $n_inBetween-2) ?>
										<?php if(count($tmpInBetweenList)>0): ?>
											<div class="route__step-between__list-wrapper">
												<div class="route__step-between__list">
													<?php foreach($tmpInBetweenList as $inBetween): ?>
														<div class="route__step-between__node">
															<?php echo $inBetween->getNames()[0]; ?>
														</div>
													<?php endforeach; ?>
												</div>
											</div>
										<?php endif; ?>

										<?php if($n_inBetween>1): ?>
											<div class="route__step-between__node route__step-between__node--last">
												<?php echo $inBetweenList[$n_inBetween-1]->getNames()[0]; ?>
											</div>
										<?php endif; ?>
									</div>
								</div>
							<?php endif; ?>
							<?php if($target->getType()!="waypoint"): ?>
								<div class="route__step-label route__step-row route__step-row--target">
									<?php echo '<strong>Até</strong>: '. $target->getNames()[0]; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>				
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>		
















