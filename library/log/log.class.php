<?php

class Linko_Log
{
	const EMERGENCY = 0;

	const ALERT = 1;

	const CRITICAL = 2;

	const ERROR = 3;

	const WARNING = 4;

	const NOTICE = 5;

	const INFO = 6;

	const DEBUG = 7;

	private $_oWriter;

	public function __construct()
	{
		$this->_aLevel = array(
			self::EMERGENCY => 'emergency',
			self::ALERT => 'alert',
			self::CRITICAL => 'critical',
			self::ERROR => 'error',
			self::WARNING => 'warning',
			self::NOTICE => 'notice',
			self::INFO => 'info',
			self::DEBUG => 'debug'
		);
	}

	public function setWriter(Linko_Log_Writer_Interface $oWriter)
	{
		$this->_oWriter = $oWriter;

		return $this;
	}

	public function log($iLevel, $sMsg, array $aParam = array())
	{
		if(!$this->_oWriter instanceof Linko_Log_Writer_Interface)
		{
			return Linko::Error()->trigger('Invalid or no log writer specified.');
		}

		if(!is_string($sMsg))
		{
			//return Linko::Error()->trigger('Log message must be a string');
		}

		$this->_oWriter->write(array(
			'time' => Date::now(),
			'message' => $this->interpolate($sMsg, $aParam),
			'level' => $iLevel,
			'name' => $this->_aLevel[$iLevel]
		), $aParam);
	}

	public function emergency($sMsg, array $aParam = array())
	{

	}

	public function alert($sMsg, array $aParam = array())
	{
		$this->log(self::ALERT, $sMsg, $aParam);

		return $this;
	}

	public function critical($sMsg, array $aParam = array())
	{

	}

	public function error($sMsg, array $aParam = array())
	{

	}

	public function warning($sMsg, array $aParam = array())
	{

	}

	public function notice($sMsg, array $aParam = array())
	{

	}

	public function info($sMsg, array $aParam = array())
	{
		$this->log(self::INFO, $sMsg, $aParam);

		return $this;
	}

	public function debug($sMsg, array $aParam = array())
	{

	}

    public function interpolate($sMsg, $aParam)
    {
        foreach($aParam as $sFind => $sReplace)
        {
            $aParam['{' . $sFind . '}'] = $sReplace;
        }

        return str_replace(array_keys($aParam), array_values($aParam), $sMsg);
    }

	public function __call($sMethod, $aArgs)
	{
		if(!$this->_oWriter instanceof Linko_Log_Interface)
		{
			return Linko::Error()->trigger('Invalid Log Writer');
		}

		if(!method_exists($this->_oWriter, $sMethod))
		{
			return Linko::Error()->trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()');
		}

		return call_user_func_array(array($this->_oWriter, $sMethod), $aArgs);
	}
}