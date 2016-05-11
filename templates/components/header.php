
<aside id="header">
	<div class="header__logo --pd-h-sm txt--xs-center">
		<strong><?php Main::getAppName(true); ?></strong>
	</div>
	<div class="header__search">
		<form action="" id="search-form">
			<label for="origin" class="btn search__place">
				<span class="search__label">Origem</span>
				<span class="search__value"></span>
			</label>
			<label for="target" class="btn search__place">
				<span class="search__label">Destino</span>
				<span class="search__value"></span>
			</label>
			<div class="filters">
				<span class="filters__title"><strong>Filtros</strong></span>
				<div class="filters__section">
					<span class="filters__section__title">Desconsiderar</span>
					<div class="filters__item">
						<span class="form-control form-control--radio">
							<input type="checkbox" name="filter-pathway[]" class="js-radio" id="filter-pathway-stair" value="STAIR_CONNECTION">
							<label for="filter-pathway-stair">Escadas</label>
						</span>
					</div>
					<div class="filters__item">
						<span class="form-control form-control--radio">
							<input type="checkbox" name="filter-pathway[]" class="js-radio" id="filter-pathway-lift" value="LIFT_CONNECTION">
							<label for="filter-pathway-lift">Elevadores</label>
						</span>
					</div>
				</div>
			</div>
			<button for="target" class="btn btn--submit">
				Me ajuda!
			</button>
			<input type="hidden" name="origin" class="search-origin">
			<input type="hidden" name="target" class="search-target">
		</form>
	</div>
</aside>

<aside class="searchViewer">
	<input type="text" class="searchViewer__input form-control" placeholder="Buscar...">
	<ul class="searchViewer__list">
		<?php 
		$query = QUERY_SEARCH_LIST;
		$result = Neo::$client->run($query);
		$alphabet = 
		[
		"find" => [	  '[áã]', '[éê]', '[íî]', '[óô]', '[úüû]', 'ç', ' - ', '\\.', 'professora?'],
		"replace" => ['a',    'e',    'i',    'o',    'u',     'c', ' ',   '',    'prof']
		];

		foreach ($result->records() as $record)
		{
			$names = $record->value('names');
			$id = $record->value('id');

			$n_names = count($names);
			$n_alphabet = count($alphabet["find"]);
			$finalNames = $names;

			for($i = 0; $i < $n_names; $i++)
			{
				for($j = 0 ; $j < $n_alphabet; $j++)
				{
					$names[$i] = preg_replace('/'.$alphabet["find"][$j].'/imu',$alphabet["replace"][$j],$names[$i]);
				}
				$names[$i] = strtolower($names[$i]);
			}
			echo '<li class="searchViewer__item" data-id="'.$id.'" data-names="'.implode('|', $names).'"><span class="searchViewer__item__content">';
			echo '<span class="searchViewer__item__name">'.$finalNames[0].'</span>';
			if($n_names>1)
			{
				for($i = 1; $i < $n_names; $i++)
					echo '<small>'.$finalNames[$i].($i+1<$n_names?'':'').'</small>';
			}


			echo '</span></li>';
		}
		?>
	</ul>
	<span class="searchViewer__no-results txt--xs-center">Nenhum resultado encontrado</span>
</aside>