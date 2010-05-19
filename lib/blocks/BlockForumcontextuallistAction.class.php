<?php
class forum_BlockForumcontextuallistAction extends forum_BlockBaseAction
{
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	public function execute($context, $request)
	{
		// Get the parent topic
		$ancestor = $context->getAncestors();
        $topicId = f_util_ArrayUtils::lastElement($ancestor);

        $container = DocumentHelper::getDocumentInstance($topicId);
		$this->setParameter('container', $container);
		$this->setPreferences($context, $request);
		
		// Get the list of element for the container
		$items = forum_ForumService::getInstance()->getPublishedByTopic($container);
		$forums = $this->filterVisibleForums($items);
		
		
		if (count($forums) > 0)
		{
			$this->setParameter('forums', $forums);
		}

		return block_BlockView::SUCCESS;
	}
}