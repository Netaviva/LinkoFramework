<?php

class Linko_Locale_Date
{
    private $_sTimezone;
    
    private $_aTimezones;
    
    public function __construct()
    {
        $this->_buildTimezones();
        
        // Set default timezone to Africa/Lagos
        $this->setTimezone('Africa/Lagos');
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
     * @param int $iOffset timezone offset
     * @return object
     */
    public function setTimezone($sTimezone)
    {
        if(!in_array($sTimezone, $this->getTimezones()))
        {
            return Linko::Error()->trigger('Invalid timezone: ' . $sTimezone); // @todo throw exception
        }
        
        $this->_sTimezone = $sTimezone;
        
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
     * @param int $iOffset timezone offset
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
     * @param int $iOffset timezone offset
     * @return object
     */    
    public function getTime($sFormat = null, $iTime = 'now')
    {
        $iTime = is_int($iTime) ? $iTime : strtotime($iTime);
        
        $oDate = new DateTime(null, new DateTimeZone($this->_sTimezone));
        
        $oDate->setTimestamp($iTime);
        
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