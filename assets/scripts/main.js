/* global Visc */
/* global _alphabet */
/* global ajax_url */
/* global base_url */
/* global Tiq */
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
	, Url
	, Attributes;

	Url =
	{
		Query: '',
		Parameters: '',
		Hash: ''
	};

	// ------- //
	Attributes = 
	{
		isLoading: {'body':true, 'main': false},
		shouldSearch: false
	};

	Reusable = 
	{
		search_form: $('#search-form'),
		search_viewer: $('.search-viewer'),
		main: $('.main'),
		btnMobile: $('.js-mobile-btn')
	};

	Reusable.search_viewer_content = Reusable.search_viewer.children('.search-viewer__content');
	Reusable.search_viewer_bg = Reusable.search_viewer.children('.search-viewer__bg');

	Events = 
	{
		'common': 
		{
			init: function()
			{
				Url.Query = Tools.parseQueryString(window.location.search, true);
				Url.Parameters = Tools.parseUrlParams(window.location.pathname);

				Reusable.btnMobile.on('click', (function()
				{
					var _headerActive = false;
					var $search = $('.header__search');
					var $menu = $('.header__menu');
					var $header = $('.header');
					return function()
					{
						_headerActive = !_headerActive;
						$header.toggleClass('header--active');
					};
				})());

				Util.genericHandlers();
				Util.searchHandler();

				Util.routeHandlers();
			},
			end: function()
			{
				new Tiq()
				.add(150, function() 
				{
					Util.setLoading(false, 'body', $(d.body)); 
					if(Attributes.shouldSearch)
						Reusable.search_form.trigger('submit');
				})
				.run();
			}
		}
	};

	Tools = 
	{
		'sanitize': function(str)
		{
			for (var val in _alphabet)
				str = str.replace(new RegExp(val, 'g'), _alphabet[val]);

			return str;
		},
		parseQueryString: function(qstr, removeFirst)
		{
			var _query = {}
			, _a = qstr.substr(removeFirst ? 1 : 0).split('&');

			for (var i = 0; i < _a.length; i++)
			{
				var _b = _a[i].split('=');
				_query[decodeURIComponent(_b[0])] = decodeURIComponent(_b[1] || '');
			}

			return _query;
		},
		parseUrlParams: function(str)
		{
			var _ret = []
			, _params = str.split('/');

			for(var i = 0; i < _params.length; i++)
			{
				switch(_params[i])
				{
					case 'origem':
					case 'destino':
					_ret[_params[i]] = _params[i+1];
					i++;
					break;
				}
			}
			return _ret;
		}
	};

	Util = 
	{
		routeHandlers: function()
		{
			$('body')
			.on('click', '.route__step-between__list-wrapper', function()
			{
				var $_ = $(this);
				$_.toggleClass('route__step-between__list-wrapper--active');
			})
			.on('click', '.js-route__node-img', function(e)
			{
				e.preventDefault();
				$.swipebox( [
					{ href:$(this).attr('href'), title:$(this).attr('data-title') }
					] );

			});
		},
		genericHandlers: function()
		{
			$(window).on('keyup', function(e)
			{
				var _code = e.keyCode;

				if(_code===27)
				{
					if(d.body.classList.contains('locked--search'))
						Util.closeSearchViewer();
				}
			});
		},
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
				console.log(e.keyCode);
				if(e.keyCode < 33 && e.keyCode !== 8)
					return;

				var $_= $(this);
				console.log($_.prop('value').toLowerCase());
				var _searchParam = Tools.sanitize($_.prop('value').toLowerCase());
				console.log(_searchParam);
				console.log();

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
					//console.log("_curName: " + _names);
				});
				console.log("search: " + _searchParam);

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

					Util.closeSearchViewer();
				}

			});

			Reusable.search_form.on('submit', function(e)
			{
				e.preventDefault();
				var $_ = $(this);

				var _origemID = $_.children('.search-origem').prop('value');
				var _destinoID = $_.children('.search-destino').prop('value');

				var _readyToSubmit = _origemID.length > 0 && _destinoID.length > 0;

				if(_readyToSubmit && !Attributes.isLoading.main)
				{
					Util.closeSearchViewer();
					if($(w).outerWidth()<=767)
						Reusable.btnMobile.trigger('click');

					var _itemSlug = {};

					_itemSlug.origem = $searchItems
					.filter('li[data-id="'+_origemID+'"]')
					.attr('data-slug');

					_itemSlug.destino =  $searchItems
					.filter('li[data-id="'+_destinoID+'"]')
					.attr('data-slug');

					var _serialized = $_.serialize();
					var _formParams = Tools.parseQueryString(_serialized, false);
					var slug = 'origem/'+_itemSlug.origem+'/destino/'+_itemSlug.destino+'/';
					var _formParamsStr;

					
					delete _formParams.origem;
					delete _formParams.destino;

					_formParamsStr = $.param(_formParams);

					if(_formParamsStr.length>0)
						slug += '?' + _formParamsStr;

					Util.loadPage(ajax_url
						,'action=findRoute&'+_serialized
						, slug
						, 'route');
				}
			});

			['origem','destino'].forEach(function(val)
			{
				var curSlug = Url.Query[val] || Url.Parameters[val];

				Attributes.shouldSearch = Attributes.shouldSearch || !!curSlug;

				if(curSlug)
				{
					var $item = $searchItems.filter('[data-slug="'+curSlug+'"]');
					var _name = $item.find('.search-viewer__item__name').text();

					Reusable.search_form.children('label[for="'+val+'"]')
					.addClass('search__place--done')
					.children('.search__value')
					.text(_name);

					Reusable.search_form.children('input.search-'+val).prop('value', $item.attr('data-id'));
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
			var _oppositeRole = 'destino';

			var $_ = $(this)
			, $sv = Reusable.search_viewer_content
			, _searchRole = $_.attr('for')
			, _curRole = $sv.attr('for')
			, _isActive = Reusable.search_viewer.hasClass('search-viewer--active');

			if(_searchRole === 'destino')
				_oppositeRole = 'origem';

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
				$(d.body).addClass('locked--search');

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
				.find('.search-viewer__item[data-id="'+_oppositeID+'"],.search-viewer__item[data-only-for="'+_oppositeRole+'"]')
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

			$(d.body).removeClass('locked--search');

			Reusable.search_viewer.removeClass('search-viewer--active');
		},
		loadPage: function(url, data, slug, mainName, callback, errorCallback)
		{
			Util.setLoading(true, 'main');

			if(slug)
				history.replaceState(null, null, base_url + slug);

			console.log(url + '?' + data);
			$.get({
				url: url,
				data: data,
				error: function(e)
				{ 
					if(!!errorCallback && typeof errorCallback === 'function')
						errorCallback.apply(this, [e]);
					//console.log(e);
				},
				success: function(response)
				{
					var $mainContent = Reusable.main.children('.main__content');
					Reusable.main.attr('data-page', mainName);

					Util.setLoading(false,'main');
					$mainContent.addClass('main__content--hidden');
					setTimeout(function()
					{
						$mainContent.html(response).removeClass('main__content--hidden');
					},310);

					if(!!callback && typeof callback === 'function')
						callback.apply(this, [response]);
				}
			});
		},
		setLoading: function(shouldLoad, loadingClass, wrapper)
		{
			var $wrapper = wrapper || Reusable.main;

			Attributes.isLoading[loadingClass] = shouldLoad;
			if(shouldLoad)
			{
				$wrapper.append('<div class="spinner-wrapper"><div class="spinner"></div></div>');
				setTimeout(function(){ $wrapper.addClass('js--loading'); }, 10);				
			}
			else
			{
				$wrapper.removeClass('js--loading');
				setTimeout(function(){$wrapper.children('.spinner-wrapper').remove(); }, 310);
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
