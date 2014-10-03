<?php
namespace wcf\action; 
use wcf\action\AbstractAction;
use Zend\XmlRpc\Server as XmlRpcServer; 
use Zend\Loader\StandardAutoloader as ZendLoader;

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
class PingbackAction extends AbstractAction {
	
	/**
	 * @see \wcf\action\AbstractAction::$neededPermissions
	 */
	public $neededPermissions = array('user.message.trackback.canUser');
	
	/**
	 * @see \wcf\action\AbstractAction::$neededModules
	 */
	public $neededModules = array('MODULE_TRACKBACK');
	
	/**
	 * a XmlRpcServer
	 * @var \Zend\XmlRpc\Server
	 */
	public $server = null; 
	
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->setupServer(); 
	}
	
	/**
	 * setup a new XmlRpcServer
	 */
	public function setupServer() {
		require_once(WCF_DIR.'lib/system/api/zend/Loader/StandardAutoloader.php');
		$zendLoader = new ZendLoader(array(ZendLoader::AUTOREGISTER_ZF => true));
		$zendLoader->register();
		
		$this->server = new XmlRpcServer();
		$this->server->setClass('\wcf\system\trackback\PingbackHandler', 'pingback');
		
		// set content type 
		@header("Content-Type: text/xml");
	}
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		$this->server->setReturnResponse(true);
		$handle = $this->server->handle(); 
		
		if (!($handle instanceof \Zend\XmlRpcServer\Fault) && !($handle instanceof \Zend\XmlRpc\Fault)) {
			$value = $handle->getReturnValue(); 
		}
		
		if (!($handle instanceof \Zend\XmlRpcServer\Fault) && !($handle instanceof \Zend\XmlRpc\Fault) && is_array($value) && $value['error']) {
			echo $this->server->fault($value['message'], $value['code']);
		} else {
			echo $handle; 
		}
	}
}
