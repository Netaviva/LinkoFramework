<?php

class Linko_Database_Driver_Mysqli extends Linko_Database_Abstract
{
	protected $_sHost;
	
	protected $_sUser;
	
	protected $_sPass;
	
	protected $_sPort = null;
	
	protected $_sDatabase;
	
	protected $_sPrefix;
	
	protected $_bPersistent = false;
    
    protected $_sIdentifier = '`%s`';
    
    protected $_sBuilder = 'Sql';
	
	public function __construct($aParams)
	{		
		$this->_sHost = $aParams['host'];
		
		$this->_sUser = $aParams['username'];
		
		$this->_sPass = $aParams['password'];
		
		$this->_sDatabase = $aParams['database'];
		
		$this->_sPort = isset($aParams['port']) ? $aParams['port'] : null;
		
		$this->_sPrefix = isset($aParams['prefix']) ? $aParams['prefix'] : null;
		
		$this->_bPersistent = isset($aParams['persistent']) ? $aParams['persistent'] : false;

		$this->_sCharset = isset($aParams['charset']) ? $aParams['charset'] : null;
		
		$this->connect();
		
		parent::__construct($aParams);
	}
	
	public function connect()
	{
		Linko::Profiler()->start('connect');
        
        if($this->_bPersistent)
        {
            $this->_sHost = 'p:' . $this->_sHost;
        }
		
		if(!$this->_hHandle = mysqli_connect($this->_sHost, $this->_sUser, $this->_sPass, $this->_sDatabase, $this->_sPort))
		{
			exit('Error Connecting to database: ' . mysqli_connect_error());
		}

		if($this->_sCharset)
		{
			$this->setCharset($this->_sCharset);
		}

		Linko::Profiler()->stop('connect', array(
                'driver' => $this->getDriver(),
				'database' => $this->_sDatabase
			)
		);
		
		return $this->_hHandle;
	}
	
	public function execute($mCommand)
	{		
		if(!$this->_hQuery = mysqli_query($this->_hHandle, $mCommand))
		{
			exit(Linko::Error()->trigger($this->sqlError()));
		}
        
		return $this->_hQuery;
	}

	public function setCharset($sCharset)
	{
		$this->_sCharset = $sCharset;

		mysqli_set_charset($this->_hHandle, $sCharset);

		return $this;
	}

    public function quoteColumn($mField, $sWrapper = "`%s`")
    {
        return parent::quoteColumn($mField, $sWrapper);   
    }

	public function quoteTable($sTable)
	{
		return $this->quoteColumn($this->_sDatabase) . '.' . $this->quoteColumn($sTable);
	}

	public function fetchValue($sCol = null)
	{
		$aRow = $this->fetchRow();
		
		if(!$sCol)
		{
			if(count($aRow) == 1)
			{
				$sCol = key($aRow);
			}
		}
		
		return isset($aRow[$sCol]) ? $aRow[$sCol] : null;		
	}
		
	public function fetchRow()
	{
		$aRow = mysqli_fetch_array($this->_hQuery, MYSQLI_ASSOC);
		
        return $aRow ? $aRow : array();			
	}
	
	public function fetchRows()
	{
		$aRows = array();
		
		while($aRow = mysqli_fetch_array($this->_hQuery, MYSQLI_ASSOC))
		{
			$aRows[] = $aRow;
		}
		
		return $aRows;			
	}
	
	public function fetchObject()
	{
		$oRow = mysqli_fetch_object($this->_hQuery, 'stdClass');
		
		return $oRow ? $oRow : new stdClass;		
	}
	
	public function fetchObjects()
	{
		$aRows = array();
		
		while($oRow = mysqli_fetch_object($this->_hQuery, 'stdClass'))
		{
			$aRows[] = $oRow;	
		}
		
		return $aRows;
	}
	
	public function getCount()
	{
		return mysqli_num_rows($this->_hQuery);
	}

    public function getAffectedRows()
	{
		return mysqli_affected_rows($this->_hHandle);
	}
	
	public function getInsertId()
	{
		return mysqli_insert_id($this->_hHandle);
	}
	
	public function close()
	{
		if(is_resource($this->_hHandle))
		{
			mysqli_close($this->_hHandle);
		}
	}

	public function escape($mValue)
	{
		$sValue = mysqli_real_escape_string($this->_hHandle, $mValue);
        
        return "'" . $sValue . "'";
	}
    
	public function sqlError()
	{
		return mysqli_error($this->_hHandle);
	}
	
	public function getDriver()
	{
		return 'MySQL (with Mysqli Extension)';
	}
	
	public function getVersion()
	{
		return mysqli_get_server_info($this->_hHandle);	
	}
	
	public function __destruct()
	{
		$this->close();
        
		$this->_aQueries = array();
		
		$this->_iTotalQueries = 0;
        
		$this->_hQuery = null;
	}
}

?>