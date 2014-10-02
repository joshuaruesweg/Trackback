<?php
namespace wcf\util;

use wcf\data\ITrackbackableObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\XMLWriter;

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
final class TrackbackUtil {

	const DEFINITION_NAME = 'com.hg-202.trackback.trackback';

	/**
	 * return false if the url is blacklisted (marked as spam)
	 * 
	 * @param type $url
	 * @return boolean
	 */
	public static function isBlacklisted($url) {
		$url = parse_url($url);
		$host = $url['host'];

		$list = new \wcf\data\trackback\blacklist\entry\TrackbackBlacklistEntryList();
		$list->getConditionBuilder()->add('host = ?', array($host));
		$list->readObjects();

		if ($list->count() > 0) {
			return true;
		}

		return false;
	}

	/**
	 * build a trackbackurl for an object
	 * 
	 * @param \wcf\data\ITrackbackableObject	$object
	 * @return string
	 */
	public static function buildTrackbackURL(ITrackbackableObject $object) {
		return LinkHandler::getInstance()->getLink('Trackback', array(
			    'id' => $object->getObjectID(),
			    'objectType' => $object->getObjectTypeName()
		));
	}

	/**
	 * create a trackback-xml answer
	 * 
	 * @param	boolean			$success
	 * @param	array<mixed>		$parameters
	 * @return	string
	 */
	public static function createTrackbackAnswer($success = true, array $parameters = array()) {
		$writer = new XMLWriter();
		$writer->beginDocument('methodResponse', 'com.hg-202', 'com.hg-202'); // <- TODO 
		
		$writer->writeElement('error', (!$success) ? 1 : 0);

		self::buildDOM($writer, $parameters);

		$writer->writeElement('plugin', 'com.hg-202.trackback');

		return $writer->endDocument();
	}

	/**
	 * recursive dom building
	 * 
	 * @param	\DOMNode	$element
	 * @param	\DOMDocument	$dom
	 * @param	array<mixed>	$array
	 */
	private static function buildDOM(XMLWriter &$writer, array $array) {
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$writer->writeElement($key, $value);
			} else {
				$writer->startElement($key);
				self::buildDOM($writer, $value);
				$writer->endElement();
			}
		}
	}

	public static function generateRDF(ITrackbackableObject $object) {
		WCF::getTPL()->assign(array(
		    'link' => $object->getLink(),
		    'trackbackURL' => TrackbackUtil::buildTrackbackURL($object),
		    'title' => $object->getTitle()
		));

		return WCF::getTPL()->fetch('trackbackRDF');
	}

	private function __construct() { }
}
