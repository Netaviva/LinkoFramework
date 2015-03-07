<?php

abstract class Linko_Active_Model
{
	protected $_sTable;
	
	protected $_aField = array();
	
	protected $_sPrimaryKey;
	
	/**
	 * The relationships that have been loaded for the query.
	 *
	 * @var array
	 */
	private $_aRelationship = array();
	
	public function __construct()
	{
		$this->_sTable = $this->getTable();

		$aFields = Linko::Database()->getTableSchema($this->_sTable);
		
		foreach($aFields as $aRow)
		{
			$this->_aField[$aRow['Field']] = null;
			
			if(strtolower($aRow['Key']) == 'pri')
			{
				$this->_sPrimaryKey = $aRow['Field'];
			}
		}
	}
	
	abstract public function getTable();
	
	public function getAll()
	{
		return Linko_Object::get('Database')->select(implode(', ', $this->_aField))
			->from($this->_sTable)
				->query()
					->fetchRows();
	}
	
	public function getBy($sField, $sValue)
	{
		return Linko::Database()->select(implode(', ', array_keys($this->_aField)))
			->from($this->_sTable)
				->where("" . $sField . " = '" . $sValue . "'")
					->query()
						->fetchRow();
	}
	
	public function getOneBy($sField, $sValue)
	{
		return $this->getBy($sField, $sValue);
	}
		
	function __set($sField, $sValue) 
	{
		if(array_key_exists($sField, $this->_aField)) 
		{
			$this->_aField[$sField] = $sValue;
		}
	}
	
	function __get($sField) 
	{
		if(array_key_exists($sField, $this->_aField)) 
		{
			return $this->_aField[$sField];
		}
		
		Linko::Error()->trigger('Calling undefined property ' . $sField);
	}
}

?>