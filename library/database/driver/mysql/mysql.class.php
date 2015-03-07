<?php

/*
	[LINKO_HEADER]
*/

class Linko_Database_Driver_Mysql extends Linko_Database_Abstract
{
	protected $_sHost;
	
	protected $_sUser;
	
	protected $_sPass;
	
	protected $_sPort = null;
	
	protected $_sDatabase;
	
	protected $_sPrefix;
	
	protected $_bPersistent = false;
    
    protected $_sIdentifier = '`%s`';

    protected $_sSchema = 'Linko_Database_Driver_Mysqli_Schema';

    protected $_sExport = 'Linko_Database_Driver_Mysqli_Export';

    protected $_sBuilder = 'Sql';
	
	public function __construct($aParams)
	{		
		$this->_sHost = $aParams['host'];
		
		$this->_sUser = $aParams['username'];
		
		$this->_sPass = $aParams['password'];
		
		$this->_sDatabase = $aParams['database'];
		
		$this->_sPort = isset($aParams['port']) ? $aParams['port'] : null;
		
		$this->_sPrefix = isset($aParams['prefix']) ? $aParams['prefix'] : null;

		$this->_sCharset = isset($aParams['charset']) ? $aParams['charset'] : null;
		
		$this->_bPersistent = isset($aParams['persistent']) ? true : false;
		
		$this->connect();
		
		parent::__construct($aParams);
	}
	
	public function connect()
	{
		if($this->_sPort)
		{
			$this->_sHost = $this->_sHost . ':' . $this->_sPort;	
		}
		
		Linko::Profiler()->start('connect');
		
		if(!$this->_hHandle = ($this->_bPersistent) ? mysql_pconnect($this->_sHost, $this->_sUser, $this->_sPass) : mysql_connect($this->_sHost, $this->_sUser, $this->_sPass, true))
		{
			exit('Error Connecting to database: ' . $this->dbError());
		}
		
		if(!mysql_select_db($this->_sDatabase, $this->_hHandle))
		{
			Linko::Error()->trigger('Error Selecting Database: ' . $this->dbError());
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
	
	public function execute($sSql)
	{		
		if(!$this->_hQuery = mysql_query($sSql, $this->_hHandle))
		{
			exit(Linko::Error()->trigger($this->dbError()));
		}
		
		return $this->_hQuery;
	}

	public function setCharset($sCharset)
	{
		$this->_sCharset = $sCharset;

		mysql_set_charset($sCharset, $this->_hHandle);

		return $this;
	}

    // @Override
    public function quoteColumn($mField, $sWrapper = "`%s`")
    {
        return parent::quoteColumn($mField, $sWrapper);   
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
		$aRow = mysql_fetch_array($this->_hQuery, MYSQL_ASSOC);
		
		return $aRow ? $aRow : array();			
	}
	
	public function fetchRows()
	{
		$aRows = array();
		
		while($aRow = mysql_fetch_array($this->_hQuery, MYSQL_ASSOC))
		{
			$aRows[] = $aRow;
		}
		
		return $aRows;			
	}
	
	public function fetchObject()
	{
		$oRow = mysql_fetch_object($this->_hQuery, 'stdClass');
		
		return $oRow ? $oRow : new stdClass;		
	}
	
	public function fetchObjects()
	{
		$aRows = array();
		
		while($oRow = mysql_fetch_object($this->_hQuery, 'stdClass'))
		{
			$aRows[] = $oRow;	
		}
		
		return $aRows;
	}
	
	public function getCount()
	{
		return mysql_num_rows($this->_hQuery);
	}

    public function getAffectedRows()
	{
		return mysql_affected_rows($this->_hHandle);
	}
	
	public function getInsertId()
	{
		return mysql_insert_id($this->_hHandle);
	}
	
	public function close()
	{
		if(is_resource($this->_hHandle))
		{
			mysql_close($this->_hHandle);
		}
	}

	public function escape($mValue)
	{
		$sValue = mysql_real_escape_string($mValue, $this->_hHandle);
        
        return "'" . $sValue . "'";
	}
    
	public function dbError()
	{
		return mysql_error($this->_hHandle);
	}
	
	public function getDriver()
	{
		return 'MySQL';
	}
	
	public function getVersion()
	{
		return mysql_get_server_info($this->_hHandle);	
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