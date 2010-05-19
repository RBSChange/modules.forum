<?php
class forum_BlockThreadAction extends forum_BlockBaseAction
{
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	public function execute($context, $request)
	{
		if ($context->inBackofficeMode()) {return block_BlockView::DUMMY;}
	
		$this->setPreferences($context, $request);
		
		$action = $request->getParameter('action');
		if (empty($action))
		{
			$action = 'view';
		}
		$method = 'execute' . ucfirst($action);
		if (f_util_ClassUtils::methodExists($this, $method))
		{
			$this->setParameter('action', $action);
			return $this->{$method}($context, $request);
		}
		
		$this->setParameter('action', 'view');
		return $this->executeView($context, $request);
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeView($context, $request)
	{
		$thread = $this->getDocumentParameter();
		if (! $thread instanceof forum_persistentdocument_message)
		{
			$this->setParameter('error', 'Message introuvale');
			return block_BlockView::ERROR;
		}
		$this->setParameter('thread', $thread);
		$preferences = $this->getPreferences();
		$preferences->setThread($thread);
		
    	$context->setNavigationtitle($thread->getLabel());
    	$context->setTitle($thread->getLabel());
    	$context->setDescription(website_BBCodeService::getInstance()->removeBBCode($thread->getText()));	
    	
		$section = $thread->getSection();
		$this->setParameter('section', $section);
		$preferences->setSection($section);
		
		$forum = forum_ForumService::getInstance()->getBySection($section);
		$this->setParameter('forum', $forum);
		$preferences->setForum($forum);
		
		$items = forum_PostService::getInstance()->getPublishedByThread($thread);
        
		if (count($items) > 0)
		{
			// Get the preference of module
			$nbItemPerPage = $preferences->getItemsPerPage();
			$currentPage = 1;
	
			if (!$request->hasParameter(paginator_Paginator::REQUEST_PARAMETER_NAME))
			{
				forum_ThreadService::getInstance()->addRead($thread);
				$postId = intval($request->getParameter('post', 0));
				if ($postId > 0)
				{
					$idx = 0;
					foreach ($items as $post) 
					{
						$idx++;
						if ($postId == $post->getId())
						{
							$currentPage = (int)ceil($idx / $nbItemPerPage);
							break;
						}
					}	
				}
			}
			else
			{
				$currentPage = intval($request->getParameter(paginator_Paginator::REQUEST_PARAMETER_NAME));
			}
			$index = ($currentPage - 1) * $nbItemPerPage;
			for($i=0; $i< $nbItemPerPage; $i++)
			{
				if (isset($items[$index+$i]))
				{
					$items[$index+$i]->setPreferences($preferences);
				}
				else
				{
					break;
				}
			}
			
			$this->setParameter('beginThread', $currentPage < 2);
			
			// Set the paginator
			$postPaginator = new paginator_Paginator('forum', $currentPage, $items, $nbItemPerPage);
			$this->setParameter('postPaginator', $postPaginator);
		}
		return block_BlockView::SUCCESS;
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeCreate($context, $request)
	{
		$preferences = $this->getPreferences();
		
		$sectionId = intval($request->getParameter('section'));
		$section = DocumentHelper::getDocumentInstance($sectionId);
		$this->setParameter('section', $section);
		$preferences->setSection($section);
		
		if (!$preferences->isAuthenticated())
		{
			return $this->gotoAuthenticate();
		}
		$forummember = $preferences->getForummember();
		$this->setParameter('forummember', $forummember);
		
		$forum = forum_ForumService::getInstance()->getBySection($section);
		$this->setParameter('forum', $forum);
		$preferences->setForum($forum);
		
		$thread = forum_ThreadService::getInstance()->getNewDocumentInstance();
		$this->setParameter('thread', $thread);
		
		$fts = forum_ThreadService::getInstance();
		
		$threadParams = $this->extractRequestParams($request, array('section', 'label', 'text'));
		$fts->fillFromRequestParams($thread, $threadParams);
		
		if ($request->hasParameter('preview'))
		{
			$this->setParameter('preview', true);
		}
		else if ($request->hasParameter('create'))
		{
			try
			{
				$thread->setForummemberid($forummember->getId());
				$thread->setSign($forummember->getSign());
				
				if (! $thread->isValid())
				{
					$errors = $thread->getValidationErrors();
					$this->setParameter('errors', $errors);
				}
				else
				{
					$fts->save($thread);
					$fts->activate($thread->getId());
					$request->setParameter('cmpref', $thread->getId());
					$request->setParameter(paginator_Paginator::REQUEST_PARAMETER_NAME, 1);
					return $this->executeView($context, $request);
				}
			}
			catch (Exception $e)
			{
				Framework::exception($e);
			}
		}
		
		$context->addScript('modules.website.lib.bbeditor.jtageditor');
		$context->addStyle('modules.website.jtageditor');
		return block_BlockView::INPUT;
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeEdit($context, $request)
	{
		$preferences = $this->getPreferences();

		$thread = $this->getDocumentParameter();
		if (! $thread instanceof forum_persistentdocument_message)
		{
			$this->setParameter('error', 'Message introuvale');
			return block_BlockView::ERROR;
		}
		$this->setParameter('thread', $thread);
		$preferences->setThread($thread);
		
		$section = $thread->getSection();
		$this->setParameter('section', $section);
		$preferences->setSection($section);
		if (!$preferences->isAuthenticated())
		{
			return $this->gotoAuthenticate();
		}		

		$forummember = $preferences->getForummember();
		$this->setParameter('forummember', $forummember);
		
		$forum = forum_ForumService::getInstance()->getBySection($section);
		$this->setParameter('forum', $forum);
		$preferences->setForum($forum);
				
		$fts = forum_ThreadService::getInstance();
		
		$threadParams = $this->extractRequestParams($request, array('section', 'label', 'text'));
		$fts->fillFromRequestParams($thread, $threadParams);
		
		if ($request->hasParameter('preview'))
		{
			$this->setParameter('preview', true);
		}
		else if ($request->hasParameter('modify'))
		{
			try
			{
				$thread->setModifiedbyid($forummember->getId());				
				if (! $thread->isValid())
				{
					$errors = $thread->getValidationErrors();
					$this->setParameter('errors', $errors);
				}
				else
				{
					$fts->save($thread);
					$request->setParameter(paginator_Paginator::REQUEST_PARAMETER_NAME, 1);
					return $this->executeView($context, $request);
				}
			}
			catch (Exception $e)
			{
				Framework::exception($e);
			}
		}
		
		$context->addScript('modules.website.lib.bbeditor.jtageditor');
		$context->addStyle('modules.website.jtageditor');
		return 'Edit';
	}
}