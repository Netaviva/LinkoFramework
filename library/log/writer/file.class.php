<?php

class Linko_Log_Writer_File implements Linko_Log_Writer_Interface
{
	private $_sLogPath;

	public function setLogPath($sPath)
	{
		$this->_sLogPath = $sPath;

		return $this;
	}

	public function write($aLog, $aParam)
	{
		return File::write($this->_sLogPath, str_replace(array(
			'%t',
			'%m',
			'%n',
			'%i'
		), array(
			$aLog['time'],
			$aLog['message'],
			$aLog['name'],
			$aLog['level']
		), "- %t [%i]%n: %m"), File::APPEND);
	}
}