/* global Visc */
'use strict';

(function($) 
{
	var w = window
	, d = document
	, PAGE_PARAMS
	, Events
	, Util
	, Tools
	, Reusable
	, QueryString;

	String.prototype.replaceArray = function(find, replace) {
		var replaceString = this;
		var regex; 
		for (var i = 0; i < find.length; i++) {
			regex = new RegExp(find[i], "g");
			replaceString = replaceString.replace(regex, replace[i]);
		}
		return replaceString;
	};


	var sanitizeAlphabet = 
	{
		'[áãà]': 'a'
		, '[éê]': 'e'
		, '[íî]': 'i'
		, '[óô]': 'o'
		, '[úüû]': 'u'
		, 'ç': 'c'
		, ' - ': ' '
		, '\\.': ''
		, 'professora?': 'prof'
	};

	// ------- //

	Reusable = 
	{
		search_form: $('#search-form'),
		search_viewer: $('.search-viewer')
	};

	Reusable.search_viewer_content = Reusable.search_viewer.children('.search-viewer__content');
	Reusable.search_viewer_bg = Reusable.search_viewer.children('.search-viewer__bg');

	Events = 
	{
		'common': 
		{
			init: function()
			{
				QueryString = Tools.parseQueryString(window.location.search);
				Util.searchHandler();
			},
			end: function(){}
		}
	};

	Tools = 
	{
		'sanitize': function(str)
		{
			for (var val in sanitizeAlphabet)
				str = str.replace(new RegExp(val, 'g'), sanitizeAlphabet[val]);

			return str;
		},
		parseQueryString: function(qstr)
		{
			var query = {};
			var a = qstr.substr(1).split('&');
			for (var i = 0; i < a.length; i++) {
				var b = a[i].split('=');
				query[decodeURIComponent(b[0])] = decodeURIComponent(b[1] || '');
			}
			return query;
		},
	};

	Util = 
	{
		searchHandler: function()
		{
			var $searchInput = Reusable.search_viewer_content.children('.search-viewer__input')
			, $searchList = Reusable.search_viewer_content.children('.search-viewer__list');

			var $searchItems = $searchList.children('li');

			var 
			_lastVal = -1
			, _nResults = 0;

			Reusable.search_form.find('.search__place').on('click', Util.toggleSearchViewer);

			Reusable.search_viewer_content.children('.search-viewer__close-btn').on('click', Util.closeSearchViewer);
			Reusable.search_viewer_bg.on('click', Util.closeSearchViewer);

			$searchInput.on('keyup', function(e)
			{
				var $_= $(this);
				var _searchParam = Tools.sanitize($_.prop('value').toLowerCase());

				if(_lastVal === _searchParam)
					return;

				_nResults = 0;

				$searchItems.each(function(i, e)
				{
					// Pega os nomes do local e remove os acentos
					var _names = e.getAttribute('data-names').toLowerCase();
					//_names = Tools.sanitize(_names);

					if(_names.indexOf(_searchParam)<0)
					{
						e.classList.add('search-viewer__item--inactive');
					}
					else
					{
						e.classList.remove('search-viewer__item--inactive');
						_nResults++;
					}
					console.log("search: " + _searchParam);
					console.log("_curName: " + _names);
				});

				if(_nResults === 0)
					Reusable.search_viewer_content.children('.search-viewer__no-results').addClass('search-viewer__no-results--visible');
				else
					Reusable.search_viewer_content.children('.search-viewer__no-results').removeClass('search-viewer__no-results--visible');


				_lastVal = _searchParam;
			});

			$searchItems.on('click', function()
			{
				var $_ = $(this);

				if($_.hasClass('search-viewer__item--disabled'))
					return;

				var _id = $_.attr('data-id')
				, _name = $_.find('.search-viewer__item__name').text()
				, _curRole = Reusable.search_viewer_content.attr('for');

				var $label = Reusable.search_form.children('label[for="'+_curRole+'"]');
				var $input = Reusable.search_form.children('input.search-'+_curRole);

				var _inputID = $input.attr('value');

				// Se clicou no mesmo item selecionado, remove a marcação
				if(_id === _inputID)
				{
					$input.attr('value', '');

					$label.removeClass('search__place--done')
					.children('.search__value')
					.text('');

					$_.removeClass('search-viewer__item--current');
				}
				else // Se não, marca o item
				{
					$input.attr('value', _id);

					$label.addClass('search__place--done')
					.children('.search__value')
					.text(_name);

					$_.parent()
					.children('.search-viewer__item--current')
					.removeClass('search-viewer__item--current');

					$_.addClass('search-viewer__item--current');
				}

			});

			["origin","target"].forEach(function(val)
			{
				if(QueryString[val])
				{
					var _name = $searchItems
					.filter('[data-id="'+QueryString[val]+'"]')
					.find('.search-viewer__item__name')
					.text();

					Reusable.search_form.children('label[for="'+val+'"]')
					.addClass('search__place--done')
					.children('.search__value')
					.text(_name);

					Reusable.search_form.children('input.search-'+val).prop('value', QueryString[val]);
				}
			});

			$('.js-radio').on('click', function()
			{
				var $_ = $(this);
				if ($_.is(":checked")) 
				{
					$("input:checkbox[name='" + $_.attr("name") + "']").prop("checked", false);
					$_.prop("checked", true);
				} 
				else
					$_.prop("checked", false);
			});
		},
		toggleSearchViewer: function(e)
		{
			e.preventDefault();

			// Opposite Role para pegarmos o ID do outro termo e não deixarmos escolher o mesmo.
			var _oppositeRole = 'target';

			var $_ = $(this)
			, $sv = Reusable.search_viewer_content
			, _searchRole = $_.attr('for')
			, _curRole = $sv.attr('for')
			, _isActive = Reusable.search_viewer.hasClass('search-viewer--active');

			if(_searchRole === 'target')
				_oppositeRole = 'origin';

			// Fecha a aba de busca
			if(_isActive && _curRole === _searchRole)
			{
				Util.closeSearchViewer();
			}
			else // Abre a aba de busca
			{
				var _curID = Reusable.search_form.children('input.search-'+_searchRole).prop('value')
				,_oppositeID = Reusable.search_form.children('input.search-'+_oppositeRole).prop('value')
				, $curItem
				, $searchList = Reusable.search_viewer_content.children('.search-viewer__list')
				, $searchInput = Reusable.search_viewer_content.children('.search-viewer__input');

				if(_curID.length > 0)
					$curItem = Reusable.search_viewer_content.find('.search-viewer__item[data-id="'+_curID+'"]');

				if(_isActive)
				{
					// Se a aba já está aberta, vamos resetar o item selecionado.

					// Remove o estado ativo do botão
					Reusable.search_form.children('.search__place--active').removeClass('search__place--active');
				}

				if($searchInput.prop('value').length)
					$searchInput.prop('value', '').trigger('keyup');
			
				// Desmarca o item selecionado
				Reusable.search_viewer_content
				.find('.search-viewer__item--current')
				.removeClass('search-viewer__item--current');

				if(_curID.length > 0)
					$curItem.addClass('search-viewer__item--current');

				Reusable.search_viewer.addClass('search-viewer--active');

				$sv
				.attr('for', _searchRole);

				$_.addClass('search__place--active');

				// Vamos fazer o scroll voltar à posição do item selecionado
				if(_curID.length > 0)
				{
					if(!Visc.isVisible($curItem))
					{
						$searchList.animate(
						{
							scrollTop: $searchList.scrollTop() - $searchList.offset().top + $curItem.offset().top 
						}, 200);
					}
				}
				else
					$searchList.animate({scrollTop:0},200);

				Reusable.search_viewer_content
				.find('.search-viewer__item--disabled')
				.removeClass('search-viewer__item--disabled');

				Reusable.search_viewer_content
				.find('.search-viewer__item[data-id="'+_oppositeID+'"]')
				.addClass('search-viewer__item--disabled');
				
				setTimeout(function()
				{
					$searchInput.focus();
				}, 100);
			}
		},
		closeSearchViewer: function($btn)
		{
			Reusable.search_form.find('.search__place--active').removeClass('search__place--active');

			Reusable.search_viewer_content
			.removeClass('search-viewer__content--active')
			.children('.search-viewer__input')
			.blur();

			Reusable.search_viewer.removeClass('search-viewer--active');
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
