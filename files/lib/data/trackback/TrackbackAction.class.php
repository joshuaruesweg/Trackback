<?php
namespace wcf\data\trackback;
use wcf\system\exception\UserInputException; 
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\object\type\ObjectTypeCache; 

/**
 * 
 * 
 * @author      Joshua Rüsweg
 * @copyright   
 * @license     
 * @package     
 * @subpackage  
 * @category    
 */
class TrackbackAction extends AbstractDatabaseObjectAction {
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\trackback\TrackbackEditor';
	
	public $permissionCreate = 'user.message.canAddTrackback'; 
	
	/**
	 * @see \wcf\data\AbstractDatabaseObjectAction::validateCreate()
	 */
	public function validateCreate() {
		parent::validateCreate();
		
		if (!isset($this->parameters['data']['time'])) {
			$this->parameters['data']['time'] = TIME_NOW; 
		}
		
		if (LOG_IP_ADDRESS) {
			// add ip address
			if (!isset($this->parameters['data']['ipAddress'])) {
				$this->parameters['data']['ipAddress'] = WCF::getSession()->ipAddress;
			}
		} else {
			// do not track ip address
			if (isset($this->parameters['data']['ipAddress'])) {
				unset($this->parameters['data']['ipAddress']);
			}
		}
		
		if (!isset($this->parameters['data']['url'])) {
			throw new UserInputException('invalid url');
		}
		
		if (!isset($this->parameters['data']['objectTypeID'])) {
			throw new UserInputException('invalid objectTypeID');
		}
		
		if (!isset($this->parameters['data']['objectID'])) {
			throw new UserInputException('invalid objectID');
		}
		
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		
		if ($objectType === null) {
			throw new UserInputException('invalid objectType');
		}
		
		$proccessor = $objectType->getProcessor();
		$object = $proccessor->getObjectByID($this->parameters['data']['objectID']); 
		
		if (!$object || !$object->getObjectID()) {
			throw new UserInputException('invalid objectID');
		}
	}
	
	/**
	 * marks a trackback as spam
	 */
	public function markAsSpam() {
		$action = new TrackbackAction($this->getObjects(), 'update', array('data' => array('isBlocked' => 1))); 
		$action->executeAction(); 
		
		foreach ($this->getObjects() as $object) {
			$url = parse_url($object->url); 
			$host = $url['host']; 
			
			$action = new \wcf\data\trackback\blacklist\entry\TrackbackBlacklistEntryAction(array(), 'create', array('data' => array('host' => $host)));
			$action->executeAction(); 
		}
	}
	
	/**
	 * validate the action markAsSpam
	 */
	public function validateMarkAsSpam() {
		// @TODO permission check.. 
	}
}
 