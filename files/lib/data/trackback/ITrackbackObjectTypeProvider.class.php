<?php
namespace wcf\data\trackback;
use wcf\data\ITrackbackableObject;
use wcf\data\object\type\IObjectTypeProvider;

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
interface ITrackbackObjectTypeProvider extends IObjectTypeProvider {
	/**
	 * Returns true if the active user can trackback the object. 
	 * 
	 * @param	\wcf\data\ITrackbackableObject	$object
	 * @return	boolean
	 */
	public function hasPermissions(ITrackbackableObject $object);
}
