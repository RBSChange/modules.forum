<?php
class forum_ViewDetailAction extends generic_ViewDetailAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$document = $this->getDocumentInstanceFromRequest($request);
		
		if ($document instanceof forum_persistentdocument_section)
		{
			$tag = 'contextual_website_website_modules_forum_section-detail';
			$page = $this->getTaggedPage($tag);
		}
		elseif ($document instanceof forum_persistentdocument_thread)
		{
			$tag = 'contextual_website_website_modules_forum_thread-detail';
			$page = $this->getTaggedPage($tag);
		}
		elseif ($document instanceof forum_persistentdocument_post)
		{
			$tag = 'contextual_website_website_modules_forum_post-detail';
			$page = $this->getTaggedPage($tag);
		}
		elseif ($document instanceof forum_persistentdocument_forummember)
		{
			$tag = 'contextual_website_website_modules_forum_forummember-detail';
			$page = $this->getTaggedPage($tag);
		}
		else
		{
			return parent::_execute($context, $request);
		}

		if ($page !== null)
		{
			$request->setParameter(K::PAGE_REF_ACCESSOR, $page->getId());
			$module = 'website';
			$action = 'Display';
		}
		else
		{
			$module = AG_ERROR_404_MODULE;
			$action = AG_ERROR_404_ACTION;
		}

		// finally, forward the execution to $module / $action
		$context->getController()->forward($module, $action);
		return View::NONE;		
	}
	
	/**
	 * @param String $tag
	 * @return website_persistentdocument_page
	 */
	private function getTaggedPage($tag)
	{
		try
		{
			$website = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
			return TagService::getInstance()->getDocumentByContextualTag($tag, $website);
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		return null;	
	}
}