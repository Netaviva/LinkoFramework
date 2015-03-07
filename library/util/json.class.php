<?php

class Linko_Json
{
	public function  encode($mData)
	{
		return json_encode($mData);
	}
	
	public function decode($mData)
	{
		return json_decode($mData);	
	}
	
	/**
	 *	Makes a json object readable
	 */
	public function format($mJson)
	{
		$iIndent = 0;
		$sResult = null;
		$bInQuote = false;
		$bIgnoreText = false;
		$iLength = strlen($mJson);
		
		$sNewLine = "\n";
		$sTab = "    ";

		for($i = 0; $i < $iLength; $i++)
		{
			$sChar = $mJson[$i];
			
			switch($sChar)
			{
				case '{':
				case '[':
					$iIndent++;
					
					$sResult .= $sChar . $sNewLine . str_repeat($sTab, $iIndent);
				break;
				case '}':
				case ']':
					$iIndent--;
					$sResult = trim($sResult) . $sNewLine . str_repeat($sTab, $iIndent) . $sChar;
				break;
				case ',':
					$sResult .= $sChar . $sNewLine . str_repeat($sTab, $iIndent);
				break;
				case '"':
                    $sResult .= $sChar;
                    break; 

				break;
				default:
					$sResult .= $sChar;
				break;						
			}
		}
		
		return $sResult;
	}
}

?>