<?php

class Template_Plugin_Breadcrumb
{
	public function start($aParams = array())
	{
        extract(array_merge(array(
            'links' => true,
            'title' => true
        ), $aParams));
        
		$oLinko = Linko::getInstance();
		
		$aBreadcrumbLinks = Linko::Template()->getBreadcrumbLinks();
		$sBreadcrumbTitle = Linko::Template()->getBreadcrumbTitle();
		
		$iTotal = count($aBreadcrumbLinks);
		
		$sBreadcrumb = Html::openTag('div', array('id' => 'breadcrumb'));
            
        if($links)
        {
			$sBreadcrumb .= Html::openTag('ul', array('id' => 'breadcrumb-links'));
			$iCnt = 0;
            
			foreach($aBreadcrumbLinks as $sTitle => $sLink)
			{
				$iCnt++;
				
				if($sLink == null)
				{
					$sBreadcrumb .= Html::tag('li', Html::tag('span', $sTitle));
				}
				else
				{
					$sBreadcrumb .= Html::tag('li', Html::link($sTitle, $sLink));	
				}
				
				$sBreadcrumb .= ($iCnt != $iTotal ? '&raquo;' : null);
			}
            
			$sBreadcrumb .= Html::closeTag('ul');                
        }
		
        $sBreadcrumb .= Html::tag('h2', $sBreadcrumbTitle, array('id' => 'breadcrumb-title'));
        
		$sBreadcrumb .= Html::closeTag('div');
		
		echo $sBreadcrumb;
	}
}

?>