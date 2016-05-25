<?php global $Request; ?>
<aside id="header">
	<div class="header__logo --pd-h-sm txt--xs-center">
		<strong><?php Main::getAppName(true); ?></strong>
	</div>
	<div class="header__search">
		<form id="search-form">
			<label for="origem" class="btn search__place ">
				<span class="search__label">Origem</span>
				<span class="search__value"></span>
			</label>
			<label for="destino" class="btn search__place h--mg-t-5">
				<span class="search__label">Destino</span>
				<span class="search__value"></span>
			</label>
			<div class="filters h--mg-t-5">
				<span class="filters__title"><strong>Filtros</strong></span>
				<div class="filters__section">
					<span class="filters__section__title"><strong>Desconsiderar</strong></span>
					<?php $filters = [['STAIR_CONNECTION','Escadas','stair'],['LIFT_CONNECTION','Elevadores','lift']]; ?>
					<?php foreach($filters as $filter): ?>
						<?php $filterArray = isset($Request['desconsiderar']) ? $Request['desconsiderar'] : []; ?>
						<div class="filters__item">
							<span class="form-control form-control--radio">
								<input type="checkbox" name="desconsiderar[]" class="js-radio" id="desconsiderar-<?php echo $filter[2]; ?>" value="<?php echo $filter[0]; ?>" <?php echo ((in_array($filter[0],$filterArray))?'checked':''); ?>>
								<label for="desconsiderar-<?php echo $filter[2]; ?>"><?php echo $filter[1]; ?></label>
							</span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<button class="btn btn--scale btn--submit h--mg-t-5">
				<span>Me ajuda!</span>
			</button>
			<input type="hidden" name="origem" class="search-origem">
			<input type="hidden" name="destino" class="search-destino">
		</form>
	</div>
</aside>
<div class="search-viewer">
	<div class="search-viewer__bg"></div>
	<aside class="search-viewer__content">
		<button class="search-viewer__close-btn btn">Fechar</button>
		<input type="text" class="search-viewer__input form-control" placeholder="Buscar...">
		<ul class="search-viewer__list">
			<?php 
			$query = QUERY_SEARCH_LIST;
			$result = Neo::$client->run($query);
			$alphabet = 
			[
			"find" => [	  '[áã]', '[éê]', '[íî]', '[óô]', '[úüû]', 'ç', ' - ', '\\.', 'professora?'],
			"replace" => ['a',    'e',    'i',    'o',    'u',     'c', ' ',   '',    'prof']
			];
			global $alphabet;

			foreach ($result->records() as $record)
			{
				$names = $record->value('names');
				$id = $record->value('id');

				$n_names = count($names);
				$finalNames = $names;

				for($i = 0; $i < $n_names; $i++)
				{
					foreach ($alphabet as $find => $replace) 
					{
						$names[$i] = preg_replace('/'.$find.'/imu',$replace,$names[$i]);

					}
					$names[$i] = strtolower($names[$i]);
				}

				echo '<li class="search-viewer__item" data-slug="'.slugify($finalNames[0]).'" data-id="'.$id.'" data-names="'.implode('|', $names).'"><span class="search-viewer__item__content">';
				echo '<span class="search-viewer__item__name">'.$finalNames[0].'</span>';
				if($n_names>1)
				{
					for($i = 1; $i < $n_names; $i++)
						echo '<small>'.$finalNames[$i].($i+1<$n_names?'':'').'</small>';
				}


				echo '</span></li>';
			}
			?>
		</ul>
		<span class="search-viewer__no-results txt--xs-center">Nenhum resultado encontrado</span>
	</aside>
</div>