<?php

/*
 * @author Morrison Laju
 * @package Helpers
 * @description Directory Helper class
 */
 
class Dir extends FileSystem
{
	/**
	 * Creates a directory
	 *
	 *  <code>
	 *      Dir::create('folder');
	 *  </code>
	 *
	 * @param  string  $sPath Name of directory to create
	 * @param  integer $iChmod Chmod
	 * @param  boolean $bRecurse should the directory be recursively created if it does not exists.
	 * @return boolean
	 */
	public static function create($sPath, $iChmod = 0755, $bRecurse = true)
	{
		if(self::exists($sPath))
		{
			return true;	
		}
		
		return mkdir($sPath, $iChmod, $bRecurse);	
	}

	public static function fix($sPath)
	{
		return str_replace('/', DIRECTORY_SEPARATOR, $sPath);	
	}
	
	/**
	 * Checks if this directory exists.
	 *
	 *  <code>
	 *      if (Dir::exists('folder')) 
	 *		{
	 *          // Do Stuff       
	 *      }
	 *  </code>
	 *
	 * @param	string  $sPath Full path of the directory to check.
	 * @return	boolean				
	 */	
	public static function exists($sPath)
	{
		$sPath = (string)$sPath;
		
		if(file_exists($sPath) && is_dir($sPath))
		{
			return true;	
		}
		
		return false;
	}

	/**
	 * Moves a directory to another location. Note the source directory and its contents will be deleted.
	 *
	 *  <code>
	 *      Dir::move('path/to/src', 'path/to/dest');
	 *  </code>
	 *
	 * @param string $sSource Directory to be moved.
	 * @param boolean $sDestination Directory that it will be moved to.
	 * @return boolean
	 */
	public static function move($sSource, $sDestination) 
	{
        // Copy to the destination.
        if(self::copy($sSource, $sDestination, true, true))
        {
            // delete the source
            self::delete($sSource, true);
            
            return true;
        }
        
        return false;
    }
    
	/**
	 * Copies a directory to another location
	 *
	 *  <code>
	 *      Dir::copy('path/to/src', 'path/to/dest');
	 *  </code>
	 *
	 * @param string $sSource Directory to be copied.
	 * @param boolean $sDestination Directory that it will be copied to.
     * @param boolean $bCreateDest Set to true to create the destination directory if it doesnt exists.
     * @param boolean $bDeleteSource Set to true to delete the source file after it is copied.
	 * @return boolean
	 */
	public static function copy($sSource, $sDestination, $bCreateDest = false) 
	{ 
        if(self::exists($sSource) && (self::exists($sDestination) || ($bCreateDest && self::create($sDestination))))
        {
            foreach(self::read($sSource) as $sFile)
            {
                if(self::exists($sFile))
                {
                    self::copy($sFile, $sDestination . DS . pathinfo($sFile, PATHINFO_BASENAME), true);
                }
                else
                {
                    copy($sFile, $sDestination . DS . pathinfo($sFile, PATHINFO_BASENAME));
                }
            }

            return true;
        }
        
        return false;
	}
    	
	/**
	 * Deletes a directory
	 *
	 *  <code>
	 *      Dir::delete('folder');
	 *  </code>
	 *
	 * @param string $sPath Name of directory to delete
	 * @param boolean $bRecurse Enable to delete directories recursively.
	 * @return boolean
	 */
	public static function delete($sPath, $bRecurse = false) 
	{ 
        if(self::exists($sPath))
        {
            foreach(self::read($sPath) as $sFile)
            {
                if(self::exists($sFile))
                {
                    self::delete($sFile, $bRecurse);
                }
                else
                {
                    unlink($sFile);
                }
            }
            
            rmdir($sPath);
        }
	}

	/**
	 * Gets all files in a directory
     *
	 * @see read()
	 */	
	public static function getFiles($sPath, $bRecurse = false, $aFilter = array(), $aExclude = array())
	{
		return Arr::filter(self::read($sPath, $bRecurse, $aFilter, $aExclude), 'is_file');
	}

	/**
	 * Gets all folders in a directory
     *
	 * @see read()
	 */
	public static function getFolders($sPath, $bRecurse = false, $aFilter = array(), $aExclude = array())
	{
		return Arr::filter(self::read($sPath, $bRecurse, $aFilter, $aExclude), 'is_dir');
	}

	/**
	 * Reads all files and folders in a directory
	 *
	 */		
	public static function read($sPath, $bRecurse = false, $aFilter = array(), $aExclude = array())
	{
		$aFiles = array();
		$aExclude = array_merge(array('.', '..', '.svn'), $aExclude);
		$sFilter = count($aFilter) ? '/(' . implode('|', $aFilter) . ')/' : '/(.*)/';

		if(!$hDir = opendir($sPath))
		{
			return Linko::Error()->trigger('Invalid resource ' . $sPath, E_USER_ERROR);	
		}
		
		while($sFile = readdir($hDir))
		{
			if(!in_array($sFile, $aExclude))
			{
                $sFullPath = rtrim((str_replace('/', DS, $sPath)), DS) . DS . $sFile;

				if(preg_match($sFilter, $sFile) || Dir::exists($sFullPath))
				{
					if(is_dir($sFullPath) && $bRecurse)
					{
                        // add the directory to the array list
                        $aFiles[] = $sFullPath;

						$aFiles = array_merge($aFiles, self::read($sFullPath, $bRecurse, $aFilter, $aExclude));
					}
					else
					{
						$aFiles[] = $sFullPath;
					}
				}
			}
		}

        closedir($hDir);
		
		return $aFiles;
	} 
}

?>