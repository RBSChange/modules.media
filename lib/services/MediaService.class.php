<?php
/**
 * @package modules.media
 */
class media_MediaService extends media_FileService
{
	/**
	 * @var media_MediaService
	 */
	private static $instance;


	/**
	 * @return media_MediaService
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
	 * @return media_persistentdocument_media
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_media/media');
	}

	/**
	 * Create a query based on 'modules_media/media' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_media/media');
	}

	/**
	 * @param media_persistentdocument_media $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$title = $document->getTitle();
		if (empty($title) && media_PreferencesService::getInstance()->useFileNameAsAlt())
		{
			$document->setTitle($document->getLabel());
		}
		
		$tmpFileName = $document->getNewFileName();
		if ($tmpFileName !== null && is_file($tmpFileName))
		{
			$document->setMediatype(MediaHelper::getMediaTypeByFilename($document->getFilename()));
		} 
		else if ($document->isPropertyModified('title'))
		{
			$document->setInfo($document->getInfo());
		}
		
		parent::preSave($document, $parentNodeId);
	}

	/**
	 * @param media_persistentdocument_tmpfile $tmpFile
	 * @return media_persistentdocument_media
	 */
	public final function importFromTempFile($tmpFile)
	{
		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__ . " : " . $tmpFile->__toString());
		}
		$originalMediaId = intval($tmpFile->getOriginalfileid());
		if ($originalMediaId > 0)
		{
			$media = DocumentHelper::getDocumentInstance($originalMediaId);
		}
		else
		{
			$media = $this->getNewDocumentInstance();
		}
		$realFileName = $tmpFile->getVoFilename();
		$tmpFilePath = $this->getAbsoluteFolder($tmpFile->getId(), $tmpFile->getLang()) . $realFileName;

		$media->setNewFileName($tmpFilePath, $realFileName);
		$media->setLabel($tmpFile->getVoLabel());
		$media->setMediatype(MediaHelper::getMediaTypeByFilename($tmpFilePath));
		$media->setModificationdate(null);

		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__ . " Result : " . $media->__toString() . " : " . $tmpFilePath);
		}
		return $media;
	}

	/**
	 * @param media_persistentdocument_media $document
	 * @param string $lang
	 * @param array $parameters
	 */
	public function generateUrl($document, $lang = null, $parameters = array())
	{
		$formatKey = media_FormatterHelper::getFormatKey($parameters);
		if ($formatKey !== null)
		{
			if ($lang == null)
			{
				if ($document->isContextLangAvailable())
				{
					$lang = RequestContext::getInstance()->getLang();
				}
				else
				{
					$lang = $document->getLang();
				}
			}

			$resourceExtension = $this->getFormatedExtension($document, $lang);
			if ($resourceExtension !== false)
			{
				$fileName = $document->getFilenameForLang($lang);
				$extension = f_util_FileUtils::getFileExtension($fileName);
				$resourceDir = $this->getFormattedAbsoluteFolder($document->getId(), $lang);
				return $this->convertMediaFileNameToUrl($resourceDir . rawurlencode(basename($fileName, '.' . $extension)) . ';' . $formatKey . $resourceExtension);
			}
		}
		return parent::generateUrl($document, $lang, $parameters);
	}

	/**
	 * @param media_persistentdocument_file $document
	 * @param string $lang
	 * @param array $parameters
	 * @return String
	 */
	public function generateAbsoluteUrl($document, $lang, $parameters)
	{
		$formatKey = media_FormatterHelper::getFormatKey($parameters);
		if ($formatKey !== null)
		{
			if ($lang == null)
			{
				if ($document->isContextLangAvailable())
				{
					$lang = RequestContext::getInstance()->getLang();
				}
				else
				{
					$lang = $document->getLang();
				}
			}

			$resourceExtension = $this->getFormatedExtension($document, $lang);
			if ($resourceExtension !== false)
			{
				$fileName = $document->getFilenameForLang($lang);
				$extension = f_util_FileUtils::getFileExtension($fileName);
				$resourceDir = $this->getFormattedAbsoluteFolder($document->getId(), $lang);
				return $this->convertMediaFileNameToAbsoluteUrl($resourceDir . rawurlencode(basename($fileName, '.' . $extension)) . ';' . $formatKey . $resourceExtension);
			}
		}
		return parent::generateAbsoluteUrl($document, $lang, $parameters);
	}

	/**
	 * @param media_persistentdocument_media $document
	 * @param string $lang
	 */
	private function getFormatedExtension($document, $lang)
	{
		$mimeType = $document->getMimetypeForLang($lang);
		switch ($mimeType)
		{
			case 'image/jpeg':
				return MediaHelper::EXTENSION_JPEG;
			case 'image/png':
				return MediaHelper::EXTENSION_PNG;
			case 'image/gif':
				return MediaHelper::EXTENSION_GIF;
		}
		return false;
	}

	/**
	 * @param media_persistentdocument_media $media
	 * @return String
	 */
	public function getTextForIndexer($media)
	{
		$cacheEntry = null;
		$extractedText = null;

		// get extractedText
		try
		{
			if ($this->putExtractedTextInCache($media))
			{
				$cache = f_DataCacheService::getInstance();
				$cacheEntry = $cache->readFromCache('media_Extractedtext', $media->getId(), array('modules_media/media'));
				if ($cache->exists($cacheEntry))
				{
					$extractedText = $cacheEntry->getValue('text');
				}
			}

			switch ($media->getMediatype())
			{
				case MediaHelper::TYPE_PDF:
					$pdfExtractor = new indexer_PDFExtractor(MediaHelper::getOriginalPath($media, true));
					$extractedText = f_util_StringUtils::htmlToText($pdfExtractor->getText(), false);
					break;
				case MediaHelper::TYPE_DOC:
				case MediaHelper::TYPE_DOCX:
				case MediaHelper::TYPE_DOCM:
				case MediaHelper::TYPE_XLS:
				case MediaHelper::TYPE_XLSX:
				case MediaHelper::TYPE_ODT:
				case MediaHelper::TYPE_ODS:
					$officeExtractor = new indexer_OfficeExtractor(MediaHelper::getOriginalPath($media, true));
					$extractedText = f_util_StringUtils::htmlToText($officeExtractor->getText(), false);
					break;
			}

			if (f_DataCacheService::getInstance()->isEnabled() && $this->putExtractedTextInCache($media))
			{
				$cacheEntry->setValue('text', $extractedText);
				$cache->writeToCache($cacheEntry);
			}
		}
		catch (Exception $e)
		{
			Framework::error(__METHOD__ . ': ' . $e->getMessage());
		}

		// always append title, description and credit
		$textValue = $media->getTitle() . "\n" . $media->getDescription() . "\n" . $media->getCredit();
		if ($extractedText !== null)
		{
			$textValue .= "\n".$extractedText;
		}
		return $textValue;
	}

	/**
	 * @param media_persistentdocument_media $media
	 * @return Boolean
	 */
	private function putExtractedTextInCache($media)
	{
		if (!f_DataCacheService::getInstance()->isEnabled())
		{
			return false;
		}
		switch ($media->getMediatype())
		{
			case MediaHelper::TYPE_PDF:
			case MediaHelper::TYPE_DOC:
			case MediaHelper::TYPE_DOCX:
			case MediaHelper::TYPE_DOCM:
			case MediaHelper::TYPE_XLS:
			case MediaHelper::TYPE_XLSX:
			case MediaHelper::TYPE_ODT:
			case MediaHelper::TYPE_ODS:
				return true;
			default:
				return false;
		}
	}
	
	/**
	 * @param media_persistentdocument_media $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$data = parent::getResume($document, $forModuleName, $allowedSections);
		
		if ($document->isContextLangAvailable())
		{
			$lang = RequestContext::getInstance()->getLang();			
		}
		else
		{
			$lang = $document->getLang();
			
		}
		$rc = RequestContext::getInstance();
		try 
		{
			$rc->beginI18nWork($lang);
			$info = $document->getCommonInfo();
			
			$data['content'] = array(
				'mimetype' => $document->getMimetype(),
				'size' => $info['size']
			);
			
			$data['content']['previewimgurl'] = array('id' => $document->getId(), 'lang' => $lang);
			if ($document->getMediatype() == MediaHelper::TYPE_IMAGE)
			{
				$pixelsLabel = f_Locale::translateUI('&modules.media.doceditor.pixels;');
				$data['content']['width'] = $info['width'].' '.$pixelsLabel;
				$data['content']['height'] = $info['height'].' '.$pixelsLabel;
				$data['content']['previewimgurl']['image'] = LinkHelper::getUIActionLink('media', 'BoDisplay')
					->setQueryParameter('cmpref', $document->getId())
					->setQueryParameter('max-height', 128)
					->setQueryParameter('max-width', 128)
					->setQueryParameter('lang', RequestContext::getInstance()->getLang())
					->setQueryParameter('time', date_Calendar::now()->getTimestamp())->getUrl();
			}
			else
			{
				$data['content']['previewimgurl']['image'] = '';
			}
			
			//$mediaId, $mediaLang, $documentId, $documentLang
			$mediausages = array();
			$i = 0;
			foreach ($document->getAllUsages() as $info) 
			{
				if ($i == 5) 
				{
					$mediausages[] = '...';
					break;
				}
				$i++;
				try 
				{
					$doc = $this->pp->getDocumentInstance($info[2]);
					$model = $doc->getPersistentModel();
					$moduleName = f_Locale::translateUI('&modules.'. $model->getModuleName() .'.bo.general.Module-name;');		
					$docName = f_Locale::translateUI('&modules.'. $model->getModuleName() .'.document.'. $model->getDocumentName().'.Document-name;');
					$lang = ($info[3] != '') ?  $info[3] : $doc->getLang();
					$mediausages[] = $doc->getLabelForLang($lang) . ' (' . $moduleName . ', ' .$docName . ')';
				}
				catch (Exception $e)
				{
					if (Framework::isDebugEnabled())
					{
						Framework::exception($e);
					}
					$mediausages[] = f_Locale::translateUI('&modules.media.bo.doceditor.property.Mediausages-error;', array('num', $info[2]));
				}		
			}
			if (count($mediausages) == 0)
			{
				$mediausages[] = f_Locale::translateUI('&modules.media.bo.doceditor.property.Mediausages-not-found;');
			}
			$data['usages']['mediausages'] = $mediausages;
			
			$rc->endI18nWork();
		}
		catch (Exception $e)
		{
			$rc->endI18nWork($e);
		}

		return $data;
	}
}