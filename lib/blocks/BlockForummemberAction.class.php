<?php
class forum_BlockForummemberAction extends forum_BlockBaseAction
{
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	public function execute($context, $request)
	{
		$this->setPreferences($context, $request);
		$this->setParameter('back', false);
		
		$action = $request->getParameter('action');
		if (! empty($action))
		{
			$method = 'execute' . ucfirst($action);
			if (f_util_ClassUtils::methodExists($this, $method))
			{
				$this->setParameter('action', $action);
				return $this->{$method}($context, $request);
			}
		}
		
		$user = users_UserService::getInstance()->getCurrentUser();
		if ($user !== null)
		{
			$forummember = forum_ForummemberService::getInstance()->getByUser($user);
			if ($forummember === null)
			{
				return $this->executeRegisterMember($context, $request);
			}
		}
		else
		{
			return $this->executeAutenthicate($context, $request);
		}
		
		$request->setParameter(K::COMPONENT_ID_ACCESSOR, $forummember->getId());
		return $this->executeView($context, $request);
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeView($context, $request)
	{
		$forummember = $this->getDocumentParameter();
		$this->setParameter('forummember', $forummember);
		$preferences = $this->getPreferences();
		$preferences->setEditForumMember($forummember);
		
		$backUrl = $request->getParameter('back');
		if (!empty($backUrl))
		{	
			$this->setParameter('back', $backUrl);
		}
				
		return block_BlockView::SUCCESS;
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeAutenthicate($context, $request)
	{
		$user = users_UserService::getInstance()->getCurrentUser();
		if ($user !== null)
		{
			return $this->executeRegisterMember($context, $request);
		}
		
		$login = $request->getParameter('login');
		$password = $request->getParameter('password');
		
		$us = users_UserService::getInstance();
		if (!empty($login) || !empty($password))
		{
			if ($us->checkIdentityByLogin($login, $password))
			{
				$user = $us->getUserByLogin($login);
				$us->authenticate($user);
				$forummember = forum_ForummemberService::getInstance()->getByUser($user);
				if ($forummember === null)
				{
					return $this->executeRegisterMember($context, $request);
				}
				
				$backUrl = $request->getParameter('back');
				if (!empty($backUrl))
				{
					Controller::getInstance()->redirectToUrl($backUrl);
					return block_BlockView::NONE;			
				}
				
				$request->setParameter(K::COMPONENT_ID_ACCESSOR, $forummember->getId());
				return $this->executeView($context, $request);
			}
			else
			{
				$errors = array();
				$errors[] = f_Locale::translate('&modules.users.frontoffice.authentication.BadAuthentication;');
				$this->setParameter('errors', $errors);
			}
		}
		return 'Login';
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeLogout($context, $request)
	{	
		$us = users_UserService::getInstance();
		$us->authenticate(null);
		$backUrl = $request->getParameter('back');
		if (!empty($backUrl))
		{
			Controller::getInstance()->redirectToUrl($backUrl);
			return block_BlockView::NONE;			
		}
		return $this->executeAutenthicate($context, $request);	
	}
		/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeRegisterMember($context, $request)
	{
		$us = users_UserService::getInstance();
		$user = $us->getCurrentUser();
			
		$pseudonym = $request->getParameter('pseudonym');
		
		$ffm = forum_ForummemberService::getInstance();
		$forummember = $ffm->getNewDocumentInstance();
		$forummember->setUser($user);
		
		$sign = $request->getParameter('sign');
		if (!empty($sign))
		{
			$forummember->setSign($sign);
		}
		
		$itemperpage = intval($request->getParameter('itemperpage', 10));
		if ($itemperpage != 20 && $itemperpage != 30)
		{
			$itemperpage = 10;
		}
		$forummember->setItemperpage($itemperpage);
			
		if (!empty($pseudonym))
		{
			$forummember->setPseudonym($pseudonym);
			$ffm->save($forummember);
			$ffm->activate($forummember->getId());
			
			$backUrl = $request->getParameter('back');
			if (!empty($backUrl))
			{
				Controller::getInstance()->redirectToUrl($backUrl);
				return block_BlockView::NONE;			
			}
			
			$request->setParameter(K::COMPONENT_ID_ACCESSOR, $forummember->getId());
			return $this->executeView($context, $request);
		}
		
		$context->addScript('modules.website.lib.bbeditor.jtageditor');
		$context->addStyle('modules.website.jtageditor');
		$this->setParameter('forummember', $forummember);
		return 'Register';
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeCreate($context, $request)
	{
		$ffm = forum_ForummemberService::getInstance();
		$forummember = $ffm->getNewDocumentInstance();
		
		$ufeu = users_FrontenduserService::getInstance();
		$user = $ufeu->getNewDocumentInstance();
		$forummember->setUser($user);
		
		$parameters = $this->extractRequestParams($request, array('title', 'firstname', 'lastname', 'email', 'login', 'pseudonym', 'sign', 'itemperpage'));
		$ffm->fillFromRequestParams($forummember, $parameters);
		
		if ($request->hasParameter('create'))
		{
			try
			{
				$errors = array();
			
				$error = $this->getEmptyError($user->getLogin(), 'Identifiant');
				if ($error !== null)
				{
					$errors[] = $error;
				} 
				else 
				{
					$error = $this->getLoginError($user->getLogin(), 'Identifiant');
					if ($error !== null)
					{
						$errors[] = $error;
					}
				}
				
				$password = $request->getParameter('memberpwd');
				$error = $this->getEmptyError($password, 'Mot de passe');
				if ($error === null)
				{
					$passwordconfirm = $request->getParameter('memberpwdconfirm');
					$error = $this->getPasswordError($password, $passwordconfirm, 'Mot de passe');
					if ($error !== null)
					{
						$errors[] = $error;
					}
					else
					{
						$user->setPassword($password);
					}
				}
				else
				{
					$errors[] = $error;
				}
				
				$error = $this->getEmptyError($user->getFirstname(), 'Prénom');
				if ($error !== null){$errors[] = $error;}
				
				$error = $this->getEmptyError($user->getLastname(), 'Nom');
				if ($error !== null){$errors[] = $error;}
				
				$error = $this->getEmptyError($user->getEmail(), 'Adresse e-mail');
				if ($error !== null){$errors[] = $error;}
				
				$error = $this->getEmptyError($forummember->getPseudonym(), 'Pseudonyme');
				if ($error !== null){$errors[] = $error;}
				
				if (count($errors) > 0)
				{
					$this->setParameter('errors', $errors);
				}
				else 
				{
					$ffm->create($forummember);
					$ffm->sendActivationCode($context->getPageDocument(), $forummember);
					$request->setParameter(K::COMPONENT_ID_ACCESSOR, $forummember->getId());
					$this->setParameter('activationsend', true);
					return $this->executeView($context, $request);						
				}
			}
			catch (Exception $e)
			{
				Framework::exception($e);
			}
		}	
		$context->addScript('modules.website.lib.bbeditor.jtageditor');
		$context->addStyle('modules.website.jtageditor');
		$this->setParameter('forummember', $forummember);	
		return 'Create';
	}
	
		/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeActivate($context, $request)
	{
		$forummember = $this->getForummemberParameter();
		$activationCode = $request->getParameter('activationcode');
		if ($forummember !== null && $activationCode !== null)
		{
			$activated = $forummember->getDocumentService()->activateMember($forummember, $activationCode);
			if (!$activated)
			{
				$this->setParameter('activationerror', true);	
			}
			else
			{
				$this->setParameter('activationsuccess', true);
			}
		}
		return $this->executeView($context, $request);
	}
	
	
	/**
	 * @return forum_persistentdocument_forummember
	 */
	private function getForummemberParameter()
	{
		return $this->getDocumentParameter();
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String view name
	 */
	private function executeEdit($context, $request)
	{
		$ffm = forum_ForummemberService::getInstance();
		$forummember = $this->getForummemberParameter();

		$preferences = $this->getPreferences();
		$preferences->setEditForumMember($forummember);
		if (!$preferences->canEditForumMember())
		{
			return block_BlockView::NONE;
		}
		
		$user = $forummember->getUser();		
		$parameters = $this->extractRequestParams($request, array('title', 'firstname', 'lastname', 'email', 'pseudonym', 'sign', 'itemperpage'));
		$ffm->fillFromRequestParams($forummember, $parameters);		
	
		if ($request->hasParameter('modify'))
		{
			try
			{	
				
				$errors = array();
				
				$password = $request->getParameter('memberpwd');
				$error = $this->getEmptyError($password, 'Mot de passe');
				if ($error === null)
				{
					$passwordconfirm = $request->getParameter('memberpwdconfirm');
					$error = $this->getPasswordError($password, $passwordconfirm, 'Mot de passe');
					if ($error === null)
					{
						$user->setPassword($password);
					}
					else 
					{
						$errors[] = $error;
					}
				}
		
				$error = $this->getEmptyError($user->getFirstname(), 'Prénom');
				if ($error !== null){$errors[] = $error;}
				
				$error = $this->getEmptyError($user->getLastname(), 'Nom');
				if ($error !== null){$errors[] = $error;}
				
				$error = $this->getEmptyError($user->getEmail(), 'Adresse e-mail');
				if ($error !== null){$errors[] = $error;}
				
				$error = $this->getEmptyError($forummember->getPseudonym(), 'Pseudonyme');
				if ($error !== null){$errors[] = $error;}
				
				if (count($errors) > 0)
				{
					$this->setParameter('errors', $errors);
				}
				else 
				{
					$forummember->setModificationdate(null);
					$ffm->save($forummember);
					$request->setParameter(K::COMPONENT_ID_ACCESSOR, $forummember->getId());
					return $this->executeView($context, $request);						
				}
			}
			catch (Exception $e)
			{
				Framework::exception($e);
			}
		}	
		
		$context->addScript('modules.website.lib.bbeditor.jtageditor');
		$context->addStyle('modules.website.jtageditor');
		$this->setParameter('forummember', $forummember);	
		return 'Edit';
	}
    
    private function getEmptyError($value, $fieldName)
    {
    	if (empty($value))
    	{
    		return 'Le champ "'.$fieldName.'" ne peut être vide.';
    	}
    	return null;
    }
    
    private function getPasswordError($value1, $value2, $fieldName)
    {
    	if ($value1 != $value2)
    	{
    		return 'Le champ "'.$fieldName.'" n\'a pas été confirmé.';
    	}
    	return null;
    }
    
    private function getLoginError($login, $fieldName)
    {
    	$user = users_UserService::getInstance()->getUserByLogin($login);
    	if ($user !== null)
    	{
    		return 'La valeur du champ "'.$fieldName.'" est déjà utilisé.';
    	}
    	return null;
    }
}