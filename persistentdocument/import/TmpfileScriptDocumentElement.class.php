<?php
/**
 * media_TmpfileScriptDocumentElement
 * @package modules.media.persistentdocument.import
 */
class media_TmpfileScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return media_persistentdocument_tmpfile
	 */
	protected function initPersistentDocument()
	{
		return media_TmpfileService::getInstance()->getNewDocumentInstance();
	}
}