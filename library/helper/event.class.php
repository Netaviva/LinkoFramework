<?php

class Event
{
	private static $_aEvents = array();
	
	public static function listen($sName, $sCallback)
	{
		if(isset(self::$_aEvents[$sName]))
		{
			self::$_aEvents[$sName] = array();	
		}
		
		self::$_aEvents[$sName][] = $sCallback;
	}
	
	public static function fire($mName, $mParam = null)
	{
		$aRes = array();
		
		if(!is_array($mName))
		{
			$mName = array($mName);	
		}
		
		if(!is_array($mParam))
		{
			$mParam = array($mParam);	
		}
		
		foreach($mName as $sName)
		{
			if(isset(self::$_aEvents[$sName]) && count(self::$_aEvents))
			{
				foreach(self::$_aEvents[$sName] as $sCallback)
				{
					$sRes = call_user_func_array($sCallback, $mParam);
					
					$aRes[] = $sRes;
				}
			}
		}
		
		return $aRes;
	}
		
}

?>