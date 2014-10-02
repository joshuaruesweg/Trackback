<?php
namespace wcf\data; 

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
interface ITrackbackableObject extends ITitledObject, ILinkableObject, IMessage, \wcf\system\request\IRouteController {
	
	/**
	 * returns the objecttype name
	 */
	public static function getObjectTypeName();
}
