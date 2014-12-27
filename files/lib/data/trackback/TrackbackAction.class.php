<?php
namespace wcf\data\trackback;
use wcf\system\exception\UserInputException; 
use wcf\system\exception\PermissionDeniedException; 
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\object\type\ObjectTypeCache; 
use wcf\system\WCF; 

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
	
	public $permissionsDelete = array('mod.general.trackback.canDelete'); 
	
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
	
	public function create() {
		parent::create();
		
		// update trackback count for object
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		$proccessor = $objectType->getProcessor();
		$object = $proccessor->getObjectByID($this->parameters['data']['objectID']); 
		$proccessor->updateTrackbackCount(1, $object); 
	}
	
	/**
	 * toogle block for trackbacks
	 */
	public function toogleBlock() {
		$block = array(); 
		$unblock = array(); 
		
		foreach ($this->getObjects() as $object) {
			if ($object->isBlocked) {
				$unblock[$object->getObjectID()] = $object; 
			} else {
				$block[$object->getObjectID()] = $object; 
			}
		}
		
		if (count($unblock)) {
			$action = new TrackbackAction($unblock, 'update', array('data' => array('isBlocked' => 0))); 
			$action->executeAction();
		}
		
		if (count($block)) {
			$action = new TrackbackAction($block, 'update', array('data' => array('isBlocked' => 1))); 
			$action->executeAction();
		}
		
		return array(
			'blocked' => array_keys($block), 
			'unblocked' => array_keys($unblock)
		); 
	}
	
	/**
	 * validate the action toogleBlock
	 */
	public function validateToogleBlock() {
		WCF::getSession()->checkPermissions(array('mod.general.trackback.canBlock'));
		
		if (empty($this->objects)) {
			$this->readObjects(); 
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * remove a trackback
	 */
	public function remove() {
		$objectIDs = array();
		
		foreach ($this->objects as $object) {
			$objectIDs[] = $object->getObjectID();
			
			if (!isset($objectTypes[$object->objectTypeID][$object->objectID])) {
				$objectTypes[$object->objectTypeID][$object->objectID] = 0; 
			}
			$objectTypes[$object->objectTypeID][$object->objectID]++;
		}
		
		parent::delete();
		
		foreach ($objectTypes as $objectTypeID => $objects) {  
			// update trackback count for object
			$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
			$proccessor = $objectType->getProcessor();
			
			foreach ($objects as $objectID => $count) {
				$object = $proccessor->getObjectByID($objectID); 
				$proccessor->updateTrackbackCount(-1 * $count, $object); 
			}
		}
		
		return array(
			'objectIDs' => $objectIDs
		);
	}
	
	/**
	 * validate remove a trackback
	 */
	public function validateRemove() {
		parent::validateDelete(); 
	}
	
	public function loadTrackbacks() {
		$trackbackList = new TrackbackList(); 
		$trackbackList->getConditionBuilder()->add('trackback.time < ?', array($this->parameters['lastSeenTime'])); 
		$trackbackList->sqlLimit = 10; 
		$trackbackList->sqlOrderBy = 'trackback.time DESC'; 
		$trackbackList->readObjects(); 
		
		$objects = $trackbackList->getObjects(); 
		$last = end($objects); 
		
		WCF::getTPL()->assign(array(
			'trackbacks' => $trackbackList->getObjects()
		));
		
		return array(
			'template' => WCF::getTPL()->fetch('trackbackList'), 
			'count' => $trackbackList->count(), 
			'lastSeenTime' => $trackbackList->count() ? $last->time : $this->parameters['lastSeenTime']
		);
	}
	
	public function validateLoadTrackbacks() {
		$this->readString('objectType'); 
		$this->readInteger('objectID'); 
		$this->readInteger('lastSeenTime'); 
		
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.hg-202.trackback.trackback', $this->parameters['objectType']);
		
		if ($objectType === null) {
			throw new UserInputException('objectType');
		}
		
		$proccessor = $objectType->getProcessor();
		$object = $proccessor->getObjectByID($this->parameters['objectID']); 
		
		if (!$object || !$object->getObjectID()) {
			throw new UserInputException('objectID');
		}
		
		if (!$object->canRead()) {
			throw new PermissionDeniedException(); 
		}
	}
}
 