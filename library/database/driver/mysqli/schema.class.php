<?php

class Linko_Database_Driver_Mysqli_Schema
{
    private $_aTableStatus = array();

    public function __construct($aParams = array())
	{
        $this->connection = $aParams['connection'];
	}

	public function createTable($sTable, array $aFields = array(), $bIfNotExists = false)
	{
		$sSql = ($bIfNotExists ? 'CREATE TABLE IF NOT EXISTS ' : 'CREATE TABLE ') . $this->connection->quoteTable($sTable) . "\n";
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
				$sSql .= " DEFAULT " . $this->connection->quote($aAttr['default']);
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
				$sSql .= ", \n KEY " . $sField . " (" . $sField . ")";
			}
		}
						
		$sSql .= "\n) ";

		$sCharset = $this->connection->getCharset();

		if(strpos($sCharset, '_') === false)
		{
			$sCollate = $sCharset . '_bin';
		}
		else
		{
			$sCollate = $sCharset;
			$sCharset = substr($sCollate, 0, strpos($sCollate, '_'));
		}

		$sSql .= "DEFAULT CHARSET=" . $sCharset . " COLLATE=" . $sCollate . ";";

		return $sSql;			
	}
    
	public function dropTable($sTable, $bIfExists = true)
	{
		$sSql = "DROP TABLE IF EXISTS " . $sTable . "\n";
		
		return $sSql;	
	}

    public function dropColumn($sTable, $sColumn)
    {

    }

	/**
	 * @param $sTable
	 * @param $sColumn
	 * @param $aParam
	 * @param $sNewName
	 *
	 * @return string
	 */
	public function alterColumn($sTable, $sColumn, $aParam, $sNewName = null)
    {
	    $aColumn = $this->getColumn($sTable, $sColumn);
	    list($aColumn['Type'], $aColumn['Unsigned']) = array_pad(explode(' ', $aColumn['Type']), 2, false);

	    if(!$sNewName)
	    {
		    $sNewName = $sColumn;
	    }

	    $aParam['auto_increment'] = !isset($aParam['auto_increment']) ? ($aColumn['Extra'] == 'auto_increment' ? true : false) : $aParam['auto_increment'];
	    $aParam['default'] = !isset($aParam['default']) ? ($aColumn['Default'] ? $aColumn['Default'] : false) : $aParam['default'];
	    $aParam['type'] = !isset($aParam['type']) ? $aColumn['Type'] : $aParam['type'];
	    $aParam['null'] = !isset($aParam['null']) ? ($aColumn['Null'] == 'Yes' ? true : false) : $aParam['null'];
	    $aParam['collation'] = !isset($aParam['collation']) ? ($aColumn['Collation'] ? $aColumn['Collation'] : "utf8_bin" /** @todo update this */) : $aParam['collation'];
	    $aParam['unsigned'] = !isset($aParam['unsigned']) ? ($aColumn['Unsigned'] ? true : false) : $aParam['unsigned'];

	    $aSql = array();

	    $aSql[] = "ALTER TABLE " . $this->connection->quoteTable($sTable) . " CHANGE " . $this->connection->quoteColumn($sColumn) . " " . $this->connection->quoteColumn($sNewName);

	    $aSql[] = $aParam['type'];

	    if($aParam['unsigned'])
	    {
		    $aSql[] = "UNSIGNED";
	    }

	    if(!preg_match('#int#', $aParam['type']))
	    {
		    $aSql[] = "CHARACTER SET " . substr($aParam['collation'], 0, strpos($aParam['collation'], '_')) . " COLLATE " . $aParam['collation'];
	    }

	    if($aParam['null'])
	    {
			$aSql[] = "NULL";
	    }
	    else
	    {
			$aSql[] = "NOT NULL";
	    }

	    if(array_key_exists('auto_increment', $aParam) && ($aParam['auto_increment'] === true))
	    {
		    $aSql[] = "AUTO_INCREMENT";
	    }
	    elseif($aParam['default'])
	    {
		    $aSql[] = "DEFAULT " . $this->connection->quote($aParam['default']);
	    }

	    $sSql = implode(" ", $aSql);

	    return $sSql;
    }

	public function addColumn($sTable, $sColumn, $aParam)
	{
		$aSql = array();

		$aSql[] = "ALTER TABLE " . $this->connection->quoteTable($sTable) . " ADD " . $this->connection->quoteColumn($sColumn);

		if(array_key_exists('type', $aParam))
		{
			$aSql[] = $aParam['type'];
		}

		if(!preg_match('#int#', $aParam['type']))
		{
			$sCollation = isset($aParam['collation']) ? $aParam['collation'] : $this->connection->getCollation();

			$aSql[] = "CHARACTER SET " . substr($sCollation, 0, strpos($sCollation, '_')) . " COLLATE " . $sCollation;
		}

		if(array_key_exists('null', $aParam) && ($aParam['null'] === true))
		{
			$aSql[] = 'NULL';
		}
		else
		{
			$aSql[] = 'NOT NULL';
		}

		$sSql = implode(" ", $aSql);

		return $sSql;
	}

	public function renameColumn($sTable, $sColumn, $sNewName)
	{
		return $this->alterColumn($sTable, $sColumn, array(), $sNewName);
	}

	public function addIndex($sTable, $sColumn)
	{

	}

	public function getTableStatus()
	{
        // try to store this query so other request like tableExists() won't make extra queries if called again.
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

	public function getColumns($sTable)
	{
		$aRows = $this->connection->query("SHOW FULL COLUMNS FROM " . $this->connection->quoteTable($sTable))->fetchRows();

		$aColumns = array();

		foreach($aRows as $aRow)
		{
			$aColumns[$aRow['Field']] = $aRow;
		}

		return $aColumns;
	}

	public function getColumn($sTable, $sColumn)
	{
		$aColumns = $this->getColumns($sTable);

		return isset($aColumns[$sColumn]) ? $aColumns[$sColumn] : array();
	}

	public function getIndexes($sTable)
	{

	}

	public function getColumnCharacterSet($sTable, $sColumn)
	{
		$aRow = $this->connection->query("SELECT character_set_name, collation_name FROM information_schema.`COLUMNS` C WHERE table_name = '" . $sTable . "' AND column_name = '" . $sColumn . "'")->fetchRow();
	}
}

?>