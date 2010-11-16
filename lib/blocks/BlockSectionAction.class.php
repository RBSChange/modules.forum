<?php
class forum_BlockSectionAction extends forum_BlockBaseAction
{
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
    public function execute($context, $request)
    {
    	if ($context->inBackofficeMode()) {return block_BlockView::DUMMY;}
    
    	$preferences = $this->setPreferences($context, $request);
    	$section = $this->getDocumentParameter();
    	$this->setParameter('section', $section);
    	$preferences->setSection($section);
    	
    	$context->setNavigationtitle($section->getLabel());
    	$context->setTitle($section->getLabel());
    	$context->setDescription($section->getDescription());		    	
    	
    	$forum = forum_ForumService::getInstance()->getBySection($section);
		$this->setParameter('forum', $forum);
		$preferences->setForum($forum);
        
		$items = forum_ThreadService::getInstance()->getPublishedBySection($section);
        if (count($items) > 0)
        {
			// Get the preference of module
			$nbItemPerPage = $preferences->getItemsPerPage();
	
			// Set the paginator
			$threadPaginator = new paginator_Paginator('forum', $request->getParameter(paginator_Paginator::PAGEINDEX_PARAMETER_NAME, 1), $items, $nbItemPerPage);
			$this->setParameter('threadPaginator', $threadPaginator);   
        }     
        
        return block_BlockView::SUCCESS;
    }
}