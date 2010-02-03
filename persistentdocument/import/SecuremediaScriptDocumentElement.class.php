<?php
class media_SecuremediaScriptDocumentElement extends media_MediaScriptDocumentElement
{
    /**
     * @return media_persistentdocument_securemedia
     */
    protected function initPersistentDocument()
    {
    	return media_SecuremediaService::getInstance()->getNewDocumentInstance();
    }
}