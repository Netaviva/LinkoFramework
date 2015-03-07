<?php

class Linko_Database_Query_Builder
{
	public $connection;
	
	private $_aSql;
	
	private $_aQueryHistory = array();
	
	private $_sTable;
	
	private $_sAlias;
	
	private $_bInsert = false;
	
	private $_bSelect = false;
	
	private $_bUpdate = false;
	
	private $_bDelete = false;
	
	private $_aField = array();
	
	private $_aSelect = array();
	
	private $_aWhere = array();
	
	private $_aJoin = array();
	
	private $_aGroup = array();
	
	private $_aOrder = array();
	
	private $_aHaving = array();
	
	private $_iLimit;
	
	private $_iOffset;

	public function __construct($aParams = array())
	{
		$this->connection = $aParams['connection'];	
	}
	
	public function field(array $aField = array(), $bEscape = true)
	{
		foreach($aField as $sField => $sValue)
		{
			if(strtolower($sValue) == 'now()')
			{
				$sValue = 'NOW()';
			}
			else if(strtolower($sValue) == 'null')
			{
				$sValue = 'NULL';
			}
			else
			{
				$sValue = '\'' . ($bEscape ? $this->connection->escape($sValue) : $sValue) . '\'';	
			}
			
			$this->_aField[$sField] = $sValue;
		}
	}
	
	public function select($sSelect = null)
	{
		if(is_null($sSelect))
		{
			$sSelect = '*';
		}
		
		$this->_bSelect = true;
		
		$this->_aSelect[] = $sSelect;
	}
	
	public function insert($mTable = null, $aField = array(), $bEscape = true)
	{
		$this->_bInsert = true;
		
		if(is_string($mTable))
		{
			$this->table($mTable);	
		}
		else if(is_array($mTable))
		{
			$aField = $mTable;	
		}
		
		if(count($aField))
		{
			$this->field($aField, $bEscape);	
		}	
	}
			
	public function update($mTable = null, $aUpdate = array(), $mCondition = null, $bEscape = true)
	{
		$this->_bUpdate = true;
		
		if(is_string($mTable))
		{
			$this->table($mTable);	
		}
		
		if(is_array($mTable))
		{
			$aUpdate = $mTable;
		}
		
		if(is_array($aUpdate))
		{
			$this->field($aUpdate, $bEscape);
		}
		
		if($mCondition != null)
		{
			$this->where($mCondition);
		}
	}

	public function delete($sTable = null, $mCondition = null)
	{
		$this->_bDelete = true;
		
		if(is_string($sTable))
		{
			$this->table($sTable);	
		}
		
		if($mCondition != null)
		{
			$this->where($mCondition);
		}
		
		return $this;
	}
	
	// Just added it to look more sql like (SELECT * FROM)
	public function from($sTable, $sAlias = null)
	{
		$this->table($sTable, $sAlias);
	}
	
	public function table($sTable, $sAlias = null)
	{
		$this->_sTable = $this->connection->prefix($sTable);
		
		$this->_sAlias = $sAlias;
		
		return $this;
	}

	public function order($sOrder)
	{
		if ($sOrder)
		{		
			$this->_aOrder[] = $sOrder;
		}
		
		return $this;
	}
	
	public function group($sGroup)
	{
		$this->_aGroup[] = $sGroup;
	}
	
	public function having($sHaving)
	{
		$this->_aHaving[] = $sHaving;
	}
	
	public function leftJoin($sTable, $sAlias, $mParam = null)
	{
		$this->_join('LEFT JOIN', $sTable, $sAlias, $mParam);
	}
	
	public function innerJoin($sTable, $sAlias, $mParam = null)
	{
		$this->_join('INNER JOIN', $sTable, $sAlias, $mParam);		
	}
	
	public function join($sTable, $sAlias, $mParam = null)
	{
		$this->_join('JOIN', $sTable, $sAlias, $mParam);
	}

	public function limit5($iPage, $sLimit = null, $iCnt = null)
	{
		if ($sLimit === null && $iCnt === null && $iPage !== null)
		{
			$this->_iLimit = $iPage;	
			
			return;
		}
		
		$this->_iLimit = $sLimit;
		
		$this->_iOffset = ($iCnt === null ? $iPage : ($sLimit * (max(1, min(ceil($iCnt / $sLimit), $iPage)) - 1)));
	}
    
    public function offset($iOffset)
    {
        $this->_iOffset = $iOffset;
        
        return $this;
    }

    public function limit($iLimit)
    {
        $this->_iLimit = $iLimit;
        
        return $this;
    }
    
    public function filter($iPage, $iLimit, $iCount)
    {
        $this->limit($iLimit);
        
        $this->offset($iCount === null ? $iPage : ($iLimit * (max(1, min(ceil($iCount / $iLimit), $iPage)) - 1)));
        
        return $this;
    }
        
	public function where($mConds)
	{
		$sWhere = '';
		
		if (is_array($mConds) && count($mConds))
		{
			foreach ($mConds as $sValue)
			{
				$sWhere .= $sValue . ' ';
			}
		}
		else 
		{
			if (!empty($mConds))
			{
				$sWhere .= $mConds;	
			}
		}
		
		$this->_aWhere[] = trim(preg_replace("/^(AND|OR)(.*?)/i", "", trim($sWhere)));
	}
    
	// @todo
	public function or_where($sField, $sOperator, $sValue)
	{
		$this->_where($sField, $sOperator, $sValue, 'OR');	
	}
	
	protected function _where($sField, $sOperator, $sValue, $sConnector = 'AND')
	{
		
	}
		
	protected function _join($sType, $sTable, $sAlias, $mParam = null)
	{
		$sJoin = $sType . " " . $this->connection->prefix($sTable) . " AS " . $sAlias;
		
		if (is_array($mParam))
		{
			$sJoin .= " ON(";
			
			foreach ($mParam as $sValue)
			{
				$sJoin .= $sValue . " ";
			}
		}
		else 
		{
			if (preg_match("/(AND|OR|=)/", $mParam))
			{
				$sJoin .= " ON(" . $mParam . "";
			}
			else 
			{

			}
		}
		
		$this->_aJoin[] = preg_replace("/^(AND|OR)(.*?)/i", "", trim($sJoin)) . ")\n";
	}
	
	public function build()
	{
		$sSql = '';
		
		if ($this->_bSelect)
		{
			// build select query
			
			$sSql .= "SELECT " . implode(' ', array_filter($this->_aSelect, function($sStr){ return (string) $sStr !== null; })) . "\n";
			
			$sSql .= "FROM " . ($this->_sTable) . ($this->_sAlias ? ' AS ' . $this->_sAlias : '') . "\n";
			
			$sSql .= (count($this->_aJoin) ? implode(' ', $this->_aJoin) . "\n" : '');
			
			$sSql .= (count($this->_aWhere) ? "WHERE " . $this->_buildWhere() . "\n" : '');
			
			$sSql .= (count($this->_aGroup) ? "GROUP BY " . implode(' ', $this->_aGroup) . "\n" : '');
			
			$sSql .= (count($this->_aHaving) ? "HAVING " . implode(' ', $this->_aHaving) . "\n" : '');
			
			$sSql .= (count($this->_aOrder) ? "ORDER BY " . implode(' ', $this->_aOrder) . "\n" : '');
			
			$sSql .= ($this->_iLimit ? "LIMIT " . $this->_iLimit . " " : " ");
			
			$sSql .= ($this->_iOffset ? "OFFSET " . $this->_iOffset . " " : " ");
		}
		else if($this->_bInsert)
		{
			// build insert query
			
			$sSql .= "INSERT INTO " . $this->_sTable . "\n";
			
			$sSql .= "(" . implode(', ', array_keys($this->_aField)) . ") \n";
			
			$sSql .= "VALUES";
			
			$sSql .= "(" . implode(', ', array_values($this->_aField)) . ") \n";	
		}
		else if ($this->_bUpdate)
		{
			// build update query
			
			$sSql .= "UPDATE " . $this->_sTable . " SET \n";
			
			$aSets = array();
			if(count($this->_aField))
			{
				foreach($this->_aField as $sField => $sValue)
				{
					$aSets[] = "" . $sField . " = " . $sValue . "";	
				}
			}
			
			$sSql .= implode(", ", $aSets) . "\n";
			
			$sSql .= " " . (count($this->_aWhere) ? "WHERE " . $this->_buildWhere() . "\n" : '');
		}		
		else if ($this->_bDelete)
		{
			// build delete query
			
			$sSql .= "DELETE FROM " . $this->_sTable . "\n";
			
			$sSql .= " " . (count($this->_aWhere) ? "WHERE " . $this->_buildWhere() . "\n" : Linko::Error()->trigger('Trying to delete without condition', E_USER_WARNING));
		}
				
		$this->reset();
		
		return $sSql;		
	}
	
	public function rebuild()
	{
		foreach($this->_aQueryHistory as $sProperty => $sValue)
		{
			$this->$sProperty = $sValue;
		}
		
		$this->_aQueryHistory = array();
		
		return $this;
	}
	
	/*
		After Querying, Reset and prepare 
		the properties for a new sql
	*/
	public function reset()
	{
		// store the query in the query history before reseting
		$this->_aQueryHistory = array(
			'_aSql' => $this->_aSql,
			'_aField' => $this->_aField,
			'_aSelect' => $this->_aSelect,
			'_aWhere' => $this->_aWhere,
			'_aJoin' => $this->_aJoin,
			'_aGroup' => $this->_aGroup,
			'_aOrder' => $this->_aOrder,
			'_aHaving' => $this->_aHaving,
			'_iLimit' => $this->_iLimit,
			'_iOffset' => $this->_iOffset,
			
			'_bInsert' => $this->_bInsert,
			'_bSelect' => $this->_bSelect,
			'_bUpdate' => $this->_bUpdate,
			'_bDelete' => $this->_bDelete,
			'_sTable' => $this->_sTable,
		);
		
		$this->_aSql = array();
		$this->_aField = array();
		$this->_aSelect = array();
		$this->_aWhere = array();
		$this->_aJoin = array();
		$this->_aGroup = array();
		$this->_aOrder = array();
		$this->_aHaving = array();
		$this->_iLimit = null;
		$this->_iOffset = null;
		
		$this->_bInsert = false;
		$this->_bSelect = false;
		$this->_bUpdate = false;
		$this->_bDelete = false;
		$this->_sTable = null;
	}
	
	private function _buildWhere()
	{
		return (count($this->_aWhere) ? implode(' ', $this->_aWhere) : '');	
	}
}

?>