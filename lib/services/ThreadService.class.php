<?php
class forum_ThreadService extends forum_MessageService
{
	/**
	 * @var forum_ThreadService
	 */
	private static $instance;

	/**
	 * @return forum_ThreadService
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
	 * @return forum_persistentdocument_thread
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_forum/thread');
	}

	/**
	 * Create a query based on 'modules_forum/thread' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_forum/thread');
	}


	/**
	 * @param forum_persistentdocument_thread $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function postInsert($document, $parentNodeId = null)
	{
		parent::postInsert($document, $parentNodeId);
		forum_SectionService::getInstance()->addNewThread($document->getSection(), $document);
		$forummember = $document->getForummember();
		if ($forummember !== null)
		{
			forum_ForummemberService::getInstance()->addNewThread($forummember, $document);
		}
	}

	/**
	 * @param forum_persistentdocument_thread $document
	 * @return void
	 */
	protected function preDelete($document)
	{
		parent::preDelete($document);
		$forumMember = $document->getForummember();
		if ($forumMember)
		{
			forum_ForummemberService::getInstance()->removeThread($forumMember, $document);
		}
		$section = $document->getSection();
		$section->setThreadcount($section->getThreadcount() - 1);
		
		forum_PostService::getInstance()->deleteByThread($document);
	}

	/**
	 * @param forum_persistentdocument_thread $document
	 * @param string $modelName Restrict to model $modelName.
	 *
	 * @return f_persistentdocument_PersistentDocument[]
	 */
	public function getAncestorsOf($document, $modelName = null)
	{
		$section = $document->getSection();
		if ($section instanceof forum_persistentdocument_section)
		{
			$docs = forum_SectionService::getInstance()->getAncestorsOf($section, $modelName);
			if ($modelName === null || $modelName == 'modules_forum/section')
			{
				$docs[] = $section;
			}
			return $docs;
		}
		return array();
	}	
	
	/**
	 * @see f_persistentdocument_DocumentService::getWebsiteId()
	 *
	 * @param forum_persistentdocument_thread $document
	 * @return integer
	 */
	public function getWebsiteId($document)
	{
		$section = $document->getSection();
		if ($section instanceof forum_persistentdocument_section)
		{
			return forum_SectionService::getInstance()->getWebsiteId($section);
		}
		return null;
	}
	
	/**
	 * @param forum_persistentdocument_thread $thread
	 * @param forum_persistentdocument_post $post
	 */
	public function addNewPost($thread, $post)
	{
		$thread->setPostcount($thread->getPostcount() + 1);
		$thread->setModificationdate($post->getCreationdate());
		$thread->setLastpostid($post->getId());
		try
		{
			$this->tm->beginTransaction();		
			$this->pp->updateDocument($thread);
			forum_SectionService::getInstance()->addNewPost($thread->getSection(), $post);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}		
	}
	
	/**
	 * @param forum_persistentdocument_thread $thread
	 */
	public function addRead($thread)
	{
		$thread->setReadcount($thread->getReadcount() + 1);
		try
		{
			$this->tm->beginTransaction();		
			$this->pp->updateDocument($thread);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}		
	}
	
	
	/**
	 * @param forum_persistentdocument_section $section
	 * @return array<forum_persistentdocument_thread>
	 */
	public function getPublishedBySection($section)
	{
		$query = $this->createQuery()->add(Restrictions::published())
		->add(Restrictions::eq('section', $section->getId()))
		->addOrder(Order::desc('document_modificationdate'));
		
		return $query->find();
	}
	
	/**
	 * @param forum_persistentdocument_thread $thread
	 * @param array<string, string> $parameters
	 */
	public function fillFromRequestParams($thread, $parameters)
	{
		DocumentHelper::setPropertiesTo($parameters, $thread);
	}
	
	/**
	 * @param forum_persistentdocument_section $section
	 */	
	public function deleteBySection($section)
	{
		$this->createQuery()->add(Restrictions::eq('section', $section->getId()))->delete();
	}
}