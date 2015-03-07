<?php

class Linko_Config
{
	private $_aDefault = array();
	
	private $_aConfig = array();
	
	public function init()
	{
		$this->_aDefault = array(
            'app' => array(
				'delimeter' => '',
			),
			'component' => array(
				'handler' => 'module',
			),
			'ext' => array(
				'theme' => '.phtml',
				'cache' => '.cache.php',
				'log' => '.log.txt',
				'controller' => '.php',
				'model' => '.php',
				'ajax' => '.php',
				'plugin' => '.php',
			),
            'date' => array(
                'format' => 'F j, Y g:i a'
            ),
			'cache' => array(
				'enable' => true,
				'storage' => 'file',
				'prefix' => 'linko_',
			),
			'session' => array(
				'prefix' => 'linko_',
				'storage' => 'default',
			),
  			'cookie' => array(
				//'prefix' => 'linko_',
			),
            'language' => array(
                'handler' => 'default'
            ),
			'template' => array(
				'handler' => 'default',
			),
			'application' => array(
				'handler' => 'default',
			),
			'database' => array(
				'default' => 'default',
				'prefix' => '',
                'driver' => 'mysql',
				'connection' => array(
					'default' => array(
						'host' => '',
						'username' => '',
						'password' => '',
						'database' => '',
						'prefix' => ''
					)
				)
			)
		);
	}
    
	/**
	 * ::Config->set('database.connection', 'value')
	 * ::Config->set('database.connection', array('var' => 'value'))
     * ::Config->set(array('database' => array('connection' => array('var' => 'value'))))
     * 
	 * @param mixed $mVar
	 * @param mixed $mValue
	 * @return void
	 */
	public function set($mVar, $mValue = null)
	{        
        if(is_array($mVar))
        {
             $this->_aConfig = array_merge($this->_aConfig, $mVar); 
        }
        else
        {
            $mVar = strtolower($mVar);
            
    		if(strpos($mVar, '.') === false)
    		{
    			$this->_aConfig[$mVar] = $mValue;	
    		}
    		else
    		{
    			$aParts = explode('.', $mVar, 3);

    			switch(count($aParts))
    			{
    				case 2:

    					$this->_aConfig[$aParts[0]][$aParts[1]] = $mValue;

    				break;
    				case 3:
    					$this->_aConfig[$aParts[0]][$aParts[1]][$aParts[2]] = $mValue;
    				break;
    			}
    		}
        }
	}
	
	public function getConfig()
	{
		return array_merge($this->_aDefault, $this->_aConfig);	
	}
	
	public function get($mVar)
	{
		$mVar = strtolower($mVar);
		$aVars = array();
		$sGroup = null;
		
		if(strpos($mVar, '.') !== false)
		{
			$aVars = explode('.', $mVar, 3);
			$sGroup = $aVars[0];

			if(!isset($this->_aDefault[$sGroup]) && !isset($this->_aConfig[$sGroup]))
			{
				return Linko::Error()->trigger('No group ' . $sGroup);
			}			
		}

		switch(count($aVars))
		{
			case 2:
				if(isset($this->_aConfig[$sGroup]) && array_key_exists($aVars[1], $this->_aConfig[$sGroup]))
				{
					return $this->_aConfig[$sGroup][$aVars[1]];
				}
				else if(isset($this->_aDefault[$sGroup][$aVars[1]]))
				{
					return $this->_aDefault[$sGroup][$aVars[1]];	
				}
			break;
			case 3:
				if(isset($this->_aConfig[$sGroup][$aVars[1]]) && array_key_exists($aVars[2], $this->_aConfig[$sGroup][$aVars[1]]))
				{
					return $this->_aConfig[$sGroup][$aVars[1]][$aVars[2]];
				}
				else if(isset($this->_aDefault[$sGroup][$aVars[1]][$aVars[2]]))
				{
					return $this->_aDefault[$sGroup][$aVars[1]][$aVars[2]];	
				}			
			break;	
		}
		
		return isset($this->_aConfig[$mVar]) ? $this->_aConfig[$mVar] : (isset($this->_aDefault[$mVar]) ? $this->_aDefault[$mVar] : null);
	}
	
	public function has($sConfig)
	{
		return ;	
	}
}

?>