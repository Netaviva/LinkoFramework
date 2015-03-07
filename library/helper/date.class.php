<?php

class Date
{
    private static $_aMonthDays =  array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	/**
	 * @param string $sFormat Date String Format eg Y-m-D
	 *
	 * @return int
	 */
	public static function now($sFormat = null)
    {
        return Linko::Locale('date')->getTime($sFormat, 'now');
    }
    
	public static function getTimezones()
	{
		return Linko::Locale('date')->getTimezones();
	}
    
	public static function setTimezone($sTimezone)
	{
		Linko::Locale('date')->setTimezone($sTimezone);
	}
    
    public static function getTime($sFormat = null, $iTime = 'now')
    {
        return Linko::Locale('date')->getTime($sFormat, $iTime);
    }
    
    public static function getOffset()
    {
        return Linko::Locale('date')->getOffset();
    }

    public static function isLeapYear($iYear = null)
    {
        if($iYear == null)
        {
            $iYear = Date::now('Y');
        }

        return $iYear % 400 == 0 || ($iYear % 4 == 0 AND $iYear % 100 != 0) ? true : false;
    }

    public static function daysInMonth($iMonth, $iYear = null)
    {
        if($iYear == null)
        {
            $iYear = Date::now('Y');
        }

        if(self::isLeapYear($iYear))
        {
            return 29;
        }

        return self::$_aMonthDays[$iMonth - 1];
    }

    public static function timeAgo($iTime, $sFormat = null)
    {
        if($sFormat == null)
        {
            $sFormat = Linko::Config()->get('date.format');
        }

        $iTime = (int) $iTime;
        $iDiff = self::now() - $iTime;

        $iSeconds = (int)round(abs($iDiff));

        if($iSeconds < 60)
        {
            if($iSeconds < 5)
            {
                return Lang::t('date.just_now');
            }

            return Lang::t('date.x_seconds_ago', array('x' => $iSeconds));
        }

        $iMinutes = floor($iSeconds / 60);

        if($iMinutes < 60)
        {
            if($iMinutes == 1)
            {
                return Lang::t('date.a_minute_ago');
            }

            return Lang::t('date.x_minutes_ago', array('x' => $iMinutes));
        }

        $iHours = floor($iMinutes / 60);

        if($iHours < 24)
        {
            if($iHours == 1)
            {
                return Lang::t('date.an_hour_ago');
            }

            return Lang::t('date.x_hours_ago', array('x' => $iHours));
        }

        if($iHours < 48)
        {
            return Lang::t('date.yesterday');
        }

        $iDays = floor($iHours / 24);

        if($iDays < 7 && $iDays > 1)
        {
            return Lang::t('date.x_days_ago', array('x' => $iDays));
        }

        return Date::getTime($sFormat, $iTime);
    }
}

?>