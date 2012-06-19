<?php
/**
 * @package modules.media
 */
class transformer_MediaMediaToMediaSecuremedia extends f_persistentdocument_transformer_DefaultTransformer
{
	public function __construct ()
	{
		$srcModel = f_persistentdocument_PersistentDocumentModel::getInstance('media', 'media');
		$destModel = f_persistentdocument_PersistentDocumentModel::getInstance('media', 'securemedia');
		parent::__construct($srcModel, $destModel);
	}

	/**
	 * @param media_persistentdocument_media $sourceDocument
	 * @param media_persistentdocument_securemedia $destDocument
	 */
	public function transform ($sourceDocument, &$destDocument)
	{
		parent::transform($sourceDocument, $destDocument);
		$ms = media_MediaService::getInstance();
		$mss = media_SecuremediaService::getInstance();
		
		$rc = RequestContext::getInstance();
		
		foreach ($rc->getSupportedLanguages() as $lang)
		{
			try
			{
				$rc->beginI18nWork($lang);
				if ($sourceDocument->isContextLangAvailable())
				{
					$srcPath = $ms->getOriginalPath($sourceDocument);
					if (!is_readable($srcPath))
					{
						throw new Exception(__METHOD__ . ' Invalid source file name : ' . $srcPath);
					}
					$ms->deleteFormatedMedia($sourceDocument, $lang);
					
					$destPath = $mss->getOriginalPath($sourceDocument);
					f_util_FileUtils::mkdir(dirname($destPath));
					
					if (Framework::isInfoEnabled())
					{
						Framework::info(__METHOD__ . " move ($srcPath => $destPath)");
					}
					rename($srcPath, $destPath);
					f_util_FileUtils::rmdir(dirname($srcPath));				   
				}
				$rc->endI18nWork();
			} catch (Exception $e)
			{
				$rc->endI18nWork($e);
			}
		}
	}
}