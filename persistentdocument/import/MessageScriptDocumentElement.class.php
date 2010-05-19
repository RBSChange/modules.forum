<?php
/**
 * forum_MessageScriptDocumentElement
 * @package modules.forum.persistentdocument.import
 */
class forum_MessageScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return forum_persistentdocument_message
     */
    protected function initPersistentDocument()
    {
    	return forum_MessageService::getInstance()->getNewDocumentInstance();
    }
}