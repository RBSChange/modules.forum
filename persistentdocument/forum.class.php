<?php
/**
 * forum_persistentdocument_forum
 * @package modules.forum
 */
class forum_persistentdocument_forum extends forum_persistentdocument_forumbase implements indexer_IndexableDocument
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
		$indexedDoc->setDocumentModel('modules_forum/forum');
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
}