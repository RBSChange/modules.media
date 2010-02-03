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
		return $this->pp->createQuery('modules_media/tmpfile');
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
	 * @param date_Calendar $start
	 */
	function cleanOldFiles($start)
	{
		$tmpFiles = $this->createQuery()
		->add(Restrictions::eq('model', 'modules_media/tmpfile'))
		->add(Restrictions::lt('creationdate', $start))->find();

		$rc = RequestContext::getInstance();
		foreach ($tmpFiles as $tmpFile)
		{
			$rc->beginI18nWork($tmpFile->getLang());

			$containers = $this->getContainers($tmpFile);
			if (count($containers) > 0)
			{
				if (Framework::isDebugEnabled())
				{
					Framework::debug('Convert tmp file : ' . $tmpFile->__toString()." to file");
				}
				// tmp file is used in some documents, convert it to file
				$this->convertToFile($tmpFile);
			}
			else
			{
				if (Framework::isDebugEnabled())
				{
					Framework::debug('Remove tmp file : ' . $tmpFile->__toString());
				}
				$this->delete($tmpFile);
			}

			$rc->endI18nWork();
		}
	}
}