<?php
class transformer_MediaTmpfileToMediaMedia extends f_persistentdocument_transformer_DefaultTransformer
{
	public function __construct()
	{
		$srcModel = f_persistentdocument_PersistentDocumentModel::getInstance('media', 'tmpfile');
		$destModel = f_persistentdocument_PersistentDocumentModel::getInstance('media', 'media');
		parent::__construct($srcModel, $destModel);
	}
	
	/**
	 * @param media_persistentdocument_tmpfile $sourceDocument
	 * @param media_persistentdocument_media $destDocument
	 */
	public function transform($sourceDocument, &$destDocument)
	{
		parent::transform($sourceDocument, $destDocument);
		$tfs = media_TmpfileService::getInstance();
		$ms = media_MediaService::getInstance();
		$rc = RequestContext::getInstance();
		foreach ($sourceDocument->getI18nInfo()->getLangs() as $lang)
		{
			try
			{
				$rc->beginI18nWork($lang);
				
				$srcPath = $tfs->getOriginalPath($sourceDocument);
				if (!is_readable($srcPath))
				{
					throw new Exception(__METHOD__ . ' Invalid source file name : ' . $srcPath);
				}
				$destPath = $ms->getOriginalPath($sourceDocument);
				f_util_FileUtils::mkdir(dirname($destPath));
				if ($srcPath != $destPath)
				{
					if (Framework::isInfoEnabled())
					{
						Framework::info(__METHOD__ . " move ($srcPath => $destPath)");
					}
					rename($srcPath, $destPath);
					f_util_FileUtils::rmdir(dirname($srcPath));
				}
				$mediaType = MediaHelper::getMediaTypeByFilename($destDocument->getFilename());
				$destDocument->setMediatype($mediaType);
				
				$rc->endI18nWork();
			}
			catch (Exception $e)
			{
				$rc->endI18nWork($e);
			}
		}
	}
}