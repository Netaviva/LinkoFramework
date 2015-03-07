<?php

class Linko_Database_Sql_Query_Builder implements  Linko_Query_Builder
{
	/**
	 * @var Linko_Database_Abstract
	 */
	public $connection;
	
	private $_aSql;
	
	private $_aQueryHistory = array();
	
	private $_sTable;
	
	private $_sAlias;
	
	private $_bInsert = false;

	private $_bMultiInsert = false;
	
	private $_bSelect = false;
	
	private $_bUpdate = false;
	
	private $_bDelete = false;
	
	private $_aField = array();

	private $_aInsert = array();

	private $_aUpdate = array();
	
	private $_aSelect = array();
	
	private $_aWhere = array();
	
	private $_aJoin = array();
	
	private $_aGroup = array();
	
	private $_aOrder = array();
	
	private $_aHaving = array();
	
	private $_iLimit;
	
	private $_iOffset;

	/**
	 * @var Linko_Database_Driver_Mysqli_Schema
	 */
	private $_oSchema;

	public function __construct($aParams = array())
	{        
        $this->_sHash = null;
        
        $this->connection = $aParams['connection'];

        $this->_sExport = $aParams['export'];

		$this->_oSchema = Linko_Object::get($aParams['schema'], array('connection' => $this->connection));
	}
    
	public function table($sTable, $sAlias = null)
	{
		$this->_sTable = $this->connection->prefix($sTable);
		
		$this->_sAlias = $sAlias ? $this->connection->quoteColumn($sAlias) : null;

		return $this;
	}
    
	public function field(array $aDatas = array(), $bEscape = true)
	{
		$aData = array();

		foreach($aDatas as $sField => $sValue)
		{
			if(strtolower($sValue) == 'now()')
			{
				$sValue = 'NOW()';
			}
			else if(strtolower($sValue) == 'null')
			{
				$sValue = 'NULL';
			}
			else if(preg_match('/^inc\((\-?\d+)\)$/i', ($sValue), $aMatch))
			{
                $sValue = $this->connection->quoteColumn($sField) . ' + ' . $aMatch[1];
			}
			else
			{
				$sValue = ($bEscape ? $this->connection->escape($sValue) : $sValue);
			}
			
			$aData[$this->connection->quoteColumn($sField)] = $sValue;
		}
        
        return $aData;
	}
	
	public function select()
	{
        $this->_bSelect = true;
        
        $aArgs = func_get_args();
        
        $iArgs = func_num_args();
        
        $aSelect = array();
        
        if($iArgs == 0)
        {
            $aArgs = array('*');
        }
        
        foreach($aArgs as $iKey => $mArg)
        {
            if(is_string($mArg) && strpos($mArg, ','))
            {
                unset($aSelect[$iKey]);
                
                $aSelect = array_merge($aSelect, explode(',', $mArg));
            }
            else
            {
                $aSelect[] = $mArg;
            }
        }

        $oConn = $this->connection;
        
        $sSelect = implode(', ', array_map(function($mSelect) use ($oConn)
        {
            if(is_string($mSelect) && strpos($mSelect, 'AS'))
            {
                $mSelect = explode('AS', $mSelect);
            }
            
            if(is_array($mSelect))
            {
                list($mSelect, $sAlias) = $mSelect;
            }

            if(is_object($mSelect) && $mSelect instanceof Linko_Database_Abstract)
            {
                $mSelect = '(' . $mSelect->getQuery() . ')';
            }
                       
            $mSelect = trim($mSelect);
            
            $mSelect = $oConn->quoteColumn(trim($mSelect));
            
            if(isset($sAlias))
            {
                $mSelect .= ' AS ' . $oConn->quoteColumn($sAlias);
            }
                    
            return $mSelect;
            
        }, $aSelect));
        
		$this->_aSelect[] = $sSelect;
        
        return $this;
	}

	/**
	 * Operation: insert
	 *
	 * For single insert
	 * Linko::Database()->table('table_name')
	 *  ->insert(array(
	 *      'column' => 'value'
	 *  ))
	 *
	 * For multi insert
	 *
	 * Linko::Database()->table('table_name')
	 *  ->insert(
	 *      array('column' => 'value'),
	 *      array('column' => 'value)
	 *  ))
	 *
	 * or
	 *
	 * Linko::Database()->table('table_name')
	 *  ->insert(array('column1', 'column2'),
	 *      array(
	 *		    array('value1', 'value2'),
	 *          array('value1', 'value2')
	 *      ),
	 *  ))
	 *
	 * @param array $aDatas field/value
	 * @param array $aValues
	 * @param bool  $bEscape
	 *
	 * @return \Linko_Database_Sql_Query_Builder
	 */
	public function insert($aDatas = array(), $aValues = null, $bEscape = true)
	{
		$this->_bInsert = true;

		if(is_numeric(key($aDatas)) || is_array($aValues))
		{
			$this->_bMultiInsert = true;

			if($aValues)
			{
				$aFields = $aDatas;

				unset($aDatas);

				foreach($aValues as $aValue)
				{
					$aDatas[] = array_combine($aFields, $aValue);
				}
			}

			// multi insert
			foreach($aDatas as $aData)
			{
				$this->_aInsert[] = $this->field($aData);
			}
		}
		else
		{
			$this->_aInsert = $this->field($aDatas);
		}

        return $this;	
	}

	public function update($aUpdate = array(), $mCondition = null, $bEscape = true)
	{
		$this->_bUpdate = true;
        
		if(is_array($aUpdate))
		{
			$this->_aUpdate = $this->field($aUpdate, $bEscape);
		}
		
		if($mCondition != null)
		{
			$this->where($mCondition);
		}
        
        return $this;
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

	public function order($mField, $sOrder = null)
	{
        if($sOrder)
        {
            $mField = $this->connection->quoteColumn($mField);
        }
        else
        {
            list($mField, $sOrder) = explode(' ', $mField, 2);
            
            $mField = $this->connection->quoteColumn($mField);
        }
		
        $this->_aOrder[] =  $mField . ' ' . $sOrder;
        
		return $this;
	}
	
	public function group($sGroup)
	{
		$this->_aGroup[] = $sGroup;
        
        return $this;
	}
	
	public function having($sHaving)
	{
		$this->_aHaving[] = $sHaving;
        
        return $this;
	}
	
	public function leftJoin($sTable, $sAlias, $mParam = null)
	{
		$this->_join('LEFT JOIN', $sTable, $sAlias, $mParam);
        
        return $this;
	}
	
	public function innerJoin($sTable, $sAlias, $mParam = null)
	{
		$this->_join('INNER JOIN', $sTable, $sAlias, $mParam);
        
        return $this;		
	}
	
	public function join($sTable, $sAlias, $mParam = null)
	{
		$this->_join('JOIN', $sTable, $sAlias, $mParam);
        
        return $this;
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
        
	public function where($mField, $sOperator = null, $mValue = null)
	{
        if($sOperator)
        {
            $this->_where($mField, $sOperator, $mValue);
        }
        else
        {
            $this->_where($mField);
        }
        
        return $this;
	}

	public function orWhere($mField, $sOperator = null, $sValue = null)
	{
		$this->_where($mField, $sOperator, $sValue, 'OR');
        
        return $this;
	}
    
    public function whereIn($sField, $mValue = null)
    {
        $this->_where($sField, 'IN', $mValue, 'AND');
        
        return $this;
    }

    public function orWhereIn($sField, $mValue = null)
    {
        $this->_where($sField, 'IN', $mValue, 'OR');
        
        return $this;
    }

    public function whereNotIn($sField, $mValue = null)
    {
        $this->_where($sField, 'NOT IN', $mValue, 'AND');
        
        return $this;
    }

    public function orWhereNotIn($sField, $mValue = null)
    {
        $this->_where($sField, 'NOT IN', $mValue, 'OR');
        
        return $this;
    }
	
	protected function _where()
	{
        $iArgs = func_num_args();
        
        if($iArgs == 0)
        {
            return;
        }
        
        list($mField, $sOperator, $mValue, $sConnector) = array_pad(func_get_args(), 4, null); 
        
        $sConnector = $sConnector ? $sConnector : 'AND';
        
        $sValue = null;

        if($mField instanceof Closure)
        {
            $this->_aWhere[] = $sConnector . ' (';
            
            call_user_func($mField, $this);
            
            $this->_aWhere[] = ')';
        } 
        else
        {
            if($iArgs == 1)
            {
                $sValue = $mField;
                
                if(is_null($sValue))
                {
                    return;
                }
                    
                $this->_aWhere[] = $sConnector . ' ' . $mField;
            }
            else
            {
                if(is_object($mValue) && (($mValue instanceof Linko_Query_Builder) || ($mValue instanceof Linko_Database_Abstract)))
                {
	                $sValue = '(' . $mValue->getQuery() . ')';
                }
                else if(!is_array($mValue))
                {
                    $sValue = $this->connection->quote($mValue);
                }

                switch(strtolower($sOperator))
                {
                    case 'in':
                    
                        if(!$mValue instanceof Linko_Database_Abstract && !$mValue instanceof Linko_Query_Builder)
                        {
                            if(!is_array($mValue))
                            {
                                $mValue = array_map('trim', explode(',', $mValue));
                            }
                            
                            $sValue = implode(', ', array_map(array($this->connection, 'quote'), $mValue));
                        }
                        
                        $sValue = '(' . $sValue . ')';
                        
                        break;
                    default:
                        break;
                }
                
                $this->_aWhere[] = $sConnector . ' ' . $this->connection->quoteColumn($mField) . ' ' . $sOperator . ' ' . $sValue;   
            }            
        }
        
		return $this;
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
		
		$this->_aJoin[] = preg_replace("/^(AND|OR)(.*?)/i", "", trim($sJoin)) . ")";
        
        return $this;
	}
	
	public function build()
	{
		if ($this->_bSelect)
		{
			// build select query
			
			$this->_aSql[] = "SELECT " . implode(', ', array_filter($this->_aSelect, function($sStr){ return (string) $sStr !== null; }));
			
			$this->_aSql[] = "FROM " . ($this->_sTable) . ($this->_sAlias ? ' AS ' . $this->_sAlias : '');
			
			$this->_aSql[] = (count($this->_aJoin) ? implode(' ', $this->_aJoin) : '');
			
			$this->_aSql[] = (count($this->_aWhere) ? "WHERE " . $this->_buildWhere() : '');
			
			$this->_aSql[] = (count($this->_aGroup) ? "GROUP BY " . implode(' ', $this->_aGroup) : '');
			
			$this->_aSql[] = (count($this->_aHaving) ? "HAVING " . implode(' ', $this->_aHaving) : '');
			
			$this->_aSql[] = (count($this->_aOrder) ? "ORDER BY " . implode(', ', $this->_aOrder) : '');
			
			$this->_aSql[] = ($this->_iLimit ? "LIMIT " . $this->_iLimit . " " : " ");
			
			$this->_aSql[] = ($this->_iOffset ? "OFFSET " . $this->_iOffset . " " : " ");
		}
		else if($this->_bInsert)
		{
			// build insert query
			
			$this->_aSql[] = "INSERT INTO " . $this->_sTable;

			if($this->_bMultiInsert)
			{
				// get the fields
				if($aFields = array_keys(current($this->_aInsert)))
				{
					$this->_aSql[] = "(" . implode(', ', $aFields) . ") ";

					$this->_aSql[] = "VALUES";

					$aSql = array();

					foreach($this->_aInsert as $aInsert)
					{
						$aSql[] = '(' . implode(', ', array_values($aInsert)) . ')';
					}

					$this->_aSql[] = implode(', ', $aSql);
				}
			}
			else
			{
				$this->_aSql[] = "(" . implode(', ', array_keys($this->_aInsert)) . ") ";

				$this->_aSql[] = "VALUES";

				$this->_aSql[] = "(" . implode(', ', array_values($this->_aInsert)) . ") ";
			}
		}
		else if ($this->_bUpdate)
		{
			// build update query
			
			$this->_aSql[] = "UPDATE " . $this->_sTable . " SET ";
			
			$aSets = array();
			if(count($this->_aUpdate))
			{
				foreach($this->_aUpdate as $sField => $sValue)
				{
					$aSets[] = "" . $sField . " = " . $sValue . "";	
				}
			}
			
			$this->_aSql[] = implode(", ", $aSets);
			
			$this->_aSql[] = " " . (count($this->_aWhere) ? "WHERE " . $this->_buildWhere() : '');
		}		
		else if ($this->_bDelete)
		{
			// build delete query
			
			$this->_aSql[] = "DELETE FROM " . $this->_sTable;
			
			$this->_aSql[] = " " . (count($this->_aWhere) ? "WHERE " . $this->_buildWhere() : Linko::Error()->trigger('Trying to delete without condition', E_USER_NOTICE));
		}
		
        $sSql = implode(" \n", $this->_aSql);
		
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
        
        return $this;
	}
    

	/**
	 * Schema: Creates a database table
	 *
	 * @param array $aFields key/value representing columns and column param
	 * @param bool  $bIfNotExists creates table if not exists
	 *
	 * @return Linko_Database_Sql_Query_Builder
	 */
	public function create(array $aFields = array(), $bIfNotExists = false)
    {
        $this->_aSql[] = $this->_oSchema->createTable($this->_sTable, $aFields, $bIfNotExists);
        
        return $this;
    }

	/**
	 * Schema: Drops a database table
	 *
	 * @param bool $bIfExists drops table if exists
	 *
	 * @return Linko_Database_Sql_Query_Builder
	 */
	public function drop($bIfExists = false)
    {
        $this->_aSql[] = $this->_oSchema->dropTable($this->_sTable, $bIfExists);
        
        return $this;
    }

	/**
	 * Schema: Checks if a table exists
	 *
	 * @return bool
	 */
	public function exists()
    {
        return $this->_oSchema->tableExists($this->_sTable, $this);
    }

	/**
	 * Schema: Gets all columns of a table
	 *
	 * @return array
	 */
	public function columns()
	{
		return $this->_oSchema->getColumns($this->_sTable);
	}

	public function addColumn($sColumn, $aParam)
	{
		$this->_aSql = array();

		$this->_aSql[] = $this->_oSchema->addColumn($this->_sTable, $sColumn, $aParam);

		return $this;
	}

	public function alterColumn($sColumn, $aParam)
	{
		$this->_aSql = array();

		$this->_aSql[] = $this->_oSchema->alterColumn($this->_sTable, $sColumn, $aParam);

		return $this;
	}

	public function renameColumn($sColumn, $sNewName)
	{
		$this->_aSql = array();

		$this->_aSql[] = $this->_oSchema->renameColumn($this->_sTable, $sColumn, $sNewName);

		return $this;
	}

	public function dropColumn($sColumn)
	{
		$this->_aSql = array();

		$this->_aSql[] = $this->_oSchema->dropColumn($this->_sTable, $sColumn);

		return $this;
	}

	/**
	 * Schema: Gets all table index keys
	 *
	 * @return array
	 */
	public function indexes()
	{
		return $this->_oSchema->getIndexes($this->_sTable);
	}

    /**
     * @return Linko_Database_Driver_Mysqli_Export
     */
    public function export()
    {
        return Linko_Object::get($this->_sExport, array('connection' => $this->connection, 'table' => $this->_sTable));
    }

    /**
     * @param $mParams
     * @param null $sValue
     * @return Linko_Database_Sql_Query_Builder
     */
    public function param($mParams, $sValue = null)
	{
		$this->connection->param($mParams, $sValue);
        
        return $this;
	}

    /**
     * @param array $aParams
     *
     * @return Linko_Database_Abstract
     */
    public function query($aParams = array())
    {
	    $sSql = $this->build();

	    $this->reset();

        return $this->connection->query($sSql, $aParams, $this);
    }

	public function getQuery()
	{
		$sSql = $this->build();

		$this->reset();

		return $sSql;
	}

	private function _buildWhere()
	{
		return (count($this->_aWhere) ? trim(preg_replace("/^(AND|OR)|((?<=\(\s)(AND|OR))(.*?)/i", "", implode(' ', $this->_aWhere))) : '');	
	}
}

?>