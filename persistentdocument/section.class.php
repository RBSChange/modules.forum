<?php
/**
 * forum_persistentdocument_section
 * @package module.forum
 */
class forum_persistentdocument_section extends forum_persistentdocument_sectionbase implements indexer_IndexableDocument
{
	/**
	 * Get the indexable document
	 *
	 * @return indexer_IndexedDocument
	 */
	public function getIndexedDocument()
	{
		$indexedDoc = new indexer_IndexedDocument();

		$indexedDoc->setId($this->getId());
		$indexedDoc->setDocumentModel('modules_forum/section');
		$indexedDoc->setLabel($this->getLabel());
		$indexedDoc->setLang($this->getLang());
		$indexedDoc->setText($this->getDescription());
		
		$websiteId = $this->getDocumentService()->getWebsiteId($this);
		if ($websiteId)
		{
			$indexedDoc->setParentWebsiteId($websiteId);
		}
		
		return $indexedDoc;
	}
	
	/**
	 * @return String
	 */
	public function getDescriptionAsHtml()
	{
		return website_BBCodeService::getInstance()->toHtml(parent::getDescription());
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
			$this->forum = forum_ForumService::getInstance()->getBySection($this);
		}
		return $this->forum;
	}
	
	/**
	 * @return string
	 */
	public function getForumLabel()
	{
		return $this->getForum()->getLabel();
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
}