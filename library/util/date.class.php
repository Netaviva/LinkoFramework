<?php

class Linko_Date
{
	public function getTimezones()
	{
		return Linko::Locale('date')->getTimezones();
	}
    
	public function setTimezone($sTimezone)
	{
		Linko::Locale('date')->setTimezone($sTimezone);
        
        return $this;
	}
    
    public function getTime($sFormat = null, $iTime = 'now')
    {
        return Linko::Locale('date')->getTime($sFormat, $iTime);
    }
    
    public function getOffset()
    {
        return Linko::Locale('date')->getOffset();
    }
}

?>