<?php

class Linko_Plugin extends Linko_Core
{
	private $_aPlugin = array();
	
	private $_aHook = array();
	
	public function __construct()
	{
		$this->_cachePlugins();
	}

	public function getPlugins()
	{
		return $this->_aPlugins;
	}
		
	public function getHooks()
	{
		return $this->_aHook;
	}
	
	public function call($sName)
	{
		list($sModule, $sAction) = array_pad(explode('.', $sName), 2, 'global');
        
        if(!isset($this->_aHook[$sModule]))
		{
			$this->_aHook[$sModule] = array();	
		}
		
		if(!in_array($sAction, $this->_aHook[$sModule]))
		{
			$this->_aHook[$sModule][] = $sAction;
		}
		
		$aArgs = array_slice(func_get_args(), 1);
		
		if(!isset($this->_aPlugin[$sModule]))
		{
			return; 	
		}
		
		foreach($this->_aPlugin[$sModule] as $sPlugin)
		{
			$sPluginModule = strtolower(substr($sPlugin, 0, strpos($sPlugin, 'Plugin_') - 1));
			
			if(!Linko::Module()->isModule($sPluginModule))
			{
				// 	
			}
			
			Linko_Object::map($sPlugin, Linko::Config()->get('dir.module') . $sPluginModule . DS . 'plugin' . DS . $sModule . '.php');
			$oPlugin = Linko_Object::get($sPlugin);
			
			if(class_exists($sPlugin) && method_exists($sPlugin, $sAction))
			{
				call_user_func_array(array($oPlugin, $sAction), $aArgs);
			}
		}
		
		Linko::Profiler()->stop('plugin_call', array('module' => $sModule, 'action' => $sAction));
	}
	
	private function _cachePlugins()
	{
		Linko::Cache()->set(array('application', 'plugin'));
		
		if(!$this->_aPlugin = Linko::Cache()->read())
		{
			foreach(Linko::Module()->getModules() as $sModule => $aModule)
			{
				if(!Linko::Module()->isEnabled($sModule))
				{
					continue;	
				}
				
				$sPath = $aModule['dir'];
				
				if(!Dir::exists($sPath . 'plugin' . DS))
				{
					continue;	
				}
				
				$aFiles = Dir::getFiles($sPath . 'plugin' . DS, false, array('\\' . Linko::Config()->get('ext.plugin') . '$'));
				
				foreach($aFiles as $sFile)
				{
					$sPluginModule = File::name($sFile);
					
					$this->_aPlugin[$sPluginModule][] = Inflector::classify($sModule . '_Plugin_' . $sPluginModule);
				}
			}
			
			Linko::Cache()->write($this->_aPlugin);
		}
	}
}

?>