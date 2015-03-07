<?php

class Inflector
{
    public static function camelize($sStr) 
	{
        return str_replace(" ", "", ucwords(str_replace(array("_", "-"), " ", $sStr)));
    }
	
    public static function humanize($sStr) 
	{
        return ucfirst(str_replace('_', ' ', $sStr));
    }
	
    public static function underscore($sStr)
	{
        return preg_replace('/[\s]+/', '_', trim($sStr));
    }
	
    public static function hyphenToUnderscore($sStr) 
	{
        return str_replace("-", "_", $sStr);
    }
    	
	public static function classify($sStr)
	{
		return str_replace(' ', '_', ucwords(str_replace(array('_', '/', '-'), ' ', $sStr)));
	}
	
	public static function slugify($sStr, $sSeparator = '-')
	{
		setlocale(LC_ALL, 'en_us.UTF8');
		
		$sSlug = iconv('UTF-8', 'ASCII//TRANSLIT', $sStr);
		
		$sSlug = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $sSlug);
		
		$sSlug = strtolower(trim($sSlug, '-'));
		
		$sSlug = preg_replace("/[\/_|+ -]+/", $sSeparator, $sSlug);
		
		return $sSlug;
	}	
}

?>