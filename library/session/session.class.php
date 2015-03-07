<?php

class Linko_Session
{
	private $_oStorage;
	
	private $_sPrefix;
	
	private $_sStorage;
	
	private $_aStorageMap = array(
		'default' => 'Linko_Session_Storage_Default',
		'file' => 'Linko_Session_Storage_File',
		'database' => 'Linko_Session_Storage_Database',
		'memcache' => 'Linko_Session_Storage_Memcache'
	);
	
	public function __construct()
	{
		$this->_sStorage = Linko::Config()->get('Session.storage');
		
		Linko_Object::map($this->_aStorageMap[$this->_sStorage],LINKOBASE.'library'.DS.'session'.DS.'storage'.DS.$this->_sStorage.'.class.php');
		$this->_oStorage = Linko_Object::get($this->_aStorageMap[$this->_sStorage]);
	}
	
	public function getInstance()
	{
		return $this->_oStorage;	
	}
}

?>