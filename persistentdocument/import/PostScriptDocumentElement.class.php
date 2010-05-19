<?php

class forum_PostScriptDocumentElement extends import_ScriptDocumentElement
{

    /**
     * @return forum_persistentdocument_post
     */
    protected function initPersistentDocument ()
    {
        $post = forum_PostService::getInstance()->getNewDocumentInstance();
        $thread = $this->getParent()->getPersistentDocument();
        $post->setThread($thread);
        $memberscript = $this->script->getElementById($this->attributes['forummemberid-ref']);
        unset($this->attributes['forummemberid-ref']);
        $post->setForummemberid($memberscript->getPersistentDocument()->getId());

        return $post;
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