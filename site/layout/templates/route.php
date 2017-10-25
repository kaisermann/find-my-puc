<?php global $route; ?>
<?php global $graph; ?>

<div class="route-wrapper">
	<div class="route">
		
		<?php 
		$stepNumber = -1;
		$finalTarget = end(end($route)['nodes']);
		$isFinalTargetGeneric = isset(array_flip($finalTarget->getLabels())['Generic']);
		$shouldBreak = false;
		?>

		<?php foreach($route as $step): ?>

			<?php 
			++$stepNumber;
			$step['nodes'] = array_values($step['nodes']);
			$lastIndex = count($step['nodes'])-1; 
			$origin = $step['nodes'][0];
			$originType = $origin->getType();

			$target = $step['nodes'][$lastIndex];
			$targetType = $target->getType();
			$inBetweenList = array_slice($step['nodes'], 1, $lastIndex-1);

			?>
			<?php if($step['type'] == 'FLOOR_CHANGE'): ?>
				<?php 
				$parentA = $graph->getNode($origin->getAttr('parent'));
				$parentB = $graph->getNode($target->getAttr('parent'));
				?>
				<div class="route__step">
					<div class="route__step-left">
						<span class="route__step-direction route__step-direction--hidden"></span>
						<span class="route__step-steps"><?php echo $step['weight']; ?> </span>
					</div>
					<div class="route__step-right">
						<div class="route__step-label route__step-label--big h-bg--blue">
							<?php 
							echo '<strong>Troca de nível<br>'.$parentA->getNames()[0].' > '.$parentB->getNames()[0].'</strong>';
							?>
						</div>
					</div>
				</div>
			<?php else: ?>
				<div class="route__step" dir="<?php echo $step['dir']; ?>" dir-type="<?php echo $step['dir-type']; ?>">
					<div class="route__step-left">
						<span class="route__step-direction"></span>
						<?php if($step['weight']!=NULL): ?>
							<span class="route__step-steps">
								<?php echo $step['weight']; ?>
							</span>
							<small class="route__step-meters"><?php echo ($step['weight']*0.82).'m'; ?></small>
						<?php endif; ?>
					</div>
					<div class="route__step-right">
						<?php if($originType!='waypoint'): ?>
							<div class="route__step-row route__step-row--origin">
								<div class="route__node-name">
									<?php 
									$names = $origin->getNames();
									if($originType == "passagem")
										echo '<div class="route__step-instruction">De:</div> ';
									else
										echo '<div class="route__step-instruction">De:</div> ';

									echo '<div class="route__node-info">';
									echo '<div class="route__node-names">';
									echo $names[0]; 

									if($originType!=NULL && $originType!="passagem")
										echo  ' <small>('.ucfirst($origin->getAttr('subtype')).')</small>';	

									$n_names = count($names);
									if($n_names>1)
									{
										for($a = 1; $a < $n_names; $a++)
											echo '<small class="route__node-names__item">'.$names[$a].'</small>';
									}
									echo '</div>';
									echo '</div>';
									if($originType != "passagem")
									{
										$img = Main::getNodeImage($origin);
										if($img)
											echo '<a href="'.$img.'" data-title="'.$names[0].'" class="route__node-img-wrapper js-route__node-img" style="background-image:url('.$img.');"></a>';
									}
									?>
								</div>
							</div>
						<?php endif; ?>
						<?php if($step['dir']): ?>
							<div class="route__step-row route__step-label route__step-verbose-direction">
								<?php 
								if($step['dir-type']==='hard')
									echo 'Olhando para Norte siga ';
								else if($stepNumber == 0 && $step['nodes'][0]->getAttr('exit_dir')!==NULL)
									echo 'Saindo do local, siga ';
								else
									echo 'Siga ';
								echo Route::translateDirection($step['dir'], true); 
								?>
							</div>
						<?php endif; ?>
						<?php 
						$inBetweenList = array_values(array_filter($inBetweenList, function($var)
						{
							return $var->getType()!='waypoint';
						}));
						$n_inBetween = count($inBetweenList); 
						?>
						<?php if($n_inBetween>0): ?>
							<div class="route__step-row route__step-row--between">
								<div class="route__step-instruction">Passando por:</div>
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
						<?php if($targetType!="waypoint"): ?>
							<div class="route__step-row route__step-row--target">
								<div class="route__node-name">

									<?php 
									$names = $target->getNames();
									if($targetType == "passagem")
										echo '<div class="route__step-instruction route__step-instruction--big">Passe por:</div>';
									else
										echo '<div class="route__step-instruction">Até:</div>';

									echo '<div class="route__node-info">';
									echo '<div class="route__node-names">';
									if($isFinalTargetGeneric && $graph->checkNodeParent($target, $finalTarget->id()))
									{
										$target = $finalTarget;
										$shouldBreak = true;
									}
									echo $names[0];

									if($targetType != NULL && $targetType!="passagem" && $target->getLabels()[0]!='Generic')
										echo  ' <small>('.ucfirst($target->getAttr('subtype')).')</small>';	

									$n_names = count($names);
									if($n_names>1)
									{
										for($a = 1; $a < $n_names; $a++)
											echo '<small class="route__node-names__item">'.$names[$a].'</small>';
									}
									echo '</div>';
									echo '</div>';
									if($targetType != "passagem")
									{
										$img = Main::getNodeImage($target);
										if($img)
											echo '<a href="'.$img.'" data-title="'.$names[0].'" class="route__node-img-wrapper js-route__node-img" style="background-image:url('.$img.');"></a>';
									}
									?>
								</div>
							</div>
						<?php endif; ?>
					</div>				
				</div>
			<?php endif; ?>
			<?php 

			if($shouldBreak)
				break;
			?>
		<?php endforeach; ?>
	</div>
</div>		
















