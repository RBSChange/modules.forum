<?php
/**
 * forum_persistentdocument_forummember
 * @package modules.forum
 */
class forum_persistentdocument_forummember extends forum_persistentdocument_forummemberbase 
{
	/**
	 * @retun string
	 */
	public function getSignAsHtml()
	{
		return website_BBCodeService::getInstance()->ToHtml(parent::getSign());
	}
	
	/**
	 * @return string
	 */
	public function getSignAsInput()
	{
		return htmlspecialchars(parent::getSign());
	}	
	
	/**
	 * @retun string
	 */
	public function getFullnameAsHtml()
	{
		return $this->getUser()->getFullnameAsHtml();
	}	
	
	/**
	 * @retun string
	 */
	public function getFirstnameAsHtml()
	{
		return $this->getUser()->getFirstnameAsHtml();
	}	
	
	/**
	 * @retun string
	 */
	public function getLastnameAsHtml()
	{
		return $this->getUser()->getLastnameAsHtml();
	}
	
	/**
	 * @retun string
	 */
	public function getEmailAsHtml()
	{
		return $this->getUser()->getEmailAsHtml();
	}	

	/**
	 * @retun string
	 */
	public function getLoginAsHtml()
	{
		return $this->getUser()->getLoginAsHtml();
	}	
	
	/**
	 * @return forum_persistentdocument_post
	 */
	public function getLastPost()
	{
		$lastPostId = intval($this->getLastpostid());
		if ($lastPostId > 0)
		{
			return DocumentHelper::getDocumentInstance($this->getLastpostid());
		}
		return null;
	}
	
	/**
	 * @return boolean
	 */
	public function isActif()
	{
		return $this->isPublished() && $this->getUser()->isPublished();
	}
	
	/**
	 * @return boolean
	 */
	public function activationNeeded()
	{
		return $this->getPublicationstatus() == 'DRAFT';
	}
	
	/**
	 * @return boolean
	 */
	public function isLocked()
	{
		return !$this->isActif() && !$this->activationNeeded();
	}
}