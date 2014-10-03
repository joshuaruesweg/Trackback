<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;

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
class TrackbackRouteListener implements IEventListener {

	/**
	 * @see	wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (!MODULE_TRACKBACK) return; 
		
		$route = new Route('trackback');
		$route->setSchema('{controller}/{objectType}/{id}');
		$route->setParameterOption('controller', null, '^Trackback$');
		$route->setParameterOption('objectType', null, '[a-z0-9\-](\.[a-z0-9\-]*)+');
		$route->setParameterOption('id', null, '\d+', true);
		$eventObj->addRoute($route);
	}

}
