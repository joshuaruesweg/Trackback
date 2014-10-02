<?php
namespace wcf\data\trackback\blacklist\entry;
use wcf\data\AbstractDatabaseObjectAction;

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
class TrackbackBlacklistEntryAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\trackback\blacklist\entry\TrackbackBlacklistEntryEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.canEditOption');
}
 