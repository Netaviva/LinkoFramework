<?php

class Linko_Ajax
{
 	/**
 	 * Holds the output type
 	 * 
 	 * @var string
 	 */      
    private $_sType = 'json';
 
 	/**
 	 * Holds the output
 	 * 
 	 * @var mixed
 	 */      
    private $_mOutput;

    /**
     * @var array
     */
    private $_aParam;

 	public function __construct($mParam)
	{
        if(is_string($mParam))
        {
            parse_str($mParam, $mParam);
        }

		$this->_aParam = $mParam;
	}

    public function output($mData, $sFormat = null)
    {
        $this->_sType = $sFormat;

        switch($sFormat)
        {
            case 'json':
                $this->_mOutput = json_encode($mData);
                break;
            case 'xml':
                $this->_mOutput = Linko::Xml()->parse($mData);
                break;
            case 'script':

                break;
            case 'html':
            default:
                $this->_mOutput = $mData;
                break;
        }

        return $this;
    }

    public function getParam($sParam = null)
    {
        if($sParam)
        {
            return isset($this->_aParam[$sParam]) ? $this->_aParam[$sParam] : null;
        }

        return $this->_aParam;
    }

    public function toJson($mData)
    {
        return $this->output($mData, 'json');
    }
    
    public function toXml($mData)
    {
        return $this->output($mData, 'xml');
    }

    final public function getAjax()
    {
		return array($this->_mOutput, $this->_sType);   
    }
}