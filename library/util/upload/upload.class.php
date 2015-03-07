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

    private $_aFile = array();

    private $_aError = array();

    private $_aAllowedType = array();

    private $_aAllowedMime = array();

    private $_sDestination;

    private $_iMaxSize = 512000;

    private $_sFilename;

    private $_sExtension;

    private $_sFile;

    private $_bOverwrite = false;

    public function __construct($aFile = array())
    {
        if($aFile && is_array($aFile))
        {
            $this->_buildFile($aFile);
        }
    }

    /**
     * Checks if the file is uploaded to the server
     */
    public function isUploaded()
    {
        return (($this->_aFile['tmp_name'] != null) && is_uploaded_file($this->_aFile['tmp_name']));
    }

    /**
     * Saves the uploaded file to the destination
     *
     * @param string $sDestination [optional] directory to save the uploaded file
     *
     * @return bool true on success or false on failure
     */
    public function save($sDestination = null)
    {
        if($sDestination)
        {
            $this->setDestination($sDestination);
        }

        // check destination directory exists
        if(!is_dir($this->_sDestination))
        {
            $this->_aError[] = 'Destination Folder Not Found.';
        }

        // check for allowed file types
        if(count($this->_aAllowedType) && (!in_array($this->_aFile['extension'], $this->_aAllowedType)))
        {
            $this->_aError[] = 'File type {extension} not allowed.';
        }

        // check for allowed mimes
        if(count($this->_aAllowedMime) && (!in_array($this->_aFile['mime'], $this->_aAllowedMime)))
        {
            $this->_aError[] = 'File mime not allowed {mime}.';
        }

        // check file size
        if($this->_aFile['size'] > $this->_iMaxSize)
        {
            $this->_aError[] = 'Size of file too large.';
        }

        $this->_sFile = (rtrim(str_replace('/', DIRECTORY_SEPARATOR, $this->_sDestination), '/ \\') . DIRECTORY_SEPARATOR) . $this->getFilename() . '.' . $this->getExtension();

        if($this->_bOverwrite === false)
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
                @unlink($this->_aFile['tmp_name']);

                return true;
            }
        }

        return false;
    }

    /**
     * Set allowed file types
     *
     * @param array $aAllowed
     * @return Linko_Upload
     */
    public function setAllowedType($mAllowed)
    {
	    if(!is_array($mAllowed))
	    {
			$mAllowed = array($mAllowed);
	    }

        $this->_aAllowedType = array_map('strtolower', $mAllowed);

        return $this;
    }

    /**
     * Set allowed file mimes
     *
     * @param array $aAllowed
     * @return Linko_Upload
     */
    public function setAllowedMime($aAllowed)
    {
        $this->_aAllowedMime = array_map('strtolower', $aAllowed);

        return $this;
    }

    /**
     * Sets the maximum size allowed to be uploaded in kilobytes (KB)
     *
     * @param int $iSize
     * @param string $sSize
     * @return Linko_Upload
     */
    public function setMaxSize($iSize, $sSize = 'B')
    {
        $sSize = strtolower($sSize);

        $aSizes = array(
            'b' => 1,
            'kb' => 1024,
            'mb' => (1024 * 1024),
            'gb' => (1024 * 1024 * 1024)
        );

        if(array_key_exists($sSize, $aSizes))
        {
            $iSize = $iSize * ($aSizes[$sSize]);
        }

        $this->_iMaxSize = $iSize;

        return $this;
    }

    /**
     * Sets the upload destination directory
     *
     * @param string $sDir Directory path
     * @return Linko_Upload
     */
    public function setDestination($sDir)
    {
        $this->_sDestination = $sDir;

        return $this;
    }

    /**
     * Sets the name to save the file.
     *
     * @param string $sName
     * @return Linko_Upload
     */
    public function setFilename($sName)
    {
        if(strpos($sName, '.') == true)
        {
            $this->_sFilename = pathinfo($sName, PATHINFO_FILENAME);

            $this->_sExtension = pathinfo($sName, PATHINFO_EXTENSION);
        }
        else
        {
            $this->_sFilename = $sName;
        }

        return $this;
    }

    public function setExtension($sExt)
    {
        $this->_sExtension = $sExt;

        return $this;
    }

    /**
     * Set if existing files should be overwritten with the uploaded file
     *
     * @param booean $bOverwrite
     * @return Linko_Upload
     */
    public function setOverwrite($bOverwrite)
    {
        $this->_bOverwrite = $bOverwrite;

        return $this;
    }

    /**
     * Returns the uploaded file location.
     * If the file was saved, it returns the file path else returns the tmp file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->_sFile == null ? $this->_aFile['tmp_name'] : $this->_sFile;
    }

    /**
     * Returns the extension of uploaded file
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->_sExtension == null ? $this->_aFile['extension'] : $this->_sExtension;
    }

    /**
     * Returns the file name of uploaded file without its extension
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_sFilename == null ? $this->_aFile['filename'] : $this->_sFilename;
    }

    /**
     * Returns the mime type
     *
     * @return string
     */
    public function getMime()
    {
        return $this->_aFile['mime'];
    }

    /**
     * Returns the file size in bytes
     *
     * @return string
     */
    public function getSize()
    {
        return $this->_aFile['size'];
    }

    /**
     * Returns all errors as an array
     *
     * @return object
     */
    public function getErrors()
    {
        if(count($this->_aError))
        {
            return array_map(array(&$this, '_parseError'), $this->_aError);
        }
        else
        {
            return 0;
        }
    }

    public function load($sItem)
    {
        if(!isset($_FILES[$sItem]))
        {
            return array();
        }

        if(isset($_FILES[$sItem]))
        {
            if(is_array($_FILES[$sItem]['name']))
            {
                $aFiles = array();

                foreach($_FILES[$sItem] as $sKey => $aInfo)
                {
                    foreach($aInfo as $iPos => $sInfo)
                    {
                        $aFiles[$iPos][$sKey] = $sInfo;
                    }
                }

                $aUpload = array();

                foreach($aFiles as $iKey => $aFile)
                {
                    $aUpload[] = Linko_Object::get('Linko_Upload', $aFile);
                }

                return $aUpload;
            }
            else
            {
                $this->_buildFile($_FILES[$sItem]);

                return true;
            }
        }

        return false;
    }

    private function _buildFile($aFile)
    {
        $this->_aFile = array_merge(array(
            'name' => null,
            'tmp_name' => null,
            'error' => 4,
            'type' => 'application/unknown',
            'extension' => isset($aFile['name']) ? pathinfo($aFile['name'], PATHINFO_EXTENSION) : null,
            'filename' => isset($aFile['name']) ? pathinfo($aFile['name'], PATHINFO_FILENAME) : null
        ), $aFile, $this->_aFile);

        if(function_exists('finfo_file') && $this->_aFile['tmp_name'] != null)
        {
            $rFinfo = finfo_open(FILEINFO_MIME_TYPE);

            $this->_aFile['mime'] = finfo_file($rFinfo, $aFile['tmp_name']);
        }
        else
        {
            $this->_aFile['mime'] = $aFile['type'];
        }
    }

    private function _parseError($sValue)
    {
        $aReplace = array(
            '{file}' => $this->_aFile['name'],
            '{extension}' => $this->getExtension(),
            '{mime}' => $this->getMime()
        );

        return str_replace(array_keys($aReplace), array_values($aReplace), $sValue);
    }
}