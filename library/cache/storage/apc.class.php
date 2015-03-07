<?php

class Linko_Cache_Storage_Apc implements Linko_Cache_Interface
{
	private $_sPrefix;
	
	private $_sId;
	
	private $_aId;
	
	public function __construct()
	{
		$this->_sPrefix = Linko::Config()->get('Cache.prefix');	
	}
	
	public function set($mName)
	{
		if(is_array($mName))
		{
			$mName = $mName[0].'/'.$mName[1];
		}
		
		$this->_sId = $mName;
		$this->_aId[$this->_sId] = $mName;
	}
	
	public function read($sId = null, $iTime = 0)
	{	
		if(!$aCache = unserialize(apc_fetch($this->_getFile($this->_aId[$this->_getId($sId)]))))
		{
			return false;	
		}
		
		if(!$this->isCached($iTime, $sId, $aCache['cache_time']))
		{
			return false;	
		}
		
		$aData = $aCache['data'];
		
		if(!isset($aData))
		{
			return false;	
		}
		
		$this->reset($sId);
		
		if(!is_array($aData) && empty($aData))
		{
			return false;	
		}
		
		if(is_array($aData) && !count($aData))
		{
			return true;	
		}
		
		return $aData;
	}
	
	public function write($sData, $sId = null)
	{
		$sCache = serialize(array('cache_time' => time(), 'data' => $sData));
		
		return apc_store($this->_getFile($this->_aId[$this->_getId($sId)]), $sCache);
	}
	
	public function delete($sId = null)
	{
		$sFile = $this->_getId($sId);
		
		return true;
	}
	
	public function isCached($iTime = 0, $sId = null, $iCacheTime = 0)
	{
		if(Linko::Config()->get('Cache.enable') === false)
		{
			return false;
		}
		
		if(isset($this->_aId[$this->_getId($sId)]))
		{
			if($iTime && ((time() - $iTime) > $iCacheTime))
			{
				return false;
			}
			
			return true;
		}
		
		return false;
	}
	
	public function reset($sId = null)
	{
		unset($this->_aId[$this->_getId($sId)]);	
	}
	
	private function _getFile($sId)
	{
		return $this->_getId($sId);
	}
	
	private function _getId($sId)
	{
		if(is_null($sId))
		{
			return $this->_sId;
		}
		
		return $sId;
	}
}

?>