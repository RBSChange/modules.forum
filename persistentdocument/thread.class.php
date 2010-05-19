<?php
/**
 * forum_persistentdocument_thread
 * @package modules.forum
 */
class forum_persistentdocument_thread extends forum_persistentdocument_threadbase implements indexer_IndexableDocument
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
		$indexedDoc->setDocumentModel('modules_forum/thread');
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
			$this->forum = forum_ForumService::getInstance()->getByThread($this);
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
	 * @return string
	 */
	public function getSectionLabel()
	{
		return $this->getSection()->getLabel();
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
     * @param string $moduleName
     * @param string $treeType
     * @param array<string, string> $nodeAttributes
     */
    protected function addTreeAttributes ($moduleName, $treeType, &$nodeAttributes)
    {
    	if ($this->getForummember() !== null)
    	{
    		$nodeAttributes['membername'] = $this->getForummember()->getPseudonym();
    	}
    }
}