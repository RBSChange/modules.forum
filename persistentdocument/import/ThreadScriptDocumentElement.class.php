<?php

class forum_ThreadScriptDocumentElement extends import_ScriptDocumentElement
{

    /**
     * @return forum_persistentdocument_thread
     */
    protected function initPersistentDocument ()
    {
        $thread = forum_ThreadService::getInstance()->getNewDocumentInstance();
        $section = $this->getParent()->getPersistentDocument();
        $thread->setSection($section);
        
        $memberscript = $this->script->getElementById($this->attributes['forummemberid-ref']);
        unset($this->attributes['forummemberid-ref']);
        $thread->setForummemberid($memberscript->getPersistentDocument()->getId());
        
        return $thread;
    }
    
     /**
     * @return import_ScriptDocumentElement
     */
    protected function getParentDocument()
    {
    	return null;
    }
    
    public function endProcess ()
    {
        $document = $this->getPersistentDocument();
        if ($document->getPublicationstatus() == 'DRAFT')
        {
            $document->getDocumentService()->activate($document->getId());
        }
    }

}