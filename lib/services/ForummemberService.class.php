<?php
class forum_ForummemberService extends f_persistentdocument_DocumentService
{
	/**
	 * @var forum_ForummemberService
	 */
	private static $instance;

	/**
	 * @return forum_ForummemberService
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
	 * @return forum_persistentdocument_forummember
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_forum/forummember');
	}

	/**
	 * Create a query based on 'modules_forum/forummember' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_forum/forummember');
	}

	/**
	 * @param forum_persistentdocument_forummember $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$user = $document->getUser();
		if ($user !== null)
		{
			$document->setLabel($user->getLabel());
		}
	}
	
	/**
	 * @param forum_persistentdocument_forummember $forummember
	 * @param forum_persistentdocument_thread $thread
	 */
	public function addNewThread($forummember, $thread)
	{
		$forummember->setThreadcount($forummember->getThreadcount() + 1);
		try
		{
			$this->tm->beginTransaction();		
			$this->pp->updateDocument($forummember);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}		
	}
	
	/**
	 * @param forum_persistentdocument_forummember $forummember
	 * @param forum_persistentdocument_post $post
	 */
	public function addNewPost($forummember, $post)
	{
		$forummember->setPostcount($forummember->getPostcount() + 1);
		$forummember->setLastpostid($post->getId());
		try
		{
			$this->tm->beginTransaction();		
			$this->pp->updateDocument($forummember);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}		
	}
	
	/**
	 * @param forum_persistentdocument_forummember $forummember
	 * @param forum_persistentdocument_post $post
	 */
	public function removePost($forummember, $post)
	{
		$forummember->setPostcount($forummember->getPostcount() - 1);
		if ($forummember->getLastpostid() == $post->getId())
		{
			$forummember->setLastpostid(null);
		}
		try
		{
			$this->tm->beginTransaction();		
			$this->pp->updateDocument($forummember);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}		
	}	
	
	
	/**
	 * @param forum_persistentdocument_forummember $forummember
	 * @param forum_persistentdocument_thread $thread
	 */
	public function removeThread($forummember, $thread)
	{
		$forummember->setThreadcount($forummember->getThreadcount() - 1);
		try
		{
			$this->tm->beginTransaction();		
			$this->pp->updateDocument($forummember);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}		
	}
	
	/**
	 * @param users_persistentdocument_user $user
	 * @return forum_persistentdocument_forummember
	 */
	public final function getByUser($user)
	{
		return $this->createQuery()->add(Restrictions::eq('user', $user->getId()))->findUnique();
	}
	
	/**
	 * @return forum_persistentdocument_forummember
	 */
	public final function getCurrent()
	{
		$user = users_UserService::getInstance()->getCurrentUser();
		if ($user !== null)
		{
			return $this->getByUser($user);
		}
		return null;
	}
	
	/**
	 * @param forum_persistentdocument_forummember $forummember
	 * @param array<string, string> $parameters
	 */
	public function fillFromRequestParams($forummember, $parameters)
	{
		$forummember->setLabel('Auto generated');
		
		$user = $forummember->getUser();
		$user->setLabel('Auto generated');
		
		if (isset($parameters['sign']))
		{
			$forummember->setSign($parameters['sign']);
		}
		if (isset($parameters['pseudonym']))
		{
			$forummember->setPseudonym($parameters['pseudonym']);
		}
		if (isset($parameters['itemperpage']))
		{
			switch (intval($parameters['itemperpage'])) 
			{
				case 10:
				case 20:
				case 30:
					$forummember->setItemperpage(intval($parameters['itemperpage']));
					break;
				default:
					$forummember->setItemperpage(10);
					break;
			}	
		}

		if (isset($parameters['title']) && is_numeric($parameters['title']))
		{
			$user->setTitle(DocumentHelper::getDocumentInstance($parameters['title']));
		}
		
		if (isset($parameters['firstname']))
		{
			$user->setFirstname($parameters['firstname']);
		}
		
		if (isset($parameters['lastname']))
		{
			$user->setLastname($parameters['lastname']);
		}

		if (isset($parameters['email']))
		{
			$user->setEmail($parameters['email']);
		}
		
		if (isset($parameters['login']))
		{
			$user->setLogin($parameters['login']);
		}
		
		if (isset($parameters['password']))
		{
			$user->setPassword($parameters['password']);
		}
	}
	
	/**
	 * @param forum_persistentdocument_forummember $forummember
	 */
	public function create($forummember)
	{
		try 
		{
			$this->tm->beginTransaction();
			$user = $forummember->getUser();
			$user->save(users_FrontendgroupService::getInstance()->getDefaultGroup()->getId());
			$forummember->save();
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}
	}
	
	/**
	 * @param website_persistentdocument_page $activationPage
	 * @param forum_persistentdocument_forummember $forummember
	 */
	public function sendActivationCode($activationPage, $forummember)
	{
		$activationCode = $this->generateActivationCode($forummember);
		if ($activationCode === null)
		{
			return false;
		}
		$params = array('forumParam' => array('action' => 'activate', 'cmpref' => $forummember->getId(), 'activationcode' => $activationCode));
		$url = LinkHelper::getDocumentUrl($activationPage, null, $params);
		$link = '<a href="'. htmlspecialchars($url). '">Activer</a>';
		$ns = notification_NotificationService::getInstance();
		
		$notification = $ns->getNotificationByCodeName('modules_forum/memberactivation');
		$replacementArray = array('memberactivationurl' => $link, 'fullname' => $forummember->getFullnameAsHtml());
	
		$recipients = new mail_MessageRecipients();
		$recipients->setTo($forummember->getUser()->getEmail());
		
		return $ns->send($notification, $recipients, $replacementArray, 'forum');	
	}
	
	
	/**
	 * @param forum_persistentdocument_forummember $forummember
	 * @return
	 */
	public function generateActivationCode($forummember)
	{
		if ($forummember->getPublicationstatus() == 'DRAFT')
		{
			$string = $forummember->getId() . '-' . $forummember->getUser()->getId() . '-' . $forummember->getUser()->getEmail();
			return md5($string);
		}
		return null;
	}
	
	
	/**
	 * @param forum_persistentdocument_forummember $forummember
	 * @param string $activationCode
	 * @return Boolean
	 */
	public function activateMember($forummember, $activationCode)
	{
		$goodCode = $this->generateActivationCode($forummember);
		if ($goodCode == $activationCode)
		{
			$user = $forummember->getUser();
			if ($user->getPublicationstatus() == 'DRAFT')
			{
				$user->getDocumentService()->activate($user->getId());
			}
			
			if ($forummember->getPublicationstatus() == 'DRAFT')
			{
				$forummember->getDocumentService()->activate($forummember->getId());
			}
		}
		return $forummember->isPublished();			
	}
}