<?php global $route; ?>

<div class="route-wrapper">
	<div class="route">
		<?php 
		$n_steps = count($route->steps); 
		$i_curStep = -1;
		$routeSteps = [];
		?>
		<?php for($i = 0; $i < $n_steps; $i++): ?>
			<?php
			$step = $route->steps[$i];
			$prev = ($i > 0) ? $route->steps[$i-1] : NULL;
			$dir = NULL;
			$isPassage = false;
			$curEdge = $step['edge'];
			$dirType = NULL;

			$isPassage = ($curEdge->getType()=='STAIR_CONNECTION' || $curEdge->getType()=='LIFT_CONNECTION' || $curEdge->getType()=='PARENT_OF');

			if(!$isPassage)
			{
				if($i>0 && $prev['edge']->getDirection()!=NULL)
				{
					$dir = Route::getDirection($curEdge->getDirection(),$prev['edge']->getDirection());
					$dirType = 'soft';	
				}
				else
				{
					$dir = $curEdge->getDirection();
					$dirType = 'hard';
				}
			}
			$stepSrc = $step['source'];
			$stepTgt = $step['target'];

			if(!($stepSrc->getType() == 'waypoint' && $prev['target']->getType() == 'waypoint'))
			{
				if($i_curStep == -1 || $routeSteps[$i_curStep]['dir']!=$dir)
				{
					$routeSteps[++$i_curStep] = [];
					$routeSteps[$i_curStep]['nodes'] = [];
					$routeSteps[$i_curStep]['weight'] = [];
					$routeSteps[$i_curStep]['dir'] = [];
				}
				$routeSteps[$i_curStep]['dir-type'] = $dirType;
			}

			if(end($routeSteps[$i_curStep]['dir']) != $dir)
			{
				$routeSteps[$i_curStep]['dir'][] = $dir;
				$routeSteps[$i_curStep]['weight'][] = 0;
			}

			$routeSteps[$i_curStep]['nodes'][$stepSrc->id()] = $stepSrc;//$stepSrc;
			$routeSteps[$i_curStep]['nodes'][$stepTgt->id()] = $stepTgt;//$stepTgt;

			$i_weight = count($routeSteps[$i_curStep]['weight']) - 1;

			if($curEdge->getType()=='REGULAR_CONNECTION' || $curEdge->getType()=='STAIR_CONNECTION')
				$routeSteps[$i_curStep]['weight'][$i_weight] += $curEdge->getAttr("weight");
			else
				$routeSteps[$i_curStep]['weight'][$i_weight] = NULL;

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
		<?php if(DEBUG) p_dump($routeSteps); ?>
		<?php foreach($routeSteps as $step): ?>
			<?php 
			$step['nodes'] = array_values($step['nodes']);
			$lastIndex = count($step['nodes'])-1; 
			$origin = $step['nodes'][0];
			$target = $step['nodes'][$lastIndex];
			$inBetweenList = array_slice($step['nodes'], 1, $lastIndex-1);


			?>
			<div class="route__step" dir-type="<?php echo $step['dir-type']; ?>">
				<div class="route__step-left" >
					<?php $n_dir = count($step['dir']); ?>
					<?php for($i = 0; $i < $n_dir; $i++): ?>
						<div class="route__step-direction-wrapper">
							<span class="route__step-direction" dir="<?php echo $step['dir'][$i]; ?>"></span>
							<span class="route__step-steps"><?php echo $step['weight'][$i]; ?></span>
						</div>
					<?php endfor; ?>
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
						<div class="route__step-label route__step-row route__step-row--origin">
							<?php echo '<strong>De:</strong> '. $origin->getNames()[0]; ?>
						</div>
						<?php $n_inBetween = count($inBetweenList); ?>
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
						<div class="route__step-label route__step-row route__step-row--target">
							<?php echo '<strong>Até</strong>: '. $target->getNames()[0]; ?>
						</div>
					</div>
				</div>				
			</div>
		<?php endforeach; ?>
	</div>
</div>		
















