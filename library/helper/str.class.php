<?php

define('MB_STRING', function_exists('mb_strlen') ? true : false);

class Str
{
	const TRUNCATE_CHAR = 100;
	
	const TRUNCATE_WORD = 101;
	
	const COUNT_WORD = 'COUNT_WORD';
	
	const COUNT_CHAR = 'COUNT_CHAR';
	
	const RANDOM_ALPHA = 'RANDOM_ALPHA';
	
	const RANDOM_ANY = 'RANDOM_ANY';
	
	const NEWLINE = "\n";

	public static function concat($sJoiner = '')
	{
		$aArgs = array_slice(func_get_args(), 0);
		
		return implode(' ', $aArgs);	
	}
	
	public static function truncate($sStr, $iLimit = 100, $sExtra = null,
		$iOpt = Str::TRUNCATE_CHAR)
	{
		if($iLimit <= 0)
		{
			return $sStr;
		}

		$iCount = ($iOpt == Str::TRUNCATE_WORD ? str_word_count($sStr) : strlen($sStr));

		if($iCount <= $iLimit)
		{
			return $sStr;
		}

		if($iOpt == Str::TRUNCATE_WORD)
		{
			preg_match('/^\s*+(?:\S++\s*+){1,' . $iLimit . '}/u', $sStr, $aMatches);

			return isset($aMatches[0]) ? ($aMatches[0] . $sExtra) : $sExtra;
		}

		return substr($sStr, 0, $iLimit) . $sExtra;
	}

	public static function count($sStr,
		$iOpt = Str::COUNT_CHAR)
	{
		return $iOpt == Str::COUNT_CHAR ? strlen($sStr) : str_word_count($sStr);
	}
	
	public static function wrap($sStr, $iWidth = 75, $sBreak = " ", $bCut = true)
	{
		$sFormatted = null; 
		$aParts = preg_split('/(<.*?>)|(\s)/', $sStr, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		
		foreach($aParts as $sPart)
		{
			if(isset($sPart[0]) && ($sPart[0] == '<'))
			{
				$sFormatted .= $sPart;
				
				continue;
			}
			else if(strlen($sPart) > $iWidth)
			{
				$sFormatted .= wordwrap($sPart, $iWidth, $sBreak, $bCut);
			}
			else
			{
				$sFormatted .= $sPart;
			}
		}
		
		return $sFormatted;
	}
	
	public static function title($sStr)
	{
		return Inflector::slugify($sStr);	
	}
	
	public static function upper($sStr)
	{
			
	}
	
	public static function random($iNum, $mOpt = self::RANDOM_ANY)
	{
		
	}

	public static function length($sStr)
	{
		return strlen(utf8_decode($sStr));	
	}

    public static function parseArray($mStr)
    {
        if(is_array($mStr))
        {
            return $mStr;
        }

        parse_str($mStr, $aArray);

        return $aArray;
    }
}

?>