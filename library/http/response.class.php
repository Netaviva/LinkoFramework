<?php

class Linko_Response
{
	private $_aHtml = array();
	
	private $_aHeaders = array();
	
	private $_sStatusCode;
	
	private $_sStatusText;
	
	private $_sData;
	
	private $_oHttpResponse;
	
	private $_sStatus;
	
	private $_bCompress = false;
	
	private $_sEncoding;
	
	private $_bSent = false;
	
	private $_aSenders = array();
	
	private $_aOption = array();
	
	public function __construct()
	{
		if(Linko::Request()->getServer('HTTP_ACCEPT_ENCODING'))
		{
			$aEncodings = explode(',', strtolower(preg_replace("/\s+/", "", Linko::Request()->getServer('HTTP_ACCEPT_ENCODING'))));
		
			$this->_sEncoding = in_array('x-gzip', $aEncodings) ? "x-gzip" : "gzip";
		}
	}
	
	public function setOption($mOptions, $sValue = null)
	{
		if(!is_array($mOptions))
		{
			$mOptions = array($mOptions => $sValue);	
		}
		
		foreach($mOptions as $sOption => $sValue)
		{
			$this->_aOption[$sOption] = $sValue;
		}
		
		return $this;	
	}
	
	public function setData($sData)
	{
		$this->_sData = $sData;
		
		return $this;
	}

	public function getData()
	{
		return $this->_sData ? $this->_sData : false;
	}
		
	public function setStatus($sCode)
	{
		$this->_sStatus = $sCode;
		
		return $this;
	}
	
	public function getStatus()
	{
		return $this->_sStatus ? $this->_sStatus : false;
	}

	public function setStatusCode($sCode)
	{
		$this->_sStatusCode = $sCode;
			
		return $this;
	}

	public function getStatusCode()
	{
		return $this->_sStatusCode;	
	}
	
	public function setStatusText($sTxt)
	{
		$this->_sStatusText = $sTxt;
			
		return $this;
	}

	public function getStatusText()
	{
		return $this->_sStatusText;	
	}
				
	public function prepare()
	{		
		$this->setContent($this->_sData);		
					
		return $this;
	}
	
	public function minify($sData)
	{
		$sMin = preg_replace(array('/^\\s+/m', '/\ {2,}/m'), '', $sData);
		
		return $sMin;	
	}
	
	public function compress($sData)
	{
		if (function_exists('gzencode'))
		{			
			$sGzip = gzencode($this->_sData, 9, FORCE_GZIP);	
		}
		else
		{
			if (function_exists('gzcompress') && function_exists('crc32'))
			{		
				$size = strlen($this->_sData);
				$crc = crc32($this->_sData);
				$sGzip = "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\xff";
				$sGzip .= substr(gzcompress($this->_sData, 9), 2, -4);
				$sGzip .= pack('V', $crc);
				$sGzip .= pack('V', $size);		
			}
		}
		
		$this->_bCompress = true;
		
		return $sGzip;			
	}
	
	public function send($sSender = null)
	{
		$this->_prepareData();

		$oOption = (object) array_merge(array(
			'minify' => false,
			'compress' => false,
			'cache' => false
		), $this->_aOption);
		
		if($oOption->minify)
		{
			$this->_sData = $this->minify($this->_sData);	
		}

		if($oOption->compress)
		{
			$this->setHeaders(array(
					'Vary' => 'Accept-Encoding',
					'Content-Encoding' => $this->_sEncoding,
				)
			);
			
			$this->_sData = $this->compress($this->_sData);
		}
		
		if($oOption->cache)
		{
			$this->cache();	
		}
				
		$this->sendHeaders();	
		
		echo $this->_sData;
		
		$this->_bSent = true;
	}
	
	public function getEncoding()
	{
		return $this->_sEncoding;	
	}
	
	public function cache()
	{
		$iTime = strtotime('+5 Years');
		
		$this->setHeaders(array(
			'Date' => gmdate("D, j M Y G:i:s ", time()) . 'GMT',
			'Last-Modified' => gmdate("D, j M Y G:i:s ", 0) . 'GMT',
			'Expires' => gmdate("D, j M Y H:i:s", $iTime) . " GMT",
			'Cache-Control' => 'public, max-age=' . ($iTime - time()),
			'Pragma' => 'cache'
		));		
	}
	
	public function redirect($sUri = null)
	{
        $this->setHeaders('Referer', Linko::Request()->getAddress());
		$this->setHeaders('Location', Linko::Url()->make($sUri));		
		$this->sendHeaders();
		
		exit;
	}

    public function download($sFile, $sName = null, $aHeaders = array())
    {
        if(is_null($sName))
        {
            $sName = basename($sFile);
        }

        $this->setHeaders(array_merge(array(
            'Content-Description' => 'File Transfer',
            'Content-Type' => File::mime($sFile),
            'Content-Length' => File::size($sFile),
            'Content-Transfer-Encoding' => 'binary',
            'Expires' => 0,
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
            'Content-Disposition' => sprintf('attachment; filename="%s";', str_replace(array('\\', '"'), array('\\\\', '\\"'), $sName))
        ), $aHeaders));

        $this->sendHeaders();

        readfile($sFile);

        exit;
    }

	public function setHeaders($mHeader, $sValue = null)
	{
		if(!is_array($mHeader))
		{
			$mHeader = array($mHeader => $sValue);	
		}
		
		foreach($mHeader as $sHeader => $sValue)
		{
			if(isset($this->_aHeaders[$sHeader]))
			{
				continue;	
			}
			
			$this->_aHeaders[$sHeader] = $sValue;
		}
				
		return $this;
	}
	
	
	public function removeHeader($mHeader = null)
	{
		if(!$mHeader)
		{
			$this->_aHeaders = array();
		}
		
		if(isset($this->_aHeaders[$mHeader]))
		{
			
		}
		
		return $this;
	}

    public function hasHeader($sKey)
    {
        return array_key_exists(strtr(strtolower($sKey), '_', '-'), $this->_aHeaders);
    }

    public function getHeader($sKey)
    {
        return $this->hasHeader($sKey) ? $this->_aHeaders[$sKey] : null;
    }
	
	public function sendHeaders()
	{
		if(headers_sent())
		{
			return;	
		}
		
		# Set Status
        header(sprintf('HTTP/%s %s %s', '1.01', $this->_sStatusCode, $this->_sStatusText));
		
        // Send Header	
		foreach($this->_aHeaders as $sHeader => $sValue)
		{
			header("" . $sHeader . ": " . $sValue);	
		}
		
		return $this;
	}
	
    
	/**
	 * Add content to the output
	 * 
	 * @param mixed $sTarget
	 * @param mixed $mContent
	 * @return void
	 */
	public function inject($sTarget, $mContent)
	{
		$sContent = is_callable($mContent) ? call_user_func($mContent) : $mContent;
		
		$this->_aHtml[$sTarget][] = $sContent; 
	}
	
	public function isSent()
	{
		return $this->_bSent;	
	}
	
	public function getSenders()
	{
		return $this->_aSenders;
	}
		
	private function _prepareData()
	{
		$this->_sData = preg_replace(
			array(
				'/(<\\/head\s*>)/is',
				'/(<body\b[^>]*>)/is', 
				'/(<\\/body\s*>)/is'
			), 
			array(
				'<!--[[head:end]]-->$1',
				'$1<!--[[body:start]]-->',
				'<!--[[body:end]]-->$1'
			),
		$this->_sData);
		
		preg_match_all('/(?:\<\!--\[\[(.*)\]\]--\>)/', $this->_sData, $aMatches);
		
		$aFind = array();
		$aReplace = array();

		if(count($aMatches) && isset($aMatches[0]))
		{
			foreach($aMatches[0] as $iKey => $sFind)
			{
				$aFind[] = $sFind;
				$aReplace[] = implode("\n", (isset($this->_aHtml[$aMatches[1][$iKey]]) ? $this->_aHtml[$aMatches[1][$iKey]] : array()));
			}
		}
		
		$this->_sData = str_replace($aFind, $aReplace, $this->_sData);		
	}
}

?>