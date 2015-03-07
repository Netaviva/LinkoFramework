<?php

class Template_Plugin_Cycle
{
	private $_aCycle = array();
	
	public function start($aParams = array())
	{
		$sName = isset($aParams['name']) ? $aParams['name'] : 'default';
		$bPrint = true;
		$bReset = false;
		$bAdvance = true;
		 
		if(!isset($aParams['value']))
		{
			if(!isset($this->_aCycle[$sName]['value']))
			{
				return Linko::Error()->trigger(__METHOD__ . ' missing values parameter.');
			}
		}
		else
		{
			if(isset($this->_aCycle[$sName]['value']) && ($this->_aCycle[$sName]['value'] != $aParams['value']))
			{
				$this->_aCycle[$sName]['index'] = 0;
			}
			
			$this->_aCycle[$sName]['value'] = $aParams['value'];
		}
					
		if(is_array($this->_aCycle[$sName]['value']))
		{
			$aCycle = $this->_aCycle[$sName]['value'];	
		}
		else
		{
			$aCycle = explode(',', $this->_aCycle[$sName]['value']); 
		}
		
		if(!isset($this->_aCycle[$sName]['index']) || $bReset)
		{
			$this->_aCycle[$sName]['index'] = 0;
		}
		
		$sReturn = $bPrint ? $aCycle[$this->_aCycle[$sName]['index']] : null;
		
		if($bAdvance)
		{
			if($this->_aCycle[$sName]['index'] >= count($aCycle) -1)
			{
				$this->_aCycle[$sName]['index'] = 0;
			}
			else
			{
				$this->_aCycle[$sName]['index']++;	
			}
		}
		
		echo $sReturn;
	}
}

?>