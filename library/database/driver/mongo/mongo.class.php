<?php

class Linko_Database_Driver_Mongo extends Linko_Database_Abstract
{
	/**
	 * Instance to the MongoDB class
	 * 
	 * @param object
	 */   
    public $oDb;    
    
	protected $_sHost;
	
	protected $_sUser;
	
	protected $_sPass;
	
	protected $_sPort = null;
	
	protected $_sDatabase;
	
	protected $_sPrefix;
	
	protected $_bPersistent = false;
    
    protected $_sInsertId;

	/**
	 * Instance to MongoCursor when a select query is used
	 * 
	 * @param object
	 */   
    private $_oCursor;
    	
	public function __construct($aParams)
	{		
		$this->_sHost = $aParams['host'];
		
		$this->_sUser = $aParams['username'];
		
		$this->_sPass = $aParams['password'];
		
		$this->_sDatabase = $aParams['database'];
		
		$this->_sPort = isset($aParams['port']) ? $aParams['port'] : null;
		
		$this->_sPrefix = isset($aParams['prefix']) ? $aParams['prefix'] : null;
		
		$this->_bPersistent = isset($aParams['persistent']) ? true : false;
		
		$this->connect();
		
		parent::__construct($aParams);
        
        $this->oDb = $this->getHandle()->selectDB($this->_sDatabase);
	}
	
	public function connect()
	{
        if($this->_sPort)
		{
			$this->_sHost = $this->_sHost . ':' . $this->_sPort;	
		}
		
		Linko::Profiler()->start('connect');
		
        try
        {
            $this->_hHandle = new Mongo("mongodb://" . $this->_sUser . ":" . $this->_sPass . "@" . $this->_sHost . "/" . $this->_sDatabase);
        }
		catch(MongoConnectionException $e)
		{
			exit('Could not connect to database server: ' . $e->getMessage());
		}
        
        Linko::Profiler()->stop('connect', array(
				'database' => $this->_sDatabase
			)
		);
		
		return $this->_hHandle;
	}
	
	public function execute($mCommand)
	{
        if(!isset($mCommand['method']))
        {
            return;
        }
        
        $sMethod = '_' . $mCommand['method'];
        
        unset($mCommand['method']);
        
        $this->$sMethod($mCommand);
        
        unset($mCommand);
	}

	public function setCharset($sCharset)
	{
		return $this;
	}

	public function fetchValue($sCol = null)
	{
		
	}
		
	public function fetchRow()
	{
		return $this->_oCursor->getNext();
	}
	
	public function fetchRows()
	{
        return iterator_to_array($this->_oCursor, false);	
	}
	
	public function fetchObject()
	{
		return (object)$this->_oCursor->getNext();
	}
	
	public function fetchObjects()
	{
		$aRows = array();
		
		foreach($this->_oCursor as $aDocument)
		{
			$aRows[] = (object)$aDocument;	
		}
		
		return $aRows;
	}
	
	public function getCount()
	{
        return $this->_oCursor->count();
	}

    public function getAffectedRows()
	{
	   
	}
	
	public function getInsertId()
	{
        return $this->_sInsertId;
	}
	
	public function close()
	{

	}

	public function escape($mValue)
	{

	}
    
	public function dbError()
	{

	}
	
	public function getDriver()
	{
		return 'MongoDB';
	}
	
	public function getVersion()
	{
			
	}

	// @Override
	public function quote($mValue)
	{
		return $mValue;
	}

	// @Override
    public function quoteColumn($mField, $sWrapper = "%s")
    {
        return $mField;   
    }
    
    private function _find($mParams)
    {
        /**
         * @var $collection
         * @var $criteria
         * @var $order
         * @var $limit
         * @var $offset
         */
        extract($mParams);
        
        $this->_oCursor = $this->oDb->selectCollection($collection)->find($criteria);
 
        if($order)
        {
            $this->_oCursor->sort($order);
        }
               
        if($limit)
        {
            $this->_oCursor->limit($limit);
        }

        if($offset)
        {
            $this->_oCursor->offset($offset);
        }
    }

    /**
     * @param $mParams
     *
     */
    private function _insert($mParams)
    {
        /**
         * @var $collection
         * @var $fields
         * @var $options
         */
        extract($mParams);
        
        $this->oDb->selectCollection($collection)->insert($fields, $options);
        
        if(isset($fields['_id']))
        {
            $this->_sInsertId = $fields['_id'];
        }
    }
    
    private function _command($mParams)
    {
        // $this->oDb->command($mParams);
    }   
}

?>