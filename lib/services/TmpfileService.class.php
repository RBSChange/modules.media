<?php
/**
 * @package modules.media
 */
class media_TmpfileService extends media_FileService
{
	/**
	 * @var media_TmpfileService
	 */
	private static $instance;

	/**
	 * @return media_TmpfileService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return media_persistentdocument_tmpfile
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_media/tmpfile');
	}

	/**
	 * Create a query based on 'modules_media/tmpfile' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_media/tmpfile');
	}

	/**
	 * Create a query based on 'modules_media/tmpfile' model.
	 * Only documents that are strictly instance of media_persistentdocument_tmpfile
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_media/tmpfile', false);
	}
	
	/**
	 * @param media_persistentdocument_tmpfile $tmpFile
	 * @return media_persistentdocument_file
	 */
	public final function convertToFile($tmpFile)
	{
		$file = media_FileService::getInstance()->getNewDocumentInstance();
		return $this->pp->mutate($tmpFile, $file);
	}
	
	/**
	 * @param media_persistentdocument_tmpfile $tmpFile
	 * @throws Exception
	 */
	function checkTmpFile($tmpFile)
	{
		Framework::fatal(__CLASS__ . ' ' . $tmpFile->getId() . ' ' . $tmpFile->getFilename());
		
		$tm = $this->getTransactionManager();
		try
		{
			$tm->beginTransaction();						
			$rc = RequestContext::getInstance();
			try
			{
				$rc->beginI18nWork($tmpFile->getLang());
				
				$containers = $this->getContainers($tmpFile);	
				if (count($containers) > 0)
				{
					$destModelName = 'modules_media/media';
					$sm = f_persistentdocument_PersistentDocumentModel::getInstance('media', 'securemedia');		
					foreach ($containers as $rel)
					{
						/* @var $rel f_persistentdocument_PersistentRelation */
						$pdoc = DocumentHelper::getDocumentInstance($rel->getDocumentId1());
						$prop = $pdoc->getPersistentModel()->getProperty($rel->getName());
						if ($prop && $prop->getDocumentModel()->isModelCompatible('modules_media/securemedia'))
						{
							$destModelName = 'modules_media/securemedia';
							break;
						}
					}
					
					$media = $this->transform($tmpFile, $destModelName);
					if ($media->isModified())
					{
						Framework::fatal(__CLASS__ . ' ' . implode(', ', $media->getModifiedPropertyNames()));
						$media->save();
					}
					
				}
				elseif ($tmpFile->getTreeId())
				{
					$media = $this->transform($tmpFile, 'modules_media/media');
					$media->save();
				}
				else
				{
					$this->delete($tmpFile);
				}							
				$rc->endI18nWork();
			}
			catch (Exception $e)
			{
				$rc->endI18nWork($e);
			}			
			$tm->commit();
		} 
		catch (Exception $e) 
		{
			$tm->rollback($e);
			throw $e;
		}
	}
	
	/**
	 * @deprecated
	 */
	function cleanOldFiles($start)
	{
		$chunkSize = 100;
		$startId = 0;
		$batchPath = 'modules/media/lib/bin/batchCleanTmpFile.php';
		do
		{
			$result = f_util_System::execHTTPScript($batchPath, array($chunkSize, $startId));
			if (is_numeric($result))
			{
				$startId = intval($result);
			}
			else
			{
				throw new Exception($result);
			}
		}
		while ($startId > 0);
	}
}