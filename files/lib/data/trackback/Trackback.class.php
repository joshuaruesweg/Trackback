<?php
namespace wcf\data\trackback; 
use wcf\data\DatabaseObject; 

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
class Trackback extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'trackback';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'trackbackID';
	
	/**
	 * Returns a list of trackbacks.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	\wcf\data\acl\option\ACLOptionList
	 */
	public static function getTrackbacks($objectTypeID) {
		$list = new TrackbackList(); 
		$list->getConditionBuilder()->add('trackback.objectTypeID = ?', array($objectTypeID)); 
		$list->readObjects(); 
		
		return $list; 
	}
}
