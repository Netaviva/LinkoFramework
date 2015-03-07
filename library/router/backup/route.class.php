<?php

class Linko_Route extends Linko_Core
{
	const TYPE_HOST = 'ROUTE_HOST';
	const TYPE_PATH = 'ROUTE_PATH';
	
	const REGEX_DELIM = '#';
	const REGEX_KEY     = '\[([a-zA-Z0-9_]++)\]';
	const REGEX_ANY = "([^/,;?\n]+)";
	const REGEX_INT = "([0-9]+)";
	const REGEX_ALPHA = "([a-zA-Z_-]+)";
	const REGEX_ALPHANUMERIC = "([0-9a-zA-Z_-]+)";
	const REGEX_YEAR = "(^[\d]{4}+)";
	const REGEX_MONTH = "(0[1-9]|1[0|1|2]+)";
	const REGEX_DAY = "(0[1-9]|[12][0-9]|3[01]+)";
	const REGEX_ACTIONS = 'add|edit|view|delete|del|remove';
	
	private $_aNamedPatterns = array(
		'int' => self::REGEX_INT,
		'integer' => self::REGEX_INT,
		'alpha' => self::REGEX_ALPHA,
		'alnum' => self::REGEX_ALPHANUMERIC,
		'alphanum' => self::REGEX_ALPHANUMERIC,
		'alphanumeric' => self::REGEX_ALPHANUMERIC,
		'any' => self::REGEX_ANY,
		'action' => self::REGEX_ACTIONS,
		'year' => self::REGEX_YEAR,
		'month' => self::REGEX_MONTH,
		'day' => self::REGEX_DAY,
	);
	
	private $_sId;
	
	private $_aDefault = array();
	
	private $_sBase = '';
		
	private $_sRegex;
	
	private $_sRawRegex;
	
	private $_sController;
	
	private $_aRules;
	
	private $_sType;
	
	/**
	 * Short Description for the route
	 */
	private $_sTitle;
	
	public function __construct($aParam)
	{
		
		$this->_sId = $aParam['id'];
		
		$this->_sBase = Linko::Router()->getBase();
		
		$this->_sRawRegex = $aParam['regex'];
		
		$this->_sType = $aParam['type'];
		
		$this->_sController = $aParam['controller'];
		
		$this->_aRules = $aParam['rules'];
		
		$this->_aDefault = $aParam['default'];
		
		$this->_sRegex = $this->parse($this->_sRawRegex, $this->_aRules, $this->_sType);
	}

	public function getId()
	{
		return $this->_sId;	
	}
		
	public function getRegex()
	{
		return $this->_sRegex;	
	}
	
	public function getRawRegex()
	{
		return $this->_sRawRegex;	
	}
		
	public function getType()
	{
		return $this->_sType;	
	}
		
	public function getRules()
	{
		return $this->_aRules;	
	}

	public function getFile()
	{
		return $this->_sFile;	
	}
		
	public function getController()
	{
		return $this->_sController;	
	}

	public function uri($aParams = array())
	{
		$sRegex = $this->_sRawRegex;
		if(strpos($sRegex, '[') === FALSE AND strpos($sRegex, '(') === FALSE)
		{
			
		}

		if($this->_sType == self::TYPE_HOST)
		{
			$sRegex = preg_replace('#^' . self::REGEX_ANY . '\.domain#', '', $sRegex);	
		}
		
		while(preg_match('#\([^()]++\)#', $sRegex, $aMatches))
		{
			$sSearch = $aMatches[0];
			$sReplace = substr($aMatches[0], 1, -1);

			while(preg_match('#' . self::REGEX_KEY . '#', $sReplace, $aMatches2))
			{
				list($sTag, $sKey) = $aMatches2;
				
				if (isset($aParams[$sKey]))
				{
					$sReplace = str_replace($sTag, $aParams[$sKey], $sReplace);
                    
                    unset($aParams[$sKey]);
				}
				else
				{
					$sReplace = '';
					break;
				}				
			}
			
			$sRegex = str_replace($sSearch, $sReplace, $sRegex);
		}
		
		while(preg_match('#' . self::REGEX_KEY . '#', $sRegex, $aMatches2))
		{
			list($sTag, $sKey) = $aMatches2;
			
			if(!isset($aParams[$sKey]))
			{
				$aParams[$sKey] = '';
				if(isset($this->_aDefault[$sKey]))
				{
					$aParams[$sKey] = $this->_aDefault[$sKey];
				}
				else
				{
						
				}
			}
	
			$sRegex = str_replace($sTag, $aParams[$sKey], $sRegex);
            
            unset($aParams[$sKey]);
		}
		
        $sQuery = http_build_query($aParams);
        
		return ltrim($sRegex, '/') . (($sQuery) ? '?' . $sQuery : null);
	}
	
	public function parse($sRegex, $aRules, $sType)
	{		
		if($sType == self::TYPE_HOST)
		{
			$sRegex = str_replace('domain', Linko::Request()->getDomain(), $sRegex);			
		}

		if($sRegex == '' || $sRegex == '/')
		{
			return self::REGEX_DELIM . '^' . $this->_sBase . '/$' . self::REGEX_DELIM;
		}
					
		$sRegex = trim($sRegex, '/');

		if(strpos($sRegex, '(') !== false)
		{
			$sRegex = str_replace(array('(', ')'), array('(?:', ')?'), $sRegex);	
		}
					
		$sRegex = str_replace(array('[', ']'), array('(?P<', '>' . self::REGEX_ANY . ')'), $sRegex);

		$aSearch = array();
		$aReplace = array();
		
		if(isset($aRules) && (is_array($aRules)))
		{	
			foreach($aRules as $sKey => $sRule)
			{
				$aSearch[]  = "<" . $sKey . ">" . self::REGEX_ANY;
						
				if(substr($sRule, 0, 1) == ':')
				{
					$sName = substr($sRule, 1);
					
					if(isset($this->_aNamedPatterns[$sName]))
					{
						$aReplace[] = "<" . $sKey . ">" . $this->_aNamedPatterns[$sName] . "";
					}
					else
					{
						$aReplace[] = "<" . $sKey . ">" . $this->_aNamedPatterns['any'] . "";
					}
				}
				else
				{
					$aReplace[] = "<" . $sKey . ">" . $sRule . "";
				}
			}
		}
		
		$sRegex = str_replace($aSearch, $aReplace, $sRegex);
		
		$sRegex = self::REGEX_DELIM . '^' . $sRegex . '$' . self::REGEX_DELIM . 'x';
		
		return $sRegex;
	}
}


?>