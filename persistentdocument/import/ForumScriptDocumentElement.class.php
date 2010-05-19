<?php
class forum_ForumScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return forum_persistentdocument_forum
     */
    protected function initPersistentDocument()
    {
    	return forum_ForumService::getInstance()->getNewDocumentInstance();
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