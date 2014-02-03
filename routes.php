<?php

class Routes
{
	public static $mapping = array();
	public static $current = array();

	/*
		@param $pattern - regexp, kas atbilst urlim
		@param $controller - kontrolieris, ko darbināt
		@param $title - ar kadu nosaukumu šo rādīt pie main menu administrācijas 
		@param $name - nosaukums, pec kā šo route identific
	*/
	static function Set($pattern, $controller, $title = null, $name = null)
	{
		Routes::$mapping[] = array('pattern'=>$pattern, 'controller'=>$controller, 'title' =>$title, 'name'=> $name);
	}
	

	static function registerRoute($method, $pattern, $controller, $title = null, $name = null)
	{
		
		$regex = preg_replace('/{([a-z]+)}/', '([a-zA-Z_0-9-]+)', $pattern);
		$regex = str_replace('/', '\/', $regex);
		$regex = '/'.$regex.'/';
		Routes::$mapping[] = array(
				'method'	=> $method, 
				'regex'		=> $regex,
				'pattern'	=> $pattern, 
				'controller'=> $controller, 
				'title' 	=> $title, 
				'name'		=> $name);
	}

	static function Get($pattern, $controller, $title = null, $name = null)
	{
		Routes::registerRoute('GET',$pattern, $controller, $title, $name);
	}

	static function Post($pattern, $controller, $title = null, $name = null)
	{
		Routes::registerRoute('POST',$pattern, $controller, $title, $name);
	}

	static function Any($pattern, $controller, $title = null, $name = null)
	{
		Routes::registerRoute('ANY',$pattern, $controller, $title, $name);
	}

	static function isCurrent($name)
	{
		return Routes::$current['name'] == $name;
	}


	static function Follow($name, $params = array())
	{
		foreach (Routes::$mapping as $route) 
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
	
	static function Route()
	{
		
		foreach(Routes::$mapping as $route)
	 	{
	 		if ($route['method'] == 'ANY' || $route['method'] == $_SERVER['REQUEST_METHOD'])
	 		{
	 			if (preg_match($route['regex'], $_SERVER['REQUEST_URI'], $result))
		 		{
			 		preg_match_all('/{([a-z]+)}/',$route['pattern'], $matches);
					Routes::$current = $route;	
					$params = array();
					for($i=0;$i<count($matches[1]);$i++)
					{
						$params[$matches[1][$i]] = $result[$i+1];
					}
				//	var_dump($result,$matches,$params);
					$route['controller']->index($params);
					
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
		foreach (Routes::$mapping as $item) 
		{
			if ($item['title'] !== null)
			{
				$result[] = $item;
			}
		}

		return $result;
	}
}


?>