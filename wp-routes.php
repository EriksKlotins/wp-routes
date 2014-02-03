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

	private static function registerRoute($method, $pattern, $controller, $title = null, $name = null)
	{
		$regex = preg_replace('/{([a-z]+)}/', '([a-zA-Z_0-9-]+)', $pattern);
		$regex = str_replace('/', '\/', $regex);
		$regex = '/'.$regex.'/';
		Route::$mapping[] = array(
				'method'	=> $method, 
				'regex'		=> $regex,
				'pattern'	=> $pattern, 
				'controller'=> $controller, 
				'title' 	=> $title, 
				'name'		=> $name);
	}


	public static function get( $pattern,  $handler,  $name = null,  $title = null)
	{
		Route::registerRoute('GET',$pattern, $handler, $title, $name);
	}

	public static function post( $pattern,  $handler,  $name = null,  $title = null)
	{
		Route::registerRoute('GET',$pattern, $handler, $title, $name);
	}

	public static function any( $pattern, $handler,  $name = null,  $title = null)
	{
		Route::registerRoute('ANY',$pattern, $handler, $title, $name);
	}

	public static function to(string $name)
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
				return get_bloginfo('home'). $url;
			}
		}
		return null;
	}

	public static function isCurrent(string $name)
	{
		return Route::$current['name'] == $name;
	}

	private static function run($handler, $params)
	{
		if ($handler instanceof Closure)
		{
			$handler($params);
		}elseif (is_string($handler))
		{
			$controller = new $handler();
			$controller->index($params);
		}
	}

	static function execute()
	{
		
		foreach(Route::$mapping as $route)
	 	{
	 		if ($route['method'] == 'ANY' || $route['method'] == $_SERVER['REQUEST_METHOD'])
	 		{
	 			if (preg_match($route['regex'], $_SERVER['REQUEST_URI'], $result))
		 		{
			 		preg_match_all('/{([a-z]+)}/',$route['pattern'], $matches);
					Route::$current = $route;	
					$params = array();
					for($i=0;$i<count($matches[1]);$i++)
					{
						$params[$matches[1][$i]] = $result[$i+1];
					}
				//	var_dump($result,$matches,$params);
					Route::run($route['controller'], $params);//->index($params);
					
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
