<?php

class Input
{
	public static function get($sKey = null, $sDefault = null)
	{
		return Linko::Request()->getGET($sKey, $sDefault);
	}
	
	public static function post($sKey = null, $sDefault = null)
	{
		return Linko::Request()->getPOST($sKey, $sDefault);
	}

    public static function isAjax()
    {
        return Linko::Request()->isAjax();
    }
}

?>