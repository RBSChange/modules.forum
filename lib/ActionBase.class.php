<?php
class forum_ActionBase extends f_action_BaseAction
{
	
	/**
	 * Returns the forum_MessageService to handle documents of type "modules_forum/message".
	 *
	 * @return forum_MessageService
	 */
	public function getMessageService()
	{
		return forum_MessageService::getInstance();
	}
	
	/**
	 * Returns the forum_PostService to handle documents of type "modules_forum/post".
	 *
	 * @return forum_PostService
	 */
	public function getPostService()
	{
		return forum_PostService::getInstance();
	}
	
	/**
	 * Returns the forum_ThreadService to handle documents of type "modules_forum/thread".
	 *
	 * @return forum_ThreadService
	 */
	public function getThreadService()
	{
		return forum_ThreadService::getInstance();
	}
	
	/**
	 * Returns the forum_SectionService to handle documents of type "modules_forum/section".
	 *
	 * @return forum_SectionService
	 */
	public function getSectionService()
	{
		return forum_SectionService::getInstance();
	}
	
	/**
	 * Returns the forum_ForumService to handle documents of type "modules_forum/forum".
	 *
	 * @return forum_ForumService
	 */
	public function getForumService()
	{
		return forum_ForumService::getInstance();
	}
	
	/**
	 * Returns the forum_ForummemberService to handle documents of type "modules_forum/forummember".
	 *
	 * @return forum_ForummemberService
	 */
	public function getForummemberService()
	{
		return forum_ForummemberService::getInstance();
	}
	
}