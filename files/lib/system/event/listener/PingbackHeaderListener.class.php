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
class PingbackHeaderListener implements IEventListener {

	/**
	 * @see	wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (!MODULE_TRACKBACK) return;
		
		@header('X-Pingback: '.\wcf\system\request\LinkHandler::getInstance()->getLink('Pingback')); 
	}
}
