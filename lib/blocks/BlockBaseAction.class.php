<?php
abstract class forum_BlockBaseAction extends block_BlockAction
{
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return forum_Preferences
	 */	
	protected final function setPreferences($context, $request)
	{
		$preferences = new forum_Preferences(forum_ForummemberService::getInstance()->getCurrent());
		$preferences->setBackUrl($context->getPageDocument(), $request->getParameters());
		$this->setParameter('preferences', $preferences);
		return $preferences;
	}
	
	/**
	 * @return forum_Preferences
	 */
	protected final function getPreferences()
	{
		return $this->getParameter('preferences');
	}
	
	/**
	 * @param block_BlockRequest $request
	 * @param String $paramNames
	 * @return array
	 */
	protected final function extractRequestParams($request, $paramNames)
	{
		$result = array();
		foreach ($paramNames as $name)
		{
			if ($request->hasParameter($name))
			{
				$result[$name] = $request->getParameter($name);
			}
		}
		return $result;
	}
	
	protected function filterVisibleForums($items)
	{
		$preferences = $this->getPreferences();
		
		$result = array();
		foreach ($items as $forum) 
		{
			$preferences->setForum($forum);
			if ($preferences->canReadForum())
			{
				$result[] = $forum;
			}
		}
		
		return $result;
	}
	
	protected function filterVisibleSections($items)
	{
		$preferences = $this->getPreferences();
		
		$result = array();
		foreach ($items as $section) 
		{
			$preferences->setSection($section);
			if ($preferences->canReadSection())
			{
				$result[] = $section;
			}
		}
		return $result;
	}
	
	protected function gotoAuthenticate()
	{
		$page = TagService::getInstance()->getDocumentByContextualTag('contextual_website_website_modules_forum_forummember-detail', 
					website_WebsiteModuleService::getInstance()->getCurrentWebsite());
	
		$url = LinkHelper::getDocumentUrl($page, null, array('forumParam' => array('back' => $this->getPreferences()->getBackUrl())));
		Controller::getInstance()->redirectToUrl($url);
		return block_BlockView::NONE;
	}
}