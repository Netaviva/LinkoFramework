<?php

defined('LINKO') or exit('Linko Not Defined!');

abstract class Linko_Database_Abstract implements Linko_Database_Interface
{
	protected $_hQuery;
	
	protected $Util;
	
	protected $_aSql;
	
	protected $_hHandle;
	
	protected $_aQueries = array();
	
	protected $_iTotalQueries = 0;
	
	protected $_oQuery;

	protected $_aReplace = array();
    
    protected $_aParam = array();
	
	protected $_sDriver;

    protected $_mQuery;
    
    protected $_sBuilder;
    
    protected $_sSchema;

    protected $_sExport;
    
    protected $_sIdentifier;

	protected $_sCharset = 'utf8';

	public function __construct($aParams = array())
	{
        $this->_aParam = $aParams;
	}
    	
	public function prefix($sTable)
	{
		return $this->_sPrefix . $sTable;
	}
	
	public function getPrefix()
	{
		return $this->_sPrefix;
	}
	
	public function getQueries()
	{
		return $this->_aQueries;	
	}
		
	public function getLastQuery()
	{
		return $this->_mQuery;
	}
    
	public function getQuery()
	{
		return $this->getLastQuery();
	}

    public function getHandle()
    {
        return $this->_hHandle;
    }
    	
	public function query($mQuery = null, $aParams = array(), $oBuilder = null)
	{
        $this->_oQuery = $oBuilder;
        
		Linko::Profiler()->start('query');
		
		if(count($aParams))
		{
			$this->param($aParams);
		}
		
        if(is_string($mQuery))
        {
            $mQuery = str_replace(array_keys($this->_aReplace), array_values($this->_aReplace), $mQuery);
        }
		
        $this->_mQuery = $mQuery;
        
        $this->_aQueries[] = $mQuery;
        
		$this->_iTotalQueries++;
		
		Linko::Profiler()->stop('query', array(
				'query' => $this->_mQuery
			)
		);
		
		$this->execute($this->_mQuery);

		$this->_aReplace = array();
		
		return $this;
	}

	public function getCharset()
	{
		return $this->_sCharset;
	}

	/**
	 * Gets default collation. This is not tied to any table or schema or column
	 */
	public function getCollation()
	{
		if(strpos($this->_sCharset, '_') === false)
		{
			return $this->_sCharset . '_bin';
		}
		else
		{
			return $this->_sCharset;
		}
	}

    /**
     * Pagination query
     * 
     * list($iTotal, $aRows) = Linko::Database()->query(...)->paginate(2, 5);
     * 
     * @param int $iPage Current Page
     * @param int $iLimit Rows Per Page
     * @return array
     */
    public function paginate($iPage, $iLimit)
    {
        // get total no of records from query
        $iTotal = $this->getCount();
        
        $aRows = $this->rebuild() // rebuild from the last query
            ->filter($iPage, $iLimit, $iTotal) // filter results based on limit, page num and total result and sets the offset and limit
            ->query() // execute query
            ->fetchRows(); // get filtered rows
        
        return array($iTotal, $aRows);
    }
    
	public function param($mParams, $sValue = null)
	{
		if(!is_array($mParams))
		{
			$mParams = array($mParams => $sValue);
		}
		
		foreach($mParams as $sKey => $sValue)
		{
			$this->_aReplace[$sKey] = $this->escape($sValue);
		}

		return $this;
	}

    public function quote($sValue)
    {
		if($sValue === NULL)
		{
			return 'NULL';
		}
		elseif($sValue === TRUE)
		{
			return "'1'";
		}
		elseif($sValue === FALSE)
		{
			return "'0'";
		}
        
        return $this->escape($sValue);       
    }

    public function quoteColumn($mField, $sWrapper = '%s')
    {
        settype($mField, 'string');
        
        $sField = trim($mField);
        
		if($sField == '*')
		{
			return $mField;
		}
		elseif(strpos($mField, '(') !== FALSE)
		{
            if(preg_match('/(.+)["|\'](.+?)["|\'](.+)/i', $mField, $aMatch))
            {
                $mField = $aMatch[1] . $this->quoteColumn($aMatch[2]) . $aMatch[3];
            }
		}
		elseif (strpos($mField, '.') !== FALSE)
		{
			$aPart = explode('.', $mField);

			foreach ($aPart as $iKey => $sPart)
			{
                $sPart = trim($sPart);
                
				if ($sPart !== '*')
				{
					$aPart[$iKey] = sprintf($sWrapper, $sPart);
				}
			}
            
			$mField = implode('.', $aPart);
		}
        else
        {
            $mField = sprintf($sWrapper, $sField);
        }
        
        return $mField;
    }

	public function quoteTable($sTable)
	{
		return $sTable;
	}

    public function table($sTable, $sAlias = null)
    {
        $sClass = Inflector::classify('Linko_Database_' . $this->_sBuilder . '_Query_Builder');
        $sExport = $this->_sExport ? $this->_sExport : Inflector::classify('Linko_Database_Driver_' . $this->_aParam['driver'] . '_Export');
        $sSchema = $this->_sSchema ? $this->_sSchema : Inflector::classify('Linko_Database_Driver_' . $this->_aParam['driver'] . '_Schema');

        return Linko_Object::get($sClass, array(
            'connection' => $this,
            'schema' => $sSchema,
            'export' => $sExport,
            'hash' => md5($sTable . $sAlias)
        ))->table($sTable, $sAlias);
    }
    
    /**
     * Use previous query to build new query
     * Optionally you can use clone
     * 
     * @return object
     */
    public function rebuild()
    {
        return $this->_oQuery->rebuild();
    }
    
	public function __call($sMethod, $aArguments)
	{        
		if(in_array($sMethod, array('createTable', 'dropTables')))
		{
			call_user_func_array(array($this->Util, $sMethod), $aArguments);
			
			return $this;
		}
		
		return Linko::Error()->trigger("Call to undefined method Database::" . $sMethod . "()");		
	}
}

?>