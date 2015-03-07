<?php

class Linko_Application_Template extends Linko_Template_Abstract
{
	/*
		Themes
	*/
	private $_aThemes = array();
	
	/*
		Theme Type
	*/
	private $_sType = 'frontend';
	
	/*
		Theme name
	*/
	protected $_sTheme;

	protected $_sDefaultTheme = 'default';
	
	/*
		Default Layout
	*/
	protected $_sLayout = 'template';
	
	/*
		Layout Folder
		Directory to find layout files
	*/
	protected $_sLayoutDirectory = 'theme_layout';
	
	/*
		Holds List Of Plugins
	*/
	protected $_aPlugins = array();
	
	protected $_aPluginStack = array();
    
    private $_aSetting = array();

    private $_aPathAlias = array();

    private $_aTranslation = array();

	public function __construct()
	{
		parent::__construct();
		
		/*
			Register Inbuilt Plugin 
		*/
		$this->registerPlugin('cycle', APPLICATION_DIR.'template'.DS.'plugin'.DS.'cycle.php');
        $this->registerPlugin('error', APPLICATION_DIR.'template'.DS.'plugin'.DS.'error.php');
        $this->registerPlugin('flash', APPLICATION_DIR.'template'.DS.'plugin'.DS.'flash.php');
        $this->registerPlugin('block', APPLICATION_DIR.'template'.DS.'plugin'.DS.'block.php');
        $this->registerPlugin('breadcrumb', APPLICATION_DIR.'template'.DS.'plugin'.DS.'breadcrumb.php');
        $this->registerPlugin('option', APPLICATION_DIR.'template'.DS.'plugin'.DS.'option.php');
	}
	
	public function setType($sType)
	{
		$this->_sType = $sType;
		
		return $this;	
	}
	
	public function getType()
	{
		return $this->_sType;	
	}
	
    public function addTheme($sType, $aThemes)
    {
        if(!isset($this->_aThemes[$sType]))
        {
            $this->_aThemes[$sType] = array();
        }
        
        $this->_aThemes[$sType] = $aThemes;
        
        return $this;
    }
    
	public function setTheme($sTheme)
	{
        $this->_aThemes[$this->getType()] = $sTheme;
		
		return $this;	
	}

	public function setDefaultTheme($sTheme)
	{
		$this->_sDefaultTheme = $sTheme;

		return $this;
	}

	public function getDefaultTheme()
	{
		return $this->_sDefaultTheme;
	}
	
	public function getTheme()
	{
		$sType = $this->getType();

		return array_key_exists($sType, $this->_aThemes) ? (($sTheme = array_search(true, $this->_aThemes[$sType])) && $sTheme != null ? $sTheme : $this->getDefaultTheme($sType)) : $this->getDefaultTheme($sType);
	}

	public function isTheme($sTheme, $sType = null)
	{
		if($sType)
		{
			return isset($this->_aThemes[$sType][$sTheme]);	
		}
		
		$bExists = false;
		
		foreach(array_keys($this->_aThemes) as $sType)
		{
			if(isset($this->_aThemes[$sType][$sTheme]))
			{
				$bExists = true;
				
				break;	
			}
		}
		
		return $bExists;
	}
	
	public function getThemes()
	{
		return $this->_aThemes;	
	}
		
	public function setTitleDelim($sDelimeter)
	{
		$this->_sTitleDelim = $sDelimeter;
		
		return $this;	
	}

    public function addSetting($sType, $sTheme, $mSetting, $sValue = null)
    {
  		if (!is_array($mSetting))
		{
			$mSetting = array($mSetting => $sValue);
		}
		
		foreach ($mSetting as $sVar => $sValue)
		{
			$this->_aSetting[$sType][$sTheme][$sVar] = $sValue;
		}
        
        return $this;
    }
    
    public function getSetting($sVar)
    {
        return isset($this->_aSetting[$this->getType()][$this->getTheme()][$sVar]) ? $this->_aSetting[$this->getType()][$this->getTheme()][$sVar] : null;
    }

    public function getSettings()
    {
        return $this->_aSetting;
    }

    public function setTranslation()
    {
        $aRef = func_get_args();

        if(func_num_args() == 1 && is_array($aRef[0]))
        {
            $aRef = $aRef[0];
        }

        $aTranslation = array();

        foreach($aRef as $sRef)
        {
            if($sValue = Linko::Language()->translate($sRef))
            {

            }
            else
            {
                $sValue = $sRef;
            }

            $this->_aTranslation[$sRef] = $sValue;
        }

        return $this;
    }

    public function getTranslation()
    {
        $sScript = "var Translation = ({";

        foreach($this->_aTranslation as $sRef => $sTranslation)
        {
            $sScript .= "'" . $sRef . "' : '" . $sTranslation . "', ";
        }

        $sScript = rtrim($sScript, ', ');

        $sScript .= "});";

        return Html::script($sScript, array());
    }

	/**
	 * Gets Controller Content
	 */
	public function getContent()
	{
		return Linko::Module()->getTemplate();		
	}
	
	/**
	 * Gets a template file
	 */
	public function getTemplate($sComponent, $aParams = array(), $bReturn = false)
	{
        $aParams = (array) $aParams;

		$sFile = $this->_getTemplateFile($sComponent);

        $aVars = $this->_aVars;

        if(isset($this->_aVars[$sComponent]))
        {
            $aVars = $this->_aVars[$sComponent];
        }
		
		extract(array_merge($aVars, $aParams));

        if($bReturn)
        {
            ob_start();
        }
            
        require $sFile;

        if($bReturn)
        {
            $sContent = ob_get_clean();

            return $sContent;
        }
	}
	
	public function setLayout($sLayout)
	{
		$this->_sLayout = $sLayout;	
		
		return $this;
	}
	
	public function isLayout($sLayout)
	{
		return File::exists($this->_getLayoutFile($sLayout));
	}
	
	public function getLayout($sLayoutName = null, $aExtra = array())
	{
		extract(array_merge($aExtra, $this->_aVars));

        ($sFile = $this->_getLayoutFile((!is_null($sLayoutName) ? $sLayoutName : $this->_sLayout))) && File::exists($sFile) ? require($sFile) : Linko::Error()->trigger('Layout file not found ' . $sFile);
	}
	
	public function setLayoutDirectory($sDir)
	{
		$this->_sLayoutDirectory = $sDir;
		
		return $this;
	}

	public function getLayoutDirectory($sTheme)
	{
		return $this->_sLayoutDirectory;
	}

	/**
	 * See Linko_Module->getBlock()
	 */
	public function getBlock($sName, $aParam = array(), $bReturn = false)
	{
		return Linko::Module()->getBlock($sName, $aParam, $bReturn);
	}

	/**
	 * See Linko_Module->getBlocks()
	 */
	public function getBlocks($sPosition)
	{
		return Linko::Module()->getBlocks($sPosition);
	}

	/**
	 * See Linko_Module->hasBlock()
	 */
	public function hasBlocks($sPosition)
	{
		return Linko::Module()->hasBlocks($sPosition);
	}

	public function url($mUri, $aParams = array())
	{
		return Linko::Url()->make($mUri, $aParams);
	}

	/**
	 * Returns the full path to a theme.
	 * If no argument is passed, returns the path of current theme set.
	 * Particularly usefull inside theme templates.
	 *
	 * @param string $sType theme type
	 * @param string $sTheme theme name
	 * @return string
	 */
	public function getThemePath($sType = null, $sTheme = null)
    {
        if($sTheme == null)
        {
            $sTheme = $this->getTheme();
        }

        if($sType == null)
        {
            $sType = $this->getType();
        }

        return Linko::Config()->get('dir.theme') . $sTheme . DS . $sType . DS;
    }

	/**
	 * Gets the full url path to a theme.
	 *
	 * @return string
	 */
	public function getThemeUrl()
    {
        return Linko::Url()->path('theme/' . $this->getTheme() . '/' . $this->getType());
    }

	// @Override
	public function findScript($sName, $sLocation, $sType)
	{
		return $this->getPathFromAlias($sName, $sLocation, $sType);
	}

    // @Override
    public function getHeader()
    {
        $this->setHeader($this->getTranslation());

        return parent::getHeader();
    }

	public function getImage($sName, $sLocation)
	{
        return $this->getPathFromAlias($sName, $sLocation, 'image');		
	}
    
    public function setPathAlias($sAlias, $mPath = null)
    {
        $this->_aPathAlias[$sAlias] = $mPath;
        
        return $this;
    }
    
    /**
     * Linko_Application_Template::getPathFromAlias()
     * 
     * @param mixed $mPath
     * @param string $sAlias
     * @param string $sType
     * @return string path to file
     */
    public function getPathFromAlias($mPath, $sAlias, $sType)
    {
        $sPath = null;
		if(preg_match('/^module_(.*)$/', $sAlias, $aMatch))
		{
			$sComponent = trim($aMatch[1]);

            // first check the theme
            $sPath = $this->getTheme() . '/' . $this->getType() . '/module/' . $sComponent . '/asset/' . $sType . '/' . $mPath;

            if(File::exists(Linko::Config()->get('dir.theme') . Dir::fix($sPath)))
            {
                $sPath = Linko::Url()->path('theme') . $sPath;
            }
            else
            {
                $sPath = Linko::Url()->path('module') . $sComponent . '/asset/' . $sType . '/' . $mPath;
            }
		}
		else if($sAlias == 'theme_' . $sType)
		{
			$sPath = $this->getTheme() . '/' . $this->_sType . '/' . $sType . '/' . $mPath;
			
			if(!File::exists(Linko::Config()->get('dir.theme') . Dir::fix($sPath)))
			{
				$sPath = $this->getDefaultTheme() . '/' . $this->_sType . '/' . $sType . '/' . $mPath;
			}

			$sPath = Linko::Url()->path('theme') . $sPath;
		}
		else if($sAlias == 'asset_' . $sType)
		{
			$sPath = Linko::Url()->path('asset') . $sType . '/' . $mPath; 
		}
        else if(isset($this->_aPathAlias[$sAlias]))
        {
            if(is_callable($this->_aPathAlias[$sAlias]))
            {
                $sPath = call_user_func($this->_aPathAlias[$sAlias], $mPath);
            }
        }
        else
        {
            $sPath = parent::findScript($mPath, $sAlias, $sType);
        }
        
        return $sPath;    
    }
    		
	public function plugin($sName, $aProperties = array())
	{
		$sName = trim(strtolower($sName));

		$sClass = Inflector::classify('Template_Plugin_'.$sName);
		
		if((substr($sName, 0, 1)) == '/' && ($sTag = substr($sName, 1)))
		{
			if(($oWidget = array_pop($this->_aPluginStack)) != null)
			{
				$sClass = get_class($oWidget);
				if(substr(strtolower($sClass), strlen('widget')) != $sTag)
				{
					return Linko::Error()->trigger('Wrong nested widgets');
				}
				
				if(method_exists($oWidget, 'end'))
				{
					//$sContent = ob_get_clean();
				
					//$oWidget->end($sContent);
				}
			}
		}
		else
		{			
			if(!class_exists($sClass))
			{
				return Linko::Error()->trigger('Could not load Plugin "' . $sClass . '".');
			}
			
            $oWidget = Linko_Object::get($sClass);
					
			$sResult = $oWidget->start($aProperties);
			
			if(method_exists($oWidget, 'end'))
			{
				//ob_start();
				
				//ob_implicit_flush(false);
				
				$this->_aPluginStack[] = $oWidget;	
			}
			else
			{
				$this->_aPluginStack[] = null;
			}
			
			return $sResult;
		}
	}
	
	public function registerPlugin($sName, $sFile = null)
	{
        $sClass = Inflector::classify('template_plugin_' . $sName);
        
        if($sFile)
        {
            Linko_Object::map($sClass, $sFile);
        }
        
        $this->_aPlugins[$sName] = $sClass;	
	}
		
	private function _getLayoutFile($sLayout)
	{
        $sFile = $this->_getLayoutDirectory($this->getTheme()) . $sLayout . Linko::Config()->get('Ext.theme');
		
		if(!File::exists($sFile))
		{
			$sFile = $this->_getLayoutDirectory($this->getDefaultTheme()) . $sLayout . Linko::Config()->get('Ext.theme');
		}

		return $sFile;
	}
	
	private function _getLayoutDirectory($sTheme)
	{
		$sType = $this->getType() ? DS . $this->getType() . DS : DS;
		
		if($this->_sLayoutDirectory == 'theme_layout')
		{
			$sDirectory = Linko::Config()->get('dir.theme') . $sTheme . $sType . 'layout' . DS;
		}
		else if(preg_match('/^module_(.*)$/', $this->_sLayoutDirectory, $aMatches))
		{
			$sDirectory = Linko::Config()->get('dir.module') . $aMatches[1] . DS . 'layout' . $sType;
		}
		else
		{
			return $this->_sLayoutDirectory;	
		}
		
		return $sDirectory;
	}
	
	private function _getTemplateFile($sTemplate)
	{
		$aParts = explode('/', $sTemplate);
		$sComponent = $aParts[0];
		$sController = substr_replace($sTemplate, '', 0, strlen($sComponent) + 1);
		$sController = str_replace('/', DS, $sController);
		
		$sFile = Linko::Config()->get('dir.theme') . $this->getTheme() . DS . $this->getType() . DS . 'module' . DS . $sComponent . DS . 'template' . DS . $sController . Linko::Config()->get('Ext.theme');
		
		if(!file_exists($sFile))
		{
			$sFile = Linko::Config()->get('dir.module') . $sComponent . DS . 'template' . DS . $sController . Linko::Config()->get('Ext.theme');
		}
		
		if(!file_exists($sFile))
		{
			return Linko::Error()->trigger('Could not load Controller Template: ' . $sFile, E_USER_ERROR);	
		}
		
		return $sFile;		
	}
}

?>