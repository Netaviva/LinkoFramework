<?php

interface Linko_Query_Builder
{
    public function table($sTable, $sAlias = null);

    public function select();

    public function field(array $aDatas = array(), $bEscape = true);

    public function insert($aDatas = array(), $aValues = null, $bEscape = true);

    public function update($aUpdate = array(), $mCondition = null, $bEscape = true);

    public function delete($sTable = null, $mCondition = null);

    public function order($mField, $sOrder = null);

    public function group($sGroup);

    public function having($sHaving);

    public function leftJoin($sTable, $sAlias, $mParam = null);

	public function innerJoin($sTable, $sAlias, $mParam = null);

    public function join($sTable, $sAlias, $mParam = null);

    public function offset($iOffset);

    public function limit($iLimit);

    public function filter($iPage, $iLimit, $iCount);

    public function where($mField, $sOperator = null, $mValue = null);

    public function orWhere($mField, $sOperator = null, $sValue = null);

    public function whereIn($sField, $mValue = null);

    public function orWhereIn($sField, $mValue = null);

    public function whereNotIn($sField, $mValue = null);

    public function orWhereNotIn($sField, $mValue = null);

    public function build();

    public function create(array $aFields = array(), $bIfNotExists = false);

    public function drop($bIfExists = false);

    public function exists();

    public function columns();

    public function export();

    public function query($aParams = array());

    public function getQuery();
}

?>