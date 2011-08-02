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
	 * @return String
	 */
	public function generateAbsoluteUrl($document, $lang, $parameters)
	{
		return parent::generateAbsoluteUrl($document, $lang, $parameters);
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
			$mediausagesCount = count($document->getAllUsages());
			$ls = LocaleService::getInstance();
			if ($mediausagesCount == 0)
			{
				$mediausages = $ls->transBO('m.media.bo.doceditor.property.mediausages-not-found');
			}
			else
			{
				$mediausages = $mediausagesCount;
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

	/**
	 * @param media_persistentdocument_media $document
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
	public function addTreeAttributes($document, $moduleName, $treeType, &$nodeAttributes)
	{
		if ($treeType == 'wlist')
		{
			$nodeAttributes['countreferences'] =  $document->countReferences();
		}

		switch ($document->getMediatype())
		{
			case MediaHelper::TYPE_IMAGE:
				$nodeAttributes['actualtype'] = 'modules_media_image';
				$nodeAttributes['hasPreviewImage'] = true;
				if ($treeType == 'wmultilist')
				{
					$lang = RequestContext::getInstance()->getLang();
					$alt = htmlspecialchars($document->getTitle(), ENT_COMPAT, 'UTF-8');
					$src = MediaHelper::getUrl($document, K::XUL);
					$nodeAttributes['htmllink'] = '<img class="image" src="' . $src . '" cmpref="' . $document->getId() . '" alt="' . $alt . '" lang="' . $lang . '" xml:lang="' . $lang . '" usemediaalt="true" />';
					$nodeAttributes['block'] = $nodeAttributes['actualtype'];
				}
				if ($treeType == 'wlist')
				{
					$nodeAttributes['thumbnailsrc'] = LinkHelper::getUIActionLink('media', 'BoDisplay')
					->setQueryParameter('cmpref', $document->getId())
					->setQueryParameter('format', 'modules.uixul.backoffice/thumbnaillistitem')
					->setQueryParameter('lang', RequestContext::getInstance()->getLang())
					->setQueryParameter('time', date_Calendar::now()->getTimestamp())->getUrl();
				}
				break;
					
			case MediaHelper::TYPE_PDF:
				$nodeAttributes['actualtype'] = 'modules_media_pdf';
				if ($treeType == 'wmultilist')
				{
					$nodeAttributes['htmllink'] = PHPTAL_Php_Attribute_CHANGE_download::render($document, null, true);
					$nodeAttributes['block'] = $nodeAttributes['actualtype'];
				}
				break;
					
			case MediaHelper::TYPE_DOC:
				$nodeAttributes['actualtype'] = 'modules_media_doc';
				if ($treeType == 'wmultilist')
				{
					$nodeAttributes['htmllink'] = PHPTAL_Php_Attribute_CHANGE_download::render($document, null, true);
					$nodeAttributes['block'] = $nodeAttributes['actualtype'];
				}
				break;
					
			case MediaHelper::TYPE_FLASH:
				$nodeAttributes['actualtype'] = 'modules_media_flash';
				if ($treeType == 'wmultilist')
				{

				 $styleAttributes = array();
				 $mediaInfos = $document->getInfo();
				 if (isset($mediaInfos['height']))
				 {
				 	$styleAttributes['height'] = $mediaInfos['height'] . 'px';
				 }
				 if (isset($mediaInfos['width']))
				 {
				 	$styleAttributes['width'] = $mediaInfos['width'] . 'px';
				 }
				 $title = htmlspecialchars($document->getTitle());

				 $style = f_util_HtmlUtils::buildStyleAttribute($styleAttributes);
				 $link = '<a rel="cmpref:' . $document->getId() . '" href="#" class="media-flash-dummy" title="' . $title . '" lang="' . RequestContext::getInstance()->getLang() . '" style="' . $style . '">' . $title . '&#160;</a>';
					$nodeAttributes['htmllink'] = $link;
					$nodeAttributes['block'] = $nodeAttributes['actualtype'];
				}
				break;

			case MediaHelper::TYPE_VIDEO:
				$nodeAttributes['actualtype'] = 'modules_media_video';
				if ($treeType == 'wmultilist')
				{
					$nodeAttributes['htmllink'] = '';
					$nodeAttributes['block'] = $nodeAttributes['actualtype'];
				}
				break;
			default:
				if ($treeType == 'wmultilist')
				{
					$nodeAttributes['htmllink'] = PHPTAL_Php_Attribute_CHANGE_download::render($document, null, true);
				}
				break;
		}
	}

	/**
	 * @param media_persistentdocument_media $document
	 * @param array $attributes
	 * @param string $content
	 * @param string $lang
	 * @return string
	 */
	public function getXhtmlFragment($document, $attributes, $content, $lang)
	{		
		if ($document->getMediatype() == MediaHelper::TYPE_FLASH)
		{
			$xhtml = $this->renderFlashTag($document, $attributes);
		}
		else if ($document->getMediatype() == MediaHelper::TYPE_IMAGE)
		{
			$parameters = array();
			if (isset($attributes['width']))
			{
				$parameters['width'] = intval($attributes['width']);
			}
			if (isset($attributes['height']))
			{
				$parameters['height'] = intval($attributes['height']);
			}
			$attributes['href'] = LinkHelper::getDocumentUrl($document, $lang, $parameters);
			$xhtml = f_util_HtmlUtils::buildLink($attributes, $content);
		}
		else
		{
			$xhtml = parent::getXhtmlFragment($document, $attributes, $content, $lang);
		}
		return $xhtml;
	}
	
	/**
	 * @param media_persistentdocument_media $document
	 * @param Array $attributes
	 * @return String
	 */
	private function renderFlashTag($document, $attributes)
	{
		$attributes['id'] = 'media-' . $document->getId();
		$attributes['url'] = LinkHelper::getDocumentUrl($document);
		$attributes = array_merge($attributes, MediaHelper::getImageSize(media_MediaService::getInstance()->getOriginalPath($document)));
		unset($attributes['src']);
		$attributes['alt'] = $document->getTitle();
		if ($document->getDescription())
		{
			$attributes['description'] = $document->getDescriptionAsHtml();
		}

		$templateComponent = TemplateLoader::getInstance()->setpackagename('modules_media')->setMimeContentType(K::HTML)->load('Media-Block-Flash-Success');
		$templateComponent->setAttribute('medias', array($attributes));
		$content = $templateComponent->execute();
		return $content;
	}
}