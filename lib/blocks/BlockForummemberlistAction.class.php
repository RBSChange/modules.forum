<?php
class forum_BlockForummemberlistAction extends forum_BlockBaseAction
{
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	public function execute($context, $request)
	{
		$preferences = $this->setPreferences($context, $request);
		
		// Get the list of element for the container
		$items = forum_ForummemberService::getInstance()->createQuery()->find();
		if (count($items) > 0)
		{
			$nbItemPerPage = $preferences->getItemsPerPage();
			$currentPage = intval($request->getParameter(paginator_Paginator::REQUEST_PARAMETER_NAME));
			if ($currentPage <= 0)
			{
				$currentPage = 1;
			}
			
			$forummemberPaginator = new paginator_Paginator('forum', $currentPage, $items, $nbItemPerPage);
			$this->setParameter('forummemberPaginator', $forummemberPaginator);
		}
		
		return block_BlockView::SUCCESS;
	}
}