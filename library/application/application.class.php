<?php

class Linko_Application_Module extends Linko_Application_Abstract
{	
	public function start()
	{
        define('APPLICATION_DIR', LINKOBASE . 'library' . DS . 'application' . DS);
        
        // Autoload
		Linko_Object::map(array(
				'Linko_Module' => APPLICATION_DIR . 'module/module.class.php',
				
				'Linko_Controller' => APPLICATION_DIR . 'module/controller.class.php',
				
				'Linko_Model' => APPLICATION_DIR . 'module/model.class.php',
				
				'Linko_Active_Model' => APPLICATION_DIR . 'module/activemodel.class.php',
				
				'Linko_Plugin' => APPLICATION_DIR . 'module/plugin.class.php',
                
                'Linko_Ajax' => APPLICATION_DIR . 'ajax/ajax.class.php'
			)
		);
		
        // Config
        Linko::Config()->set(array(
            'dir' => array(
				'vendor' => Dir::fix(APP_PATH.'system/vendor/'),
				'storage' => Dir::fix(APP_PATH.'storage/'),
				'session_save_path' => Dir::fix(APP_PATH.'storage/session/'),
				'cache' => Dir::fix(APP_PATH.'storage/cache/'),
				'tmp' => Dir::fix(APP_PATH.'storage/tmp/'),
				'log' => Dir::fix(APP_PATH.'storage/log/'),
				'module' => Dir::fix(APP_PATH.'module/'),
				'asset' => Dir::fix(APP_PATH.'asset/'),
				'theme' => Dir::fix(APP_PATH.'theme/')            
            )
        ));
        
        Linko::extend('ajax', 'Linko_Ajax');
        
		// add method Linko::Plugin() for Linko_Plugin class
		Linko::extend('plugin', 'Linko_Plugin');
		
		// Add Method Linko::Module() or Linko::Module('module_name')
		Linko::extend('module', function($sModule = null)
		{
			$oObject = Linko_Object::get('Linko_Module');
			
			if($sModule)
			{
				/*
					@todo implement this - will produce access to module details and functions
                    example usage:
                        Linko::Module('MyModule')->getInfo()
                        Linko::Module('MyModule')->getSetting()
                        Linko::Module('MyModule')->getDescription()
					return $oObject->getModule($sModule);	
				*/
			}
			
			return $oObject;
		});
		
		// add method Linko::Model() or Linko::Model('module/model')
		Linko::extend('model', function($sModel = null)
		{		
			$oObject = Linko_Object::get('Linko_Module');
			
			if($sModel)
			{
				return $oObject->getModel($sModel);	
			}
				
			return $oObject;	
		});	
	}
	
    /**
     * Application ajax interface
     * 
     * @return string
     */
    public function ajax()
    {
        $oModule = Linko::Module();
        $oRequest = Linko::Request();

        // Require Loaders: A File that is executed on every page
        foreach($oModule->getLoaders() as $sFile)
        {
            require($sFile);
        }

        if(!$oRequest->isAjax())
        {
            exit('Invalid Ajax Request.');
        }

        $sAction = $oRequest->get('action');
        $aParam = $oRequest->get('param');

        $mData = null;
               
        list($sModule, $mFunc) = array_pad(explode('/', $sAction, 2), 2, NULL);

        if($sModule == null || $mFunc == null)
        {
            return;
        }
        
		$sFile = Linko::Config()->get('dir.module') . $sModule . DS . 'ajax' . DS . 'ajax' . Linko::Config()->get('Ext.ajax');

        if(File::exists($sFile))
		{
            require_once $sFile;
            
            $sClass = Inflector::classify($sModule . '_Ajax');
            
            if(class_exists($sClass))
            {
                /**
                 * @var $oClass Linko_Ajax
                 */
                $oClass = new $sClass($aParam);

                $oClass->$mFunc();
                
                list($mData, $sType) = $oClass->getAjax();

                switch($sType)
                {
                    case 'json':
                        Linko::Response()->setHeaders("Content-type", "application/x-json");
                        break;
                    case 'xml':
                        Linko::Response()->setHeaders("Content-type", "application/xml");
                        break;
                    default:
                        Linko::Response()->setHeaders('Content-type', 'text/html');
                        break;
                }
            }
		}
        
        Linko::Response()->setData($mData);      
    }

    /**
     * Application web interface
     * 
     * @return void
     */    
	public function web()
	{
		$oRoute = $this->getRoute();

		$oTemplate = Linko::Template();

		$oModule = Linko::Module();

		$oPlugin = Linko::Plugin();

		$oResponse = Linko::Response();

		ob_start();
        
		// Require Loaders: A File that is executed on every page 
		foreach($oModule->getLoaders() as $sFile)
		{
			require($sFile);
		}
        
	    $oPlugin->call('application.before_controller', $oRoute->controller);

		$oModule->set($oRoute->controller, $oRoute->args);

		$oPlugin->call('application.module_' . $oModule->getModuleName() . '_controller', $oRoute->controller);

        $oPlugin->call('application.after_controller', $oRoute->controller);
        
		if($oTemplate->displayLayout())
		{
            if(($sLoader = Linko::Config()->get('dir.theme') . $oTemplate->getTheme() . DS . $oTemplate->getType() . DS . 'loader.php') && File::exists($sLoader))
            {
                require($sLoader);
            }

			$oPlugin->call('application.before_layout');

			$oTemplate->getLayout();
            
            $oPlugin->call('application.after_layout');
		}
        
		$sData = ob_get_clean();
		
		if(!$oResponse->getStatus())
		{
			$oResponse->setStatusCode(200);
		}
		
		if(!$oResponse->getData())
		{
			$oResponse->setData($sData);
		}		
	}
}