<?php

class Linko_Error
{
	private $_aErrors = array();

	public function set($sMsg, $sName = 'global')
	{
		$this->_aErrors[$sName][] = $sMsg;
		
		return false;
	}
	
	public function get($sName = 'global')
	{
		return (isset($this->_aErrors[$sName]) && count($this->_aErrors[$sName])) ? $this->_aErrors[$sName] : array();
	}
	
	public function isPassed()
	{
		return (!count($this->_aErrors) ? true : false);
	}

	public function hasErrors()
	{
		return self::isPassed() ? false : true;
	}
		
	public function trigger($sMsg, $sCode = E_USER_WARNING)
	{
		trigger_error(strip_tags($sMsg), $sCode);
		
		if ($sCode == E_USER_ERROR)
		{
			exit;
		}
		
		return false;
	}
}

?>