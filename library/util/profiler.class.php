<?php

class Linko_Profiler
{
	private $_iStartTime = 0;
	
	private $_aLogs = array();
	
	private $_aProfile = array();
	
	private $_aExtended = array();
	
	public function __construct()
	{
		$this->_iStartTime = $this->_getMicroTime();
	}
	
	public function start($sName)
	{
		$this->_aProfile[$sName] = array(
			'name' => $sName,
			'start_time' => $this->_getMicroTime(),
			'start_memory' => $this->_getMemory(),
		);
	}
	
	public function stop($sName, $aExtra = array())
	{
		if(isset($this->_aProfile[$sName]))
		{
			$iEndMemory = $this->_getMemory();
			$iEndTime = $this->_getMicroTime();
			$iMemoryUsed = ($iEndMemory - $this->_aProfile[$sName]['start_memory']);
			$iTimeExecuted = sprintf('%0.7f', ($iEndTime - $this->_aProfile[$sName]['start_time']));
			$this->_aLogs['custom'][$sName][] = array_merge($this->_aProfile[$sName], $aExtra, array(
				'end_time' => $iEndTime,
				'end_memory' => $this->_getMemory(),
				'memory_used' => $this->_getReadableSize($iMemoryUsed),
				'time_used' => $this->_getReadableTime($iTimeExecuted),
			));
			
			$this->reset($sName);
		}
	}
	
	public function reset($sName = null)
	{
		if ($sName === null)
		{
			$this->_aProfile = array();
			return;	
		}
		
		unset($this->_aProfile[$sName]);
	}
	
	public function getDetails()
	{
		$this->_getMemoryData();
		$this->_getTimeData();
		$this->_getExtendedData();
		
		return $this->_aLogs;		
	}
	
	public function extend($sKey, $aParams = array())
	{
		$this->_aExtended[$sKey] = $aParams;
	}

	private function _getExtendedData()
	{
		foreach($this->_aExtended as $sKey => $aExtended)
		{
			$this->_aLogs[$sKey] = $aExtended;
		}
	}
			
	private function _getMicroTime()
	{
		return array_sum(explode(' ', microtime()));
	}
	
	private function _getMemory()
	{
		return memory_get_usage();	
	}
		
	private function _getMemoryData()
	{
		$this->_aLogs['memory']['memory_usage'] = $this->_getReadableSize(memory_get_peak_usage());
		$this->_aLogs['memory']['memory_limit'] = $this->_getReadableSize(ini_get('memory_limit'));	
	}
	
	private function _getTimeData()
	{
		$this->_aLogs['time']['used_time'] = $this->_getReadableTime($this->_getMicroTime() - $this->_iStartTime);	
	}
	
	private function _getReadableSize($size, $retstring = null)
	{
        // adapted from code at http://aidanlister.com/repos/v/function.size_readable.php
		$sizes = array('bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

		if ($retstring === null) { $retstring = '%01.2f %s'; }

		$lastsizestring = end($sizes);

		foreach ($sizes as $sizestring) {
	       	if ($size < 1024) { break; }
	           if ($sizestring != $lastsizestring) { $size /= 1024; }
	       }
	       if ($sizestring == $sizes[0]) { $retstring = '%01d %s'; } // Bytes aren't normally fractional
	       return sprintf($retstring, $size, $sizestring);	
	}
	
	private function _getReadableTime($time) 
	{
		$ret = $time;
		$formatter = 0;
		$formats = array('ms', 's', 'm');
		
		if($time >= 1000 && $time < 60000) 
		{
			$formatter = 1;
			$ret = ($time / 1000);
		}
		
		if($time >= 60000) 
		{
			$formatter = 2;
			$ret = ($time / 1000) / 60;
		}
		
		$ret = number_format($ret,3,'.','') . ' ' . $formats[$formatter];
		
		return $ret;
	}
}

?>