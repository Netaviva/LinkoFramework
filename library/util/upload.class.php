<?php

class Linko_Upload
{
    const ERROR_INI_SIZE = "The file '{file}' exceeds the defined ini size";
    const ERROR_FORM_SIZE = "The file '{file}' exceeds the defined size";
    const ERROR_PARTIAL = "The file '{file}' was only partially uploaded";
    const ERROR_NO_FILE = "The file '{file}' was not uploaded";
    const ERROR_NO_TMP_DIR = "No temporary directory was found for the file '{file}'";
    const ERROR_CANT_WRITE = "The file '{file}' can't be written";
    const ERROR_EXTENSION = "The extension returned an error while uploading the file '{file}'";
    const ERROR_ATTACK = "The file '{file}' was illegal uploaded, possible attack";
    const ERROR_FILE_NOT_FOUND = "The file '{file}' was not found";
    const ERROR_UNKNOWN = "Unknown error while uploading the file '{file}'";
		
	private $_sKey;
	
	private $_aFile = array();
	
	private $_sFilename;
	
	private $_sFile;
	
	private $_sDestination = 'tmp/';
	
	private $_aAllowed = array();
	
	private $_iMaxSize = 512000;
	
	private $_aError = array();
	
	private $_bOverride = false;
	
	public function __construct()
	{
		
	}
	
	public function set($sFormItem, $sDestination = null, $aAllowed = array(), $iMaxSize = false)
	{
		if($aAllowed)
		{
			$this->setAllowed($aAllowed);
		}
		
		if($iMaxSize)
		{
			$this->setMaxSize($iMaxSize);	
		}
		
		if($sDestination)
		{
			$this->setDestination($sDestination);	
		}
		
		$this->_sKey = $sFormItem;
		
		$this->_buildFile();
				
		return $this;
	}
	
	/**
	 * Checks if the file has been uploaded
	 */
	public function isUploaded()
	{
		return (isset($this->_aFile['tmp_name']) &&  ($this->_aFile['tmp_name'] != null));
	}

	/**
	 * Saves the file to the destination
	 * It returns true on success or false on failure
	 * @param string $sDestination directory to save the file
	 * @return bool
	 */	
	public function save($sDestination = null)
	{
		if($sDestination)
		{
			$this->_sDestination = $sDestination;	
		}
		
		if(!is_dir($this->_sDestination))
		{
			$this->_aError[] = 'Destination Folder Not Found.';	
		}
		
		if(count($this->_aAllowed) && (!in_array($this->_aFile['extension'], $this->_aAllowed)))
		{
			$this->_aError[] = 'Invalid File Extension {extension}.';
		}
		
		/**
		 * @todo 
		 * Check the mime type of the file to see if it matches the mime of the extensions we allow
		 */
		
		if($this->_aFile['size'] > $this->_iMaxSize)
		{
			$this->_aError[] = 'Filesize is too large';
		}

		$sFilename = (($this->_sFilename != null) ? $this->_sFilename : $this->_aFile['filename']) . '.' . $this->_aFile['extension'];
		
		$this->_sFile = (rtrim(str_replace('/', DIRECTORY_SEPARATOR, $this->_sDestination), '/ \\') . DIRECTORY_SEPARATOR) . $sFilename;
				
		if($this->_bOverride === false)
		{
			if(is_file($this->_sFile))
			{
				$this->_aError[] = 'File {file} already exists.';	
			}
		}
		
		switch($this->_aFile['error'])
		{
			case 0:
				if(!is_uploaded_file($this->_aFile['tmp_name']))
				{
					$this->_aError[] = self::ERROR_ATTACK;	
				}
				break;
			case UPLOAD_ERR_INI_SIZE:
				$this->_aError[] = self::ERROR_INI_SIZE; 
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$this->_aError[] = self::ERROR_FORM_SIZE;
				break;
			case UPLOAD_ERR_PARTIAL:
				$this->_aError[] = self::ERROR_PARTIAL;
				break;
			case UPLOAD_ERR_NO_FILE:
				$this->_aError[] = self::ERROR_NO_FILE;
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$this->_aError[] = self::ERROR_NO_TMP_DIR;
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$this->_aError[] = self::ERROR_CANT_WRITE;
				break;
			case UPLOAD_ERR_EXTENSION:
				$this->_aError[] = self::ERROR_EXTENSION;
				break;
			default:
				$this->_aError[] = self::ERROR_UNKNOWN;
				break;
		}
		
		if(count($this->_aError))
		{
			@unlink($this->_aFile['tmp_name']);
			
			return false;	
		}
		else
		{			
			if(move_uploaded_file($this->_aFile['tmp_name'], $this->_sFile))
			{
				// remove tmp file
				@unlink($this->_aFile['tmp_name']);
				
				return true;	
			}
		}
	}
	
	public function setMaxSize($iSize)
	{
		$this->_iMaxSize = $iSize;
	}

	public function setDestination($sDir)
	{
		$this->_sDestination = $sDir;
		
		return $this;
	}

	public function setFilename($sName)
	{
		$this->_sFilename = $sName;	
			
		return $this;
	}

	public function setOverride($bOverride)
	{
		$this->_bOverride = $bOverride;
				
		return $this;
	}
				
	public function setAllowed($aAllowed)
	{
		$this->_aAllowed = array_map('strtolower', $aAllowed);
		
		if (in_array('jpg', $this->_aAllowed))
		{
			array_push($this->_aAllowed, 'jpeg');
		}
	
		if (in_array('mpg', $this->_aAllowed))
		{
			array_push($this->_aAllowed, 'mpeg');
		}
				
		return $this;
	}
	
	public function getErrors()
	{
		if(count($this->_aError))
		{
			return array_map(array(&$this, '_error'), $this->_aError);
		}
		else
		{
			return 0;	
		}
	}

	public function getFile()
	{
		return $this->_sFile;
	}
		
	public function getTmp()
	{
		return $this->_aFile['tmp_name'];
	}

	public function getSize()
	{
		return $this->_aFile['size'];
	}

	public function getFullname()
	{
		return $this->_aFile['name'];
	}

	public function getName()
	{
		return $this->_aFile['filename'];
	}
		
	public function getExension()
	{
		return $this->_aFile['extension'];
	}
		
	public function getMime()
	{
		return $this->_aFile['mime'];	
	}
		
	private function _buildFile()
	{
		if(isset($_FILES[$this->_sKey]))
		{
			$this->_aFile = $_FILES[$this->_sKey];

			list($sName, $sExtension) = $this->_getFileParts();
		
			$this->_aFile['extension'] = $sExtension;
		
			$this->_aFile['filename'] = $sName;
			
			if(!$this->_aFile['error'])
			{
				if(function_exists('finfo_file'))
				{
					$hFinfo = finfo_open(FILEINFO_MIME_TYPE);
					
					$this->_aFile['mime'] = finfo_file($hFinfo, $this->_aFile['tmp_name']);
				}
				else
				{
					$this->_aFile['mime'] = $this->_aFile['type'];
				}
			}
			else
			{
				$this->_aFile['mime'] = 'application/unknown';	
			}
		}
	}
	
    private function _getFileParts()
    {
    	$sFilename = $this->_aFile['name'];
		$aParts = explode('.', $sFilename);
		$sExtension = array_pop($aParts);
		$sName = implode('.', $aParts);		
		return array($sName, $sExtension);
    }
	
	private function _error($sValue)
	{
		$aFind = array('{file}', '{extension}');
		
		$aReplace = array($this->_aFile['name'], $this->_aFile['extension']);
			
		return str_replace($aFind, $aReplace, $sValue);
	}
}

?>

