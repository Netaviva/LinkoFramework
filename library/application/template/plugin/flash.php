<?php

class Template_Plugin_Flash
{
	public function start($aParams = array())
	{
		extract(array_merge(array(
			'class' => 'flash-message'
		), $aParams));

        $sHtml = null;

        if(Linko::Flash()->getMessage())
        {
            $class = $class . ' ' . Linko::Flash()->getType();

            $sHtml .= Html::tag('div', Linko::Flash()->getMessage(), array('class' => $class));
        }
        
        echo $sHtml;
	}
}

?>