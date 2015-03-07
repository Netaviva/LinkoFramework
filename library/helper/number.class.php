<?php

class Number
{
	public static function size($iBytes, $sReturn = null)
	{
		$aSizes = array('bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

		if ($sReturn === null) 
		{ 
			$sReturn = '%01.2f %s'; 
		}

		$sLast = end($aSizes);

		foreach($aSizes as $sSize) 
		{
	       	if($iBytes < 1024) 
			{ 
				break; 
			}
	          
			if($sSize != $sLast) 
			{ 
				$iBytes /= 1024; 
			}
		}
	    
		if($sSize == $aSizes[0]) 
		{ 
			$sReturn = '%01d %s'; 
		}
		
		return sprintf($sReturn, $iBytes, $sSize);			
	}
}

?>