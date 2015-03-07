<?php

class Linko_Database_Driver_Sqlite_Schema
{
    private $_aTableStatus = array();
    
    public function __construct($aParams = array())
	{
        $this->connection = $aParams['connection'];
	}
    
	public function createTable($sTable, array $aFields = array(), $bIfNotExists = false)
	{
		$sSql = ($bIfNotExists ? 'CREATE TABLE IF NOT EXISTS ' : 'CREATE TABLE ') . $sTable . "\n";
		$sSql .= '(' ."\n";
		$aKeys = array();
		$iCnt = 0;
		
		$aFields = array_change_key_case($aFields, CASE_LOWER);
		
		foreach ($aFields as $sField => $aAttr)
		{
			$iCnt++;
			
            $sField = $this->connection->quoteColumn($sField);
            		
			$sSql .= $sField;

			if(array_key_exists('type', $aAttr))
			{
				$sSql .= " " . $aAttr['type'];
			}
			
			if(array_key_exists('unsigned', $aAttr) && ($aAttr['unsigned'] === true))
			{
				$sSql .= " UNSIGNED";
			}

			if(array_key_exists('auto_increment', $aAttr) && ($aAttr['auto_increment'] === true))
			{
				$sSql .= " AUTO_INCREMENT";
			}
			
			if(array_key_exists('default', $aAttr))
			{
				$sSql .= " DEFAULT '" . $aAttr['default'] . "'";
			}
											
			if(isset($aAttr['primary_key']) && ($aAttr['primary_key'] == true))
			{
				$aKeys['primary_key'] = $sField;
			}

			if(isset($aAttr['unique_key']) && ($aAttr['unique_key'] == true))
			{
				$aKeys['unique_key'][] = $sField;
			}

			if(isset($aAttr['key']) && ($aAttr['key'] == true))
			{
				$aKeys['key'][] = $sField;
			}
									
			if(array_key_exists('null', $aAttr) && ($aAttr['null'] === true))
			{
				$sSql .= ' NULL';
			}
			else
			{
				$sSql .= ' NOT NULL';
			}
			
			if($iCnt < count($aFields))
			{
				$sSql .= ', ' . "\n";	
			}
		}
		
		if (isset($aKeys['primary_key']))
		{
			$sSql .= ", \n PRIMARY KEY (" . $aKeys['primary_key'] . ")";
		}

		if (isset($aKeys['unique_key']))
		{

		}

		if (isset($aKeys['key']))
		{
			foreach($aKeys['key'] as $sField)
			{
				$sSql .= ", \n KEY " . $sField . " (`" . $sField . "`)";	
			}
		}
						
		$sSql .= "\n)";
		
		return $sSql;			
	}
    
	public function dropTable($sTable, $bIfExists = true)
	{
		$sSql = "DROP TABLE IF EXISTS " . $sTable . "\n";
		
		return $sSql;	
	}

	public function getTableStatus()
	{
        // try to store this query so other request like tableExists() won't make extra queries.
        if($this->_aTableStatus)
        {
            return $this->_aTableStatus;
        }
        
		$this->_aTableStatus =  $this->connection->query('SHOW TABLE STATUS')->fetchRows();
        
        return $this->_aTableStatus;
	}
       
	public function tableExists($sTable)
	{
		$aTables = $this->getTableStatus();
		
		foreach($aTables as $aTable)
		{
			if($aTable['Name'] == $sTable)
			{
				return true;
			}
		}
		
		return false;	
	}
}

?>