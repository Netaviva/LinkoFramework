<?php

class Linko_Module
{	
	private $_sController;
	
	private $_sModule;
	
	private $_bLoadTemplate = true;
	
	private $_aBlocks = array();
	
	private $_aParams = array();
	
	private $_aModules = array();
	
	private $_aModule = array();
	
	private $_aModels = array();
	
	private $_aNamed = array();
	
	private $_aLoaders = false;
	
	private $_aCallbacks = false;
	
	private $_aSetting = array();
	
	private $_sSettingDelim = '.';
	
	public function __construct()
	{
		$this->_cacheModules();
		
		$this->_cacheSetting();
		
		$this->_aNamed = array(
			'_index_' => 'core/index',
			'_404_' => 'error/404',
		);
	}
	
	public function setAlias($sAlias, $sController)
    {
        $this->_aNamed[$sAlias] = $sController;
        
        return $this;
    }
    	
	/**
	 *	Sets the controller to be loaded
	 */	
	public function set($sController, $aParams = array())
	{
		if(array_key_exists($sController, $this->_aNamed))
		{
			$sController = $this->_aNamed[$sController];	
		}
		
		$aParts = explode('/', $sController);
		
		$this->_sModule = $aParts[0];
		
		$this->_sController = substr_replace($sController, '', 0, strlen($this->_sModule) + 1);
		
		$this->_aParams = $aParams;
		
		$this->getController();
	}

	/**
	 *	Gets Current Controller Name
	 */	
	public function getControllerName()
	{		
		return $this->_sController;
	}

	/**
	 *	Gets Current Module Name
	 */	
	public function getModuleName()
	{		
		return $this->_sModule;
	}
		
	/**
	 *	Loads a controller
	 */	
	public function getController()
	{		
		return $this->load($this->_sModule, $this->_sController, 'controller');
	}

	/**
	 *	Loads a block
	 */	
	public function getBlock($sName, $aParams = array(), $bReturn = false)
	{
		ob_start();
		
		$aParts = explode('/', $sName);
		
		$sModule = $aParts[0];
		
		$sBlock = substr_replace($sName, '', 0, strlen($sModule) + 1);
		
		$this->load($sModule, $sBlock, 'block', $aParams, $bReturn);
		
		$sContent = ob_get_clean();
		
		return $sContent;
	}

	/**
	 * Checks if there are blocks defined for a position.
	 */	 
	public function hasBlocks($sPosition)
	{		
		return (isset($this->_aBlocks[$sPosition]) && count($this->_aBlocks[$sPosition]));
	}
	
	/**
	 * Adds a block
	 *
	 * Usage:
	 * Linko::Module()->setBlocks('position_name', array(
	 *			array('module/block_one'),
	 *			array('module/block_two', array('param' => 'value')),
	 *			array('module/block_three', array('param2' => 'value2'), 'Title Three')
	 * 		)
	 *	);
	 *
	 * @param $sKey the block location
	 * @param $mBlock details about the blocks to load
	 */	
	public function setBlocks($sPosition, $mBlock = array())
	{
		/* 
			Converts setBlocks('sidebar', 'page/user'); to 
			         setBlocks(array(array('page/user', array()));
		*/
		if(!is_array($mBlock))
		{
			$mBlock = array(array($mBlock, array(), null));	
		}
		
		/*
			Converts setBlocks('sidebar', array('page/user', array('param' => 'value'))); to
					 setBlocks('sidebar', array(array('page/user', array('param' => 'value'))));
		*/
		if(count($mBlock) && !is_array($mBlock[0]))
		{
			$mBlock = array($mBlock);
		}

        
		foreach($mBlock as $aBlock)
		{
			if(!is_array($aBlock))
			{
				$aBlock = array($aBlock, array());	
			}
			
			$mBlock = array(
				'block' => $aBlock[0],
				'module' => substr($aBlock[0], 0, strpos($aBlock[0], '/')),
				'param' => isset($aBlock[1]) ? $aBlock[1] : array(),
				'title' => isset($aBlock[2]) ? $aBlock[2] : null,
				
			);
			
            
			$this->_aBlocks[$sPosition][] = $mBlock;
		}

		return $this;
	}
	
	/**
	 * Gets all the blocks for a position.
	 */	 
	public function getBlocks($sPosition)
	{		
		if(isset($this->_aBlocks[$sPosition]))
		{
			return $this->_aBlocks[$sPosition];
		}
		
		return array();
	}
	
	
	/**
	 *	Sets if a controller template should be loaded
	 */	
	public function loadTemplate()
	{
		return $this->_bLoadTemplate;	
	}
	
	/**
	 *	Loads a module component (ie controller or block)
	 */
	public function load($sModule, $sController, $sType = 'controller', $aParams = array(), $bReturn = false)
	{
		if(!$this->isEnabled($sModule))
		{
			return false;	
		}
		
		$sClass = Inflector::classify($sModule . '_' . $sType . '_' . str_replace(array('/', '-'), '_', $sController));
		
		$sHash = md5($sClass . $sType);
		
		$aParams = array_merge(
			$this->_aParams, 
			$aParams, 
			array(
				'linko.module' => $sModule, 
				'linko.controller' => $sController
			)
		);
		
		if(isset($this->_aModule[$sHash]))
		{
			$this->_aModule[$sHash]->__construct($aParams);	
		}
		else
		{	
			$sFile = Linko::Config()->get('dir.module') . $sModule . DS . $sType . DS . str_replace('/', DS, $sController) . '.php';
			
			if(!file_exists($sFile))
			{
				return Linko::Error()->trigger('Controller File: ' . $sFile . ' Not Found', E_USER_ERROR);	
			}
					
			Linko_Object::map($sClass, $sFile);
			
			$this->_aModule[$sHash] = Linko_Object::get($sClass, $aParams);
			
			if(!$this->_aModule[$sHash] instanceof Linko_Controller)
			{
				return Linko::Error()->trigger('Controller Class : ' . get_class($this->_aModule[$sHash]) . ' Must Implement the Linko_Controller Class', E_USER_ERROR);	
			}
			
			if(!method_exists($this->_aModule[$sHash], 'main'))
			{
				return Linko::Error()->trigger('' . get_class($this->_aModule[$sHash]) . '::main() Not Found', E_USER_ERROR);
			}
		}
		
		// execute the main() method
		$mReturn = $this->_aModule[$sHash]->main();
		
		if(is_bool($mReturn) && !$mReturn)
		{
			$this->_bLoadTemplate = false;
			
			if($sType == 'controller')
			{
				Linko::Template()->bLayout = false;	
			}
			
			return $this->_aModule[$sHash];
		}
		
		if($sType == 'block' && $this->_bLoadTemplate)
		{
			Linko::Template()->getTemplate($sModule . '/' . $sType . '/' . $sController);
		}
		
		return $this->_aModule[$sHash];
	}
	
	/**
	 * Gets the template view file for the current controller
	 */
	public function getTemplate()
	{
		if(!$this->isEnabled($this->_sModule))
		{
			return;
		}
		
		if($this->_bLoadTemplate === false)
		{
			return false;
		}
		
		$sModule = $this->_sModule . '/controller/' . $this->_sController;
		
		return Linko::Template()->getTemplate($sModule);
	}
	
	/**
	 *	Gets a model class
	 */	
	public function getModel($sClass, $aParams = array())
	{
		$sClass = strtolower($sClass);
		$sHash = md5($sClass . serialize($aParams));
		
		if (isset($this->_aModels[$sHash]))
		{
			return $this->_aModels[$sHash];	
		}
		
		if (preg_match('/\//', $sClass) && ($aParts = explode('/', $sClass)) && isset($aParts[1]))
		{
			$sModule = $aParts[0];
			$sModel = $aParts[1];
            			
		}
		else 
		{
			$sModule = $sClass;
			$sModel = $sClass;
		}
		
        
		$sModel = str_replace('/', DS, $sModel);
		
		$sFile = Linko::Config()->get('dir.module') . $sModule . DS . 'model' . DS . $sModel . Linko::Config()->get('Ext.model');
		
		if(!file_exists($sFile))
		{
			if (isset($aParts[2]))
			{
				$sFile = Linko::Config()->get('dir.module') . $sModule . DS . 'model' . DS . $sModel . DS . $aParts[2] . Linko::Config()->get('Ext.model');
				
				if(!is_file($sFile))
				{
					
				}
				else
				{
					$sModel .= '_'.$aParts[2];	
				}
			}
			else
			{
				$sFile = Linko::Config()->get('dir.module') . $sModule . DS . 'model' . DS . $sModel . DS . $sModel . Linko::Config()->get('Ext.model');
			}	
		}
		
		if(!is_file($sFile))
		{
			Linko::Error()->trigger('Unable to load model: ' . $sFile, E_USER_ERROR);	
		}
		
		$sName = Inflector::classify($sModule . '_model_' . $sModel);
		
		Linko_Object::map($sName, $sFile);
		
		$this->_aModels[$sHash] = Linko_Object::get($sName, $aParams);		
	
		return $this->_aModels[$sHash];
	}
	
	/**
	 * Calls a method from the module callback
	 * @returns object
	 */
	public function callback($sFunc)
	{
		foreach($this->getCallbacks() as $sClass => $sFile)
		{
			Linko_Object::map($sClass, $sFile);
			
			$oLoader = Linko_Object::get($sClass);
			
			if(method_exists($oLoader, $sFunc))
			{
				$oLoader->$sFunc();	
			}
		}
		
		return $this;
	}
    
    // @todo: remove
    public function simulate($sModule, $aParams = array())
    {
        $this->_aModules[$sModule] = array_merge($aParams, array(
                'module_id' => $sModule,
                'dir' => Linko::Config()->get('dir.module') . $sModule . DS
            )
        );
    }
	/**
	 * Checks if a Module exists
	 * @param $sModule name of the Module
	 * @returns boolean
	 */	
	public function isModule($sModule)
	{
		$sModule = strtolower($sModule);
		
		return isset($this->_aModules[$sModule]) ? true : false;
	}

	/**
	 * Checks if a Module is Enabled (Active)
	 * @param $sModule name of the Module
	 * @returns boolean true if it's enabled or false otherwise
	 */	
	public function isEnabled($sModule)
	{
		return (isset($this->_aModules[$sModule]) && ((bool)$this->_aModules[$sModule]['enabled'])) ? true : false; 
	}
	
	/**
	 * Gets a list of all Modules
	 * @returns array
	 */	
	public function getModules()
	{
		return $this->_aModules;
	}

	/**
	 * Gets a list of all loaders
	 * @returns array
	 */	
	public function getLoaders()
	{
        if(!is_array($this->_aLoaders))
        {
            $this->_cacheLoaders();   
        }
        
		return $this->_aLoaders;
	}

	/**
	 * Gets a list of all callbacks
	 * @returns array
	 */	
	public function getCallbacks()
	{
        if(!is_array($this->_aCallbacks))
        {
            $this->_cacheCallbacks();   
        }
        
		return $this->_aCallbacks;
	}
			
	/**
	 *	Gets a module setting
	 */	
	public function getSetting($mSetting)
	{
		return isset($this->_aSetting[$mSetting]) ? $this->_aSetting[$mSetting] : null;
	}

	public function setSettingType($sValue, $sType)
	{
		$sType = trim($sType);
		
		switch($sType)
		{
			case 'boolean':
			case 'bool':
				$sValue = (bool)$sValue;
			break;
			default:
				$sValue = trim($sValue);
			break;	
		}
		
		return $sValue;
	}
	
	/**
	 *	Set and cache module settings
	 */	
	private function _cacheSetting()
	{
		Linko::Cache()->set(array('application', 'setting'));
		
		if(!$this->_aSetting = Linko::Cache()->read())
		{
            $oDbSetting = Linko::Database()->table('setting');
            
            if($oDbSetting->exists())
            {
    			$aRows = $oDbSetting
                    ->select('setting_id, module_id, setting_var, setting_type, setting_value, setting_value_default')
    				->query()
                    ->fetchRows(); 

    			foreach($aRows as $aRow)
    			{
    				$sVar = (($aRow['module_id'] != '') ? $aRow['module_id'] . $this->_sSettingDelim : null) . $aRow['setting_var'];
    				$this->_aSetting[$sVar] = $this->setSettingType($aRow['setting_value'], $aRow['setting_type']);	
    			}               
            }
			
			Linko::Cache()->write($this->_aSetting);
		}
	}
	
	/**
	 *	Cache all modules
	 */	
	private function _cacheModules()
	{
		$sCacheModules = Linko::Cache()->set(array('application', 'modules'));
				
		if(!Dir::exists(Linko::Config()->get('dir.module')))
		{
			return false;	
		}
		
		if(!$this->_aModules = Linko::Cache()->read($sCacheModules))
		{
			$aModules = array();
			
            if(Linko::Database()->table('module')->exists())
            {
    			$aRows = Linko::Database()->table('module')
                    ->select('*')
    				->query()->fetchRows();
    				
    			foreach($aRows as $aRow)
    			{
    				$aModules[$aRow['module_id']] = array_merge($aRow, array(
    					'dir' => Linko::Config()->get('dir.module') . $aRow['module_id'] . DS,
    				));
    			}                
            }
            				
			Linko::Cache()->write($this->_aModules, $sCacheModules);
		}
        
        $this->_aModules = count($aModules) ? $aModules : array();
	}
    
    private function _cacheLoaders()
    {
        $sCacheLoaders = Linko::Cache()->set(array('application', 'module_loaders'));
        
        if(!$this->_aLoaders = Linko::Cache()->read($sCacheLoaders))
        {
    		foreach($this->_aModules as $sModule => $aModule)
    		{
    			if($aModule['enabled'] == 0)
    			{
    				continue;
    			}
    			
    			$sDir = $aModule['dir'];
    			
    			if(File::exists($sDir . 'loader.php'))
    			{
    				$this->_aLoaders[Inflector::classify($sModule . '_loader')] = $sDir . 'loader.php';
    			}
    		} 
            
            $this->_aLoaders = is_bool($this->_aLoaders) ? array() : $this->_aLoaders;
                        
            Linko::Cache()->write($this->_aLoaders, $sCacheLoaders);	
            	   
        }  
    }
    
    private function _cacheCallbacks()
    {
        $sCacheCallbacks = Linko::Cache()->set(array('application', 'module_callbacks'));
        
        if(!$this->_aCallbacks = Linko::Cache()->read($sCacheCallbacks))
        {
    		foreach($this->_aModules as $sModule => $aModule)
    		{
    			if($aModule['enabled'] == 0)
    			{
    				continue;
    			}
    			
    			$sDir = $aModule['dir'];
    			
    			if(File::exists($sDir . 'callback.php'))
    			{
    				$this->_aCallbacks[Inflector::classify($sModule . '_callback')] = $sDir . 'callback.php';
    			}
    		} 

            $this->_aCallbacks = is_bool($this->_aCallbacks) ? array() : $this->_aCallbacks;
                        
            Linko::Cache()->write($this->_aCallbacks, $sCacheCallbacks);           	   
        }
    }
}

?>