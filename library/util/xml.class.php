<?php

class Linko_Xml
{
	private $_aOpenTags = array();
	private $_sTabs;	
	private $_sDoc;
	private $_aXml = array();
	private $_oXml = null;
	private $_sXml;
	private $_aData = array();
	private $_iError = 0;	
	private $_aStack = array();
	private $_sCdata;
	private $_bIncludeFirstTag = false;
	private $_iTagCnt = 0;
	private $_iErrorCode = 0;
	private $_iErrorLine = 0;
	
	public function __construct()
	{
	}
	
	public function setXml($aParams)
	{
		$this->_aXml = $aParams;
	}
	
	public function addGroup($sTag, $aAttr = array())
	{
		$this->_aOpenTags[] = $sTag;
		$this->_sDoc .= $this->_sTabs . $this->_buildTag($sTag, $aAttr) . "\n";
		$this->_sTabs .= "\t";
	}

	public function closeGroup()
	{
		$sTag = array_pop($this->_aOpenTags);
		$this->_sTabs = substr($this->_sTabs, 0, -1);
		$this->_sDoc .= $this->_sTabs . "</$sTag>\n";
	}

	public function addTag($sTag, $sContent = '', $aAttr = array(), $bCdata = false, $bHtmlSpecialChars = false)
	{
		$this->_sDoc .= $this->_sTabs . $this->_buildTag($sTag, $aAttr, ($sContent === ''));
		if ($sContent !== '')
		{
			if ($bHtmlSpecialChars)
			{
				$this->_sDoc .= $this->_htmlSpecialcharsUni($sContent);
			}
			elseif ($bCdata || preg_match('/[\<\>\&\'\"\[\]]/', $sContent))
			{
				$this->_sDoc .= '<![CDATA[' . $this->_escapeCdata($sContent) . ']]>';
			}
			else
			{
				$this->_sDoc .= $sContent;
			}
			$this->_sDoc .= "</$sTag>\n";
		}
		
		return $this;
	}
	
	public function output()
	{
		if (!empty($this->_aOpenTags))
		{
			return trigger_error('Certain tags are still open.', E_USER_ERROR);
		}
		
		$sDoc = (count($this->_aXml) ? '<?xml version="' . $this->_aXml['version'] . '" encoding="' . $this->_aXml['encoding'] . '"?>' . "\n" : '');		
		$sDoc .= rtrim($this->_sDoc);		
		
		$this->_aOpenTags = array();
		$this->_sTabs = '';
		$this->_sDoc = '';
		
		return $sDoc;
	}	

	public function getXml($mFile)
	{		
		if (!preg_match("/<(.*?)>/i", $mFile) && file_exists($mFile))		
		{
			return file_get_contents($mFile);
		}
		
		return $mFile;
	}

	public function parse($mFile, $sEncoding = 'ISO-8859-1', $bEmptyData = true)
	{
		$this->_sXml = $this->getXml($mFile);
		
		if (empty($this->_sXml) || $this->_iError > 0)
		{			
			return false;
		}

		if (!($this->_oXml = xml_parser_create($sEncoding)))
		{			
			return false;
		}
		
		xml_parser_set_option($this->_oXml, XML_OPTION_SKIP_WHITE, 0);
		xml_parser_set_option($this->_oXml, XML_OPTION_CASE_FOLDING, 0);
		xml_set_character_data_handler($this->_oXml, array(&$this, '_handleCdata'));
		xml_set_element_handler($this->_oXml, array(&$this, '_handleElementStart'), array(&$this, '_handleElementEnd'));		
	
		xml_parse($this->_oXml, $this->_sXml);
		
		$bError = xml_get_error_code($this->_oXml);

		if ($bEmptyData)
		{
			$this->_sXml = '';
			$this->_aStack = array();
			$this->_sCdata = '';
		}

		if ($bError)
		{			
			$this->_iErrorCode = @xml_get_error_code($this->_oXml);
			$this->_iErrorLine = @xml_get_current_line_number($this->_oXml);
			
			xml_parser_free($this->_oXml);
			print_r($this->_iErrorLine);
			return trigger_error($this->errorString(), E_USER_ERROR);
		}

		xml_parser_free($this->_oXml);

		return $this->_aData;
	}
	
	public function errorString()
	{		
		if ($sError = xml_error_string($this->_iErrorCode))
		{			
			return $sError;
		}
		else
		{			
			return 'unknown';
		}
	}
	
	public function errorLine()
	{
		if ($this->_iErrorLine)
		{
			return $this->_iErrorLine;
		}
		else
		{
			return 0;
		}
	}

	public function errorCode()
	{
		if ($this->_iErrorCode)
		{
			return $this->_iErrorCode;
		}
		else
		{
			return 0;
		}
	}	

	private function _handleCdata(&$oParser, $sData)
	{
		$this->_sCdata .= $sData;
	}

	private function _handleElementStart(&$oParser, $sName, $aAttributes)
	{
		$this->_sCdata = '';

		foreach ($aAttributes AS $sKey => $sValue)
		{
			if (preg_match('#&[a-z]+;#i', $sValue))
			{
				$aAttributes[$sKey] = unhtmlspecialchars($sValue);
			}
		}

		array_unshift($this->_aStack, array(
			'name' => $sName, 
			'attributes' => $aAttributes, 
			'tag_count' => ++$this->_iTagCnt
		));
	}

	private function _handleElementEnd(&$oParser, $sName)
	{
		$aTag = array_shift($this->_aStack);
		
		if ($aTag['name'] != $sName)
		{
			return;
		}

		$sOutput = $aTag['attributes'];

		if (trim($this->_sCdata) !== '' || $aTag['tag_count'] == $this->_iTagCnt)
		{
			if (sizeof($sOutput) == 0)
			{
				$sOutput = $this->_unescapeCdata($this->_sCdata);
			}
			else
			{
				$this->_addNode($sOutput, 'value', $this->_unescapeCdata($this->_sCdata));
			}
		}

		if (isset($this->_aStack[0]))
		{
			$this->_addNode($this->_aStack[0]['attributes'], $sName, $sOutput);
		}
		else
		{
			if ($this->_bIncludeFirstTag)
			{
				$this->_aData = array($sName => $sOutput);
			}
			else
			{
				$this->_aData = $sOutput;
			}
		}


		$this->_sCdata = '';
	}

	private function _addNode(&$aChildrens, $sName, $sValue)
	{
		if (!is_array($aChildrens) || !in_array($sName, array_keys($aChildrens)))
		{
			$aChildrens[$sName] = $sValue;
		}
		elseif (is_array($aChildrens[$sName]) && isset($aChildrens[$sName][0]))
		{
			$aChildrens[$sName][] = $sValue;
		}
		else
		{
			$aChildrens[$sName] = array($aChildrens[$sName]);
			$aChildrens[$sName][] = $sValue;
		}
	}

	private function _unescapeCdata($sXml)
	{
		static $sFind, $sReplace;

		if (!is_array($sFind))
		{
			$sFind = array('�![CDATA[', ']]�', "\r\n", "\n");
			$sReplace = array('<![CDATA[', ']]>', "\n", "\r\n");
		}

		return str_replace($sFind, $sReplace, $sXml);
	}
	
	private function _buildTag($sTag, $aAttr, $closing = false)
	{
		$tmp = "<$sTag";
		if (!empty($aAttr))
		{
			foreach ($aAttr as $aAttr_name => $aAttr_key)
			{
				if (strpos($aAttr_key, '"') !== false)
				{
					$aAttr_key = $this->_htmlSpecialcharsUni($aAttr_key);
				}
				$tmp .= " $aAttr_name=\"$aAttr_key\"";
			}
		}
		$tmp .= ($closing ? " />\n" : '>');
		return $tmp;
	}

	private function _escapeCdata($sXml)
	{
		$sXml = preg_replace('#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#', '', $sXml);

		return str_replace(array('<![CDATA[', ']]>'), array('�![CDATA[', ']]�'), $sXml);
	}

	private function _htmlSpecialcharsUni($sText, $bEntities = true)
	{
		return str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), preg_replace('/&(?!' . ($bEntities ? '#[0-9]+|shy' : '(#[0-9]+|[a-z]+)') . ';)/si', '&amp;', $sText));
	}
		
}

?>