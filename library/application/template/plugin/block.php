<?php

class Template_Plugin_Block
{
	public function start($aParams = array())
	{
        extract(array_merge(array(
            'position' => null,
            'title' => true,
        ), $aParams));
        
		if(!$position)
		{
			return Linko::Error()->trigger('Template Extension (block): missing argument \'name\'');	
		}
        		
        $sHtml = null;
                
		foreach(Linko::Module()->getBlocks($aParams['position']) as $aBlock)
        {
            $sHtml .= Html::openTag('div', array('class' => 'block'));
            
            if($title && array_key_exists('title', $aBlock) && $aBlock['title'])
            {
                $sHtml .= Html::tag('h3', $aBlock['title'], array('class' => 'block-title'));
            }
            
            $sHtml .= Html::tag('div', $aBlock['content'], array('class' => 'block-inner'));
            
            $sHtml .= Html::closeTag('div');
        }
        
        echo $sHtml;
	}
}

?>