<?php

class Linko_Application
{
	private $_oApplication;
	
	public function __construct()
	{
		$mClass = Linko::Config()->get('Application.handler');
		
        if($mClass == 'default')
        {
            Linko_Object::map('Linko_Application_Module', LINKOBASE.'library/application/application.class.php');
			
			$oApplication = Linko_Object::get('Linko_Application_Module');	            
        }
        else
        {
    		if(is_object($mClass))
    		{
    			$oApplication = $mClass;
    		}
    		else if(class_exists($mClass))
    		{
    			$oApplication = new $mClass;	
    		}           
        }
		
		$this->_oApplication = $oApplication;
	}
	
	public function getInstance()
	{
		return $this->_oApplication;
	}
}

?>