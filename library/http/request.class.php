<?php

defined('LINKO') or exit('Linko Not Defined!');

class Linko_Request
{
	private $_aServer = array();
	
	private $_sResponse = 200;
	
	private $_aArgs = array();
	
	private $_sUri = null;
	
	public function __construct()
	{
		if(!empty($_SERVER['PATH_INFO']))
		{
			$this->_sUri = $_SERVER['PATH_INFO'];
		}
		else
		{
			if(isset($_SERVER['REQUEST_URI']))
			{
				$this->_sUri = rawurldecode($_SERVER['REQUEST_URI']);	
			}
		}

		$this->_sUri = trim(preg_replace('#' . preg_quote(dirname($_SERVER['SCRIPT_NAME'])) . '#', '', $this->_sUri, 1), '/');

		$this->_aServer = &$_SERVER;

		$this->_aArgs = $this->_clean(array(
				'get' => &$_GET,
				'post' => &$_POST,
			)
		);

		$this->_aArgs['global'] = array_merge(
			$this->_aArgs['get'], 
			$this->_aArgs['post']
		);
	}
	
    /**
    * 	Set a request args manually.
    */
    public function set($mName, $sValue = null)
    {
    	if (!is_array($mName) && $sValue !== null)
    	{
    		$mName = array($mName => $sValue);
    	}
    	
    	foreach ($mName as $sKey => $sValue)
    	{
    		$this->_aArgs[$sKey] = $sValue;
    	}
    }
		
	public function get($mVar, $sDef = null, $sMethod = 'global')
	{
		$sMethod = strtolower($sMethod);
		
		return isset($this->_aArgs[$sMethod][$mVar]) ? $this->_aArgs[$sMethod][$mVar] : ($sDef != null ? $sDef : null);
	}
	
	public function getGET($mVar = null, $sDef = null)
	{
		if(is_null($mVar))
		{
			return $this->_aArgs['get'];
		}
		
		return $this->get($mVar, $sDef, 'get');
	}

	public function getPOST($mVar = null, $sDef = null)
	{
		if(is_null($mVar))
		{
			return $this->_aArgs['post'];
		}
		
		return $this->get($mVar, $sDef, 'post');	
	}

	public function getServer($mVar)
	{
		return isset($this->_aServer[$mVar]) && !empty($this->_aServer[$mVar]) ? $this->_aServer[$mVar] : null;
	}
			
	public function getRequests()
	{
		return $this->_aArgs;
	}
	
	public function getIp()
	{
 		if (PHP_SAPI == 'cli')
		{
			return 0;
		}
 		
 		$sAltIP = $this->_aServer['REMOTE_ADDR'];
 
 		if (isset($this->_aServer['HTTP_CLIENT_IP']))
 		{
 			$sAltIP = $this->_aServer['HTTP_CLIENT_IP'];
 		}
 		elseif (isset($this->_aServer['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $this->_aServer['HTTP_X_FORWARDED_FOR'], $aMatches))
 		{
 			foreach ($aMatches[0] as $sIP)
 			{
 				if (!preg_match("#^(10|172\.16|192\.168)\.#", $sIP))
 				{
 					$sAltIP = $sIP;
 					break;
 				}
 			}
 		}
 		elseif (isset($this->_aServer['HTTP_FROM']))
 		{
 			$sAltIP = $this->_aServer['HTTP_FROM'];
 		}
 
 		return $sAltIP;		
	}

 	public function getSubstrIp($iLength = null)
 	{
 		if ($iLength === null || $iLength > 3)
 		{
 			$iLength = 0;
 		}

 		return implode('.', array_slice(explode('.', $this->getIp()), 0, 4 - $iLength));
 	}
	
	public function isIP($iIp)
	{
		if(preg_match('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', $iIp, $aMatches))
		{
			for($i=1;$i<=4;$i++)
			{
				if (($aMatches[$i] > 255) || ($aMatches[$i] < 0))
				{
					return false;
				}
			}
		}
        else
		{
			return false;
		}

        return true;
	}
	
	public function getUserAgent()
	{
		static $sAgent;
		
		$aUserAgents = array('HTTP_X_DEVICE_USER_AGENT','HTTP_X_OPERAMINI_PHONE_UA', 'HTTP_USER_AGENT');
		
	   	foreach ($aUserAgents as $aUserAgent) 
	   	{
			if (!empty($this->_aServer[$aUserAgent])) 
		  	{
				$sAgent = $this->_aServer[$aUserAgent];
			 	break;
		  	}
		}
		
		return $sAgent;
	}

    function domain($sUrl) 
    { 

	}
	
	public function getDomain()
	{
		if(!strpos($this->getHost(), '.'))
		{
			return $this->getHost();
		}
		
 		$aParts = explode('.', $this->getHost()); 
		
		$iCnt = count($aParts);
		 
		$iCnt -= 3;
		 
		if(strlen($aParts[$iCnt + 2]) == 2) 
		{ 
			$sDomain = $aParts[$iCnt] . '.' . $aParts[$iCnt + 1] . '.' . $aParts[$iCnt + 2]; 
		} 
		else if(strlen($aParts[$iCnt + 2]) == 0) 
		{ 
			$sDomain = $aParts[$iCnt] . '.' . $aParts[$iCnt + 1]; 
		} 
		else 
		{ 
			$sDomain = $aParts[$iCnt + 1] . '.' . $aParts[$iCnt + 2]; 
		} 
		
		return $sDomain; 
	}
		
	public function getHost()
	{
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	}
	
	public function getAddress()
	{
		return 'http' . ($this->isSecure() ? 's' : null) . '://' . $this->getHost();
	}
	
	public function getUri()
	{
		return $this->_sUri;
	}
	
	public function getQuery()
	{
		return $this->getServer('QUERY_STRING');
	}
	
	public function getReferer()
	{
		if($this->getServer('HTTP_REFERER'))
		{
			return $this->getServer('HTTP_REFERER');
		}
		else
		{
			# Try using http.class.php to get the referer
		}
	}
	
	public function getProtocol()
	{
		if($this->getServer('SERVER_PROTOCOL'))
		{
			return $this->getServer('SERVER_PROTOCOL');
		}
		else
		{
			# Try using http.class.php to get the protocol
		}
	}
	
	public function isSecure()
	{
		if((($this->getServer('HTTPS')) AND filter_var($this->getServer('HTTPS'), FILTER_VALIDATE_BOOLEAN)) || ($this->getServer('SERVER_PORT') == 443))
		{
			return true;
		}
		
		return false;
	}

	public function isCli()
	{
		return PHP_SAPI === 'cli';
	}

	public function isXhr()
	{
		return strtolower($this->getServer('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/*
		Alias ->isXhr()
	*/
	public function isAjax()
	{
		return $this->isXhr();
	}
				
    /**
	* 	Receive the raw post data. Useful for services such as REST, XMLRPC and SOAP
	* 	which communicate over HTTP POST but don't use the traditional parameter format.
    */
    function getRawPost()
    {
        return empty($_ENV['RAW_POST_DATA']) ? '' : $_ENV['RAW_POST_DATA'];
    }
		
	public function method()
	{
		return (isset($this->_aServer['REQUEST_METHOD']) ? strtolower($this->_aServer['REQUEST_METHOD']) : 'get');
	}
	
	public function isGet()
	{
		return $this->method() === 'get';	
	}
	
	public function isPost()
	{
		return $this->method() === 'post';	
	}
	
	public function isPut()
	{
		return $this->method() === 'put';	
	}
	
	public function isHead()
	{
		return $this->method() === 'head';	
	}
	
	public function isDelete()
	{
		return $this->method() === 'delete';	
	}
	
	private function _clean($mVars)
	{
		if(is_array($mVars))
		{
			return array_map(array(&$this, '_clean'), $mVars);	
		}
		
		if(get_magic_quotes_gpc())
		{
			stripslashes($mVars);	
		}
		
		$mVars = trim($mVars);
		
		return $mVars;
	}
}

?>