<?php

class Linko_Cookie
{
    private $_sPrefix;
    
    public function __construct()
    {
        $this->_sPrefix = Linko::Config()->get('cookie.prefix'); 
    }
    
    public function set($sName, $sValue, $iExpire = 0)
    {
        $sName = $this->_sPrefix . $sName;

		setcookie($sName, $sValue, (($iExpire != 0 || $iExpire != -1) ? $iExpire : (time() + (60*60*24*$iExpire))), '/', '');       
    }
    
    public function get($sName)
    {
		$sName = $this->_sPrefix . $sName;

		return (isset($_COOKIE[$sName]) ? $_COOKIE[$sName] : null);       
    }
    
    public function remove($sName)
    {
        return $this->set($sName, '', -1);
    }
}

?>