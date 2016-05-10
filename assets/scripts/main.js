/* global Visc */
'use strict';

(function($) 
{
	var w = window
	, d = document
	, PAGE_PARAMS
	, Events
	, Util
	, Helpers
	, Reusable;

	var accentMap = {'á':'a', 'ã':'a', 'é':'e', 'í':'i','ó':'o','ú':'u', 'ê':'e'};

	// ------- //

	Reusable = 
	{
		searchForm: $('#search-form'),
		searchViewer: $('.searchViewer') 
	};

	Events = 
	{
		'common': 
		{
			init: function()
			{
				var $searchBtns = Reusable.searchForm.find('.search__place');
				$searchBtns.on('click', Util.toggleSearchViewer);

				Util.searchHandler();
			},
			end: function(){}
		}
	};

	Helpers = 
	{
		removeAccentuation: function(s) 
		{
			if (!s)
				return '';
			var ret = '';
			for (var i = 0; i < s.length; i++)
				ret += accentMap[s[i]] || s[i];
			return ret;
		}
	};

	Util = 
	{
		searchHandler: function()
		{
			var $searchInput = Reusable.searchViewer.children('.searchViewer__input')
			, $searchList = Reusable.searchViewer.children('.searchViewer__list');

			var $searchItems = $searchList.children('li');

			var 
			_lastVal = -1
			, _nResults = 0;

			$searchInput.on('keyup', function(e)
			{
				var $_= $(this);
				var _searchParam = Helpers.removeAccentuation($_.prop('value').toLowerCase());

				if(_lastVal === _searchParam)
					return;

				_nResults = 0;

				$searchItems.each(function(i, e)
				{
					// Pega os nomes do local e remove os acentos
					var _names = e.getAttribute('data-names').toLowerCase();
					_names = Helpers.removeAccentuation(_names);

					if(_names.indexOf(_searchParam)<0)
					{
						e.classList.add('searchViewer__item--inactive');
					}
					else
					{
						e.classList.remove('searchViewer__item--inactive');
						_nResults++;
					}
				});

				if(_nResults === 0)
					Reusable.searchViewer.children('.searchViewer__no-results').addClass('searchViewer__no-results--visible');
				else
					Reusable.searchViewer.children('.searchViewer__no-results').removeClass('searchViewer__no-results--visible');


				_lastVal = _searchParam;
			});

			$searchItems.on('click', function()
			{
				var $_ = $(this);

				if($_.hasClass('searchViewer__item--disabled'))
					return;

				var _id = $_.attr('data-id')
				, _name = $_.children('span').text()
				, _curRole = Reusable.searchViewer.attr('for');

				var $label = Reusable.searchForm.children('label[for="'+_curRole+'"]');
				var $input = Reusable.searchForm.children('input.search-'+_curRole);

				var _inputID = $input.attr('value');

				// Se clicou no mesmo item selecionado, remove a marcação
				if(_id === _inputID)
				{
					$input.attr('value', '');

					$label.removeClass('search__place--done')
					.children('.search__value')
					.text('');

					$_.removeClass('searchViewer__item--current');
				}
				else // Se não, marca o item
				{
					$input.attr('value', _id);

					$label.addClass('search__place--done')
					.children('.search__value')
					.text(_name);

					$_.parent()
					.children('.searchViewer__item--current')
					.removeClass('searchViewer__item--current');

					$_.addClass('searchViewer__item--current');
				}

			});
		},
		toggleSearchViewer: function(e)
		{
			// OppositeRole para pegarmos o ID do outro termo e não deixarmos escolher o mesmo.
			var _oppositeRole = 'target';

			e.preventDefault();

			var activeClass = 'searchViewer--active'
			, btnActiveClass = 'search__place--active';

			var $_ = $(this)
			, $sv = Reusable.searchViewer
			, _searchRole = $_.attr('for')
			, _curRole = $sv.attr('for')
			, _isActive = $sv.hasClass(activeClass);

			if(_searchRole === 'target')
				_oppositeRole = 'origin';

			// Fecha a aba de busca
			if(_isActive && _curRole === _searchRole)
			{
				$sv.removeClass(activeClass);
				$_.removeClass(btnActiveClass);
				Reusable.searchViewer.children('.searchViewer__input').blur();
			}
			else // Abre a aba de busca
			{
				var _curID = Reusable.searchForm.children('input.search-'+_searchRole).prop('value')
				,_oppositeID = Reusable.searchForm.children('input.search-'+_oppositeRole).prop('value')
				, $curItem
				, $searchList;

				if(_curID.length > 0)
					$curItem = Reusable.searchViewer.find('.searchViewer__item[data-id="'+_curID+'"]');

				if(_isActive)
				{
					// Se a aba já está aberta, vamos resetar o item selecionado.

					// Remove o estado ativo do botão
					Reusable.searchForm.children('.'+btnActiveClass).removeClass(btnActiveClass);
				}
				// Desmarca o item selecionado
				Reusable.searchViewer
				.find('.searchViewer__item--current')
				.removeClass('searchViewer__item--current');

				if(_curID.length > 0)
					$curItem.addClass('searchViewer__item--current');

				$sv.attr('for', _searchRole);
				$sv.addClass(activeClass);
				$_.addClass(btnActiveClass);

				// Vamos fazer o scroll voltar à posição do item selecionado
				if(_curID.length > 0)
				{
					if(!Visc.isVisible($curItem))
					{
						$searchList = Reusable.searchViewer.children('.searchViewer__list');
						$searchList.animate(
						{
							scrollTop: $searchList.scrollTop() - $searchList.offset().top + $curItem.offset().top 
						}, 200);
					}
				}

				Reusable.searchViewer
				.find('.searchViewer__item--disabled')
				.removeClass('searchViewer__item--disabled');

				Reusable.searchViewer
				.find('.searchViewer__item[data-id="'+_oppositeID+'"]')
				.addClass('searchViewer__item--disabled');
				
				setTimeout(function()
				{
					Reusable.searchViewer.children('.searchViewer__input').focus();
				}, 100);
			}
		},
		fire: function(func, funcname, args) 
		{
			var fire, namespace = Events;
			funcname = funcname || 'init';
			fire = (func !== '' && namespace[func] && typeof namespace[func][funcname] === 'function');

			if (fire)
				namespace[func][funcname](args);
		},
		loadEvents: function() 
		{
			Util.fire('common');
			PAGE_PARAMS = d.body.className.replace(/-/g, '_').split(/\s+/);
			for(var i = 0; i < PAGE_PARAMS.length; i++)
			{
				Util.fire(PAGE_PARAMS[i]);
				Util.fire(PAGE_PARAMS[i], 'end');
			}
			Util.fire('common', 'end');
		}
	};

	if(d.readyState === "interactive" || d.readyState === "complete")
		Util.loadEvents();
	else
		d.addEventListener( "DOMContentLoaded", function loadListener()
		{
			d.removeEventListener( "DOMContentLoaded", loadListener, false );
			Util.loadEvents();
		}, false );

})(window.jQuery || window.Zepto || window.Cash || undefined);
