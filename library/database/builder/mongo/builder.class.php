<?php

class Linko_Database_Mongo_Query_Builder implements Linko_Query_Builder
{
	public $connection;
	
	private $_mCommand;
	
	private $_aQueryHistory = array();
	
	private $_sCollection;
	
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
        $this->_sHash = null;
        
        $this->connection = $aParams['connection'];
        
        $this->_oSchema = $aParams['schema'];
	}
    
	public function table($sTable, $sAlias = null)
	{
		$this->_sCollection = $this->connection->prefix($sTable);
		
		$this->_sAlias = $sAlias ? $this->connection->quoteColumn($sAlias) : null;
		
		return $this;
	}
    
	public function field(array $aField = array(), $bEscape = true)
	{
		foreach($aField as $sField => $sValue)
		{
			if(strtolower($sValue) == 'now()')
			{
				$sValue = Date::now();
			}
			else if(strtolower($sValue) == 'null')
			{
				$sValue = 'NULL';
			}
			
			$this->_aField[$sField] = $sValue;
		}
        
        return $this;
	}
	
	public function select()
	{
        $this->_bSelect = true;
        
        $aSelect = func_get_args();
        
        $iArgs = func_num_args();
        
        if($iArgs == 0)
        {
            $aSelect = array();
        }
        else
        {
            if($iArgs == 1 && strpos($aSelect[0], ','))
            {
                $aSelect = explode(',', $aSelect[0]);
            }
        }
		
		$this->_aSelect = array_map('trim', $aSelect);
        
        return $this;
	}
	
	public function insert($aField = array(), $bEscape = true)
	{
		$this->_bInsert = true;
		
		if(count($aField))
		{
			$this->field($aField, $bEscape);	
		}
        
        return $this;	
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
        
        return $this;
	}

	public function delete($mCondition = null)
	{
		$this->_bDelete = true;
		
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

        switch(strtolower($sOrder))
        {
            case 'desc':
                $sOrder = -1;
                break;
            default:
                $sOrder = 1;
                break;
        }
        		
        $this->_aOrder[$mField] = $sOrder;
        
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
		return $this;
	}
	
	public function innerJoin($sTable, $sAlias, $mParam = null)
	{
		return $this;		
	}
	
	public function join($sTable, $sAlias, $mParam = null)
	{
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
		// $this->_where($mField, $sOperator, $sValue, 'OR');
        
        return $this;
	}
    
    public function whereIn($sField, $mValue = null)
    {
        $this->_where($sField, 'IN', $mValue, 'AND');
        
        return $this;
    }

    public function orWhereIn($sField, $mValue = null)
    {
        // $this->_where($sField, 'IN', $mValue, 'OR');
        
        return $this;
    }

    public function whereNotIn($sField, $mValue = null)
    {
        $this->_where($sField, 'NOT IN', $mValue, 'AND');
        
        return $this;
    }

    public function orWhereNotIn($sField, $mValue = null)
    {
        // $this->_where($sField, 'NOT IN', $mValue, 'OR');
        
        return $this;
    }
	
	protected function _where()
	{
        $iArgs = func_num_args();
        
        list($mField, $sOperator, $mValue, $sConnector) = array_pad(func_get_args(), 4, null);
        
        if($mField instanceof Closure)
        {
            //  No support for nested wheres
            // call_user_func($mField, $this);
        }
        else if(is_array($mField))
        {
            $this->_aWhere = $mField;
        }
        else
        {
            switch(strtolower($sOperator))
            {
                case '<=':
                    $mValue = array('$lte' => $mValue);
                    break;
                case '<':
                    $mValue = array('$lt' => $mValue);
                    break;
                case '>=':
                    $mValue = array('$gte' => $mValue);
                    break;
                case '>':
                    $mValue = array('$gt' => $mValue);
                    break;
                case 'in':
                    if(is_array($mValue))
                    {
                        
                    }
                    else
                    {
                        if(is_string($mValue) && strpos(',', $mValue))
                        {
                            $mValue = explode(',', $mValue);   
                        }
                        else
                        {
                            $mValue = array($mValue);
                        }
                    }
                    
                    $mValue = array('$in' => $mValue);
                    break;
                case 'not in':
                    if(is_array($mValue))
                    {
                        
                    }
                    else
                    {
                        if(is_string($mValue) && strpos(',', $mValue))
                        {
                            $mValue = explode(',', $mValue);   
                        }
                        else
                        {
                            $mValue = array($mValue);
                        }
                    }
                    $mValue = array('$nin' => $mValue);
                    break;
                case '!=':
                case '<>':
                    $mValue = array('$ne' => $mValue);
                    break;
                case '=':
                default:

                    break;
            }            
        }
        
        // not fully implemented... still testing
        if($sConnector == 'OR')
        {
            if(array_key_exists($mField, $this->_aWhere))
            {
                // $this->_aWhere['$or'] = array(array($mField => $this->_aWhere[$mField]), array($mField => $mValue));
                
                // unset($this->_aWhere[$mField]);
            }
        }
        else
        {
            $this->_aWhere[$mField] = $mValue;   
        }
                       
		return $this;
	}
		
	protected function _join($sType, $sTable, $sAlias, $mParam = null)
	{
       
        return $this;
	}
	
	public function build()
	{
		if ($this->_bSelect)
		{
			// build select query

            $this->_mCommand = array(
                'method' => 'find',
                'collection' => $this->_sCollection,
                'fields' => $this->_aSelect,
                'criteria' => $this->_aWhere,
                'order' => $this->_aOrder,
                'limit' => $this->_iLimit,
                'offset' => $this->_iOffset,
            );
		}
		else if($this->_bInsert)
		{
			// build insert query
            
            $this->_mCommand = array(
                'method' => 'insert',
                'collection' => $this->_sCollection,
                'fields' => $this->_aField,
                'options' => array(
                    'fsync' => true
                )
            );
		}
		else if ($this->_bUpdate)
		{
			// build update query
            $this->_mCommand = array(
                'method' => 'update',
                'collection' => $this->_sCollection,
                'fields' => $this->_aField,
                'options' => array(
                    'fsync' => true
                )
            );			
		}		
		else if ($this->_bDelete)
		{
			// build delete query
			
		}
		else
        {
            
        }
        
        $mCommand = $this->_mCommand;
        	
		$this->reset();
		
		return $mCommand;		
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
			'_mCommand' => $this->_mCommand,
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
			'_sCollection' => $this->_sCollection,
		);
		
		$this->_mCommand = array();
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
		$this->_sCollection = null;
        
        return $this;
	}
    
    // Schema: create table
    public function create(array $aFields = array(), $bIfNotExists = false)
    {
        $this->_mCommand = $this->_oSchema->createCollection($this->_sCollection, $aFields, $bIfNotExists);
        
        return $this;
    }

    // Schema: drop table
    public function drop($bIfExists = false)
    {
        $this->_mCommand = $this->_oSchema->dropCollection($this->_sCollection, $bIfExists);
        
        return $this;
    }

    // Schema: table exists
    public function exists()
    {
        return $this->_oSchema->collectionExists($this->_sCollection);
    }
            
	public function bind($mParams, $sValue = null)
	{
		$this->connection->bind($mParams, $sValue);
        
        return $this;
	}
    	
    public function query($aParams = array())
    {
        return $this->connection->query($this->build(), $aParams, $this);
    }
}

?>