<?php

class FileSystem
{
	public static function isReadable($sFile)
	{
		if(!static::exists($sFile))
		{
			return false;
		}
		
		return is_readable($sFile);
	}
	
	public static function isWritable($sFile)
	{
		return is_writable($sFile);
	}
	
	public static function isExecutable($sFile)
	{
		return is_executable($sFile);
	}
	
	public static function exists($sFile)
	{
		return file_exists($sFile);	
	}
	
	public static function isDir($sFile)
	{
		return Dir::exists($sFile);
	}

	public static function isFile($sFile)
	{
		return File::exists($sFile);
	}
}

?>