<?php
namespace wcf\acp\form; 

use wcf\form\AbstractForm; 
use wcf\util\StringUtil; 
use wcf\system\WCF; 
use wcf\system\exception\UserInputException; 

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
class TrackbackBlacklistEntryAddForm extends AbstractForm {
	
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.trackback.blacklistadd';
	
	/**
	 * the host
	 * @var string 
	 */
	public $host = '';
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['host'])) $this->host = StringUtil::trim($_POST['host']); 
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->host)) {
			throw new UserInputException('host');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$action = new \wcf\data\trackback\blacklist\entry\TrackbackBlacklistEntryAction(array(), 'create', array('data' => array('host' => $this->host)));
		$action->executeAction(); 
		
		$this->saved(); 
		
		$this->host = ''; 
	}
	
	/**
	 * @see	\wcf\form\IForm::saved()
	 */
	public function saved() {
		parent::saved();
		
		WCF::getTPL()->assign(array('success' => true)); 
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'host' => $this->host
		));
	}
}
