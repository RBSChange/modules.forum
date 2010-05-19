<?php

class forum_SectionScriptDocumentElement extends import_ScriptDocumentElement
{

    /**
     * @return forum_persistentdocument_section
     */
    protected function initPersistentDocument ()
    {
        return forum_SectionService::getInstance()->getNewDocumentInstance();
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