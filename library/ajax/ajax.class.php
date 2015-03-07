<?php

/**
 * Handles AJAX Requests and returns data outputed
 * 
 * @author			Morrison Laju
 */
	 
class Linko_Ajax
{	
	public function toJson($mData)
    {
        return json_encode($mData);
    }
}

?>