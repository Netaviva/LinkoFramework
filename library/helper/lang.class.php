<?php

class Lang
{
    public static function t($sVar, $aParams = array(), $sLocale = null)
    {
        return Linko::Language()->translate($sVar, $aParams, $sLocale);
    }
}

?>