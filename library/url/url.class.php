<?php

class Linko_Url
{
	private $_aQuery;
	
	private $_aSegments = array();
	
	public function __construct()
	{
		$this->_rebuild();
	}
	
	public function getFull($bQuery = false)
	{
        $sQuery = Linko::Request()->getQuery();
        
		return $this->base() . trim(implode('/', $this->_aSegments), '/') . (($bQuery && $sQuery) ? '?' . $sQuery : '');
	}
	
	public function set($mReq, $sValue = null)
	{
		if(!is_array($mReq))
		{
			$mReq = array($mReq => $sValue);	
		}
		
		foreach($mReq as $sReq => $sValue)
		{
			$this->_aSegments[$sReq] = $sValue;
		}
	}
	
	public function segment($sReq)
	{
		return isset($this->_aSegments[$sReq]) ? $this->_aSegments[$sReq] : null;	
	}
	
	public function reset($sReq = null)
	{
		if(!is_null($sReq))
		{
			unset($this->_aSegments[$sReq]);	
		}
		
		$this->_aSegments = array();
		
		$this->_rebuild();
	}
	
	public function getSegments()
	{
		return $this->_aSegments;	
	}
	
	// Creates a url
	public function make($mUri = null, $aParams = array())
	{
		// if the url already containes http|ftp|ssl|udp|tcp, just return it
		if(preg_match('/^http|ftp|ssl|udp|tcp\:\/\//', $mUri))
		{
			return $mUri;	
		}
		
		// If $mUri is 'self' return the current address
		if($mUri == 'self')
		{
			return $this->getFull(true);
		}

		// if $mUri exists in the routed urls, use the router to build the url
		if($sUri = Linko::Router()->toUrl($mUri, $aParams))
		{
			return $sUri;
		}

		return $this->base() . $mUri;
	}
	
	public function base()
	{
        return Linko::Request()->getAddress() . '/' . ((($sExtra = preg_replace('/\/$/', '', str_replace(
                array(DS, str_replace(DS, '/', $_SERVER['DOCUMENT_ROOT'])),
                array('/', ''),
                APP_PATH
            ))) && $sExtra) ? trim($sExtra, '/') . '/' : '');
	}
	
	/**
	 * Converts a directory path to url address
	 */
	public function path($sPath = null)
	{
		if($sPath == null)
		{
			return $this->base();	
		}
		
		// If the path is a root path, remove the root directory it
		$sPath = str_replace(APP_PATH, '', $sPath);
		
		$sPath = APP_PATH . $sPath;
		
		return trim(str_replace(array(APP_PATH, DS), array($this->base(), '/'), $sPath), '/') . '/';
	}
	
	// Rebuilds url segments
	private function _rebuild()
	{
		$aParts = explode('/', Linko::Request()->getUri());
		
		$iCnt = 0;
		foreach($aParts as $sPart)
		{
			$iCnt++;
			$this->_aSegments[$iCnt] = $sPart;
		}	
	}
}

?>