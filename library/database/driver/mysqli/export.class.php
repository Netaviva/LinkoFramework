<?php

class Linko_Database_Driver_Mysqli_Export
{
    private $_sTable;

    private $_aTable = array();

    private $_oBuilder;

    public function __construct($aParam)
    {
        $this->connection = $aParam['connection'];

        $this->_sTable = substr($aParam['table'], strlen($this->connection->getPrefix()));

        $this->_prepare();
    }

    public function toArray()
    {
        return $this->_aTable;
    }

    /**
     *
     */
    public function toJson()
    {
        return json_encode($this->_aTable);
    }

    public function toXml()
    {
        Linko::Xml()->toXml($this->_aTable);

        return Linko::Xml()->output();
    }

    private function _prepare()
    {
        if($this->_aTable)
        {
            return $this->_aTable;
        }

        $aColumns = $this->connection->table($this->_sTable)->columns();

        $aTable = array();

        foreach($aColumns as $sColumn => $aParam)
        {
            list($sType, $bUnsigned) = array_pad(explode(' ', $aParam['Type'], 2), 2, null);

            $aTable[$sColumn]['type'] = $sType;

            if($bUnsigned)
            {
                $aTable[$sColumn]['unsigned'] = true;
            }

            if($aParam['Extra'] == 'auto_increment')
            {
                $aTable[$sColumn]['auto_increment'] = true;
            }

            if($aParam['Key'] == 'PRI')
            {
                $aTable[$sColumn]['primary_key'] = true;
            }

            if(($aParam['Default'] != null) || $aParam['Null'] == 'YES')
            {
                $aTable[$sColumn]['default'] = $aParam['Default'];
            }

            if($aParam['Key'] == 'MUL')
            {
                $aTable[$sColumn]['key'] = true;
            }

            if($aParam['Null'] == 'YES')
            {
                $aTable[$sColumn]['null'] = true;
            }
        }

        $this->_aTable[$this->_sTable] = $aTable;
    }
}

?>