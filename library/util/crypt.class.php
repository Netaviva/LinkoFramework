<?php

class Linko_Crypt
{
	private $_iBlockSize;
	
	private $_sAlgorithm = MCRYPT_RIJNDAEL_256;
	
	private $_sMode = MCRYPT_MODE_CBC;
	
	private $_sKey;
	
	private $_ivSize;
	
	public function __construct()
	{
		if($this->_iBlockSize == null)
		{
			$this->_iBlockSize = mcrypt_get_block_size($this->_sAlgorithm, $this->_sMode);
		}
	
		$this->_ivSize = mcrypt_get_iv_size($this->_sAlgorithm, $this->_sMode);
	}
	
	public function encrypt($sValue, $sKey = null)
	{
		$sKey = ($sKey != null ? $sKey : $this->getKey());
		
		$sIv = mcrypt_create_iv($this->_ivSize, $this->randomize());
	
		$sValue = $this->pad($sValue);
		
		return $this->b64encode($sIv . mcrypt_encrypt($this->_sAlgorithm, $sKey, $sValue, $this->_sMode, $sIv));
	}
	
	public function decrypt($sValue, $sKey = null)
	{
		$sKey = ($sKey != null ? $sKey : $this->getKey());
		
		$sValue = $this->b64decode($sValue);
		
		$sIv = substr($sValue, 0, $this->_ivSize);
		
		$sValue = substr($sValue, $this->_ivSize);
		
		$sValue = mcrypt_decrypt($this->_sAlgorithm, $sKey, $sValue, $this->_sMode, $sIv);
		
		return $this->unpad($sValue);
	}
	
	public function pad($sValue)
	{
		$iPad = $this->_iBlockSize - (strlen($sValue) % $this->_iBlockSize);
		
		return $sValue .= str_repeat(chr($iPad), $iPad);
	}
	
	public function unpad($sPad)
	{
		$iLength = strlen($sPad);
		
		$iPad = ord($sPad[$iLength - 1]);
		
		if($iPad && ($iPad < $this->_iBlockSize))
		{
			if(preg_match('/' . chr($iPad) . '{' . ($iPad) . '}$/', $sPad))
			{
				return substr($sPad, 0, ($iLength - $iPad));	
			}
			else
			{
				throw new Exception("Invalid Cryptic Padding. Crypt algorithm may have changed.");	
			}
		}
	}
	
	public function randomize()
	{
		return (defined('MCRYPT_DEV_URANDOM') ? MCRYPT_DEV_URANDOM : 
			(defined('MCRYPT_DEV_RANDOM') ? MCRYPT_DEV_RANDOM : 
				MCRYPT_RAND));
	}
	
	public function setKey($sKey)
	{
		$this->_sKey = $sKey;
	}
	
	public function getKey()
	{
		return $this->_sKey;
	}

	public function b64encode($sValue)
	{
		$sData = base64_encode($sValue);
		
		$sData = str_replace(array('+','/','='), array('-','_',''), $sData);
		
		return $sData;
	}

	public function b64decode($sValue)
	{
		$sData = str_replace(array('-','_'), array('+','/'), $sValue);
		
		$iMod = strlen($sData) % 4;
		
		if ($iMod)
		{
			$sData .= substr('====', $iMod);
		}

		return base64_decode($sData);
	}
		
	public function setBlockSize($iSize)
	{
		$this->_iBlockSize = $iSize;
	}

	public function getBlockSize($iSize)
	{
		return $this->_iBlockSize;
	}
}

?>