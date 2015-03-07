<?php

class Linko_Session_Storage_Default extends Linko_Session_Abstract
{
	public function __construct()
	{
		parent::__construct();
		
		if(!isset($_SESSION))
		{
			session_start();
		}
	}
	
	public function get($sName = null)
	{
		if(is_null($sName))
		{
			return isset($_SESSION[$this->_sPrefix]) ? $_SESSION[$this->_sPrefix] : array();	
		}
				
		if (isset($_SESSION[$this->_sPrefix][$sName]))
		{
			return (empty($_SESSION[$this->_sPrefix][$sName]) ? null : $_SESSION[$this->_sPrefix][$sName]);
		}

		return false;		
	}
	
	public function set($sName, $sValue)
	{
		$_SESSION[$this->_sPrefix][$sName] = $sValue;
        
        return $sValue;
	}
	
	public function remove($mName)
	{
		if (!is_array($mName))
		{
			$mName = array($mName);
		}

		foreach ($mName as $sName)
		{			
			unset($_SESSION[$this->_sPrefix][$sName]);			
		}		
	}
	
	public function open()
	{
		return true;
	}
	
	public function read($iId)
	{
		
	}
	
	public function write($iId, $mData)
	{
		
	}
	
	public function close()
	{
		
	}
	
	public function destroy($iId)
	{
		
	}
	
	public function gc($iMaxLifetime)
	{
		
	}
}

?>