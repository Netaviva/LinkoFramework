<?php

class Linko_Cache_Storage_File implements Linko_Cache_Interface
{
	private $_sPrefix;
	
	private $_sId;
	
	private $_aId;
	
	private $_bCache;
	
	public function __construct()
	{
		$this->_bCache = Linko::Config()->get('Cache.enable');
		
		$this->_sPrefix = Linko::Config()->get('Cache.prefix');
		
		if(!Dir::exists(Linko::Config()->get('dir.cache')))
		{
			$this->_bCache = false;
		}

		if(!Dir::isWritable(Linko::Config()->get('dir.cache')))
		{
			$this->_bCache = false;
		}
	}
	
	public function set($mName)
	{
		if(is_array($mName))
		{
			if($this->_bCache && Dir::isWritable(Linko::Config()->get('dir.cache')))
			{
				Dir::create(Linko::Config()->get('dir.cache').$mName[0]);
			}
			
			$mName = $mName[0].DS.$mName[1];
		}
		
		$this->_sId = $mName;
		
		$this->_aId[$this->_sId] = $mName;
        
		return $this->_sId;
	}
	
	public function read($sId = null, $iTime = 0)
	{	
		if(!$this->_bCache)
		{
			return false;	
		}
		
		$sFile = $this->_getFile($this->_aId[$this->_getId($sId)]);
		
		if(!File::exists($sFile))
		{
			return false;	
		}
		
		$aData = unserialize(file_get_contents($sFile));

		if(!$this->isCached($iTime, $sId, filemtime($sFile)))
		{
			return false;	
		}
				
		if(!isset($aData))
		{
			return false;	
		}

		if(!is_array($aData) && empty($aData))
		{
			return true;	
		}
		
		if(is_array($aData) && !count($aData))
		{
			return false;	
		}
        		
		$this->reset($sId);
		
		return $aData;
	}
	
	public function write($sData, $sId = null)
	{
		if($this->_bCache == false)
		{
			return false;
		}

		$sCache = serialize($sData);

        File::write($this->_getFile($this->_aId[$this->_getId($sId)]), $sCache, null, true);
		
		return true;
	}
	
	public function delete($sId = null, $sType = 'path')
	{
        switch($sType)
        {
            case 'path':
                $sFile = $this->_getFile($this->_getId($sId));
                
        		if(File::exists($sFile))
        		{
        			File::delete($sFile);	
        		}
                break;
            case 'dir':
                $sPath = Linko::Config()->get('dir.cache') . $this->_getId($sId);
                
                if(Dir::exists($sPath))
                {
                    foreach(Dir::getFiles($sPath) as $sFile)
                    {
                        File::delete($sFile);
                    }
                }
                break;
        }
		
		return true;
	}
	
	public function isCached($iTime = 0, $sId = null, $iCacheTime = 0)
	{
		if(Linko::Config()->get('Cache.enable') === false)
		{
			return false;
		}
		
		if(isset($this->_aId[$this->_getId($sId)]) && file_exists($this->_getFile($this->_aId[$this->_getId($sId)])))
		{
			if($iTime && ((time() - $iTime) > $iCacheTime))
			{
				return false;
			}
			
			return true;
		}
		
		return false;
	}
	
	public function reset($sId = null)
	{
		unset($this->_aId[$this->_getId($sId)]);	
	}
	
	private function _getFile($sId)
	{
		return Linko::Config()->get('dir.cache') . $sId . Linko::Config()->get('Ext.cache');
	}
	
	private function _getId($mId)
	{
		if(is_null($mId))
		{
			return $this->_sId;
		}
		
        $sId = $mId;
        
        if(is_array($mId))
        {
            $sId = $mId[0].DS.$mId[1];
        }
        
		return $sId;
	}
}

?>