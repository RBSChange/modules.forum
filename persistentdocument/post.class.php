<?php
/**
 * forum_persistentdocument_post
 * @package forum
 */
class forum_persistentdocument_post extends forum_persistentdocument_postbase  implements indexer_IndexableDocument
{

	/**
	 * @return indexer_IndexedDocument
	 */
	public function getIndexedDocument()
	{
		$indexedDoc = new indexer_IndexedDocument();
		$indexedDoc->setId($this->getId());
		$indexedDoc->setDocumentModel('modules_forum/post');
		$indexedDoc->setLabel($this->getLabel());
		$indexedDoc->setLang($this->getLang());
		$indexedDoc->setText($this->getText()); 
	
		$websiteId = $this->getDocumentService()->getWebsiteId($this);
		if ($websiteId)
		{
			$indexedDoc->setParentWebsiteId($websiteId);
		}
		return $indexedDoc;
	}
	
	
	/**
	 * @var forum_persistentdocument_forum
	 */
	private $forum;
	
	/**
	 * @return forum_persistentdocument_forum
	 */
	public function getForum()
	{
		if ($this->forum === null)
		{
			$this->checkLoaded();
			$this->forum = forum_ForumService::getInstance()->getByPost($this);
		}
		return $this->forum;
	}
	
	
	public function getIndexAsHtml()
	{
		return $this->getIndex() + 1;
	}
	
	/**
	 * @var forum_Preferences
	 */
	private $preferences;
	
	
	/**
	 * @param forum_Preferences $preferences
	 */
	public function setPreferences($preferences)
	{
		$this->preferences = $preferences;
	}
	
	public function isEditable()
	{
		if ($this->preferences)
		{
			$this->preferences->setPost($this);
			return $this->preferences->canEditPost();
		}
		return false;
	}
}