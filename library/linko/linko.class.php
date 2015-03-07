<?php
 
class Linko
{
	/* 
		HTTP Methods 
	*/	
	const HTTP_METHOD_POST = 'POST';
	
	const HTTP_METHOD_GET = 'GET';
	
	const HTTP_METHOD_DELETE = 'DELETE';
	
	const HTTP_METHOD_PUT = 'PUT';
	
	const HTTP_METHOD_HEAD = 'HEAD';
	
	private $_oDispatcher;
	
	private $_sApp;
	
	private static $_oInstance = null;
	
	private static $_aExtend = array();
	
	/**
	 * Class Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		self::$_oInstance = $this;
	}
	
	public function startApplication()
	{
		// Just for profiling
		Linko::Profiler()->start('Application');
		
		// Start Session Handler
		Linko::Session();
		
		// Loads and Start Application handler
		Linko::Application()->start();
        
        return $this;
	}
	
	public function runApplication($sApp = 'web')
	{
        
        switch($sApp)
        {
            case 'ajax':
                // Ajax calls          
                Linko::Application()->ajax();                 
                break;
            case 'cli':
            
                break;
            case 'web':
            default:
                // Web Frontend        
                Linko::Application()->web();                             
                break;
        }
				
		// Just for profiling
		Linko::Profiler()->stop('Application:' . $sApp);
		
        Linko::Response()->sendHeaders();
        
        // Returns output to client
		Linko::Response()->send();
						
		return $this;
	}
		
	/**
	 * Used to get this class instance
	 * 
	 * @return object or boolean
	 */
	public static function getInstance()
	{
		if(isset(self::$_oInstance))
		{
			return self::$_oInstance;	
		}
		
		return false;
	}

	/**
	 * Extend this class
	 * 
	 * @return void
	 */	
	public static function extend($mMethod, $sClass = null)
	{
		if(!is_array($mMethod))
		{
			$mMethod = array($mMethod => $sClass);	
		}
		
		foreach($mMethod as $sMethod => $sClass)
		{
			$sMethod = strtolower($sMethod);
			
			self::$_aExtend[$sMethod] = $sClass;
		}
	}

	public static function __callStatic($sMethod, $aArgs)
	{
		$sMethod = strtolower($sMethod);
		
		if(isset(self::$_aExtend[$sMethod]))
		{
			if(is_callable(self::$_aExtend[$sMethod]))
			{
				return call_user_func_array(self::$_aExtend[$sMethod], $aArgs);
			}
			else
			{
				return Linko_Object::get(self::$_aExtend[$sMethod]);
			}
		}
		
		return Linko::Error()->trigger('Call to undefined method ' . $sMethod);
	}
}

?>