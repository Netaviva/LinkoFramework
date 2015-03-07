<?php

class Linko_Form extends Linko_Core
{
	/*
		$this->Form->has($_POST, array('field_one', 'field_two'));
	*/
	public function has($aVals, $aKeys = array())
	{
		$iNotFound = 0;
		
		foreach($aKeys as $sKey)
		{
			if(!isset($aVals[$sKey]))
			{
				$iNotFound++;
			}
		}
		
		if($iNotFound > 0)
		{
			return false;
		}
		
		return true;		
	}
}

?>