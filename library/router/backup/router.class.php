<?php

/*
	The Route takes care of parsing the request and figures returns parameters 
	that tells the dispatcher what action to take.
*/

class Linko_Router extends Linko_Core
{
	private $_sBase = null;
	
	private $_oInfo = array();
	
	private $_iRoutes = 0;
	
	private $_sKey = 'linko';
	
	private $_aCompiled = array();
	
	private $_aRoutes = array();
					
	public function __construct()
	{		
		
	}
	
	/*
		Build Routes
	*/
	public function prepare()
	{
		return $this;	
	}
	
	public function getBase()
	{
		return $this->_sBase;	
	}

	public function setBase($sBase)
	{
		$this->_sBase = $sBase;
		
		return $this;	
	}
	
	public function setKey($sKey)
	{
		$this->_sKey = $sKey;
		
		return $this;	
	}
	
	public function getKey()
	{
		return $this->_sKey;	
	}
    
	public function getKeys()
	{
		return array_keys($this->_aRoutes);
	}
	
	/**
	 * Checks if routes has been defined
	 * 
	 * @param string $sKey
	 * @return boolean
	 */
	public function hasRoutes($sKey = null)
	{
		$sKey = $sKey ? $sKey : $this->_sKey;
		
		return count($this->_aRoutes[$sKey]) > 0;
	}

	/*
		Alias For Router->add('/', array('controller' => 'mycontroller'));
	*/
	public function index($aParams = array())
	{
		$this->_addRoute('/', $aParams);
		
		return $this;
	}
			
	public function add($sRegex, $aParams = array())
	{
		$this->_addRoute($sRegex, $aParams);
		
		return $this;
	}
	
	public function route($sUri)
	{
		
		$this->_oInfo = $this->_process($sUri);
		
		/* @todo */
		return $this->_oInfo;
	}
	
	public function getInfo()
	{
		return $this->_oInfo;	
	}
	
	public function getId()
	{
		return $this->_oInfo->id;	
	}
	
	public function toUrl($sId, $aParams = array(), $bFull = true)
	{
		foreach($this->getKeys() as $sKey)
		{
			if(isset($this->_aRoutes[$sKey][$sId]))
			{
				return ($bFull ? Linko::Url()->base() : null) . $this->_aRoutes[$sKey][$sId]->uri($aParams);
			}
		}

		return false;
	}
	
	public function getRoutes($sKey = null)
	{
		if($sKey && isset($this->_aRoutes[$sKey]))
		{
			return $this->_aRoutes[$sKey];	
		}
		
		return $this->_aRoutes;	
	}
	
	private function _process($sUri)
	{
		if(empty($sUri))
		{
			$sUri = '/';	
		}
		
		$bPass = false;
		$oRes = null;
		
		foreach($this->_aRoutes[$this->_sKey] as $sId => $oRoute)
		{
			$sUrl = rtrim($sUri, '/'); 
			if($oRoute->getType() == Linko_Route::TYPE_HOST)
			{
				$sUrl = Linko::Request()->getHost() . ((strlen($sUrl) > 0) ? '/' . $sUrl : $sUrl);	
			}
			else
			{
				$sUrl = ($this->_sBase ? $this->_sBase . '/' : null) . $sUrl;	
			}
			
			if($sUrl == '')
			{
				$sUrl = '/';	
			}
			
			$sRegex = $oRoute->getRegex();
			
			$sController = $oRoute->getController();
			
			if(preg_match($sRegex, $sUrl, $aMatches))
			{
				foreach($aMatches as $sKey => $sMatch)
				{
					if(is_numeric($sKey))
					{
						unset($aMatches[$sKey]);	
					}
				}
				
				$bPass = true;
				
				return (object)array(
					'id' => $sId,
					'controller' => $sController, 
					'args' => $aMatches,
					'route' => $oRoute
				);
				
				break;
			}
		}
        		
		return (object)array(
			'id' => null,
			'controller' => '_404_',
			'file' => '',
			'args' => array('url' => $sUri),
			'route' => null,
		);
	}
	
	private function _addRoute($sRoute, $aParams = array())
	{
		$aParams = array_merge(array(
			'controller' => '',
			'id' => ++$this->_iRoutes,
			'type' => Linko_Route::TYPE_PATH,
			'rules' => array(),
			'default' => array(),
		), $aParams);
		
		$sBase = null;
		
		if($aParams['type'] != Linko_Route::TYPE_HOST)
		{
			$sBase = $this->_sBase . '/';
			
			if($sRoute == '/')
			{
				$sBase = null;
			}
		}
					
		$aParams['regex'] = $sBase . $sRoute;
		$aParams['title'] = isset($aParams['title']) ? $aParams['title'] : $aParams['id'];
		
		$this->_aRoutes[$this->_sKey][$aParams['id']] = new Linko_Route($aParams);
	}
}

?>