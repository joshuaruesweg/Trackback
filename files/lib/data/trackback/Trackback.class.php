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
	 * returns the host of the trackback url
	 * 
	 * @return String the host
	 */
        public function getHost() {
		$url = @parse_url($this->url);
		
		if (isset($url['host'])) return $url['host']; 
		
		return false; 
        }
        
	/**
	 * Returns a list of trackbacks.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	array<\wcf\data\trackback\Trackback>
	 */
	public static function getTrackbackList($objectTypeID, $objectID) {
		$list = new TrackbackList(); 
		$list->getConditionBuilder()->add('trackback.objectTypeID = ?', array($objectTypeID)); 
		$list->getConditionBuilder()->add('trackback.objectID = ?', array($objectID));
		
		return $list; 
	}
}
