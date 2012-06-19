<?php
/**
 * media_FileScriptDocumentElement
 * @package modules.media.persistentdocument.import
 */
class media_FileScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return media_persistentdocument_file
	 */
	protected function initPersistentDocument()
	{
		return media_FileService::getInstance()->getNewDocumentInstance();
	}
}