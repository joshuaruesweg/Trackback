<?php
namespace wcf\util;

use wcf\system\request\RouteHandler;
use wcf\system\cache\builder\ControllerCacheBuilder;
use wcf\util\TrackbackUtil;
use wcf\system\Regex; 

/**
 * 
 * 
 * @author		Joshua Rüsweg
 * @copyright   
 * @license     
 * @package		com.hg-202.trackback
 * @subpackage  
 * @category    
 */
final class PingbackUtil {
	
	/**
	 * fetch the objectTypeID from a specefic url. If the URL hasn't a
	 * objectTypeID, the method returns false. 
	 * 
	 * @param	mixed	 $url
	 */
	public static function getObjectTypeFromURL($url) {
		$routes = RouteHandler::getInstance()->getRoutes();

		$url = parse_url($url);
		// no handling without query :) 
		if (!isset($url['query'])) {
			return false; 
		}
		
		$query = $url['query']; 
		
		if (!empty($query)) {
			parse_str($query, $parts);
			foreach ($parts as $key => $value) {
				if ($value === '') {
					$path = $key;
					break;
				}
			}
		}
		
		$controller = null;
		
		foreach ($routes as $route) {
			if ($route->matches($path)) {
				$routeData = $route->getRouteData();

				$controller = $routeData['controller'];
				$ciController = mb_strtolower($controller);
				break;
			}
		}
		
		if ($controller === null) {
			return false; 
		}

		$objectTypes = \wcf\data\object\type\ObjectTypeCache::getInstance()->getObjectTypes(TrackbackUtil::DEFINITION_NAME);

		$controllers = ControllerCacheBuilder::getInstance()->getData(array(
			'environment' => 'user'
		));
		
		foreach ($objectTypes as $type) {
			$application = $type->application;

			if ($application === null) {
				// try to guess application 
				$packageID = $type->packageID;
				$package = new \wcf\data\package\Package($packageID);

				if (!$package->isPlugin()) {
					$application = \wcf\data\package\Package::getAbbreviation($package->package);
				} else {
					$application = 'wcf';
				}
			}
			$objectTypeController = $type->controller;
			
			foreach ($controllers[$application] as $pageType) {
				foreach ($pageType as $page) {
					if (isset($page[$ciController])) {
						return $type->getObjectID();
					}
				}
			}
		}
		
		return false; 
	}
	
	/**
	 * gets the objectID from an url 
	 * 
	 * @param	String	$url
	 * @param	String	$idIdentifer
	 * @return	boolean|null
	 */
	public static function getObjectIDFromURL($url, $idIdentifer = 'id') {
		$routes = RouteHandler::getInstance()->getRoutes();

		$url = parse_url($url);
		// no handling without query :) 
		if (!isset($url['query'])) {
			return null; 
		}
		
		$query = $url['query']; 
		
		if (!empty($query)) {
			parse_str($query, $parts);
			foreach ($parts as $key => $value) {
				if ($value === '') {
					$path = $key;
					break;
				}
			}
		}
		
		foreach ($routes as $route) {
			if ($route->matches($path)) {
				$routeData = $route->getRouteData();
				
				if (isset($routeData[$idIdentifer])) {
					return $routeData[$idIdentifer];
				}
				
				break; 
			}
		}
		
		return null; 
	}
	
	/**
	 * pings automaticly all urls in a text
	 * 
	 * @param string				$text
	 * @param \wcf\data\ITrackbackableObject	$trackback
	 */
	public static function autoPing($text, \wcf\data\ITrackbackableObject $trackback) {
		// @see https://github.com/WoltLab/WCF/blob/master/wcfsetup/install/files/lib/system/bbcode/PreParser.class.php#L122
		$urlPattern = new Regex('
		(?<!\B|"|\'|=|/|\]|,|\?|\.)
		(?:						# hostname
			(?:ftp|https?)://'.static::$illegalChars.'(?:\.'.static::$illegalChars.')*
			|
			www\.(?:'.static::$illegalChars.'\.)+
			(?:[a-z]{2,63}(?=\b))			# tld
		)

		(?::\d+)?					# port

		(?:
			/
			[^!.,?;"\'<>()\[\]{}\s]*
			(?:
				[!.,?;(){}]+ [^!.,?;"\'<>()\[\]{}\s]+
			)*
		)?', Regex::IGNORE_WHITESPACE | Regex::CASE_INSENSITIVE);
		$urlPattern->match($text);
		$matches = $urlPattern->getMatches(); 
		
		foreach ($matches as $match) {
			self::ping($match[0], $trackback);
		}
	}
	
	/**
	 * ping a url
	 * @param string					$url
	 * @param \wcf\data\ITrackbackableObject	$trackback
	 */
	public static function ping($url, \wcf\data\ITrackbackableObject $trackback) {
		$server = self::getPingbackLink($url);
		
		if ($server !== null) {
			// we can ping it
			$request = new HTTPRequest($server, array(), ''); 
			$request->execute(); 
		} 
	}
	
	/**
	 * 
	 * @param type $url
	 * @return null
	 */
	public static function getPingbackLink($url) {
		$request = new HTTPRequest($url);
		$request->execute();
		$body = $request->getReply();

		if (isset($body['headers']['X-Pingback'])) {
			return $body['headers']['X-Pingback'][0]; 
		}
		
		if (preg_match('#<link rel="pingback" href="([^"]+)" ?/?>#', $body['body'], $m)) {
			return $m[1];
		}
		
		return null; 
	}
	
	public static function getPingRequest($source, $target) {
		
	}
}