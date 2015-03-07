<?php

class File extends FileSystem
{
	const PREPEND = 0x001;
	
	const APPEND = 0x002;
	
	/**
	 * Creates a new file.
	 *
	 *  <code>
	 *      File::create('path/to/file.txt'))
	 *  </code>
     *  
	 * @param string $sFile
	 * @param bool $bRecurse set to true to create directory recursively.
	 * @return boolean
	 */
	public static function create($sFile, $bRecurse = false)
	{
		if(Dir::exists(pathinfo($sFile, PATHINFO_DIRNAME)) || 
            ($bRecurse && Dir::create(pathinfo($sFile, PATHINFO_DIRNAME))))
		{
            if(touch($sFile))
            {
                return true;
            }
		}
		
		return false;
	}
	
	/**
	 * Checks if a file exists.
	 *
	 *  <code>
	 *      if (File::exists('path/to/file')) 
	 *		{
	 *          // Do Stuff       
	 *      }
	 *  </code>
	 *
	 * @param string $sFile Full path of the file to check.
	 * @return boolean				
	 */
	public static function exists($sFile)
	{
		$sFile = (string)$sFile;
		
		if(file_exists($sFile) && is_file($sFile))
		{
			return true;	
		}
		
		return false;
	}


    /**
     * Cleans a file name. removes invalid file characters
     *
     * @param string $sFile file
     * @return string new file name
     */
    public static function clean($sFile)
    {
        $sDir = ltrim(pathinfo($sFile, PATHINFO_DIRNAME), '.');
        
        $sExt = self::extension($sFile);
        
        return ($sDir ? $sDir . DS : '') . preg_replace('#(?:[^\w\.-]+)#', '_', basename($sFile, $sExt)) . $sExt;
    }
	
	public static function size($sFile)
	{
		return filesize($sFile);
	}

	public static function copy($sFile)
	{
		
	}
	
	public static function rename($sFile)
	{
		
	}
	
	public static function delete($sFile)
	{
		if(@unlink($sFile))
		{
			return true;
		}
		
		return false;
	}
		
	public static function extension($sFile)
	{
    	return pathinfo($sFile, PATHINFO_EXTENSION);
	}
	
	public static function name($sFile, $bIncludeExtension = false)
	{
		return pathinfo($sFile, PATHINFO_FILENAME) . ($bIncludeExtension ? '.' . self::extension($sFile) : '');	
	}

	public static function path($sFile, $bIncludeName = false)
	{
		return pathinfo(realpath($sFile), PATHINFO_DIRNAME) . DS . ($bIncludeName ? self::name($sFile) : '');	
	}
    	
	public static function mime($sFile)
	{
		if(!self::exists($sFile))
		{
			return false;
		}
		
		$sMime = false;
		
		if(function_exists('finfo_open')) 
		{
			$hFinfo = finfo_open(FILEINFO_MIME_TYPE);
			
			$sMime = finfo_file($hFinfo, $sFile);
			
			finfo_close($hFinfo);
		}
		elseif(function_exists('mime_content_type')) 
		{
			$sMime = mime_content_type($sFile);
		}
		
		return $sMime;
	}
	
	public static function read($sFile)
	{
		if(self::exists($sFile) && self::isReadable($sFile))
		{
			return file_get_contents($sFile);	
		}
		
		return false;
	}
	
	public static function write($sFile, $sContent, 
		$iMode = null, $bCreate = false)
	{
		if((!self::exists($sFile)) && ($bCreate))
		{
			self::create($sFile, true);
		}
		
		if(self::exists($sFile) && self::isWritable($sFile))
		{
			switch($iMode)
			{
				case File::PREPEND:
					return file_put_contents($sFile, $sContent . self::read($sFile));
				break;
				case File::APPEND:
					return file_put_contents($sFile, $sContent, FILE_APPEND);
				break;
				default:
					return file_put_contents($sFile, $sContent);
				break;		
			}
		}
		
		return false;
	}
}

?>