<?php
class media_PreferencesScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return media_persistentdocument_preferences
     */
    protected function initPersistentDocument()
    {
    	$document = ModuleService::getInstance()->getPreferencesDocument('media');
    	return ($document !== null) ? $document : media_PreferencesService::getInstance()->getNewDocumentInstance();
    }
}