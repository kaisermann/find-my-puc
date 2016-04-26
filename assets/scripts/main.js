'use strict';

(function($) 
{
	var w = window
	, d = document
	, PAGE_PARAMS
	, Events
	, Util;

	Events = 
	{
		'common': 
		{
			init: function(){},
			end: function(){}
		}
	};

	Util = 
	{
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
