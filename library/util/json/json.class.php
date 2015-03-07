<?php

class Linko_Json
{
    const PRETTY_PRINT = 0x001;
    
    const JAVASCRIPT_FORMAT = 0x002;
    
	public function encode($mData, $iOpts = null)
	{
        $sJson = json_encode($mData);
        
        if($iOpts & self::JAVASCRIPT_FORMAT)
        {
            $sJson = str_replace("\\/", "/", $sJson);
        }        

        if($iOpts & self::PRETTY_PRINT)
        {
            $sJson = $this->format($sJson); 
        }
                
        return $sJson;
    }
	
	public function decode($mData)
	{
		return json_decode($mData);	
	}
	
	/**
	 *	Makes a json output readable
	 */
	public function format($mJson)
	{
		$iIndent = 0;
		$sResult = null;
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