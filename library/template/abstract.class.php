<?php

abstract class Linko_Template_Abstract implements Linko_Template_Interface
{
	protected $_bShowLayout = true;
	
	protected $_aTitle = array();
	
	protected $_aScripts = array();
	
	protected $_aVars = array();
	
	protected $_aHeader = array();
	
	protected $_aFooter = array();
	
	protected $_aStyles = array();
	
	protected $_aScript = array();
	
	protected $_aMeta = array();
	
	protected $_aHeaders = array();
	
	protected $_iCacheTime = 60;
		
	/*
		Holds Breadcrumb Links and Breadcrumb title
	*/
	protected $_aBreadcrumb = array();

	public function __construct()
	{
		$this->_aBreadcrumb['title'] = null;
		$this->_aBreadcrumb['links'] = array();	
	}
	
	public function setTheme($sTheme)
	{
		$this->_sTheme = $sTheme;
		
		return $this;	
	}

	public function getTheme()
	{
		return $this->_sTheme;
	}
		
	public function setLayout($sLayout)
	{
		$this->_sLayout = $sLayout;
		
		return $this;
	}
	
	public function setScript($mScript, $sPath = null, $sLocation = 'header')
	{
		$this->setClientScript($this->_aScript, $mScript, $sPath, $sLocation);
		
		return $this;
	}

	public function getScript($sLocation = 'header', $bReturn = false)
	{	
		return $this->getClientScript($this->_aScript, $sLocation, $bReturn, 'js');		
	}

	public function clearScript($mScript = null, $sPath = null)
	{
		$this->clearClientScript($this->_aScript, $mScript, $sPath);
				
		return $this;
	}
			
	public function setStyle($mCss, $sPath = null, $sLocation = 'header')
	{
		$this->setClientScript($this->_aStyles, $mCss, $sPath, $sLocation);
		
		return $this;
	}

	public function getStyle($sLocation = 'header', $bReturn = false)
	{
		return $this->getClientScript($this->_aStyles, $sLocation, $bReturn, 'css');	
	}
	
	public function clearStyle($mStyle = null, $sPath = null)
	{
		$this->clearClientScript($this->_aStyles, $mStyle, $sPath);
				
		return $this;
	}

    /**
     * Sets the point in the header you want to place this item.
     * Positions are labelled 0 - 100
     *
     * @param mixed $mHeaders header content
     * @param int $iPos
     * @return Linko_Template_Abstract
     */
    public function setHeader($mHeaders = null, $iPos = 20)
	{
		if($iPos > 100)
		{
			$iPos = 100;
		}

		$this->_aHeader[$iPos][] = $mHeaders;

        return $this;
	}

	public function getHeader()
	{
		$sHeader = null;

        $this->setHeader($this->getMeta(false), 10);
        $this->setHeader($this->getStyle('header', false), 50);
        $this->setHeader($this->getScript('header', false), 50);

        ksort($this->_aHeader);

        $aHeaders = Arr::flatten($this->_aHeader);

		foreach($aHeaders as $aHeader)
		{
			if(!is_array($aHeader))
			{
				$aHeader = array($aHeader);	
			}
			
			foreach($aHeader as $mKey => $sValue)
			{
				if(is_numeric($mKey))
				{
					if ($sValue === null)
					{
						continue;
					}				

					$sHeader .= $sValue . "\n";
				}
			}
		}

	    $this->_aHeader = array();
		
		return $sHeader;
	}
		
	public function setFooter($mFooter)
	{
		$this->_aFooter[] = $mFooter;
	}

	public function getFooter()
	{
		$sFooter = '';
		
		$sFooter .= $this->getStyle('footer', false) . "\n";
		$sFooter .= $this->getScript('footer', false) . "\n";
		
		foreach($this->_aFooter as $aFooter)
		{
			if(!is_array($aFooter))
			{
				$aFooter = array($aFooter);	
			}
			
			foreach($aFooter as $mKey => $sValue)
			{
				if(is_numeric($mKey))
				{
					if ($sValue === null)
					{
						continue;
					}				

					$sFooter .= "\t\t" . $sValue . "\n";	
				}
			}
		}
		
		$this->_aFooter = array();
		
		return $sFooter;		
	}
	
	public function setVars($mVar, $mValue = null)
	{
		if(!is_array($mVar))
		{
			$mVar = array($mVar => $mValue);
		}
		
		foreach($mVar as $sVar => $sValue)
		{
            if(is_object($mValue) && $mValue instanceof Linko_Controller)
            {
                $this->_aVars[$mValue->getPath()][$sVar] = $sValue;
            }
            else
            {
                $this->_aVars[$sVar] = $sValue;
            }
		}
		
		return $this;
	}
	
	public function resetVar($mVar = null)
	{
		if($mVar)
		{
			if(!is_array($mVar))
			{
				$mVar = array($mVar);	
			}
			
			foreach($mVar as $sVar => $sValue)
			{
				if(isset($this->_aVars[$sVar]))
				{
					unset($this->_aVars[$sVar]);	
				}
			}	
		}
		
		$this->_aVars = array();
		
		return $this;
	}

	public function getVars()
	{
		return $this->_aVars;	
	}
	
	public function setTitle()
	{
		$aTitle = func_get_args();
		
		if(func_num_args())
		{
			if(is_array($aTitle[0]))
			{
				$aTitle = $aTitle[0];	
			}
			
			foreach($aTitle as $sTitle)
			{
				$this->_aTitle[] = $sTitle;	
			}
		}
		return $this;
	}
	
	public function getTitle($sDelim = '&raquo;')
	{
		$sTitle = '';
		$iCnt = 0;
		$iTotal = count($this->_aTitle);
		
		$sTitle = ($sTitle != '' ? $sDelim : null);
		
		if($iTotal)
		{
			foreach($this->_aTitle as $mTitle)
			{
				$iCnt++;
				$sTitle .= $mTitle . ' ' . (($iCnt == $iTotal) ? '' : $sDelim . ' ');
			}
		}		
		
		return $sTitle;
	}

	public function setMeta($mMeta, $mValue = null)
	{
		if(!is_array($mMeta))
		{
			$mMeta = array($mMeta => $mValue);	
		}
		
		$aNCMeta = array('keywords', 'description');
		
		foreach($mMeta as $sKey => $mValue)
		{
			if(isset($this->_aMeta[$sKey]))
			{
				if($sKey == 'keywords')
				{
					$this->_aMeta[$sKey]['content'] .= ', ' . $mValue;
				}
			}
			else
			{
				if(in_array($sKey, $aNCMeta))
				{
					$this->_aMeta[$sKey]['name'] = $sKey;
					
					if(!is_array($mValue))
					{
						$this->_aMeta[$sKey]['content'] = $mValue;
					}
				}
				else
				{		
					$this->_aMeta[$sKey] = $mValue;
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Shortcut for (Template)->setMeta('description', 'Meta Description')
	 */
	public function setMetaDescription($sDescription)
	{
		$this->setMeta('description', $sDescription);
		
		return $this;	
	}

	/**
	 * Shortcut for (Template)->setMeta('keywords', 'Meta, key, words')
	 */
	public function setMetaKeywords($sKeywords)
	{
		$this->setMeta('keywords', $sKeywords);
		
		return $this;	
	}
		
	/**
	 *
	 */
	public function getMeta($bReturn = false)
	{
		if($bReturn)
		{
			return $this->_aMeta;	
		}
		
		$sMeta = null;
		
		if(count($this->_aMeta))
		{
			foreach($this->_aMeta as $sKey => $mMeta)
			{
				switch($sKey)
				{
					case 'keywords':
						
						break;
					case 'description':
					
						break;	
				}
				
				$sMeta .= Html::tag('meta', null, $mMeta) . Str::NEWLINE;
			}
		}

		return $sMeta;
	}
	
	public function setBreadcrumb($mLinks, $sTitle = null)
	{
        if($sTitle)
        {
            $this->setBreadcrumbTitle($sTitle);
        }

        $this->setBreadcrumbLinks($mLinks);
		
		return $this;
	}
	
	public function getBreadcrumb()
	{
		return $this->_aBreadcrumb;	
	}
	
	public function setBreadcrumbTitle($sTitle)
	{
		$this->_aBreadcrumb['title'] = $sTitle;
		
		return $this;	
	}
		
	public function setBreadcrumbLinks($mParam, $sLink = null)
	{
		if(!is_array($mParam))
		{
			$mParam = array($mParam => $sLink);	
		}
		
		foreach($mParam as $sTitle => $sLink)
		{
			$this->_aBreadcrumb['links'][$sTitle] = $sLink;	
		}
		
		return $this;
	}
	
	public function getBreadcrumbLinks()
	{
		if(!isset($this->_aBreadcrumb['links']))
		{
			return array();	
		}
			
		foreach($this->_aBreadcrumb['links'] as $sTitle => $sLink)
		{
			if(is_numeric($sTitle))
			{
				$this->_aBreadcrumb['links'][$sLink] = null;
				
				unset($this->_aBreadcrumb['links'][$sTitle]);
			}
		}
		
		return $this->_aBreadcrumb['links'];
	}
	
	public function getBreadcrumbTitle()
	{
		return isset($this->_aBreadcrumb['title']) ? $this->_aBreadcrumb['title'] : '';	
	}
	
	/*
		Checks if any breadcrumb has been set for the page
	*/
	public function hasBreadcrumb()
	{
		return count($this->_aBreadcrumb) ? true : false;	
	}

	/*
		Checks if any breadcrumb links has been set for the page
	*/
	public function hasBreadcrumbLinks()
	{
		return count($this->_aBreadcrumb['links']) ? true : false;	
	}

	/*
		Checks if any breadcrumb title has been set for the page
	*/
	public function hasBreadcrumbTitle()
	{
		return count($this->_aBreadcrumb['title']) ? true : false;	
	}		
	/*
	 * Gets the location of a client side script
	 * This method may or should be overriden by the child classes
	 * Example of how this can be overriden to better suite your application interface
     * can be seen with the default application template class
	 *
	 * @param $sPath Name representing the location
	 * @param $sType Type of client script 'css' or 'js'
	 */	
	public function findScript($sFile, $sPath, $sType)
	{
		return $sPath . $sFile;
	}

	/**
	 * Enables and disbles display of template layout.
	 * If set to false, the template layout for that controller will not be rendered
	 * else if true, will display the layout file for the controller.
	 */
	public function displayLayout($mShow = null)
	{
		if(is_bool($mShow))
		{
			$this->_bShowLayout = $mShow;
			
			return $this;	
		}
		
		return $this->_bShowLayout;
	}
	
	protected function setClientScript(&$aHolder, $mScript, $sPath, $sLocation)
	{
		if(is_string($mScript))
		{
			$mScript = array($mScript);
		}

		if(!isset($aHolder[$sLocation]))
		{
			$aHolder[$sLocation] = array();
		}

		if(!isset($aHolder[$sLocation][$sPath]))
		{
			$aHolder[$sLocation][$sPath] = array();	
		}
				
		foreach($mScript as $sScript)
		{
			$aHolder[$sLocation][$sPath][$sScript] = array();		
		}
		
		return $this;		
	}
	
	protected function getClientScript(&$aHolder, $sLocation = 'header', $bReturn = false, $sType = null)
	{
		if($bReturn)
		{
			$aScript = array();
            
			if(isset($aHolder[$sLocation]))
			{
				foreach($aHolder[$sLocation] as $sPath => $aScripts)
				{
				    foreach($aScripts as $sName => $aParam)
					{
						$aScript[$sPath][$sName] = $this->findScript($sName, $sPath, $sType);
					}
				}
				
				return Arr::flatten($aScript);
			}
			
			return $aScript;
		}
				
		$sScript = null;
		
		$iCount = count($aHolder);

		if(isset($aHolder[$sLocation]))
		{
			$sHtml = ($sType == 'js') ? Html::script(null, array('src' => '%s', 'type' => 'text/javascript')) : Html::tag('link', null, array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => '%s'));
			 	
			foreach($aHolder[$sLocation] as $sPath => $mScript)
			{
				foreach($mScript as $sName => $aParam)
				{
				    if($sLocation = $this->findScript($sName, $sPath, $sType))
					{
						$sScript .= sprintf($sHtml, $sLocation) . "\n";
					}
				}
			}
		}
		
		return $sScript;		
	}
	
	protected function clearClientScript(&$aHolder, $mScript, $sPath)
	{
		if($mScript === null)
		{
			$aHolder = array();
		}
		
		if(!is_array($mScript))
		{
			$mScript = array($mScript);
		}
		
		foreach($mScript as $sScript)
		{
			if($sPath != null)
			{
				foreach(array_keys($aHolder) as $sLocation)
				{
					unset($aHolder[$sLocation][$sPath][$sScript]);
				}
			}
			else
			{
				foreach($aHolder as $sLocation => $aScript)
				{
					foreach(array_keys($aScript) as $sPath)
					{
						unset($aHolder[$sLocation][$sPath][$sScript]);
					}
				}
			}
		}		
	}
}

?>