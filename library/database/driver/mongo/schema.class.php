<?php

class Linko_Database_Driver_Mongo_Schema
{
    public function __construct($aParams = array())
	{
        $this->connection = $aParams['connection'];
	}
    
	public function createCollection($sCollection, array $aFields = array(), $bIfNotExists = false)
	{
        $bCapped = isset($aFields['capped']) ? $aFields['capped'] : false;
        $iSize = (isset($aFields['size']) && $bCapped) ? $aFields['size'] : 0;
        
        return array(
            'create' => $sCollection,
            'size' => 0,
            'capped' => false,
            'max' => 0
        );  		
	}
    
	public function dropCollection($sCollection, $bIfExists = true)
	{
	   return array(
            'drop' => $sCollection,
        );
	}

	public function getCollections()
	{
		return $this->connection->oDb->listCollections();
	}
       
	public function collectionExists($sCollection)
	{
        foreach($this->getCollections() as $oCollection)
        {
            if($oCollection == $sCollection)
            {
                return true;
            }
        }
        
		return false;	
	}
}

?>