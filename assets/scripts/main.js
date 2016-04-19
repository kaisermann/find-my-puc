'use strict';

(function($) 
{
	var PAGE_PARAMS;

	var Kage = 
	{
		'common': 
		{
			init: function() 
			{
			},
			end: function() 
			{
			}
		},
		'home':
		{
			init: function() 
			{
			},
			end: function() 
			{
			}
		}
	};

	var UTIL = 
	{
		fire: function(func, funcname, args) 
		{
			var fire;
			var namespace = Kage;
			funcname = (funcname === undefined) ? 'init' : funcname;
			fire = func !== '';
			fire = fire && namespace[func];
			fire = fire && typeof namespace[func][funcname] === 'function';

			if (fire)
				namespace[func][funcname](args);
		},
		loadEvents: function() 
		{
			UTIL.fire('common');
			
			PAGE_PARAMS = document.body.className.replace(/-/g, '_').split(/\s+/);
			for(var i = 0; i < PAGE_PARAMS.length; i++)
			{
				var classnm = PAGE_PARAMS[i];

				UTIL.fire(classnm);
				UTIL.fire(classnm, 'end');
			}

			UTIL.fire('common', 'end');
		}
	};

	$(document).ready(UTIL.loadEvents);

})(jQuery);
