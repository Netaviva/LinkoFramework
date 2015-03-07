<?php

class Linko_Pager
{
	/**
	 * Stores the route id
	 *
	 * @var int
	 **/	
	private $_sRouteId;

	/**
	 * Holds the page key name in the route
	 *
	 * @var int
	 **/	
	private $_sRouteKey = 'page';

	/**
	 * Holds extra params you want to pass to the router
	 *
	 * @var int
	 **/	
	private $_aRouteParam = array();
			
	/**
	 * Url That Holds Current Page Number
	 *
	 * @var int
	 **/
	private $_sPageUrl = 1;
	
	/**
	 * Current Page Beign Viewed
	 *
	 * @var int
	 **/	
	private $_iCurrentPage = 0;

	/**
	 * Next Page Number
	 *
	 * @var int
	 **/	
	private $_iNextPage = 1;

	/**
	 * Previous Page Number
	 *
	 * @var int
	 **/	
	private $_iPreviousPage = 0;
		
	/**
	 * Total Pages
	 *
	 * @var int
	 **/	
	private $_iTotalPages;

	/**
	 * Page First Record
	 *
	 * @var int
	 **/	
	private $_iFirstRecord;

	/**
	 * Page Last Record
	 *
	 * @var int
	 **/	
	private $_iLastRecord;

	/**
	 * Number Of Records To Display Per Page
	 *
	 * @var int
	 **/	
	private $_iRowsPerpage = 0;

	/**
	 * Numbers To Display On Pagination Links
	 *
	 * @var int
	 **/	
	private $_iNumLinks = 5;

	/**
	 * Holds Pagination Details
	 *
	 * @var array
	 **/	
	private $_aPager = array();
		
	/**
	 * Query Options
	 *
	 * @var array
	 **/	
	private $_aOptions = array();
	
	public function set($aOptions)
	{
		$aOptions = array_merge(array(
			'rows_per_page' => 5,
			'current_page' => 1,
            'route_id' => Linko::Router()->getId(),
			'route_key' => 'page',
			'route_param' => array(),
			'total_items' => 0,
		), $aOptions);
        
		$this->_iRowsPerpage =  max(intval($aOptions['rows_per_page']), 1);
		$this->_iTotalCount = max(intval($aOptions['total_items']), 0);
		$this->_iTotalPages = ceil($this->_iTotalCount / $this->_iRowsPerpage);
		$this->_iCurrentPage = max(1, min($this->_iTotalPages, intval($aOptions['current_page'])));
		$this->_iNextPage = min($this->_iCurrentPage + 1, $this->_iTotalPages);
		$this->_iPreviousPage = max($this->_iCurrentPage - 1, 1);
		$this->_iFirstRecord = $this->_iRowsPerpage * ($this->_iCurrentPage - 1);
		$this->_iLastRecord = min($this->_iFirstRecord + $this->_iRowsPerpage, $this->_iTotalCount);
		$this->_iNumLinks = max(intval($this->_iNumLinks), 1);
		
		$this->_sRouteId = $aOptions['route_id'];
		$this->_sRouteKey = $aOptions['route_key'] ;
		$this->_aRouteParam = $aOptions['route_param'];
		
		$this->_build();	
	}

	public function get()
	{
		return $this->_aPager;	
	}
	
	public function getFirstPage()
	{
		return $this->_iPreviousPage;	
	}
	public function getNextPage()
	{
		return $this->_iNextPage;	
	}

	public function getPrevPage()
	{
		return $this->_iPrevPage;	
	}
		
	public function getLastPage()
	{
		return $this->_iTotalPages;
	}
		
	private function _build()
	{
		$aPager = array(
			'url_key' => $this->_sPageUrl,
			'current_page' => $this->_iCurrentPage,
			'page_rows' => $this->_iRowsPerpage,
			'total_count' => $this->_iTotalCount,
			'total_pages' => $this->_iTotalPages,
			'first_record' => $this->_iFirstRecord + 1,
			'last_record' => $this->_iLastRecord,
		);
		
		if($this->_iCurrentPage != 1)
		{
			$aPager['first_page'] = 1;
			$aPager['first_page_url'] = $this->_getUrl(1);

			$aPager['prev_page'] = $this->_iPreviousPage;
			$aPager['prev_page_url'] = $this->_getUrl($this->_iPreviousPage);
		}
				
		list($this->_iStart, $this->_iEnd) = $this->_calcLinks();

		for($i = $this->_iStart; $i <= $this->_iEnd; $i++)
		{
			if($this->_iCurrentPage == $i)
			{
				$aPager['selected_link'] = $i;	
			}
			
			$aPager['links'][$this->_getUrl($i)] = $i; 
		}
		
		if($this->_iTotalPages != $this->_iCurrentPage)
		{
			$aPager['next_page'] = $this->_iNextPage;
			$aPager['next_page_url'] = $this->_getUrl($this->_iNextPage);
			
			$aPager['last_page'] = $this->_iTotalPages;
			$aPager['last_page_url'] = $this->_getUrl($this->_iTotalPages);
		}
		
		$this->_aPager = $aPager;
	}
	
	private function _getUrl($iLink)
	{
		$aParams = array_merge($this->_aRouteParam, array($this->_sRouteKey => $iLink));

		return Linko::Router()->toUrl($this->_sRouteId, $aParams);
	}
	
	private function _calcLinks()
	{
		$iStart = 1;
		
		if(($this->_iCurrentPage - ($this->_iNumLinks / 2)) > 0)
		{
			if(($this->_iCurrentPage + ($this->_iNumLinks / 2)) > $this->_iTotalPages)
			{
				$iStart = ((($this->_iTotalPages - $this->_iNumLinks) > 0) ? $this->_iTotalPages - $this->_iNumLinks + 1 : 1);
			}
			else
			{
				$iStart = $this->_iCurrentPage - floor($this->_iNumLinks / 2);
			}
		}
		
		$iEnd = (($iStart + ($this->_iNumLinks - 1)) < $this->_iTotalPages) ? ($iStart + $this->_iNumLinks - 1) : $this->_iTotalPages;
		
		return array($iStart, $iEnd);
	}
}
	
?>