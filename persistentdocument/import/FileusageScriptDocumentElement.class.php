<?php
/**
 * media_FileusageScriptDocumentElement
 * @package modules.media.persistentdocument.import
 */
class media_FileusageScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return media_persistentdocument_fileusage
     */
    protected function initPersistentDocument()
    {
    	return media_FileusageService::getInstance()->getNewDocumentInstance();
    }
}