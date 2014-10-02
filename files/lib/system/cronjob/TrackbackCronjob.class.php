<?php
namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;

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
class TrackbackCronjob extends AbstractCronjob {

	const CHECKINTERVAL = 604800; // one week
	
	const MAXFAILCOUNT = 3; 
	
	/**
	 * @see	wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		if (!ENABLE_TRACKBACK_CHECK) return; 
		
		$list = new \wcf\data\trackback\TrackbackList(); 
		$list->getConditionBuilder()->add('trackback.lastCheckTime < ?', array(TIME_NOW - self::CHECKINTERVAL)); 
		$list->sqlLimit = 5; 
		$list->sqlOrderBy = 'trackback.lastCheckTime ASC';
		$list->readObjects(); 
		
		foreach ($list as $trackback) {
			try {
				$http = new \wcf\util\HTTPRequest($trackback->url);
				$http->execute();
				
				$reply = $http->getReply();
				$body = $reply['body'];
				
				if (strpos($body, $targetURI) === false) {
					$this->increaseFailCount($trackback);
				}
			} catch (\Exception $e) {
				$this->increaseFailCount($trackback);
			}
		}
	}

	public function increaseFailCount(\wcf\data\trackback\Trackback $trackback) {
		if ($trackback->failedCount < 2) {
			$editor = new \wcf\data\trackback\TrackbackEditor($trackback); 
			$editor->updateCounters(array(
			    'failedCount' => 1
			)); 
		} else {
			$action = new \wcf\data\trackback\TrackbackAction(array($trackback), 'delete');
			$action->validateAction(); 
			$action->executeAction(); 
		}
	}
}
