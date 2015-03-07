<?php

class Arr
{
	const INARRAY_IGNORE_CASE = 0x100;

    const FILTER_VALUE  = 0x102;

    const FILTER_KEY = 0x104;

	private static $_iDumpCnt = 0;

    public static function filter(Array $aArray, $mCallback, $iOpt = Arr::FILTER_VALUE)
    {
        $aFiltered = array();

        foreach($aArray as $mKey => $mValue)
        {
            if(is_array($mValue))
            {
                $aFiltered[$mKey] = array_merge($aFiltered, self::filter($mValue, $mCallback));
            }
            else
            {
                if($mCallback($mValue))
                {
                    $aFiltered[$mKey] = $mValue;
                }
            }
        }

        return $aFiltered;
    }

	public static function inArray($sNeedle, $aArr, 
		$bCase = Arr::INARRAY_IGNORE_CASE)
	{
		if($bCase == Arr::INARRAY_IGNORE_CASE)
		{
			$sNeedle = strtolower($sNeedle);
			
			array_map(array('Str', 'lower'), $aArr);
		}
		
		return in_array($sNeedle, $aArr);
	}
	
	public static function hasKeys($aArray, $aKeys = array())
	{
		$iFailed = 0;
        
        $aKeys = func_get_args();
 
        array_shift($aKeys);
        
        if(count($aKeys) == 1 && is_array($aKeys[0]))
        {
            $aKeys = $aKeys[0];
        }
        
		foreach($aKeys as $sKey)
		{
			if(!array_key_exists($sKey, $aArray))
			{
				$iFailed++;	
			}
		}
		
		return ($iFailed > 0) ? false : true;
	}

	public static function hasValues($aArray, $aValues)
	{
		$iFailed = 0;
		
		foreach($aValues as $sValue)
		{
			if(!self::inArray($sValue, $aArray))
			{
				$iFailed++;	
			}
		}
		
		return ($iFailed > 0) ? false : true;
	}

	
	public static function flatten($aArray = array())
	{
        $aFlatten = array();

        foreach($aArray as $mKey => $mValue)
		{
			if(is_array($mValue))
            {
                $aFlatten = array_merge($aFlatten, static::flatten($mValue));
            }
            else
            {
                $aFlatten[] = $mValue;
            }
		}
		
		return $aFlatten;
    }
	
	public static function get($aArray, $sKey, $sDefault = null)
	{
		if (is_null($sKey)) 
		{
			return $aArray;
		}

		foreach(explode('.', $sKey) as $sSegment)
		{
			if (!is_array($aArray) or !array_key_exists($sSegment, $aArray))
			{
				return $sDefault;
			}

			$aArray = $aArray[$sSegment];
		}

		return $aArray;		
	}
	
	public static function dump($mArgs, $sKey = null, $bReturn = false, $iLevel = 0)
	{
		if(!$sKey)
		{
			$sKey = self::$_iDumpCnt++;
		}
		
		$sType = strtolower(gettype($mArgs));
		$sBreak = "\r\n";
		$sTab = '&nbsp;&nbsp;&nbsp;&nbsp;';
		 
		$sHtml = null;
		
		if($iLevel == 0)
		{
			$sHtml .= Html::openTag('pre') . Html::openTag('div', array('style' => 'text-align:left; background-color:white; font: 100% monospace; color:black;'));
		}
		
		$sHtml .= Html::tag('span', $sType, array('style' => 'color:#099'));
		
		if($sType == 'string')
		{
			$sHtml .= '(' . strlen($mArgs) . ') ';
			$sHtml .= Html::tag('span', '"' . str_replace(array('<', '>'), array("&lt;", "&gt;"), $mArgs) . '"', array('style' => 'color: #C00'));
		}
		if($sType == 'integer' || $sType == 'double')
		{
			$sHtml .= Html::tag('span', $mArgs, array('style' => 'color: #900'));
		}
		else if($sType == 'boolean')
		{
			$sHtml .= Html::tag('span', ($mArgs === true ? 'true' : 'false'), array('style' => 'color: #090'));
		}
		else if($sType == 'null')
		{
			$sHtml .= Html::tag('span', 'empty', array('style' => 'color: #090'));
		}
		else if($sType == 'resource')
		{
			$sHtml .= Html::tag('span', get_resource_type($mArgs), array('style' => 'color: #090'));
			$sHtml .= '(' . $mArgs . ')';
		}
		else if($sType == 'array')
		{
			$sHtml .= '(' . count($mArgs) . ')';
			$sHtml .= $sBreak . str_repeat($sTab, $iLevel) . '(';
			
			foreach($mArgs as $sKey => $mArg)
			{
				$sHtml .= $sBreak . str_repeat($sTab, $iLevel + 1) . '[' . $sKey . ']' . ' => ' . self::dump($mArgs[$sKey], $sKey, true, $iLevel + 1);
			}
			
			$sHtml .= $sBreak . str_repeat($sTab, $iLevel) . ')';
		}
		else if($sType == 'object')
		{
			$sClass = get_class($mArgs);
			$aProperties = get_object_vars($mArgs);
			
			$sHtml .= '(' . count($aProperties) . ')' . Html::tag('u', $sClass);
			
			foreach($aProperties as $sName => $sValue)
			{
				$sHtml .= $sBreak . str_repeat($sTab, $iLevel + 1) . $sName . ' => ';
				
				$sHtml .= self::dump($mArgs->$sName, $sKey, true, $iLevel + 1);	
			}
		}
		
		if($bReturn)
		{
			return $sHtml;	
		}
		
		if($iLevel == 0)
		{
			$sHtml .= Html::closeTag('div') . Html::closeTag('pre');
		}
		
		echo $sHtml;
	}
}

?>