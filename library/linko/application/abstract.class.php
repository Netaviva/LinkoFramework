<?php

abstract class Linko_Application_Abstract
{
	public function getRoute()
	{
		return Linko::Router()->route(Linko::Request()->getUri());	
	}
	
	abstract public function start();
}

?>