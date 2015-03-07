<?php

class Linko_Object
{
	/*
		Class => File
	*/
	public static $_aRegistry = array();
	
	/*
		Used For Autoloading
	*/
	public static $_aMap = array();
	
	public static $_aObject = array();

	public static function map($mClass, $sPath = null, $sCallback = null)
	{		
		if(!is_array($mClass))
		{
			$mClass = array($mClass => $sPath);	
		}
		
		foreach($mClass as $sClass => $sPath)
		{
			$sClass = strtolower($sClass);
			
			if(!isset(self::$_aRegistry[$sClass]))
			{				
				self::$_aRegistry[$sClass] = $sPath;
			}
		}		
	}
	
	public static function get($mName, $aParams = array(), $bNewInstance = false)
	{
		$sClass = strtolower($mName);
		
		if(!isset(self::$_aRegistry[$sClass]))
		{
			return Linko::Error()->trigger($sClass . " Not Found In Registry");	
		}
		
		$sHash = md5($sClass . serialize($aParams));
        
		if(isset(Linko_Object::$_aObject[$sHash]))
		{
            return Linko_Object::$_aObject[$sHash];
		}
        
        if(is_callable(self::$_aRegistry[$sClass]))
		{  
			if(self::$_aRegistry[$sClass] instanceof Closure)
			{
				Linko_Object::$_aObject[$sHash] = call_user_func_array(self::$_aRegistry[$sClass], $aParams);
			}
		}
		else
		{		
			Linko_Object::$_aObject[$sHash] = Linko_Object::create($sClass, $aParams);
		}

		return Linko_Object::$_aObject[$sHash];
	}
	
	public static function getObjects()
	{
		return self::$_aObject;
	}

	public static function getRegistry()
	{
		return self::$_aRegistry;
	}
		
	public static function create($mName, $aParams = array())
	{
		$sClass = ($mName);
		
		$sHash = md5($sClass . serialize($aParams));
		
		if (isset(Linko_Object::$_aObject[$sHash]))
		{
			return Linko_Object::$_aObject[$sHash];
		}	
		
		if (!class_exists($sClass))
		{
			Linko::Error()->trigger('Unable to call class: ' . $sClass, E_USER_ERROR);
		}		

		if ($aParams)
		{
			Linko_Object::$_aObject[$sHash] = new $mName($aParams);
		}
		else 
		{		
			Linko_Object::$_aObject[$sHash] = new $mName();
		}

		if (method_exists(Linko_Object::$_aObject[$sHash], 'getInstance'))
		{
			return Linko_Object::$_aObject[$sHash]->getInstance();
		}				
		
		return Linko_Object::$_aObject[$sHash];	
	}
	
	public static function autoload($mName)
	{
		$mName = strtolower($mName);
		
		if(isset(self::$_aRegistry[$mName]) && !is_callable(self::$_aRegistry[$mName]))
		{
			require_once str_replace('/', DS, self::$_aRegistry[$mName]);	
		}
	}

	public static function isRegistered($sName)
	{
		return in_array(strtolower($sName), array_keys(static::$_aMap));	
	}
}

?>