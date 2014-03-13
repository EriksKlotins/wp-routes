<?php 

/*
Plugin Name: WP Routes 
Plugin URI: http://localhost
Description: Adds routing to WP
Version: 1.0
Author: E.Klotins
Author URI: #
License: GPLv2 or later
*/


class Route
{

	public static $mapping = array();
	public static $current = array();
	/**
	 * Registers route
	 * @param  string $method     whate type or request route handles (GET, POST, ANY)
	 * @param  string $pattern    pattern for URL to route
	 * @param  string $controller handler of this route, controller or function. '<controller_name>@<method_name>' defines call action - default 'index'
	 * @param  [type] $title      [description]
	 * @param  [type] $name       [description]
	 * @return [type]             [description]
	 */
	private static function registerRoute($method, $pattern, $controller, $title = null, $name = null)
	{
		$action = null;
		if (is_string($controller))
		{
			$parts = explode('@', $controller);
			if(count($parts) == 2)
			{
				$controller = $parts[0];
				$action = $parts[1];
			}
			else
			{
				$action = 'index';
			}
		}

		$regex = preg_replace('/{([a-z]+)}/', '([a-zA-Z_0-9-]+)', $pattern);
		$regex = str_replace('/', '\/', $regex);
		$regex = '/'.$regex.'/';
		Route::$mapping[] = array(
				'method'	=> $method, 
				'regex'		=> $regex,
				'pattern'	=> $pattern, 
				'controller'=> $controller,
				'action'	=> $action,
				'title' 	=> $title, 
				'name'		=> $name);
	}


	public static function get( $pattern,  $handler,  $name = null,  $title = null)
	{
		Route::registerRoute('GET',$pattern, $handler, $title, $name);
	}

	public static function post( $pattern,  $handler,  $name = null,  $title = null)
	{
		Route::registerRoute('POST',$pattern, $handler, $title, $name);
	}



	/**
	 * creates a route to GET or POST request
	 * @param  [type] $pattern patter
	 * @param  [type] $handler [description]
	 * @param  [type] $name    [description]
	 * @param  [type] $title   [description]
	 * @return [type]          [description]
	 */
	public static function any( $pattern, $handler,  $name = null,  $title = null)
	{
		Route::registerRoute('ANY',$pattern, $handler, $title, $name);
	}

	public static function to( $name, $params =array())
	{
		foreach (Route::$mapping as $route) 
		{

			if ($route['name'] == $name)
			{
				$url = $route['pattern'];
				preg_match_all('/{([a-z]+)}/',$route['pattern'], $matches);
				for($i=0;$i<count($matches[1]);$i++)
				{
					$url = str_replace($matches[0][$i], $params[$matches[1][$i] ], $url);
				}
				//var_dump($_SERVER['HTTP_HOST'], $url);
				return $url;
			}
		}
		return null;
	}

	public static function redirectTo($name, $params = array())
	{
		header('location: '. Route::to($name, $params));
		die();
	}

	public static function isCurrent( $name)
	{
		return Route::$current['name'] == $name;
	}

	private static function run($handler, $params, $action)
	{
		if ($handler instanceof Closure)
		{
			$handler($params);
			die();
		}
		elseif (is_string($handler))
		{
			$controller = new $handler();
			$controller->$action($params);
			die();
		}
	}

	static function execute()
	{
		$WP_SITEURL = preg_match('/http/',WP_SITEURL)? WP_SITEURL : '';
		//var_dump('siteurl '.$WP_SITEURL, $_SERVER);
	
		foreach(Route::$mapping as $route)
	 	{
	 		if ($route['method'] == 'ANY' || $route['method'] == $_SERVER['REQUEST_METHOD'])
	 		{
	 			//var_dump($route['regex'], $_SERVER['REQUEST_SCHEME'].'://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	 			//continue;
	 			if (preg_match($route['regex'], $_SERVER['REQUEST_SCHEME'].'://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $result))
		 		{
		 			//var_dump($result);
			 		preg_match_all('/{([a-z]+)}/',$route['pattern'], $matches);
					Route::$current = $route;	
					$params = array();
					for($i=0;$i<count($matches[1]);$i++)
					{
						$params[$matches[1][$i]] = $result[$i+1];
					}
				//	var_dump($result,$matches,$params);
					Route::run($route['controller'], $params, $route['action']);//->index($params);
					//var_dump($route);
					$found = true;
					break;
		 		}
	 		}
	 		
	 		
	 	}
	 	if (!$found)
	 	{
	 		wp_die('Route not found!');
	 	}
	 	
		//die();	
	}


	static function getRoutesForNavMenus()
	{
		$result = array();
		foreach (Route::$mapping as $item) 
		{
			if ($item['title'] !== null)
			{
				$result[] = $item;
			}
		}

		return $result;
	}



}
