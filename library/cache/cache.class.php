<?php

class Linko_Cache
{
	private $_sStorage;
	
	private $_oStorage;
	
	private $_aStorageMap = array(
		'file' => 'Linko_Cache_Storage_File',
		'apc' => 'Linko_Cache_Storage_Apc',
		'memcache' => 'Linko_Cache_Storage_Memcache'
	);
	
	public function __construct()
	{
		$this->_sStorage = Linko::Config()->get('Cache.storage');
		
		if(!isset($this->_aStorageMap[$this->_sStorage]))
		{
			
		}
		
		Linko_Object::map($this->_aStorageMap[$this->_sStorage], LINKOBASE.'library'.DS.'cache'.DS.'storage'.DS.$this->_sStorage.'.class.php');
		
		$this->_oStorage = Linko_Object::get($this->_aStorageMap[$this->_sStorage]);
	}
	
	public function &getInstance()
	{
		return $this->_oStorage;	
	}
}

?>