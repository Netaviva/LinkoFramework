<?php

interface Linko_Session_Interface
{
	public function get($sNames = null);
	
	public function set($sName, $sValue);
	
	public function remove($mName);
	
	public function open();
	
	public function read($iId);
	
	public function write($iId, $mData);
	
	public function close();
	
	public function destroy($iId);
	
	public function gc($iMaxLifetime);
}

?>