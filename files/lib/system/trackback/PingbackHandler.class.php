<?php
namespace wcf\system\trackback;
use wcf\util\TrackbackUtil; 
use wcf\util\PingbackUtil;
use wcf\data\object\type\ObjectTypeCache; 

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
class PingbackHandler {

	const UNKNOWN_SOURCEURI = 0x0010;
	const MISSING_BACKLING = 0x0011;
	const UNKNOWN_TARGET = 0x0020;
	const INVALID_TARGET = 0x0021;
	const DOUBLE = 0x0030;
	const ACCESS_DENIED = 0x0031;
	const COMMUNICATION_FAILED = 0x0032;
	const INTERNAL_ERROR = 0x0033; 
	
	public static $messages = array(
		self::UNKNOWN_SOURCEURI => 'The source URI does not exist.',
		self::MISSING_BACKLING => 'The source URI does not contain a link to the target URI, and so cannot be used as a source.',
		self::UNKNOWN_TARGET => 'The specified target URI does not exist.',
		self::INVALID_TARGET => 'The specified target URI cannot be used as a target. It either doesn\'t exist, or it is not a pingback-enabled resource.',
		self::DOUBLE => 'The pingback has already been registered.',
		self::ACCESS_DENIED => 'Access denied.',
		self::COMMUNICATION_FAILED => 'The server could not communicate with an upstream server, or received an error from an upstream server, and therefore could not complete the request.', 
		self::INTERNAL_ERROR => 'Unknown error.'
	);

	/**
	 * Pingaction
	 *
	 * @param string $sourceURI
	 * @param string $targetURI
	 * @return array<mixed>
	 */
	public function ping($sourceURI, $targetURI) {
		try {
			// first we check the source
			try {
				$http = new \wcf\util\HTTPRequest($sourceURI);
				$http->execute();
			} catch (\Exception $e) {
				$this->throwException(self::UNKNOWN_SOURCEURI);
			}

			$reply = $http->getReply();
			$body = $reply['body'];

			if (strpos($body, $targetURI) === false) {
				$this->throwException(self::MISSING_BACKLING);
			}

			// set title, too :) 
			preg_match("/\<title\>(.*)\<\/title\>/", $body, $title);
			
			if (isset($title[1])) {
				$title = $title[1];
			} else {
				$title = null; 
			}
			
			
			// fetch target
			$objectTypeID = PingbackUtil::getObjectTypeFromURL($targetURI); 
			$objectID = PingbackUtil::getObjectIDFromURL($targetURI); 
			
			// validate 
			if (TrackbackUtil::isBlacklisted($sourceURI)) {
				$this->throwException(self::ACCESS_DENIED);
			}
			
			if ($objectTypeID === false || $objectID === null) {
				$this->throwException(self::INVALID_TARGET); 
			}

			$object = null; 
			
			// read object
			$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID); 
			if ($objectType->getObjectID() !== null) {
				$objectProccessor = $objectType->getProcessor();
				if ($objectProccessor !== null) $object = $objectProccessor->getObjectByID($objectID); 
			}
			
			if ($object === null) {
				$this->throwException(self::INVALID_TARGET); 
			}
			
			
			$action = new \wcf\data\trackback\TrackbackAction(array(), 'create', array('data' => array(
				'url' => $sourceURI,
				'title' => $title,
				'objectTypeID' => $objectType->getObjectID(),
				'objectID' => $object->getObjectID(), 
				'time' => TIME_NOW,
				'ipAddress' => (LOG_IP_ADDRESS) ? \wcf\system\WCF::getSession()->ipAddress : null
			)));
			$action->executeAction();
			
			return array(
				'error' => false,
				'message' => 'Thanks for pinning! :)' // <-- we are polite :) 
			);
		} catch (\wcf\system\exception\UserInputException $ex) {
			return array(
				'error' => true,
				'code' => $ex->getField(),
				'message' => (isset(self::$messages[$ex->getField()])) ? self::$messages[$ex->getField()] : '',
			);
		}
	}

	/**
	 * throws a pingback-exception
	 * @param integer	$code
	 */
	private function throwException($code) {
		throw new \wcf\system\exception\UserInputException($code);
	}

	/**
	 * Oh good, whats that? :o 
	 */
	public function ring() {
		return 'Ash nazg durbatuluuk, ash nazg gimbatul, ash nazg thrakatuluuk, agh burzum-ishi krimpatul.';
	}

}
