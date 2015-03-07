<?php

class Linko_Language
{
	protected $_sOpenDelim = '{';

	protected $_sCloseDelim = '}';

	protected $_bDebug = false;

	protected $_aTranslation = array();

	protected $_aRules = array();

	protected $_bLoad = false;

	public function __construct()
	{
		$sLocale = $this->getLocale();

		$this->_aTranslation[$sLocale] = $this->getTranslations($sLocale);
	}

	/**
	 * Sets the active locale
	 *
	 * @param string $sLocale
	 * @return \Linko_Language_Abstract
	 */
	final public function setLocale($sLocale)
	{
		Linko::Locale()->setLocale($sLocale);

		return $this;
	}

	/**
	 * Gets the active locale
	 *
	 * @return string
	 */
	final public function getLocale()
	{
		return Linko::Locale()->getLocale();
	}

	final public function setDelim($sOpen = '{', $sClose = '}')
	{
		list($this->_sOpenDelim, $this->_sCloseDelim) = array($sOpen, $sClose);

		return $this;
	}

	final public function getDelim($sDelim = 'open')
	{
		switch($sDelim)
		{
			case 'open':
				return $this->_sOpenDelim;
				break;
			case 'close':
				return $this->_sCloseDelim;
				break;
		}

		return $this;
	}

	public function addTranslation($sLocale, $mVars, $sValue = null)
	{
		if(!is_array($mVars))
		{
			$mVars = array($mVars => $sValue);
		}

		if(!isset($this->_aTranslation[$sLocale]))
		{
			$this->_aTranslation[$sLocale] = array();
		}

		$this->_aTranslation[$sLocale] = array_merge($this->_aTranslation[$sLocale], $mVars);
	}

	public function addRules($sLocale, $mVars, $sValue = null)
	{
		if(!is_array($mVars))
		{
			$mVars = array($mVars => $sValue);
		}

		if(!isset($this->_aRules[$sLocale]))
		{
			$this->_aRules[$sLocale] = array();
		}

		$this->_aRules[$sLocale] = array_merge($this->_aRules[$sLocale], $mVars);
	}

	/**
	 * Checks if a key is translated
	 *
	 * @param string $sVar
	 * @param string $sLocale
	 * @return string
	 */
	final public function isTranslated($sVar, $sLocale = null)
	{
		$sLocale = $sLocale ? $sLocale : $this->getLocale();

		return (isset($this->_aTranslation[$sLocale][$sVar]) ? true : false);
	}

	/**
	 * Enables/Disables/Gets the value of Translation Debugging
	 *
	 * @param bool $bDebug
	 * @return object or boolean
	 */
	final public function debug($bDebug = null)
	{
		if($bDebug == null)
		{
			return $this->_bDebug;
		}

		if(is_bool($bDebug))
		{
			$this->_bDebug = $bDebug;
		}

		return $this;
	}

	public function translate($sVar, $aParams = array(), $sLocale = null)
	{
		$sLocale = $sLocale ? $sLocale : $this->getLocale();

		if(!isset($this->_aTranslation[$sLocale]))
		{

		}

		if(isset($this->_aTranslation[$sLocale][$sVar]) && ($sTranslation = $this->_aTranslation[$sLocale][$sVar]))
		{
			$aFind = array();
			$aReplace = array();

			if(isset($this->_aRules[$sLocale][$sVar]))
			{
				$iCount = 0;
				$iTotal = count($this->_aRules[$sLocale][$sVar]);

				$sCode = "\$sTranslation = (";

				foreach($aParams as $sKey => $sValue)
				{
					$aFind[] = $this->_sOpenDelim . $sKey . $this->_sCloseDelim;
					$aReplace[] = "'" . $sValue . "'";
				}

				foreach($this->_aRules[$sLocale][$sVar] as $sCond => $sValue)
				{
					$iCount++;

					$sCond = str_replace($aFind, $aReplace, $sCond);

					$sCode .= ($iCount != 1 ? '(' : '') .  "(" . $sCond . ") ? '" . $sValue . "' : ";
				}

				$sCode .= "\$sTranslation" . str_repeat(')', $iTotal) . ";";

				eval($sCode);
			}

			foreach($aParams as $sKey => $sValue)
			{
				$aFind[] = $this->_sOpenDelim . $sKey . $this->_sCloseDelim;
				$aReplace[] = $sValue;
			}

			$sTranslation = str_replace($aFind, $aReplace, $sTranslation);

			if($this->debug())
			{
				$sTranslation = '{:' . $sTranslation . ' [' . $sVar . ' ' . Html::attribute($aParams) . ']:}';
			}

			return $sTranslation;
		}

		if($this->debug())
		{
			return '{' . $sVar . ' [' . Html::attribute($aParams) . ']}';
		}

		return ;
	}

	public function getTranslations($sLocale)
	{
		return isset($this->_aTranslation[$sLocale]) ? $this->_aTranslation[$sLocale] : array();
	}

	public function getRules($sLocale)
	{
		return isset($this->_aRules[$sLocale]) ? $this->_aRules[$sLocale] : array();
	}
}

?>