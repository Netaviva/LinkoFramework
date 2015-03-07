<?php

class Template_Plugin_Error
{
	public function start($aParams = array())
	{
		/**
		 * @var $container_class
		 * @var $class
		 * @var $name
		 */
		extract(array_merge(array(
			'name' => 'global',
			'container_class' => 'error-list',
			'class' => 'error-item'
		), $aParams));
		
		$oLinko = Linko::getInstance();
		$sHtml = null;
		
		if(!Linko::Error()->isPassed())
		{
			$sHtml = Html::openTag('div', array('class' => $container_class));

			foreach(Linko::Error()->get($name) as $error)
			{
				$sHtml .= Html::tag('div', $error, array('class' => $class));
			}
			$sHtml .= Html::closeTag('div');
		}
		
		echo $sHtml;
	}
}

?>