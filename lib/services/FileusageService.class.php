<?php
/**
 * @package modules.media
 */
class media_FileusageService extends f_persistentdocument_DocumentService
{
	/**
	 * @var media_FileusageService
	 */
	private static $instance;
		
	/**
	 * @var array<$modelName, array<$propertyName, $gettername>>
	 */
	private $_filePropertyNames = array();
	private $_fileDocumentPropertyNames = array();
	private $_fileFunctionNames = array();

	/**
	 * @return media_FileusageService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return media_persistentdocument_fileusage
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_media/fileusage');
	}

	/**
	 * Create a query based on 'modules_media/fileusage' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_media/fileusage');
	}

	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return array<>
	 */
	public function buildDocumentUsage($document)
	{
		$usagesArray = array();
		
		$modelName = $document->getPersistentModel()->getName();
		$this->generateFilePropertyNamesForModel($document->getPersistentModel());
		
		$documentProperties = $this->_fileDocumentPropertyNames[$modelName];
		if (count($documentProperties) > 0)
		{
			foreach ($documentProperties as $name => $getter) 
			{
				$data = $document->{$getter}();
				if (is_array($data))
				{
					$usagesArray = media_MediaUsageHelper::getByArray($data, $document->getId(), $usagesArray);
				}
				else if ($data instanceof media_persistentdocument_file) 
				{
					$usagesArray = media_MediaUsageHelper::getByDocument($data, $document->getId(), $usagesArray);
				}
			}
		}	
		
		$xhtmlProperties = $this->_filePropertyNames[$modelName];
		if (count($xhtmlProperties) > 0)
		{
			$rc = RequestContext::getInstance();
			if ($document->isLocalized())
			{
				foreach ($rc->getSupportedLanguages() as $lang) 
				{
					if ($document->isLangAvailable($lang))
					{
						try 
						{
							$rc->beginI18nWork($lang);
							if ($document->getPublicationStatus() != 'DEPRECATED')
							{
								foreach ($xhtmlProperties as $name => $getter) 
								{
									$usagesArray = media_MediaUsageHelper::getByXhtml($document->{$getter}(), $document->getId(), $lang, $usagesArray);
								}	
							}			
							$rc->endI18nWork();
						}
						catch (Exception $e)
						{
							$rc->endI18nWork($e);
						}
					}
				}
			}
			else if ($document->getPublicationStatus() != 'DEPRECATED')
			{
				foreach ($xhtmlProperties as $name => $getter) 
				{
					$usagesArray = media_MediaUsageHelper::getByXhtml($document->{$getter}(), $document->getId(), $document->getLang(), $usagesArray);
				}			
			}
		}
		
		if (count($this->_fileFunctionNames[$modelName]) > 0)
		{
			$usagesArray = $document->getDocumentService()->buildFileUsage($document, $usagesArray);
		}
		
		return $usagesArray;	
	}

	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return boolean
	 */
	public function hasFileTypeProperties($document)
	{
		return $this->hasFileTypePropertiesByModel($document->getPersistentModel());
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocumentModel $model
	 * @return boolean
	 */	
	public function hasFileTypePropertiesByModel($model)
	{
		$this->generateFilePropertyNamesForModel($model);
		$modelName = $model->getName();
		return (count($this->_filePropertyNames[$modelName]) + count($this->_fileDocumentPropertyNames[$modelName]) + count($this->_fileFunctionNames[$modelName])) > 0;
	}

	/**
	 * @param f_persistentdocument_PersistentDocumentModel $model
	 * @return array<$propertyName, $getterName>
	 */
	private function generateFilePropertyNamesForModel($model)
	{
		if (!isset($this->_filePropertyNames[$model->getName()]))
		{
			$documentPropertyNames = array();
			$xhtmlPropertyNames = array();
			foreach ($model->getEditablePropertiesInfos() as $propertyName => $propertyInfos) 
			{
				if ($propertyInfos->getType() == f_persistentdocument_PersistentDocument::PROPERTYTYPE_XHTMLFRAGMENT)
				{
					$xhtmlPropertyNames[$propertyName] = 'get' . ucfirst($propertyName);
				} 
				else if ($propertyInfos->isDocument() && 
					($propertyInfos->acceptType('modules_media/media') || $propertyInfos->acceptType('modules_media/securemedia')))
				{
					if ($propertyInfos->isArray())
					{
						$documentPropertyNames[$propertyName] = 'get' . ucfirst($propertyName) . 'Array';	
					}
					else
					{
						$documentPropertyNames[$propertyName] = 'get' . ucfirst($propertyName);
					}
				}
			}
			$this->_filePropertyNames[$model->getName()] = $xhtmlPropertyNames;		
			$this->_fileDocumentPropertyNames[$model->getName()] = $documentPropertyNames;
			$this->_fileFunctionNames[$model->getName()] = (f_util_ClassUtils::methodExists($model->getDocumentService(), 'buildFileUsage')) ? array(true) : array();
		}
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param array $usagesArray
	 */
	public function updateUsagesByDocument($document, $usagesArray)
	{
		try 
		{
			$this->tm->beginTransaction();
			$doDelete = f_util_ArrayUtils::isEmpty($usagesArray);	
			$fileUsage = $this->createQuery()->add(Restrictions::eq('documentid', $document->getId()))->findUnique();
			$removeUsage = array();
			$addUsage = array();
			
			if ($fileUsage === null && !$doDelete)
			{
				$addUsage = $usagesArray;
				$fileUsage = $this->getNewDocumentInstance();
				$fileUsage->setLabel("usage for #" . $document->getId());
				$fileUsage->setDocumentid($document->getId());
				$fileUsage->setUsages($usagesArray);
				$this->save($fileUsage);
			} 
			else if ($fileUsage !== null)
			{
				if ($doDelete)
				{
					$removeUsage = $fileUsage->getUsages();
					$this->delete($fileUsage);
				}
				else
				{
					$oldUsages = $fileUsage->getUsages();			
					$removeUsage = array_diff_key($oldUsages, $usagesArray);
					$addUsage = array_diff_key($usagesArray, $oldUsages);			
					if ((count($addUsage) + count($removeUsage)) > 0)
					{
						$fileUsage->setUsages($usagesArray);
						$this->save($fileUsage);
					}
				}
			}
			
			if ((count($addUsage) + count($removeUsage)) > 0)
			{
				$mfs = media_FileService::getInstance();
				
				foreach ($addUsage as $key => $value) 
				{
					list($mediaId, $mediaLang, $documentId, $documentLang) = $value;
					$mfs->addUsageInfo($mediaId, $mediaLang, $documentId, $documentLang);
				}
	
				foreach ($removeUsage as $key => $value) 
				{
					list($mediaId, $mediaLang, $documentId, $documentLang) = $value;
					$mfs->removeUsageInfo($mediaId, $mediaLang, $documentId, $documentLang);
				}
			}
			
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}
	}
}