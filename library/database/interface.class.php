<?php

defined('LINKO') or exit('Linko Not Defined!');

interface Linko_Database_Interface
{
    public function execute($mCommand);

    public function connect();

	public function setCharset($sCharset);

    public function fetchValue();

    public function fetchRow();

    public function fetchRows();

    public function fetchObject();

    public function fetchObjects();

    public function getCount();

    public function close();

    public function getAffectedRows();

    public function getInsertId();
}

?>