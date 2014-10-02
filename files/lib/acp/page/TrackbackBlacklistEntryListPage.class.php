<?php
namespace wcf\acp\page;

use wcf\page\SortablePage;

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
class TrackbackBlacklistEntryListPage extends SortablePage {

	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.trackback.blacklistlist';

	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_TRACKBACK');

	/**
	 * @see	wcf\page\MultipleLinkPage::$defaultSortField
	 */
	public $defaultSortField = 'entryID';

	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\trackback\blacklist\entry\TrackbackBlacklistEntryList';

	/**
	 * @see	wcf\page\MultipleLinkPage::$validSortFields
	 */
	public $validSortFields = array('entryID', 'host');
}
