
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

		foreach ($result->records() as $record)
		{
			$names = $record->value("names");
			echo '<li class="searchViewer__item" data-id="'.$record->value("id").'" data-names="'.implode('|', $names).'"><span>';
			echo $names[0];
			echo '</span></li>';
		}
		?>
	</ul>
	<span class="searchViewer__no-results txt--xs-center">Nenhum resultado encontrado</span>
</aside>