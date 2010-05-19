<?php
class forum_SectionService extends f_persistentdocument_DocumentService
{
	/**
	 * @var forum_SectionService
	 */
	private static $instance;

	/**
	 * @return forum_SectionService
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
	 * @return forum_persistentdocument_section
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_forum/section');
	}

	/**
	 * Create a query based on 'modules_forum/section' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_forum/section');
	}


	/**
	 * @param forum_persistentdocument_forum $forum
	 * @return array<forum_persistentdocument_section>
	 */
	public function getPublishedByForum($forum)
	{
		$query = $this->createQuery()->add(Restrictions::published())
		->add(Restrictions::childOf($forum->getId()));
		return $query->find();		
	}

	/**
	 * @param forum_persistentdocument_section $section
	 * @param forum_persistentdocument_thread $thread
	 */
	public function addNewThread($section, $thread)
	{
		$section->setThreadcount($section->getThreadcount() +1);
		$section->setModificationdate($thread->getCreationdate());
		try
		{
			$this->tm->beginTransaction();		
			$this->pp->updateDocument($section);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}		
	}
	
	/**
	 * @param forum_persistentdocument_section $section
	 * @param forum_persistentdocument_post $post
	 */
	public function addNewPost($section, $post)
	{
		$section->setPostcount($section->getPostcount() + 1);
		$section->setModificationdate($post->getCreationdate());
		$section->setLastpostid($post->getId());
		try
		{
			$this->tm->beginTransaction();		
			$this->pp->updateDocument($section);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}		
	}
}