<?php

class Linko_Hasher
{
	public function __construct()
	{
		
	}
	
	public function hash($sValue, $sSalt = null)
	{
		if($sSalt == null)
		{
			$sSalt = $this->salt();	
		}
		
		return $this->_crypt($sValue, $sSalt) . $sSalt;
	}
	
	public function compare($sValue, $sHashed)
	{
        if(substr($sHashed, 0, 60) == $this->_crypt($sValue, substr($sHashed, 60)))
        {
            return true;
        }

        return false;
	}
	
	public function salt()
	{
	   	$sSeed = '';
		for ($i = 1; $i <= 10; $i++)
	   	{
	    	$sSeed .= substr('./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', mt_rand(0, 63), 1);
		}
		
		return $sSeed;		
	}
	
	private function _crypt($sValue, $sSalt)
	{
        $iStrength = '08';
        
		return crypt($sValue, '$2a$' . $iStrength . '$' . $sSalt); // Blowfish;
	}
}

?>