<?php

class Linko_Shortcode
{
	private $_aTags = array();
	
	private $_sStartTag = '[';
	
	private $_sEndTag = ']';
	
	/**
	 * Contructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		
	}

	/**
	 * Add shortcode
	 *
	 *  <code> 
	 *      function makeBold($content) 
	 *		{
	 *          return '<b>' . $content . '</b>';
	 *      }
	 *      
	 *      // Add shortcode [b]Hello[/b]
	 *		Linko::Shortcode()->add('b', 'makeBold');
	 *  </code>
	 *
	 * @param string $sTag Shortcode tag to be searched in content.
	 * @param string $sCallback The callback function to replace the shortcode with.
     * @return object
	 */		 	
	public function add($sTag, $sCallback)
	{
		// make sure the tag is a string
		$sTag = (string)$sTag;
		
		if(!is_callable($sCallback, true, $sName))
		{
			return trigger_error('Trying to assign a non-callable function "' . $sName . '" to tag: ' . $sTag, E_USER_WARNING);	
		}
		
		$this->_aTags[$sTag] = $sCallback;
        
        return $this;
	}

	/**
	 * Remove shortcode tag
	 *
	 * @param string $sTag Shortcode tag to be removed.
	 */		
	public function remove($sTag)
	{
		if(isset($this->_aTags[$sTag]))
		{
			unset($this->_aTags[$sTag]);
		}	
	}
	
	/**
	 * Parses a string by searching for any registered shortcodes and replacing it with the result of the mapped callback.
	 *
	 *  <code> 
	 *      $sContent = Linko::Shortcode()->parse($sContent);
	 *  </code>
	 *
	 * @param  string $mInput Content
	 * @return string
	 */
	public function parse($mInput)
	{
		if(!count($this->_aTags))
		{
			return $mInput; 
		}
		
		$sTags = implode('|', array_map('preg_quote', array_keys($this->_aTags)));
		
		// Build Shortcode pattern	 
		$sPattern = 
			'#'
			
				. '(\\' . $this->_sStartTag . '?)' // 1. Optional openining tag for escaping shortcode.

				. 		'\\' . $this->_sStartTag // Start Tag
				
				.			'(' . $sTags . ')' // 2. All shortcode registered tags
				
				.			'\\b(.*?)(\/)?' // 3. 4. Word boundary + any content within
				
				.		'\\' . $this->_sEndTag // End Tag
				
				.			'(?(4)|'
				
				. 				'(?:' 
				
				.					'((?:[^\\[]*+|\[(?!\/?(?:\\2)])|(?R))+)' // 5. Capture Content
									
				.					'\\' . $this->_sStartTag . '\/\s*\\2\s*\\' . $this->_sEndTag // Matching Close Tag
				
				. 				')' 
				
				.			')?'
				
				. '(\\' . $this->_sEndTag . '?)' .  // 6. Optional closing tag for escaping shortcode.
				
			'#';
		
		return preg_replace_callback($sPattern, array(&$this, '_parseTag'), $mInput);
	}
	 
	private function _parseTag($mInput)
	{
		if(is_array($mInput))
		{
			list(
				$sData, 
				$sPrefix, 
				$sTagName, 
				$sAttribute, 
				$sInput,
				$sSuffix
			) = array(
				$mInput[0],
				$mInput[1], 
				$mInput[2], 
				$mInput[3], 
				$mInput[5], 
				$mInput[6],
			);
			 
			// Allow for escaping shortcodes using double tags [[shortcode]]
			if($sPrefix == $this->_sStartTag && $sSuffix == $this->_sEndTag)
			{
				return '[' . substr($sData, 2, -2) . ']';
			}
			
			$aAttr = array();
			
			if(!empty($sAttribute))
			{
				$aAttr = $this->_parseAttribute($sAttribute);
			}

			if(strpos($sInput, $this->_sStartTag) !== false)
			{
				$sInput = $this->parse($sInput);	
			}
						
			$sRes = $sPrefix . call_user_func($this->_aTags[$sTagName], $sInput, $aAttr, $sTagName) . $sSuffix;
			
			return $sRes;					
		}	
	}
	
	private static function _parseAttribute($sAttr)
	{
		$aAttr = array();
		
		if(preg_match_all('/(\w+) *= *(?:([\'"])(.*?)\\2|([^ "\'>]+))/', $sAttr, $aMatches, PREG_SET_ORDER))
		{
			foreach($aMatches as $aMatch)
			{
				$sKey = $aMatch[1];
				$sValue = (isset($aMatch[4]) && !empty($aMatch[4]))	? $aMatch[4] : (isset($aMatch[3]) ? $aMatch[3] : null);
				
				$aAttr[$sKey] = trim($sValue); 
			}
		}
		else
		{
				
		}
		
		return $aAttr;
	}
}

?>