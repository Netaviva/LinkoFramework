<?php

class Linko_Module
{	
	private $_sController;
	
	private $_sModule;
	
	private $_bLoadTemplate = true;
	
	private $_aBlocks = array();
	
	private $_aParams = array();
    
    public $_aGlobalParams = array();
	
	private $_aModules = array();
	
	private $_aModule = array();
	
	private $_aModels = array();
	
	private $_aNamed = array();
	
	private $_aLoaders = array();
	
	private $_aCallbacks = false;
	
	private $_aSetting = array();
	
	private $_sSettingDelim = '.';
	
	public function __construct()
	{
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

		return true;
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
    
	public function setParam($mParams, $sValue = null)
	{
		if (!is_array($mParams))
		{
			$mParams = array($mParams => $sValue);
		}
		
		foreach ($mParams as $sVar => $sValue)
		{
			$this->_aGlobalParams[$sVar] = $sValue;
		}
        
        return $this;
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
	 *            array('module/block_one'),
	 *            array('module/block_two', array('param' => 'value')),
	 *            array('module/block_three', array('param2' => 'value2'), 'Title Three')
	 *         )
	 *    );
	 *
	 * @param string $sPosition position to assign blocks to
	 * @param array|\details $mBlock block params
	 *
	 * @internal param \the $sKey block location
	 * @return Linko_Application_Module
	 */
	public function setBlocks($sPosition, $mBlock = array())
	{
		/* 
			Converts setBlocks('sidebar', 'page/user'); to 
			         setBlocks('sidebar', array(array('page/user', array()));
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

            $sModule = substr($aBlock[0], 0, strpos($aBlock[0], '/'));
            $aParam = isset($aBlock[1]) ? $aBlock[1] : array();
            $sTitle = isset($aBlock[2]) ? $aBlock[2] : null;

			$mBlock = array(
				'block' => $aBlock[0],
				'module' => $sModule,
				'param' => $aParam,
				'title' => $sTitle,
                'content' => $this->getBlock($aBlock[0], $aParam)
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
	 * Loads a module component (ie controller or block)
	 *
	 * @param        $sModule
	 * @param        $sController
	 * @param string $sType
	 * @param array  $aParams
	 * @param bool   $bReturn
	 *
	 * @return bool
	 */
	public function load($sModule, $sController, $sType = 'controller', $aParams = array(), $bReturn = false)
	{
		if(!$this->isModule($sModule))
		{
			return false;	
		}
		
		$sClass = Inflector::classify($sModule . '_' . $sType . '_' . str_replace(array('/', '-'), '_', $sController));
		
		$sHash = md5($sClass . $sType);
        
		$aParams = array_merge(
            $this->_aGlobalParams,
			$aParams,
			array(
				'linko.module' => $sModule,
				'linko.controller' => $sController,
                'linko.type' => $sType
			)
		);

        if($sType == 'controller')
        {
            $aParams = array_merge($this->_aParams, $aParams);
        }

		if(isset($this->_aModule[$sHash]))
		{
			$this->_aModule[$sHash]->__construct($aParams);	
		}
		else
		{	
			$sFile = Linko::Config()->get('dir.module') . $sModule . DS . $sType . DS . str_replace('/', DS, $sController) . '.php';
			
			if(!File::exists($sFile))
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
		if(!$this->isModule($this->_sModule))
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
	 * Checks if a module task exists
	 *
	 * @param $sModule
	 * @param $sTask
	 *
	 * @return bool
	 */
	public function hasTask($sModule, $sTask)
	{
        $sModel = $this->_getModelFile($sModule, 'Task');

        if(File::exists($sModel))
        {
            $oTask = $this->getModel($sModule . '/Task');

            if(method_exists($oTask, $sTask))
            {
                return true;
            }
        }

		return false;
	}

	/**
	 * Executes a task in a module
	 *
	 * @param $sModule module id
	 * @param $sTask method name
	 *
	 * @return mixed
	 */
	public function callTask($sModule, $sTask)
	{
		$oTask = $this->getModel($sModule . '/Task');

        $aArgs = array_slice(func_get_args(), 2);

        return call_user_func_array(array($oTask, $sTask), $aArgs);
	}

    /**
     * Add Module
     * 
     * @param string $sModule The module id
     * @param array $aParams Module Parameters
     * @return object
     */
    public function add($sModule, $aParams = array())
    {
        $this->_aModules[$sModule] = array_merge(array(
            'enabled' => false,
        ), $aParams, array(
            'module_id' => $sModule,
            'dir' => Linko::Config()->get('dir.module') . $sModule . DS
        ));
        
        return $this;
    }
	/**
	 * Checks if a Module exists
	 * @param $sModule name of the Module
	 * @return boolean
	 */	
	public function isModule($sModule)
	{
		$sModule = strtolower($sModule);
		
		return isset($this->_aModules[$sModule]) ? true : false;
	}
	
	/**
	 * Gets a list of all Modules
	 * @return array
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
        Linko::Cache()->set(array('application', 'module_loaders'));
        
        if(!$this->_aLoaders = Linko::Cache()->read())
        {
            $aLoaders = array();
            
    		foreach($this->_aModules as $sModule => $aModule)
    		{
    			if($aModule['enabled'] == 0)
    			{
    				continue;
    			}
    			
    			$sDir = $aModule['dir'];
    			
    			if(File::exists($sDir . 'loader.php'))
    			{
    				$aLoaders[$sModule] = $sDir . 'loader.php';
    			}
    		} 
            
            $this->_aLoaders = $aLoaders;
            
            Linko::Cache()->write($this->_aLoaders);	   
        }
        
		return $this->_aLoaders;
	}
    		
	/**
	 *	Gets a module setting
	 */	
	public function getSetting($mSetting)
	{
		return isset($this->_aSetting[$mSetting]) ? $this->_aSetting[$mSetting] : null;
	}
    
    public function addSetting($mSetting, $sValue = null)
    {
        if(!is_array($mSetting))
        {
            $mSetting = array($mSetting => $sValue);
        }
        
        foreach($mSetting as $sKey => $sValue)
        {
            $this->_aSetting[$sKey] = $sValue; 
        }
        
        return $this;
    }
    
	public function setSettingType($sValue, $sType)
	{
		$sType = trim($sType);
		
		switch($sType)
		{
			case 'int':
			case 'integer':
				settype($sValue, 'integer');
				break;
			case 'boolean':
			case 'bool':
				settype($sValue, 'boolean');
			break;
			case 'string':
			case 'text':
			case 'longtext':
			case 'longstring':
			default:
				$sValue = trim($sValue);
			break;	
		}
		
		return $sValue;
	}

    public function serializeTables($mTables)
    {
        if(!is_array($mTables))
        {
            $mTables = array($mTables);
        }

        $aTables = array();

        foreach($mTables as $sTable)
        {
            $aTables += Linko::Database()->table($sTable)->export()->toArray();
        }

        return serialize($aTables);
    }

    /**
     * Gets a model class
     *
     * @param       $sClass
     * @param array $aParams
     *
     * @return object
     */
    public function getModel($sClass, $aParams = array())
    {
        $sClass = strtolower($sClass);
        $sHash = md5($sClass . serialize($aParams));

        if (isset($this->_aModels[$sHash]))
        {
            return $this->_aModels[$sHash];
        }

        if (preg_match('/\//', $sClass) && ($aParts = explode('/', $sClass, 2)) && isset($aParts[1]))
        {
            $sModule = $aParts[0];
            $sModel = $aParts[1];
        }
        else
        {
            $sModule = $sClass;
            $sModel = $sClass;
        }

        $sFile = $this->_getModelFile($sModule, $sModel);

        if(!File::exists($sFile))
        {
            Linko::Error()->trigger('Unable to load model: ' . $sFile, E_USER_ERROR);
        }

        $sName = Inflector::classify($sModule . '_model_' . $sModel);

        Linko_Object::map($sName, $sFile);

        $this->_aModels[$sHash] = Linko_Object::get($sName, $aParams);

        return $this->_aModels[$sHash];
    }

    private function _getModelFile($sModule, $sModel)
    {
        $sModel = strtolower(str_replace('/', DS, $sModel));

        $sFile = Linko::Config()->get('dir.module') . $sModule . DS . 'model' . DS . $sModel . Linko::Config()->get('Ext.model');

        if(!File::exists($sFile))
        {
            if (isset($aParts[2]))
            {
                $sFile = Linko::Config()->get('dir.module') . $sModule . DS . 'model' . DS . $sModel . DS . $aParts[2] . Linko::Config()->get('Ext.model');

                if(File::exists($sFile))
                {
                    $sModel .= '_'.$aParts[2];
                }
            }
            else
            {
                $sFile = Linko::Config()->get('dir.module') . $sModule . DS . 'model' . DS . $sModel . DS . $sModel . Linko::Config()->get('Ext.model');
            }
        }

        return $sFile;
    }
}

?>