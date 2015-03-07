<?php

class Linko_Locale
{
    /**
     * Holds all supported locales
     * 
     * @var array
     */
    private $_aLocales = array();
    
    /**
     * Holds a supported locale identifier
     * 
     * @var string
     */   
    private $_sSupport;
    
    /**
     * Sets the default locale
     * 
     * @var string
     */
    private $_sDefault = 'en_GB';
    
    /**
     * Constructor
     * 
     */
    public function __construct($sSupport = null)
    {
        // Add en_GB Locale
        $this->addLocale('en_GB', array(
            'title' => 'English (British)',
            'charset' => 'utf-8', 
            'direction' => 'ltr'
        ));
        
        if($sSupport)
        {
            $this->_sSupport = $sSupport;
        }
    }
    
    /**
     * Set the default locale
     * 
     * @return object
     */
    final public function setLocale($sLocale)
    {
        $this->_sDefault = $sLocale;
        
        return $this;
    }

    /**
     * Get the default locale
     * 
     * @return boolean
     */
    final public function getLocale($bDetail = false)
    {
        return ($bDetail ? $this->_aLocales[$this->_sDefault] : $this->_sDefault);
    }

    /**
     * Get all supported locales
     * 
     * @return boolean
     */
    final public function getLocales($bDetail = false)
    {
        return ($bDetail ? $this->_aLocales : array_keys($this->_sDefault));
    }

    /**
     * Check if a locale is supported
     * 
     * @return boolean
     */
    final public function isLocale($sLocale)
    {
        return array_key_exists($sLocale, $this->_aLocales) ? true : false;
    }
              
    /**
     * Adds localization support for a country
     * 
     * @return object
     */
    final public function addLocale($sLocale, array $aParam)
    {
        $this->_aLocales[$sLocale] = array_merge($aParam, array(
            'iso' => $sLocale
        ));
        
        return $this;
    }
    
    public function __call($sMethod, $aArguments)
    {
        $oClass = Linko_Object::get(Inflector::classify('Linko_Locale_' . $this->_sSupport));
        
        if($this->_sSupport && method_exists($oClass, $sMethod))
        {
            return call_user_func_array(array($oClass, $sMethod), $aArguments);
        }
        
        return Linko::Error()->trigger('Call to undefined method: ' . __CLASS__ . '::' . $sMethod . '()');
    }
}

?>