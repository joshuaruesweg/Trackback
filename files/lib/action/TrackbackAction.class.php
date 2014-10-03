<?php
namespace wcf\action; 
use wcf\action\AbstractAction;
use wcf\data\object\type\ObjectTypeCache; 
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException; 
use wcf\system\message\censorship\Censorship;
use wcf\util\StringUtil;
use wcf\util\TrackbackUtil;  

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
class TrackbackAction extends AbstractAction {
	
	/**
	 * @see \wcf\action\AbstractAction::$neededPermissions
	 */
	public $neededPermissions = array('user.message.trackback.canUser');
	
	/**
	 * @see \wcf\action\AbstractAction::$neededModules
	 */
	public $neededModules = array('MODULE_TRACKBACK');
	
	/**
	 * the objecttype name for the trackback
	 * @var string 
	 */
	public $objectTypeName = ''; 
	
	/**
	 * the object type
	 * @var \wcf\data\object\type\ObjectType 
	 */
	public $objectType = null; 
	
	/**
	 * the objectProcessor for $objectType
	 * @var string 
	 */
	public $objectProcessor = null; 
	
	/**
	 * the object type
	 * @var integer 
	 */
	public $objectID = 0; 
	
	/**
	 * the object for the request 
	 * @var \wcf\data\DatabaseObject 
	 */
	public $object = null; 
	
	/**
	 * the url for the trackback
	 * @var string 
	 */
	public $url = ''; 
	
	/**
	 * the url for the trackback
	 * @var string 
	 */
	public $blogName = ''; 
	
	/**
	 * the title for the trackback
	 * @var string 
	 */
	public $title = ''; 
	
	/**
	 * the excerpt for the trackback
	 * @var string 
	 */
	public $excerpt = ''; 
	
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['objectType'])) $this->objectTypeName = StringUtil::trim($_REQUEST['objectType']); 
		if (isset($_REQUEST['objectID'])) $this->objectID = intval($_REQUEST['objectID']); 
		
		// trackback relevant
		if (isset($_POST['url'])) $this->url = $_POST['url']; 
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']); 
		if (isset($_POST['excerpt'])) $this->excerpt = StringUtil::trim($_POST['excerpt']); 
		if (isset($_POST['blog_name'])) $this->blogName = StringUtil::trim($_POST['blog_name']); 
		
		// read object
		$this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(TrackbackUtil::DEFINITION_NAME, $this->objectTypeName); 
		if ($this->objectType !== null) {
			$this->objectProcessor = $this->objectType->getProcessor();
			if ($this->objectProcessor !== null) $this->object = $this->objectProcessor->getObjectByID($this->objectID); 
		}
		
		try {
			$this->validate(); 
		} catch (UserInputException $exception) {
			$array = array('message' => $exception->getField()); 
			
			if (ENABLE_DEBUG_MODE) {
				// debug it! 
				$array['debug']['objectTypeName'] = StringUtil::encodeHTML($this->objectTypeName); 
				$array['debug']['objectID'] = StringUtil::encodeHTML($this->objectID);
				$array['debug']['url'] = StringUtil::encodeHTML($this->url); 
				$array['debug']['title'] = StringUtil::encodeHTML($this->title);
				$array['debug']['excerpt'] = StringUtil::encodeHTML($this->excerpt); 
				$array['debug']['blog_name'] = StringUtil::encodeHTML($this->blogName); 
			}
			
			@header("Content-Type: text/xml");
			echo TrackbackUtil::createTrackbackAnswer(false, $array);
			exit; 
		}
	}
	
	/**
	 * validates the inputs
	 */
	public function validate() {
		EventHandler::getInstance()->fireAction($this, 'validate'); 
		
		if (!$this->object || !$this->object->getObjectID()) {
			throw new UserInputException('unknown object'); 
		}
		
		if (empty($this->url)) {
			throw new UserInputException('incomplete request'); 
		}
		
		if (ENABLE_CENSORSHIP) {
			if (Censorship::getInstance()->test($this->excerpt) !== false || Censorship::getInstance()->test($this->title) !== false || Censorship::getInstance()->test($this->blog_name) !== false) {
				throw new UserInputException('censored'); 
			}
		}
		
		if (TrackbackUtil::isBlacklisted($this->url)) {
			throw new UserInputException('blacklisted');
		}
	}
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		$action = new \wcf\data\trackback\TrackbackAction(array(), 'create', array('data' => array(
			'url' => $this->url, 
			'title' => $this->title, 
			'excerpt' => $this->excerpt, 
			'blogTitle' => $this->blogName,
			'objectTypeID' => $this->objectType->getObjectID(), 
			'objectID' => $this->objectID
		)));
		$action->validate(); 
		$action->execute(); 
		
		$this->executed(); 
		
		@header("Content-Type: text/xml");
		echo TrackbackUtil::createTrackbackAnswer();
		exit; 
	}
}
