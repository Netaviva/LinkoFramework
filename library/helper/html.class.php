<?php

class Html
{
	private static $_aMacro = array();

	private static $_aSingleTags = array('img', 'br', 'hr', 'input', 'link', 'meta', 'col');
	
	public static function openTag($sTag, $aAttr = array())
	{
		return '<' . $sTag . static::attribute($aAttr) . (in_array($sTag, static::$_aSingleTags) ? " /" : '') . '>';	
	}
	
	public static function closeTag($sTag)
	{
		return (!in_array($sTag, static::$_aSingleTags) ? '</' . $sTag . '>' : null);	
	}
	
	public static function tag($sTag, $sValue = null, $aAttr = array())
	{
		return static::openTag($sTag, $aAttr) . (in_array($sTag, static::$_aSingleTags) ? null : ($sValue . static::closeTag($sTag)));
	}

	public static function wrap($mTags, $sContent)
	{
		if(!is_array($mTags))
		{
			$mTags = array($mTags);	
		}
		
		$iCnt = count($mTags);
		
		while(count($mTags))
		{
			$sTag = array_pop($mTags);
			
			$sContent = Html::tag($sTag, $sContent);
		}
		
		return $sContent;
	}
	
	public static function link($sTitle, $mUrl = null, $aAttr = array())
	{
		$sUrl = ($mUrl == '#') ? $mUrl : Linko::Url()->make($mUrl);
		
		return static::tag('a', $sTitle, array_merge($aAttr, array('href' => $sUrl)));
	}

	public static function script($sCode, $aAttr = array())
	{
		return static::tag('script', $sCode, array_merge(array('type' => 'text/javascript'), $aAttr));
	}

	public static function style($sCode, $aAttr = array())
	{
		return static::tag('style', $sCode, array_merge(array('type' => 'text/css'), $aAttr));
	}
			
	public static function nbsp($iRepeat = 1)
	{
		return str_repeat('&nbsp;', $iRepeat);
	}

	public static function br($iRepeat = 1)
	{
		return str_repeat('<br />', $iRepeat);
	}
		
	public static function attribute($aAttr = array())
	{
		$sAttr = null;
		
		foreach($aAttr as $sProp => $sValue)
		{
			$sAttr .= ' ' . $sProp . '="' . $sValue . '"';
		}
		
		return $sAttr;
	}
	
	public static function extend($sName, $cMacro)
	{
		static::$_aMacro[$sName] = $cMacro;
	}
	
	public static function __callStatic($sMethod, $aArguments)
	{
	    if (isset(static::$_aMacro[$sMethod]))
	    {
	        return call_user_func_array(static::$_aMacro[$sMethod], $aArguments);
	    }
	    
	    Linko::Error()->trigger("Call to undefined method " . __CLASS__ . '::' . $sMethod . '()');
	}
}

?>