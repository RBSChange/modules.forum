<?php
class forum_BlockPostAction extends forum_BlockBaseAction
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
		$post = $this->getDocumentParameter();
		if (!$post instanceof forum_persistentdocument_post)
		{
			$this->setParameter('error', 'Message introuvale');
			return block_BlockView::ERROR;
		}
		$this->setParameter('post', $post);
		$preferences = $this->getPreferences();
		$preferences->setPost($post);
		
		$thread = $post->getThread();
		$this->setParameter('thread', $thread);
		$preferences->setThread($thread);
		
		$section = $thread->getSection();
		$this->setParameter('section', $section);
		$preferences->setSection($section);
		
		$forum = forum_ForumService::getInstance()->getBySection($section);
		$this->setParameter('forum', $forum);
		$preferences->setForum($forum);
			
		return block_BlockView::SUCCESS;
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeCreate($context, $request)
	{
		$threadId =  intval($request->getParameter('thread'));
		$thread = DocumentHelper::getDocumentInstance($threadId);
		$this->setParameter('thread', $thread);
		$preferences = $this->getPreferences();
		$preferences->setThread($thread);
			
		$section = $thread->getSection();
		$this->setParameter('section', $section);
		$preferences->setSection($section);
	
		$forummember = forum_ForummemberService::getInstance()->getCurrent();
		if ($forummember === null)
		{
			$backUrl = LinkHelper::getDocumentUrl($context->getPageDocument(), null, array('forumParam' => $request->getParameters()));
			$page = TagService::getInstance()->getDocumentByContextualTag('contextual_website_website_modules_forum_forummember-detail', 
			website_WebsiteModuleService::getInstance()->getCurrentWebsite());
			$url = LinkHelper::getDocumentUrl($page, null, array('forumParam' => array('back' => $backUrl)));
			Controller::getInstance()->redirectToUrl($url);
			return block_BlockView::NONE;
		}
		$this->setParameter('forummember', $forummember);
		
		$forum = forum_ForumService::getInstance()->getBySection($section);
		$this->setParameter('forum', $forum);
		$preferences->setForum($forum);
		
		$fps = forum_PostService::getInstance();
		
		$post = $fps->getNewDocumentInstance();
		$post->setLabel("RE: " . $thread->getLabel());
		if ($request->hasParameter('message'))
		{
			$messageId = intval($request->getParameter('message'));
			if ($messageId > 0)
			{
				$message = DocumentHelper::getDocumentInstance($messageId);
				$author = $message->getForummember()->getPseudonym();
				$citation = $message->getText();
				$post->setText('[quote='.$author.']'.$citation.'[/quote]' . "\n");
				$post->setLabel("RE: " . $message->getLabel());
			}
		}
		
		$this->setParameter('post', $post);
		
		$postParams = $this->extractRequestParams($request, array('thread', 'label', 'text'));
		$fps->fillFromRequestParams($post, $postParams);
		
		if ($request->hasParameter('preview'))
		{
			$this->setParameter('preview', true);
		}
		else if ($request->hasParameter('create'))
		{
			try
			{				
				$post->setForummemberid($forummember->getId());	
				if (! $post->isValid())
				{
					$errors = $post->getValidationErrors();
					$this->setParameter('errors', $errors);
				}
				else
				{
					$fps->save($post);
					$fps->activate($post->getId());
					return block_BlockView::SUCCESS;
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
		$post = $this->getDocumentParameter();
		if (! $post instanceof forum_persistentdocument_message)
		{
			$this->setParameter('error', 'Message introuvale');
			return block_BlockView::ERROR;
		}
		$this->setParameter('post', $post);
		$preferences->setPost($post);
				
		$thread = $post->getThread();
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
		
		$fps = forum_PostService::getInstance();
		
		$post->setLabel("RE: " . $thread->getLabel());
		if ($request->hasParameter('message'))
		{
			$messageId = intval($request->getParameter('message'));
			if ($messageId > 0)
			{
				$message = DocumentHelper::getDocumentInstance($messageId);
				$author = $message->getForummember()->getPseudonym();
				$citation = $message->getText();
				$post->setText('[quote='.$author.']'.$citation.'[/quote]' . "\n");
				$post->setLabel("RE: " . $message->getLabel());
			}
		}
	
		$postParams = $this->extractRequestParams($request, array('thread', 'label', 'text'));
		$fps->fillFromRequestParams($post, $postParams);
		
		if ($request->hasParameter('preview'))
		{
			$this->setParameter('preview', true);
		}
		else if ($request->hasParameter('modify'))
		{
			try
			{				
				$post->setModifiedbyid($forummember->getId());	
				if (! $post->isValid())
				{
					$errors = $post->getValidationErrors();
					$this->setParameter('errors', $errors);
				}
				else
				{
					$fps->save($post);
					return block_BlockView::SUCCESS;
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