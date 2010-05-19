<?php
class forum_PostService extends forum_MessageService
{
	/**
	 * @var forum_PostService
	 */
	private static $instance;

	/**
	 * @return forum_PostService
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
	 * @return forum_persistentdocument_post
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_forum/post');
	}

	/**
	 * Create a query based on 'modules_forum/post' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_forum/post');
	}

	/**
	 * @param forum_persistentdocument_post $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		parent::preInsert($document, $parentNodeId);
		$thread  = $document->getThread();
		if ($thread !== null)
		{
			$document->setIndex($thread->getPostcount());
		}
	}

	/**
	 * @param forum_persistentdocument_post $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function postInsert($document, $parentNodeId = null)
	{
		parent::postInsert($document, $parentNodeId);
		forum_ThreadService::getInstance()->addNewPost($document->getThread(), $document);
		
		$forummember = $document->getForummember();
		if ($forummember !== null)
		{
			forum_ForummemberService::getInstance()->addNewPost($forummember, $document);
		}
	}


	/**
	 * @param forum_persistentdocument_post $document
	 * @return void
	 */
	protected function preDelete($document)
	{
		parent::preDelete($document);
		$forumMember = $document->getForummember();
		if ($forumMember)
		{
			forum_ForummemberService::getInstance()->removePost($forumMember, $document);
		}
	}

	/**
	 * @param forum_persistentdocument_post $document
	 * @param string $modelName Restrict to model $modelName.
	 *
	 * @return f_persistentdocument_PersistentDocument[]
	 */
	public function getAncestorsOf($document, $modelName = null)
	{
		$thread = $document->getThread();
		if ($thread instanceof forum_persistentdocument_thread)
		{
			$docs = forum_ThreadService::getInstance()->getAncestorsOf($thread, $modelName);
			if ($modelName === null || $modelName == 'modules_forum/thread')
			{
				$docs[] = $thread;
			}
			return $docs;
		}
		return array();
	}
		
	/**
	 * @see f_persistentdocument_DocumentService::getWebsiteId()
	 *
	 * @param forum_persistentdocument_post $document
	 * @return integer
	 */
	public function getWebsiteId($document)
	{
		$thread = $document->getThread();
		if ($thread instanceof forum_persistentdocument_thread)
		{
			return $thread->getDocumentService()->getWebsiteId($thread);
		}
		return null;
	}


	/**
	 * @param forum_persistentdocument_thread $thread
	 * @return array<forum_persistentdocument_post>
	 */
	public function getPublishedByThread($thread)
	{
		$query = $this->createQuery()->add(Restrictions::published())
		->add(Restrictions::eq('thread', $thread->getId()))
		->addOrder(Order::asc('index'));
		return $query->find();		
	}
	
	/**
	 * @param forum_persistentdocument_post $post
	 * @param array<string, string> $parameters
	 */
	public function fillFromRequestParams($post, $parameters)
	{
		DocumentHelper::setPropertiesTo($parameters, $post);
	}
	
	/**
	 * @param forum_persistentdocument_thread $thread
	 */	
	public function deleteByThread($thread)
	{
		try
		{
			$this->tm->beginTransaction();				
			$section = $thread->getSection();
			
			$posts = $this->createQuery()->add(Restrictions::eq('thread', $thread->getId()))->find();
			foreach ($posts as $post) 
			{
				if ($section->getLastpostid() == $post->getId())
				{
					$section->setLastpostid(null);
				}
				
				$this->delete($post);
			}
			
			$section->setPostcount($section->getPostcount() - count($posts));
			$this->pp->updateDocument($section);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}
	}
}