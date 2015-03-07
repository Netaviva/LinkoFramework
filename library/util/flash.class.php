<?php

class Linko_Flash
{
	const SESSION_KEY = 'global_flash_message';
	const SESSION_KEY_TYPE = 'global_flash_message_type';
	
	public function set($sType, $sMsg)
	{
		Linko::Session()->set(self::SESSION_KEY, $sMsg);
		Linko::Session()->set(self::SESSION_KEY_TYPE, $sType);		
	}

	public function getMessage()
	{
		return (($sMsg = Linko::Session()->get(self::SESSION_KEY)) && ($sMsg != null)) ? $sMsg : null;	
	}

	public function getType()
	{
		return (($sType = Linko::Session()->get(self::SESSION_KEY_TYPE)) && ($sType != null)) ? $sType : 'warning';	
	}
		
	public function success($sMsg)
	{
		$this->set('success', $sMsg);
	}
		
	public function error($sMsg)
	{
		$this->set('error', $sMsg);
	}
	
	public function warning($sMsg)
	{
		$this->set('warning', $sMsg);
	}

	public function hasMessage()
	{
		return ($this->getMessage() ? true : false);	
	}
	
	public function info($sMsg)
	{
		$this->set('info', $sMsg);
	}
		
	public function clear()
	{
		Linko::Session()->remove(self::SESSION_KEY);
		Linko::Session()->remove(self::SESSION_KEY_TYPE);
	}
}

?>