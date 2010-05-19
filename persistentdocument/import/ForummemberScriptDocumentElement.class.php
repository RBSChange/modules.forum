<?php
class forum_ForummemberScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return forum_persistentdocument_forummember
     */
    protected function initPersistentDocument()
    {
    	$defaultGroup = users_FrontendgroupService::getInstance()->getDefaultGroup();
    	
    	$user = users_FrontenduserService::getInstance()->getNewDocumentInstance();
    	DocumentHelper::setPropertiesTo($this->attributes, $user);
 
    	$user->save($defaultGroup->getId());
  		$member = forum_ForummemberService::getInstance()->getNewDocumentInstance();
  		
  		$member->setUser($user);
    	return $member;
    }
    
    protected function getParentDocument()
    {
    	return null;
    }
    
    protected function getDocumentProperties()
    {
    	$attrs = array();
    	if (isset($this->attributes['pseudonym'])){$attrs['pseudonym'] = $this->attributes['pseudonym'];}
    	if (isset($this->attributes['sign'])){$attrs['sign'] = $this->attributes['sign'];}
        return $attrs;   
    }
    
    public function endProcess ()
    {
        $document = $this->getPersistentDocument();
        if ($document->getPublicationstatus() == 'DRAFT')
        {
        	$service = forum_ForummemberService::getInstance();
        	$code = $service->generateActivationCode($document);
        	$service->activateMember($document, $code);
        }
    }
}