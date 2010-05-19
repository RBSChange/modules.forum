<?php
class forum_ForumService extends f_persistentdocument_DocumentService
{
	/**
	 * @var forum_ForumService
	 */
	private static $instance;

	/**
	 * @return forum_ForumService
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
	 * @return forum_persistentdocument_forum
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_forum/forum');
	}

	/**
	 * Create a query based on 'modules_forum/forum' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_forum/forum');
	}

	/**
	 * @param forum_persistentdocument_forum $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		if ($document->getIspublic())
		{
			$document->setMembers(users_FrontendgroupService::getInstance()->getDefaultGroup());
		}
		else
		{
			
		}
	}

	/**
	 * Retourne les forums publiés du topic
	 *
	 * @param website_persistentdocument_topic $topic
	 * @return array<forum_persistentdocument_forum>
	 */
	public final function getPublishedByTopic($topic)
	{
		return $this->createQuery()->add(Restrictions::published())
			->add(Restrictions::childOf($topic->getId()))->find();	
	}
	
	/**
	 * Retourne le forum de la section
	 *
	 * @param forum_persistentdocument_section $section
	 * @return forum_persistentdocument_forum
	 */
	public final function getBySection($section)
	{
		return $this->createQuery()->add(Restrictions::ancestorOf($section->getId()))->findUnique();	
	}
	
	/**
	 * Retourne le forum du thread
	 *
	 * @param forum_persistentdocument_thread $thread
	 * @return forum_persistentdocument_forum
	 */
	public final function getByThread($thread)
	{
		return $this->getBySection($thread->getSection());	
	}

	/**
	 * Retourne le forum du post
	 * @param forum_persistentdocument_post $post
	 * @return forum_persistentdocument_forum
	 */
	public final function getByPost($post)
	{
		return $this->getByThread($post->getThread());	
	}
	
}