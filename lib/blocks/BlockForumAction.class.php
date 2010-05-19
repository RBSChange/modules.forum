<?php
class forum_BlockForumAction extends forum_BlockBaseAction
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
		
    	$ids = $this->getDocumentIdsParameter();
    	if (count($ids) > 1)
    	{
			$items = $this->getDocumentsParameter();
			$forums = $this->filterVisibleForums($items);
    		if (count($forums) > 0)
			{
				$this->setParameter('forums', $forums);
			}
			return 'List';    		
    	}
    	
    	$forum = $this->getDocumentParameter();
    	
    	$context->setNavigationtitle($forum->getLabel());
    	$context->setTitle($forum->getLabel());
    	$context->setDescription($forum->getDescription());
    	
    	$this->setParameter('forum', $forum);
    	$preferences->setForum($forum);
    	
    	$items = forum_SectionService::getInstance()->getPublishedByForum($forum);
    	$sections = $this->filterVisibleSections($items);
    	
    	if (count($sections) > 0)
    	{
    		$this->setParameter('sections', $sections);
    	}
    
        return block_BlockView::SUCCESS;
    }
}