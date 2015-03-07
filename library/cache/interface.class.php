<?php

interface Linko_Cache_Interface
{
	public function set($sId);
	
	public function read($sId = null, $iExpireTime = 0);
	
	public function write($sContent, $sId = null);
	
	public function reset($sId);
	
	public function delete($sId, $sType = 'path');
	
	public function isCached($iTime = 0, $sId = null, $iCacheTime = 0);
}

?>