<?php
class forum_MessageService extends f_persistentdocument_DocumentService
{
	/**
	 * @var forum_MessageService
	 */
	private static $instance;

	/**
	 * @return forum_MessageService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return forum_persistentdocument_message
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_forum/message');
	}

	/**
	 * Create a query based on 'modules_forum/message' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_forum/message');
	}

	/**
	 * @param forum_persistentdocument_message $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$forummember = $document->getForummember();
		if ($forummember !== null)
		{
			$document->setSign($forummember->getSign());
		}
	}
}