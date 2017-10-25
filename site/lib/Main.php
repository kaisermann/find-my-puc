<?php
$Request = $_GET;
$Post = $_POST;

class Main 
{
	private static $assets = ['header'=>[],'footer'=>[]];
	private static $is = [];

	public static function is($key)
	{
		return (getKeyValue(self::$is, $key) === true);
	}

		// Structural

	public static function load()
	{
		self::loadIs();

		self::enqueueAsset(Assets::getPath('styles/main.css'),'css','header');
		self::enqueueAsset('https://code.jquery.com/jquery-2.2.3.min.js','js','footer');
		self::enqueueAsset(Assets::getPath('scripts/main.js'),'js','footer');

		self::parseUrl();
	}

	public static function parseUrl()
	{
		global $Request;
		$url = $_SERVER['REQUEST_URI'];
		$urlParams = explode('/', $url);
		$urlParams = array_slice($urlParams, 2);
		$n_params = count($urlParams);
		for($i = 0; $i < $n_params; $i++)
		{
			$urlParams[$i] = preg_split('/(\#|\?)/', $urlParams[$i])[0];
			switch($urlParams[$i])
			{
				case 'origem':
				case 'destino':
				$Request[$urlParams[$i]] = $urlParams[$i+1];
				$i++;
				break;

				default:
				$Request['id'] = $urlParams[$i];
				break;
			}
		}
	}

	public static function loadIs()
	{
		if(!isset($Request[PAGE_ID]))
		{
			self::$is['home'] = true;
		}
		else
		{
			self::$is['404'] = true;
		}
	}

	public static function enqueueAsset($url, $type, $position)
	{
		self::$assets[$position][] = [$url, $type];
	}

	public static function assetQueue($position)
	{
		$assetList = self::$assets[$position];
		foreach($assetList as $asset)
		{
			if($asset[1]==='css')
				echo '<link rel="stylesheet" href="'.$asset[0].'" />';
			else if($asset[1]==='js')
				echo '<script src="'.$asset[0].'"></script>';
		}
	}

	public static function jsonAlphabet()
	{
		global $alphabet;
		echo 'var _alphabet = '.json_encode($alphabet).';';
	}

	/* Page related */

	public static function getAppName($echo = false)
	{
		if($echo)
			echo APP_NAME;
		return APP_NAME;
	}

	public static function getPageTitle($echo = false)
	{
		$name = self::getAppName();

		if($echo)
			echo $name;
		return $name;
	}

	/* Graph Related */

	public static function getNodeImage($node)
	{
		$url = '';
		$imgSlug = $node->getAttr('img');

		if(!$imgSlug)
			$imgSlug = slugify($node->getNames()[0]);

		$url = $imgSlug.'.jpg';
		if(file_exists(IMG_DIR.$url))
			return IMG_URL.$url;
		return NULL;
	}

}
Main::load();
?>