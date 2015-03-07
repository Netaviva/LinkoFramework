<?php

/*
	[LINKO_HEADER]
*/

class Linko_Database_Driver_Sqlite extends Linko_Database_Abstract
{
	protected $_sHost;
	
	protected $_sUser;
	
	protected $_sPass;
	
	protected $_sPort = null;
	
	protected $_sDatabase;
	
	protected $_sPrefix;
	
	private $_oSqlite;
	
	protected $_bPersistent = false;
	
	public function __construct($aParams)
	{		
		$this->_sDatabase = $aParams['database'];
		
		$this->_sPrefix = isset($aParams['prefix']) ? $aParams['prefix'] : null;
		
		$this->_bPersistent = isset($aParams['persistent']) ? true : false;
		
		$this->connect();
        
        parent::__construct($aParams);
	}
	
	public function getDsn()
	{
		return ;	
	}
	
	public function connect()
	{
		Linko::Profiler()->start('connect');
		
		if(!$this->_hConnection = ($this->_bPersistent) ? sqlite_popen($this->_sDatabase, 0666, $sError) : sqlite_open($this->_sDatabase, 0666, $sError))
		{
			exit('Error Connecting to database: ' . $this->sqlError());
		}
		
		Linko::Profiler()->stop('connect', array(
				'database' => $this->_sDatabase
			)
		);
		
		return $this->_hConnection;
	}
	
	public function execute($sSql)
	{		
		$this->_aQueries[] = $sSql;
		
		if(!$this->_hQuery = sqlite_query($this->_hConnection, $sSql))
		{
			exit(Linko::Error()->trigger($this->sqlError()));
		}
		
		return $this->_hQuery;
	}

	public function setCharset($sCharset)
	{
		return $this;
	}
	
	public function fetchObject()
	{
		$oRow = sqlite_fetch_object($this->_hQuery, 'stdClass');
		
		return $oRow ? $oRow : new stdClass;	
	}
	
	public function fetchObjects()
	{
		$oRows = new stdClass;
		
		while($oRow = sqlite_fetch_object($this->_hQuery, 'stdClass'))
		{
			$oRows[] = $oRow;
		}
		
		return $oRows;		
	}

	public function fetchColumn($sCol = null)
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
		$aRow = sqlite_fetch_array($this->_hQuery, SQLITE_ASSOC);
		
		return $aRow ? $aRow : array();
	}
	
	public function fetchRows()
	{
		$aRows = array();
		
		while($mRow = sqlite_fetch_array($this->_hQuery, SQLITE_ASSOC))
		{
			$aRows[] = $mRow;
		}
		
		return $aRows;
	}
	
	public function getCount()
	{
		return sqlite_num_rows($this->_hQuery);
	}

    public function getAffectedRows()
	{
		return sqlite_changes($this->_hConnection);
	}
	
	public function getInsertId()
	{
		return sqlite_last_insert_rowid($this->_hConnection);
	}
	
	public function close()
	{
		if(is_resource($this->_hConnection))
		{
			sqlite_close($this->_hConnection);
		}
	}

    public function escape($mParam)
    {
        if (is_array($mParam))
        {
            return array_map(array(&$this, 'escape'), $mParam);
		}

        if (get_magic_quotes_gpc())
        {
            $mParam = stripslashes($mParam);
        }

        $mParam = sqlite_escape_string($mParam);

        return $mParam;
    }
	
	public function sqlError()
	{
		return @sqlite_error_string(@sqlite_last_error($this->_hConnection));
	}
	
	public function getDriver()
	{
		return 'SQLite';
	}
	
	public function getVersion()
	{
		return @sqlite_libversion();	
	}

	public function getTableStatus($sTable = null)
	{
		if($sTable)
		{
			return $this->query("SHOW TABLE STATUS FROM " . $this->_sDatabase . " LIKE '" . $sTable . "%'")->getRows();
		}
		
		return $this->query('SHOW TABLE STATUS')->getRows();
	}

	public function tableExists($sTable)
	{
		$aTables = $this->getTableStatus($sTable);
		
		foreach($aTables as $aTable)
		{
			if($aTable['Name'] == $sTable)
			{
				return true;	
			}
		}
		
		return false;
	}

	public function addIndex($sTable, $sField)
	{
		$sSql = 'ALTER TABLE ' . $sTable . ' ADD INDEX (' . $sField . ')';
		
		return $this->query($sSql);
	}

	public function isIndex($sTable, $sField)
	{
		$aRows = $this->query("SHOW INDEX FROM " . $sTable . "")->getRows();
		
		foreach ($aRows as $aRow)
		{
			if (strtolower($aRow['Key_name']) == strtolower($sField))
			{
				return true;
			}
		}

		return false;
	}
		
	public function isNull($sField)
	{
		return '' . $sField . ' IS NULL';
	}
	
	public function isNotNull($sField)
	{
		return '' . $sField . ' IS NOT NULL';
	}
	
	public function addField($sTable, $aParams)
	{
		$sSql = 'ALTER TABLE ' . $sTable . ' ADD ' . $aParams['field'] . ' ' . $aParams['type'] . '';
		if (isset($aParams['attribute']))
		{
			$sSql .= ' ' . $aParams['attribute'] . ' ';
		}		
		if (isset($aParams['null']))
		{
			$sSql .= ' ' . ($aParams['null'] ? 'NULL' : 'NOT NULL') . ' ';
		}		
		if (isset($aParams['default']))
		{
			$sSql .= ' ' . $aParams['default'] . ' ';
		}
		if(isset($aParams['after']))
		{
			$sSql .= ' AFTER ' . $aParams['after'] . ' ';
		}
		
		return $this->query($sSql);
	}
	
	public function isField($sTable, $sField)
	{
		$aRows = $this->query("SHOW COLUMNS FROM " . $sTable . "")->getRows();
		foreach ($aRows as $aRow)
		{
			if (strtolower($aRow['Field']) == strtolower($sField))
			{
				return true;
			}
		}

		return false;
	}

	public function optimizeTable($sTable)
	{
		return $this->query('OPTIMIZE TABLE ' . $this->escape($sTable));
	}
	
	public function repairTable($sTable)
	{
		return $this->query('REPAIR TABLE ' . $this->escape($sTable));
	}
	
	public function getSchema()
	{
		
	}
	
	public function getTableSchema($sTable)
	{
		return $this->query('SHOW columns FROM ' . $sTable)->getRows();
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