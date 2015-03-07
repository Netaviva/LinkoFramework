<?php

class Linko_Database_Query_Util
{
	public $connection;

	public function __construct($aParams = array())
	{
		$this->connection = $aParams['connection'];
	}
	
	public function createTable($sTable, array $aFields = array(), $bIfNotExists = false)
	{
		$sSql = ($bIfNotExists ? 'CREATE TABLE IF NOT EXISTS ' : 'CREATE TABLE ') . $this->_table($sTable) . "\n";
		$sSql .= '(' ."\n";
		$aKeys = array();
		$iCnt = 0;
		
		$aFields = array_change_key_case($aFields, CASE_LOWER);
		
		foreach ($aFields as $sField => $aAttr)
		{
			$iCnt++;
					
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
			$sSql .= ", \n PRIMARY KEY (`" . $aKeys['primary_key'] . "`)";
		}

		if (isset($aKeys['unique_key']))
		{

		}

		if (isset($aKeys['key']))
		{
			foreach($aKeys['key'] as $sField)
			{
				$sSql .= ", \n KEY `" . $sField . "` (`" . $sField . "`)";	
			}
		}
						
		$sSql .= "\n)";
		
		$this->connection->query($sSql);
		
		return $this->connection;			
	}
	
	public function dropTables($mTables)
	{
		$sSql = "DROP TABLE IF EXISTS " . $this->_table($mTables) . "\n";
		
		$this->connection->query($sSql);
		
		return $this->connection;	
	}
	
	private function _table($mTable)
	{
		if(!is_array($mTable))
		{
			$mTable = array($mTable);	
		}
		
		foreach($mTable as $iKey => $sTable)
		{
			$mTable[$iKey] = Linko::Database()->prefix($sTable);	
		}
		
		return "`" . implode('`, `', $mTable) . "`";
	}
}

?>