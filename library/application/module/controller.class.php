<?php

class Linko_Controller
{
	private $_aParams = array();
	
	private $_sModule;
	
	private $_sController;

    private $_sType;
	
	public function __construct($aParams = array())
	{		
		$this->_sModule = $aParams['linko.module'];
		
		$this->_sController = $aParams['linko.controller'];

        $this->_sType = $aParams['linko.type'];

        unset($aParams['linko.controller'], $aParams['linko.module'], $aParams['linko.type']);
		
		$this->_aParams = $aParams;
	}
	
    public function setGlobalParam($mParams, $sValue = null)
    {
        Linko::Module()->setParam($mParams, $sValue);
        
        return $this;
    }
    
	public function setParam($mParams, $sValue = null)
	{
		if (!is_array($mParams))
		{
			$mParams = array($mParams => $sValue);
		}
		
		foreach ($mParams as $sVar => $sValue)
		{
			$this->_aParams[$sVar] = $sValue;
		}
	}
	
	public function getParam($mName = null)
	{
		if($mName)
		{
			return isset($this->_aParams[$mName]) ? $this->_aParams[$mName] : null;
		}
		
		return $this->_aParams;
	}
	
	public function getSetting($mSetting)
	{
		return Linko::Module()->getSetting($mSetting);
	}

    public function getPath()
    {
        return $this->_sModule . '/' . $this->_sType . '/' . $this->_sController;
    }

	public function __get($sParam)
	{
		if (isset($this->_aParams[$sParam]))
		{
			return $this->_aParams[$sParam];
		}
		
		Linko::Error()->trigger('Use of undefined property: ' . $sParam, E_USER_ERROR);

        return false;
	}
}