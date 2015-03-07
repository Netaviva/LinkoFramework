<?php

class Linko_Session_Storage_File extends Linko_Session_Abstract
{
	private $_sPath;
	
	public function __construct()
	{
        parent::__construct();
        
		session_set_save_handler(
				array(&$this, 'open'),
				array(&$this, 'close'),
				array(&$this, 'read'),
				array(&$this, 'write'),
				array(&$this, 'destroy'),
				array(&$this, 'gc')
		);
		
		$this->_sPath = session_save_path();
		
		if(!isset($_SESSION))
		{
			session_start();	
		}
	}
    
    public function setSavePath($sPath)
    {
        $this->_sPath = $sPath;
        
        return $this;
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

	public function close()
	{
		return true;
	}
		
	public function read($iId)
	{
		if (!file_exists($this->_sPath . $this->_sPrefix . $iId))
		{
			return false;
		}
		
		return (string) file_get_contents($this->_sPath . $this->_sPrefix . $iId);
	}

	public function write($iId, $mData)
	{
		if ($hFp = @fopen($this->_sPath . $this->_sPrefix . $iId, "w")) 
		{
	    	$bReturn = fwrite($hFp, $mData);
	    	fclose($hFp);
	    	
	    	return $bReturn;
	  	} 
	  	else 
	  	{
	    	return(false);
	  	}		
	}
	
	public function destroy($iId)
	{
		return(@unlink($this->_sPath . $this->_sPrefix . $iId));
	}
	
	public function gc($iMaxLifetime)
	{
		foreach (glob($this->_sPath . $this->_sPrefix . '*') as $sFilename) 
		{
	    	if (filemtime($sFilename) + $iMaxLifetime < time()) 
	    	{
	      		@unlink($sFilename);
	    	}
	  	}

	  	return true;		
	}	
}

?>