<?php
class media_MediaScriptDocumentElement extends import_ScriptDocumentElement
{
	private $tmpFileName;
	
	/**
	 * @return media_persistentdocument_media
	 */
	protected function initPersistentDocument()
	{
		return media_MediaService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return import_ScriptDocumentElement
	 */
	protected function getParentDocument()
	{
		$parent = parent::getParentDocument();
		if ($parent !== null && $parent->getPersistentDocument() instanceof generic_persistentdocument_folder ) 
		{
			return $parent;
		}
		return null;	 
	}
	
	protected function getDocumentProperties()
	{
		$properties = parent::getDocumentProperties();

		if (isset($properties['path']))
		{
			$ressourcePath = f_util_FileUtils::buildProjectPath($properties['path']);
			$fileName = basename($ressourcePath);
			$properties['filename'] = $fileName;
			$properties['mediatype'] = MediaHelper::getMediaTypeByFilename($fileName);
			$extention = f_util_FileUtils::getFileExtension($fileName, true);
			if (!isset($properties['label']))
			{
				$properties['label'] = str_replace($extention, '', $fileName); 
			}
			$this->tmpFileName = f_util_FileUtils::getTmpFile('ImportedFile') . $extention; 
			copy($ressourcePath, $this->tmpFileName);
			$properties['newFileName'] = $this->tmpFileName;
			unset($properties['path']);
		}
		else
		{
			throw new Exception('Invalid argument "media[@path]"');
		}
		
		return $properties;
	}
}