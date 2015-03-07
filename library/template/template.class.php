<?php

class Linko_Template
{
	private $_oEngine;
	
	public function __construct()
	{
		$this->_oEngine = Linko::Config()->get('Template.handler');
		
		if($this->_oEngine == 'default')
		{
			Linko_Object::map('Linko_Application_Template', LINKOBASE.'library/application/template/template.class.php');
			
			$this->_oEngine = 'Linko_Application_Template';
		}
		
		if(!is_object($this->_oEngine))
		{
			$this->_oEngine = new $this->_oEngine;
		}
			
		if(!$this->_oEngine instanceof Linko_Template_Abstract)
		{
			Linko::Error()->trigger('Template Engine: ' . get_class($this->_oEngine) . ' must extend Linko_Template_Abstract.');	
		}
	}
	
	public function getInstance()
	{
		return $this->_oEngine;	
	}
}

?>