<?php

class Linko_Locale_Date
{
    private $_sTimezone;
    
    private $_aTimezones;
    
    public function __construct()
    {
        $this->_buildTimezones();
    }
    
    public function getTimezones($sSeparator = null)
    {
        $aTimezones = array();
        
        $sSeparator = $sSeparator ? $sSeparator : '/';

        foreach($this->_aTimezones as $iOffset => $sTimezone)
        {
            $sZone = implode($sSeparator, explode('/', $sTimezone));
                
            $aTimezones[$iOffset] = $sZone;
        }
        
        return $aTimezones;
    }

    /**
     * Sets the default timezone offset
     *
     * @param string $sTimezone
     * @return Linko_Locale_Date
     */
    public function setTimezone($sTimezone)
    {
        $this->_sTimezone = $sTimezone;

        date_default_timezone_set($sTimezone);
        
        return $this;
    }
 
     /**
     * Gets the current timezone
     * 
     * @return integer
     */   
    public function getTimezone()
    {
        return $this->_sTimezone;
    }

    /**
     * Gets the timezone offset
     *
     * @return object
     */    
    public function getOffset()
    {
        $oTimezone = new DateTimeZone($this->_sTimezone);
        
        $iOffset = $oTimezone->getOffset(new DateTime(null)) / 3600;
        
        return $iOffset;        
    }

    /**
     * Gets the current time and formats it.
     * Takes language and other localized formatting into consideration.
     * 
     * @param string $sFormat Date time format
     * @param mixed $mTime Unix time or str time
     * @return mixed
     */    
    public function getTime($sFormat = null, $mTime = 'now')
    {
        $bInt = (int)$mTime;
        
        $mTime = $bInt ? $mTime : strtotime($mTime);

        $oDate = new DateTime(null, new DateTimeZone(($this->_sTimezone == null ? date_default_timezone_get() : $this->_sTimezone)));
        
        $oDate->setTimestamp($mTime);
        
        $mTime = $oDate->getTimestamp();
        
        if($sFormat)
        {
            $mTime = $oDate->format($sFormat);   
        }
        
        return $mTime;
    }
    
    private function _buildTimezones()
    {
        if(method_exists('DateTimeZone', 'listIdentifiers'))
        {
            $aTimezones = DateTimeZone::listIdentifiers();
            
            sort($aTimezones);
            
            foreach($aTimezones as $iOffset => $sTimezone)
            {
                if (preg_match('/^(Africa|America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $sTimezone)) 
                { 
                    $this->_aTimezones[$iOffset] = $sTimezone;
                    
                    unset($aTimezones[$iOffset]);                 
                }
            }   
        }        
    }
}

?>