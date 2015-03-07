<?php

class Linko_Database
{
	/*
	 * Holds the default connection name
	*/
	private $_sDefault;
	
	private $_oActiveConnection = false;
	
	private $_aConnection = array();
	
	private $_aOpenConnection = array();
		
	private $_oConnection;

	private $_aDriverMap = array(
		'mysql' => 'Linko_Database_Driver_Mysql',
        'mysqli' => 'Linko_Database_Driver_Mysqli',
		'sqlite' => 'Linko_Database_Driver_Sqlite',
        'mongo' => 'Linko_Database_Driver_Mongo'
	);
		
	public function __construct()
	{
		// First add all connection configuration
		if(($aConnections = Linko::Config()->get('database.connection')) && (is_array($aConnections)))
		{
			foreach($aConnections as $sName => $aParam)
			{				
				$this->addConnection($sName, $aParam);
			}
		}
	}
    	
	public function setActiveConnection($sName)
	{
        if(!array_Key_exists($sName, $this->_aOpenConnection))
        {
            //return Linko::Error()->trigger('Database Error: Connection not available.');
        }
        
		$this->_oActiveConnection = $this->openConnection($sName);
	}
	
	public function getActiveConnection()
	{
		if(!$this->_oActiveConnection)
		{
			$this->setActiveConnection($this->getDefault());
		}
		
		return $this->_oActiveConnection;
	}

	public function getConnections()
	{
		return $this->_aConnection;	
	}

	public function getOpenConnections()
	{
		return $this->_aOpenConnection;	
	}
	
	public function resetActiveConnection()
	{
		$this->setActiveConnection($this->getDefault());
	}
		
	public function setDefault($sName)
	{
		$this->_sDefault = $sName;
	}
	
	public function getDefault()
	{
		if($this->_sDefault)
		{
			return $this->_sDefault;	
		}
		
		return Linko::Config()->get('Database.default');
	}
	
	public function addConnection($sName, $aParams)
	{
		$aParams = array_merge(array(
			'driver' => Linko::Config()->get('Database.driver'),
			'prefix' => Linko::Config()->get('Database.prefix')
		), $aParams);
                
		if(array_key_exists($sName, $this->_aConnection))
		{
			/*
				 @todo: remove connection if a connection is to be replaced
			*/
			Linko::Error()->trigger('Connection with that name already exists');
			
			return false;	
		}
		
		$this->_aConnection[$sName] = $aParams;
		
		return $this;
	}
	
	public function openConnection($sName)
	{
		$aParam = $this->_aConnection[$sName];
		
		$sHash = md5($sName . serialize($aParam));
		
		// checks if this connection been opened before and returns it
		if(isset($this->_aOpenConnection[$sHash]))
		{
			return $this->_aOpenConnection[$sHash];	
		}
		
		$sDriver = $aParam['driver'];
		$sPrefix = $aParam['prefix'];
		
		if(!isset($this->_aDriverMap[$sDriver]))
		{
			Linko::Error()->trigger('Invalid Database Driver (' . $sDriver . ') Specified.', E_USER_ERROR);	
		}
		
		Linko_Object::map($this->_aDriverMap[$sDriver], LINKOBASE.'library'.DS.'database'.DS.'driver'.DS.$sDriver.DS.$sDriver.'.class.php');
						
		$this->_aOpenConnection[$sHash] = Linko_Object::get($this->_aDriverMap[$sDriver], $aParam);
		
		if(!$this->_aOpenConnection[$sHash] instanceof Linko_Database_Abstract)
		{
			Linko::Error()->trigger('' . $sDriver . ' must be a derived class of  Linko_Database_Abstract class.', E_USER_ERROR);	
		}
		
		return $this->_aOpenConnection[$sHash];
	}
	
	public function closeConnection()
	{
		$iNames = func_num_args();
		
		if($iNames == 0)
		{
			foreach($this->_aOpenConnection as $sName => $oConnection)
			{
				$oConnection->close();	
			}
			
			$this->_aOpenConnection = array();
			
			return;
		}
		
		$aNames = func_get_args();
		
		foreach($aNames as $sName)
		{
			$this->_aOpenConnection[$sName]->close();
			
			unset($this->_aOpenConnection[$sName]);
		}			
	}
		
	public function __call($sMethod, $aArguments)
	{
        $oConnection = $this->getActiveConnection();
		
		return call_user_func_array(array($this->_oActiveConnection, $sMethod), $aArguments);
	}
}

?>