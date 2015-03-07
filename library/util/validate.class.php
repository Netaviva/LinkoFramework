<?php

/*
 *	$oVal = $this->Validate->set('form_name', array(
 *			'field_one' => array(
 *				'error' => 'Error For Field One',
 *				'function' => 'function_name:param1,param2,param3'
 *			)
 *		)
 *	);
 *	
 *	if($oVal->isValid($_POST))
 *	{
 *		// do stuff
 *	}
*/		
class Linko_Validate 
{
	private $_aFunctions = array();
	
	private $_aFields = array();
	
	private $_sName;
	
	public function __construct()
	{
		
	}
	
	public function set($mName, $mParams = array())
	{
		if(is_array($mName))
		{
			$this->_sName = uniqid();
			
			$this->_aFields[$this->_sName] = $mName;	
		}
		else
		{
			$this->_sName = $mName;
			
			$this->_aFields[$mName] = $mParams;	
		}
		
		return $this;
	}
	
	public function isValid($aVals, $sName = null)
	{
	      
		$sName = $sName == null ? $this->_sName : $sName;
		
		if(isset($this->_aFields[$sName]))
		{
			foreach($this->_aFields[$sName] as $sField => $aParam)
			{
				$this->_validate($sField, $aParam, (isset($aVals[$sField]) ? $aVals[$sField] : null));
			}
		}
		
		return Linko::Error()->isPassed();
	}
	
	public function register($sName, $mFunction)
	{
		if(isset($this->_aFunctions[$sName]))
		{
			Linko::Error()->trigger('The function name you are trying to add already exists.');	
		}
		
		if(is_callable($mFunction))
		{
			$this->_aFunctions[$sName] = $mFunction;
		}
		else
		{
			Linko::Error()->trigger('The function is not callable.');					
		}
	}
	
	private function _validate($sField, $aParam, $sValue)
	{	
		$sFunction = isset($aParam['function']) ? $aParam['function'] : 'required';
		$sError = isset($aParam['error']) ? $aParam['error'] : null;
		
		$aFunctions = is_array($sFunction) ? $sFunction : array($sFunction);
		$aErrors = is_array($sError) ? $sError : array($sError);
		
		foreach($aFunctions as $iKey => $sFunction)
		{			
			$aArguments = array();
			$sFunction = trim($sFunction);
			
			if(($iParam = strpos($sFunction, ':')))
			{
				$aArguments =  explode(',', substr($sFunction, ++$iParam));	
				$sFunction = substr($sFunction, 0, --$iParam);
			}

			if(!isset($aErrors[$iKey]))
			{
				$aErrors[$iKey] = null;	
			}
			
			if((!is_callable($sFunction)) && (isset($this->_aFunctions[$sFunction])))
			{
				$sVal = call_user_func($this->_aFunctions[$sFunction], $sValue, $aArguments, $sField, $this);
			}
			else if(is_callable($sFunction))
			{
				$sVal = call_user_func($sFunction, $sValue, $aArguments, $sField, $this);
			}
			else
			{
				$sFunction = 'validate_' . $sFunction;
				
				if(method_exists($this, $sFunction))
				{
					$sVal = $this->$sFunction($sValue, $aArguments, $sField);	
				}				
			}
			
			if($sVal === false)
			{
				Linko::Error()->set($aErrors[$iKey]);	
			}
		}
	}
	
	public function validate_required($sValue, $aArguments, $sField)
	{
		if(trim($sValue) == '')
		{
			return false;	
		}
	}

	public function validate_contain($sValue, $aArguments, $sField)
	{
		return in_array($sValue, $aArguments);
	}

	public function validate_between($sValue, $aArguments, $sField)
	{
		$iMin = isset($aArguments[0]) ? $aArguments[0] : 1;
		$iMax = isset($aArguments[1]) ? $aArguments[1] : 25;
		
		return (($sValue >= $iMin) && ($sValue <= $iMax));
	}

	public function validate_email($sValue, $aArguments, $sField)
	{
		return filter_var($sValue, FILTER_VALIDATE_EMAIL) !== false;
	}
	
	public function validate_min($sValue, $aArguments, $sField)
	{
		$iMin = isset($aArguments[0]) ? $aArguments[0] : 1;
		
		return $sValue >= $iMin;
	}
	
	public function validate_max($sValue, $aArguments, $sField)
	{
		$iMax = isset($aArguments[0]) ? $aArguments[0] : 1;
		
		return $sValue <= $iMax;
	}
	
	public function validate_length($sValue, $aArguments, $sField)
	{
		$iLength = strlen($sValue);
		
		$iMin = isset($aArguments[0]) ? $aArguments[0] : false;
		$iMax = isset($aArguments[1]) ? $aArguments[1] : false;
		
		if(count($aArguments) == 1)
		{
			$iMax = $iMin;
			
			return $iLength <= $iMax;
		}
		
		if($iMin && $iMax)
		{			
			return (($iLength >= $iMin) && ($iLength <= $iMax)); 
		}		
		else if($iMin && $iMax == null)
		{
			return $sValue <= $iMax;
		}
	}	
}

?>