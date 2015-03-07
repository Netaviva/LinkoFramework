<?php

abstract class Linko_Session_Abstract  implements Linko_Session_Interface
{
	protected $_sPrefix;
	
	public function __construct()
	{
		$this->_sPrefix = Linko::Config()->get('session.prefix');
	}
    
    public function setSavePath($sPath)
    {
        return;
    }
}

?>