<?php

defined('LINKO') or exit();

class Template_Plugin_Option
{
    public function start($aParams)
    {
        /**
         * @var array $data data to read from
         * @var string $value_key key for values
         * @var string $title_key key for titles
         * @var string $default default selected key
         */
        extract(array_merge(array(
            'data' => array(),
            'value_key' => null,
            'title_key' => null,
            'default' => null
        ), $aParams));

        if(is_string($data))
        {
            if(strpos($data, ','))
            {
                $values = array_map('trim', explode(',', $data));

                $data = array();

                foreach($values as $value)
                {
                    $data[$value] = ucwords($value);
                }

                unset($values);
            }
            else if(strpos($data, ':'))
            {
                list($sModel, $sMethod) = explode(':', $data, 2);

                $data = Linko::Model($sModel)->$sMethod();
            }
            else
            {
                $data = array($data => $data);
            }
        }

        $sOption = null;
        foreach($data as $key => $item)
        {
            $attr = array();

            $attr['value'] = $value_key == null ? $key : $item[$value_key];

            if($default == $attr['value'])
            {
                $attr['selected'] = 'selected';
            }

            $sOption .= Html::tag('option', ($title_key == null ? $item : $item[$title_key]), $attr);
        }

        unset(
            $aParams['data'],
            $aParams['value_key'],
            $aParams['title_key']
        );

        echo $sOption;
    }
}

?>