<?php
class forum_Preferences
{
	
	const READ_PERMISSION = 'modules_forum.Read';
	const WRITE_PERMISSION = 'modules_forum.Write';
	
	const MODERATE_PERMISSION = 'modules_forum.Moderate';
	
	/**
	 * @var forum_persistentdocument_forummember
	 */
	private $forumMember;
	
	
	/**
	 * @var forum_persistentdocument_forummember
	 */
	private $editForumMember;
	
	/**
	 * @var forum_persistentdocument_forum
	 */
	private $forum;
	
	
	/**
	 * @var forum_persistentdocument_section
	 */
	private $section;	
	
	
	/**
	 * @var forum_persistentdocument_thread
	 */
	private $thread;

	/**
	 * @var forum_persistentdocument_post
	 */
	private $post;
	
	/**
	 * @var string
	 */
	private $backUrl;
	
	public function __construct($forumMember = null, $forumDocument = null)
	{
		if ($forumMember !== null)
		{
			$this->forumMember = $forumMember;
		}
	
		if ($forumDocument !== null)
		{
			if ($forumDocument instanceof forum_persistentdocument_forum) 
			{
				$this->forum = $forumDocument;
			}
			elseif ($forumDocument instanceof forum_persistentdocument_section) 
			{
				$this->section = $forumDocument;
			}
			elseif ($forumDocument instanceof forum_persistentdocument_thread) 
			{
				$this->thread = $forumDocument;
			}
			elseif ($forumDocument instanceof forum_persistentdocument_post) 
			{
				$this->post = $forumDocument;
			}
			elseif ($forumDocument instanceof forum_persistentdocument_forummember) 
			{
				$this->editForumMember = $forumDocument;
			}
		}
	}

	/**
	 * @param forum_persistentdocument_forummember $forumMember
	 */
	public function setforumMember($forumMember)
	{
		$this->forumMember = $forumMember;
	}
	
	/**
	 * @param forum_persistentdocument_forummember $forumMember
	 */
	public function setEditForumMember($forumMember)
	{
		$this->editForumMember = $forumMember;
	}
	
	/**
	 * @param forum_persistentdocument_forum $forum
	 */
	public function setForum($forum)
	{
		$this->forum = $forum;
	}
	
	/**
	 * @param forum_persistentdocument_post $post
	 */
	public function setPost($post)
	{
		$this->post = $post;
	}
	
	/**
	 * @param forum_persistentdocument_section $section
	 */
	public function setSection($section)
	{
		$this->section = $section;
	}
	
	/**
	 * @param forum_persistentdocument_thread $thread
	 */
	public function setThread($thread)
	{
		$this->thread = $thread;
	}
		
	/**
	 * @return Integer
	 */
	public function getItemsPerPage()
	{
		if ($this->forumMember !== null)
		{
			return $this->forumMember->getItemperpage();
		}
		return 10;
	}
	
	/**
	 * @return Boolean
	 */
	public function isAuthenticated()
	{
		return $this->forumMember !== null;
	}
	
	/**
	 * @param website_persistentdocument_page $page
	 * @param array<string, string> $forumParameters
	 */
	public function setBackUrl($page, $forumParameters = null)
	{
		if (f_util_ArrayUtils::isNotEmpty($forumParameters))
		{
			$this->backUrl = LinkHelper::getDocumentUrl($page, null, array('forumParam' => $forumParameters));
		}
		else
		{
			$this->backUrl = LinkHelper::getDocumentUrl($page);
		}
	}
	
	/**
	 * @return string
	 */
	public function getBackUrl()
	{
		return $this->backUrl;
	}
	
	/**
	 * @return forum_persistentdocument_forummember
	 */
	public function getForummember()
	{
		return $this->forumMember;
	}
	
	
	/**
	 * @return string
	 */
	public function getPseudonym()
	{
		if ($this->forumMember !== null)
		{
			return $this->forumMember->getPseudonym();
		}
		return null;
	}
	
	
	private function getLogin()
	{
		if ($this->forumMember === null)
		{
			return null;
		}
		else
		{
			return $this->forumMember->getUser();
		}
	}
	
	public function canReadForum()
	{
		if ($this->forum === null) {return false;}
		
		if ($this->forum->getIspublic()) {return true;}
		
		if ($this->forumMember === null) {return false;}
		
		return f_permission_PermissionService::getInstance()->hasPermission($this->getLogin(), self::READ_PERMISSION, $this->forum->getId());
	}
	
	
	public function canReadSection()
	{
		if ($this->section === null || $this->forum === null) {return false;}
		if ($this->forum->getIspublic()) {return true;}
		if (!$this->forum->getIspublic() && $this->forumMember === null) {return false;}
		return f_permission_PermissionService::getInstance()->hasPermission($this->getLogin(), self::READ_PERMISSION, $this->section->getId());
	}
	
	public function canCreateThread()
	{
		if ($this->forumMember === null || (!$this->forumMember->isPublished()) ||
			$this->section === null || $this->section->getLocked())
		{
			return false;
		}
				
		return f_permission_PermissionService::getInstance()->hasPermission($this->getLogin(), self::WRITE_PERMISSION, $this->section->getId());
	}
	
	public function canEditThread()
	{
		if ($this->forumMember === null || (!$this->forumMember->isPublished()) ||
			$this->section === null || $this->section->getLocked() ||
			$this->thread === null || $this->thread->getLocked())
		{
			return false;			
		}
		return $this->thread->getForummemberid() == $this->forumMember->getId() ||
		  f_permission_PermissionService::getInstance()->hasPermission($this->getLogin(), self::MODERATE_PERMISSION, $this->section->getId());
	}
	
	public function canEditPost()
	{
		if ($this->forumMember === null || (!$this->forumMember->isPublished()) ||
			$this->section === null || $this->section->getLocked() ||
			$this->thread === null || $this->thread->getLocked() ||
			$this->post === null || $this->post->getLocked())
		{
			return false;			
		}
		
		return $this->post->getForummemberid() == $this->forumMember->getId() ||
		  f_permission_PermissionService::getInstance()->hasPermission($this->getLogin(), self::MODERATE_PERMISSION, $this->section->getId());
	}
	
	public function canCreatePost()
	{
		
		if ($this->forumMember === null || (!$this->forumMember->isPublished()) ||
			$this->section === null || $this->section->getLocked() ||
			$this->thread === null || $this->thread->getLocked())
		{
			return false;
		}
		return f_permission_PermissionService::getInstance()->hasPermission($this->getLogin(), self::WRITE_PERMISSION, $this->section->getId());
	}
	
	public function canEditForumMember()
	{
		if ($this->forumMember === null || $this->editForumMember === null)
		{
			return false;
		}
		return DocumentHelper::equals($this->forumMember, $this->editForumMember);
	}
	
	public function viewPrivateMemberInfos()
	{
		return $this->canEditForumMember();
	}
}
