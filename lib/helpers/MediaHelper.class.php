<?php
/**
 * @package modules.media
 */
class MediaHelper
{
	const ICON_PATH = '/changeicons';
	const ORIGINAL_PATH = '/media/original/';
	const FORMATTED_PATH = '/media/formatted/';
	const BACK_STATIC_PATH = '/media/backoffice/';
	const FRONT_STATIC_PATH = '/media/frontoffice/';
	const ROOT_MEDIA_PATH = '/media/';
	const THEME_PATH = '/media/themes/';

	const ADMIN = 'admin';
	const SMALL = 'small';
	const COMMAND = 'command';
	const NORMAL = 'normal';
	const BIG = 'big';
	const HUGE = 'huge';

	const LAYOUT_PLAIN = '';
	const LAYOUT_SHADOW = '/shadow';

	const EXTENSION_PNG = '.png';
	const EXTENSION_JPEG = '.jpg';
	const EXTENSION_GIF = '.gif';

	const IMAGE_PNG = 'png';
	const IMAGE_JPEG = 'jpg';
	const IMAGE_GIF = 'gif';

	const TYPE_IMAGE = 'image';
	const TYPE_AUDIO = 'audio';
	const TYPE_PDF = 'pdf';
	const TYPE_DOC = 'doc';
	const TYPE_DOCX = 'docx';
	const TYPE_DOCM = 'docm';
	const TYPE_XLS = 'xls';
	const TYPE_XLSX = 'xlsx';
	const TYPE_ODT = 'odt';
	const TYPE_ODS = 'ods';

	const TYPE_FLASH = 'flash';
	const TYPE_VIDEO = 'video';
	const TYPE_GENERIC = 'media';
	const TYPE_URL = 'url';
	const TYPE_FILE = 'file';

	const ALLOWED_IMAGE_FORMAT = 'jpg|jpeg|gif|png';
	const JPEG_QUALITY = 90;

	const WIDTH_ATTRIBUTE = 'width';
	const HEIGHT_ATTRIBUTE = 'height';
	const FORMAT_ATTRIBUTE = 'format';
	const ALT_ATTRIBUTE = 'alt';
	const TRANSIENT_ATTRIBUTE = 'transient';
	const FORCEDOWNLOAD_ATTRIBUTE = 'forceDownload';

	const PREVIEW_FORMAT = 'modules.media.frontoffice/preview';

	private static $formats = array();

	/**
	 * Return a readable file size.
	 *
	 * @param media_persistentdocument_media $media
	 * @return string
	 */
	public static function getFileSize($media)
	{
		$filePath = $media->getDocumentService()->getOriginalPath($media, true);
		return f_util_FileUtils::getReadableFileSize($filePath);
	}

	/**
	 * @param media_persistentdocument_media $media
	 * @param string $format ex: modules.media.frontoffice/thumbnail
	 * @return string
	 */
	public static function getPublicFormatedUrl($media, $formatName)
	{
		if ($formatName)
		{
			$format = self::getFormatPropertiesByName($formatName);
			if (f_util_ArrayUtils::isNotEmpty($format))
			{
				return LinkHelper::getDocumentUrl($media, null, $format);
			}
		}
		return LinkHelper::getDocumentUrl($media);
	}

	/**
	 * Return all available document's info.
	 * 'id' 'filename' 'extension' 'path' 'type' 'url' 'size' 'alt' 'width' 'height'
	 * @param media_persistentdocument_media $document
	 * @return array
	 */
	public static function getInfo($document)
	{
		return $document->getInfo();
	}

	/**
	 * @deprecated use media_FileService::getInstance()->getRelativeFolder()
	 * @param Integer $id
	 * @param String lang, default RequestContext lang
	 * @return String
	 */
	private static function getMediaFolder($id, $lang)
	{
		return media_FileService::getInstance()->getRelativeFolder($id, $lang);
	}

	/**
	 * Return the document's original file path.
	 *
	 * @deprecated use $document->getDocumentService()->getOriginalPath()
	 * @param media_persistentdocument_media $document
	 * @param boolean $mustExist
	 * @return string
	 */
	public static function getOriginalPath($document, $mustExist = false)
	{
		return $document->getDocumentService()->getOriginalPath($document, $mustExist);
	}

	/**
	 * @deprecated use $document->getDocumentService()->getOriginalFolder()
	 * @param media_persistentdocument_media $document
	 * @return string the folder that contains the files of the media, ending with '/'
	 */
	public static function getOriginalFolder($document)
	{
		return $document->getDocumentService()->getOriginalFolder($document);
	}

	/**
	 * @deprecated use $document->getDocumentService()->getOriginalPath()
	 *
	 * @param Integer $documentId
	 * @param Boolean $mustExist
	 * @return string
	 */
	public static function getOriginalPathById($documentId, $mustExist = false)
	{
		$document = DocumentHelper::getDocumentInstance($documentId);
		return $document->getDocumentService()->getOriginalPath($document, $mustExist);
	}

	/**
	 * @deprecated use LinkHelper::getDocumentUrl($media)
	 * @param media_persistentdocument_media $media
	 * @return string
	 */
	public static function getPublicUrl($media)
	{
		return LinkHelper::getDocumentUrl($media);
	}

	/**
	 * @param media_persistentdocument_media $media
	 * @param integer $width
	 * @param integer $height
	 * @return string
	 */
	public static function getPublicResizedUrl($media, $width, $height)
	{
		$fileName = self::getOriginalPath($media);
		if ($width && $height)
		{
			$format = array('max-width' => $width, 'max-height' => $height);
			$fileName = media_FormatterHelper::buildFormattedResourcePath($fileName, $format);
		}
		return self::convertMediaFilePathToPublicUrl($fileName);
	}

	/**
	 * @param string $mediaFilePath
	 * @return string ex : http://' . HOST . '/publicmedia/' . FileSubPath
	 */
	private static function convertMediaFilePathToPublicUrl($mediaFilePath)
	{
		if ($mediaFilePath)
		{
			$mediaFilePath = str_replace('%', '%25', $mediaFilePath);
			return LinkHelper::getRessourceLink('/publicmedia/'.substr($mediaFilePath, strlen(WEBEDIT_HOME.self::ROOT_MEDIA_PATH)))->getUrl();
		}
		return '';
	}

	/**
	 * Return the full HTML/XUL content for the given media document, for example :
	 *
	 * MediaHelper::getContent(11023, 320, 240) - [id, width, height]
	 * MediaHelper::getContent($document, "modules.media.frontoffice/thumbnail", K::XUL) - [document, format, content-type]
	 * MediaHelper::getContent($document, "fr", 640, K::HTML, "alternate text content", array('class' => 'specific-class'))
	 * etc.
	 *
	 * @return string
	 */
	public static function getContent()
	{
		$args = func_get_args();
		//'document' 'cmpref' 'lang' 'width' 'height' 'format' 'alt' 'contentType' 'attributes'
		$parameters = self::parseFuncArgs($args);
		if (is_null($parameters) || !isset($parameters['document']))
		{
			return null;
		}

		$rc = RequestContext::getInstance();
		$lang = isset($parameters['lang']) ? $parameters['lang'] : $rc->getLang();
		try
		{
			$link = self::getLocalizedContent($parameters, $lang);
		}
		catch (Exception $e)
		{
			$link = null;
			Framework::exception($e);
		}

		return $link;
	}

	/**
	 * @param media_persistentdocument_media $media
	 * @param string $lang
	 * @param string $width
	 * @param string $height
	 * @param string $alt
	 * @param string $format
	 * @param string $extraTagAttributes
	 */
	public static function getImageTag($media, $lang = null, $width = null, $height = null, $alt = null, $format = null, $extraTagAttributes = null)
	{
		if (!$media instanceof media_persistentdocument_media)
		{
			return null;
		}
		if ($lang === null || !$media->isLangAvailable($lang))
		{
			$lang = $media->getLang();
		}

	}

	/**
	 * @param array $parameters
	 * @param string $lang
	 * @return string
	 */
	private static function getLocalizedContent($parameters, $lang)
	{
		$document = $parameters['document'];
		//'id' 'filename' 'extension' 'path' 'type' 'url' 'size' 'alt' 'width' 'height'
		$lang = RequestContext::getInstance()->getLang();
		$urlLang = $lang;
		$docInfo = array();
			
		if ($document instanceof media_persistentdocument_file)
		{
			$urlLang = ($document->getFilenameForLang($lang)) ? $lang : $document->getLang();
			if ($lang != $urlLang)
			{
				$docInfo = array_merge($document->getInfoForLang($urlLang), $document->getInfo());
			}
			else
			{
				$docInfo = $document->getInfoForLang($lang);
			}
		}
		$parameters = array_merge($docInfo, $parameters);
		
		if (isset($parameters['format']))
		{
			$format = self::getFormatPropertiesByName($parameters['format']);
		}
		else
		{
			$format = array();
			if (isset($parameters['attributes']['max-width']) || isset($parameters['attributes']['max-height']))
			{
				$format = array('max-width' => $parameters['attributes']['max-width'], 'max-height' => $parameters['attributes']['max-height']);
			}
			else
			{
				$format = array('width' => $parameters['width'], 'height' => $parameters['height']);
			}
		}
		$parameters['url'] = LinkHelper::getDocumentUrl($document, $urlLang, $format);

		$content = "";
		if (!isset($parameters['type'])) {return $content;}
		
		switch ($parameters['type'])
		{
			case self::TYPE_PDF:
				// TODO
				break;

			case self::TYPE_DOC:
				// TODO
				break;
				 
			case self::TYPE_VIDEO:
				// TODO
				break;

			case self::TYPE_FLASH:
				$templateComponent = TemplateLoader::getInstance()
				->setpackagename('modules_media')
				->setMimeContentType(K::HTML)
				->load('Media-Block-Flash-Success');
				$parameters['id'] = 'media-' . $parameters['id'];
				if ($document->getDescriptionForLang($urlLang))
				{
					$parameters['description'] =  f_util_HtmlUtils::renderHtmlFragment($document->getDescriptionForLang($urlLang));
				}
				$templateComponent->setAttribute('medias', array($parameters));
				$content = $templateComponent->execute();
				 
				if (isset($parameters['attributes']) && is_array($parameters['attributes']))
				{
					foreach ($parameters['attributes'] as $name => $value)
					{
						if (in_array($name, array_keys($parameters)))
						{
							continue;
						}

						if ($name == 'id' && is_numeric($value))
						{
							$value = 'media-' . $value;
						}
						$content = str_replace('<object', '<object ' . $name . '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"', $content);
					}
				}
				break;
				 
			case self::TYPE_IMAGE:
				$xulContent = isset($parameters['contentType']) && $parameters['contentType'] == K::XUL;
				$content  = ($xulContent) ? '<image' : '<img';
				$content .= ' src="' . htmlspecialchars($parameters['url'], ENT_COMPAT, 'UTF-8') . '"';
				$content .= ' lang="' . $parameters['lang'] .'"';
				$content .= ' xml:lang="' . $parameters['lang'] .'"';

				if (!isset($parameters['attributes']))
				{
					$parameters['attributes'] = array();
				}

				if (!isset($parameters['attributes']['alt']) || empty($parameters['attributes']['alt']))
				{
					if (!isset($parameters['alt']))
					{
						$parameters['alt'] = '';
					}
				}
				$alt = isset($parameters['alt']) ? htmlspecialchars($parameters['alt'], ENT_COMPAT, 'UTF-8') : '';
				$content .= ' alt="' . $alt . '"';
				// #46430: do not generate title attribute any more.

				$attributes = array('class' => 'image', 'change:id' => $document->getId());
				if (isset($docInfo["isGifAnim"]))
				{
					$sizeInfo = self::computeImageSize($docInfo['width'], $docInfo['height'], $format);
					$format['width'] = $sizeInfo['width'].'px';
					$format['height'] = $sizeInfo['height'].'px';
				}
				 
				if (isset($format['width']))
				{
					$content .= ' width="' . $format['width'] .'"';
					if ($xulContent)
					{
						$attributes['style']['max-width'] = $format['width'];
					}
				}

				if (isset($format['height']))
				{
					$content .= ' height="' . $format['height'] .'"';
					if ($xulContent)
					{
						$attributes['style']['max-height'] = $format['height'];
					}
				}

				foreach ($parameters['attributes'] as $name => $value)
				{
					if (isset($attributes[$name]) && is_array($attributes[$name]))
					{
						$attributes[$name] = array_merge($attributes[$name], $value);
					}
					else if (isset($attributes[$name]) && ($name == 'class'))
					{
						$attributes[$name] = $attributes[$name] . ' ' . $value;
					}
					else
					{
						$attributes[$name] = $value;
					}
				}

				if ($document->getDescriptionForLang($urlLang))
				{
					$attributes['longdesc'] = LinkHelper::getActionUrl("media", "DisplayMediaDescription", array(K::COMPONENT_ID_ACCESSOR => $document->getId(), "label" => $document->getLabelForLang($urlLang), "lang" => $parameters["lang"]));
				}

				foreach ($attributes as $name => $value)
				{
					$apply = false;
					switch ($name)
					{
						case 'change:id':
							if ($xulContent)
							{
								$apply = true;
								// FIXME: used ????
								$name = 'id';
								$value = 'media-' . $value;
							}
							break;
						case 'id':
						case 'class':
						case 'align':
						case 'border':
						case 'height':
						case 'hspace':
						case 'ismap':
						case 'longdesc':
						case 'usemap':
						case 'vspace':
						case 'width':
						case 'style':
						case 'onclick':
						case 'ondblclick':
						case 'onmousedown':
						case 'onmouseup':
						case 'onmouseover':
						case 'onmousemove':
						case 'onmouseout':
						case 'onkeypress':
						case 'onkeydown':
						case 'onkeyup':
							$apply = true;
							break;
					}

					if ($apply)
					{
						if (is_array($value))
						{
							$content .= ' ' . $name . '="';
							foreach ($value as $subName => $subValue)
							{
								if (!is_null($subValue))
								{
									$content .= ' ' . $subName . ': ' . htmlspecialchars($subValue, ENT_COMPAT, 'UTF-8') .';';
								}
							}
							$content .= '"';
						}
						else
						{
							if (!is_null($value))
							{
								$content .= ' ' . $name . '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') .'"';
							}
						}
					}
				}

				$content .= ' />';

				if ((isset($parameters["attributes"]["zoom"]) && $parameters["attributes"]["zoom"] == "true"))
				{
					$widths = array();
					if (isset($format['max-width']))
					{
						$widths[] = $format['max-width'];
					}
					if (isset($format['width']))
					{
						$widths[] = $format['width'];
					}
					$askedWidth = (count($widths) > 0) ? min($widths) : null;

					$heights = array();
					if (isset($format['max-height']))
					{
						$heights[] = $format['max-height'];
					}
					if (isset($format['height']))
					{
						$heights[] = $format['height'];
					}
					$askedHeight = (count($heights) > 0) ? min($heights) : null;

					// TODO: externalize the percentage of tolerance: configuration / preferences
					$formatted = ($askedWidth !== null && $askedWidth < (0.85 * $docInfo['width']))
					|| ($askedHeight !== null && $askedHeight < (0.85 * $docInfo['height']));
					if ($formatted && !$xulContent)
					{
						$content = '<a class="lightbox" href="' . LinkHelper::getDocumentUrl($document, $urlLang, $docInfo) . '" title="'.$alt.'">' . $content . '</a>';
					}
				}

				break;
		}

		return $content;
	}

	// TODO: refactor with HtmlUtils::min
	private static function min($values)
	{
		$min = null;
		foreach ($values as $value)
		{
			if ($value === null) continue;
			if ($min === null || $value < $min)
			{
				$min = $value;
			}
		}
		return $min;
	}

	/**
	 * Magic method fr getting a media file path (cf. getUrl and getContent)
	 *
	 * @return string
	 */
	public static function getFilePath()
	{
		$args = func_get_args();

		$parameters = self::parseFuncArgs($args);

		if (isset($parameters['document']))
		{
			return self::getOriginalPath($parameters['document'], true);
		}
		else if (!isset($parameters['cmpref']))
		{
			return null;
		}
		else if (($absolutePath = self::getAbsolutePath($parameters['cmpref'])) && is_readable($absolutePath) && is_file($absolutePath))
		{
			return $absolutePath;
		}
		else
		{
			$filename = str_replace(WEBEDIT_HOME . self::ROOT_MEDIA_PATH, '', realpath(str_replace('//', '/', WEBEDIT_HOME . '/media/' . $parameters['cmpref'])));
			$filename = str_replace('//', '/', WEBEDIT_HOME . self::ROOT_MEDIA_PATH . '/' . $filename);

			if (is_readable($filename) && is_file($filename))
			{
				return $filename;
			}

			$filename = str_replace(WEBEDIT_HOME . self::ROOT_MEDIA_PATH, '', realpath(str_replace('//', '/', WEBEDIT_HOME . '/' . $parameters['cmpref'])));
			$filename = str_replace('//', '/', WEBEDIT_HOME . self::ROOT_MEDIA_PATH . '/' . $filename);

			if (is_readable($filename) && is_file($filename))
			{
				return $filename;
			}
		}

		return null;
	}

	/**
	 * Magic method for checking the existence of a media file (cf. getUrl and getContent)
	 *
	 * @return boolean
	 */
	public static function fileExists()
	{
		$args = func_get_args();

		$filePath = f_util_ClassUtils::callMethodArgs('MediaHelper', 'getFilePath', $args);

		return (is_readable($filePath) && is_file($filePath));
	}

	/**
	 * Return the URL of the given media document, for example :
	 *
	 * MediaHelper::getUrl(11023, 320, 240) - [id, width, height]
	 * MediaHelper::getUrl($document, "modules.media.frontoffice/thumbnail", K::XUL) - [document, format, content-type]
	 * MediaHelper::getUrl($document, "fr", 640, K::HTML)
	 * etc.
	 *
	 * @return string
	 */
	public static function getUrl()
	{
		$args = func_get_args();

		//array <'document' 'cmpref' 'lang' 'width' 'height' 'format' 'alt' 'contentType' 'attributes'>
		$parameters = self::parseFuncArgs($args, false);
		//Framework::info(var_export($parameters, true));

		if (is_null($parameters) || $parameters['document'] == null)
		{
			return null;
		}

		if ($parameters['contentType'] == K::HTML)
		{
			$document = $parameters['document'];
			if (isset($parameters['format']))
			{
				$format = self::getFormatPropertiesByName($parameters['format']);
				$lang = isset($parameters['lang']) ? $parameters['lang'] : null;
				return LinkHelper::getDocumentUrl($document, $lang, $format);
			}
			elseif (isset($parameters['width']) || isset($parameters['height']))
			{
				$format = array();
				if (isset($parameters['width'])) {$format['width'] = $parameters['width'];}
				if (isset($parameters['height'])) {$format['height'] = $parameters['height'];}
				return LinkHelper::getDocumentUrl($document, $lang, $format);
			}
			else
			{
				return LinkHelper::getDocumentUrl($document);
			}
		}

		$link = LinkHelper::getUIActionLink('media', 'Display')->setArgSeparator(f_web_HttpLink::ESCAPE_SEPARATOR);
		$link->setQueryParameter('cmpref', $parameters['cmpref']);
		$link->setQueryParameter('lang', $parameters['lang']);
		$link->setQueryParameter('width', $parameters['width']);
		$link->setQueryParameter('height', $parameters['height']);
		$link->setQueryParameter('format', $parameters['format']);

		if (isset($parameters['attributes']) && is_array($parameters['attributes']))
		{
			foreach ($parameters['attributes'] as $name => $value)
			{
				if (($name == 'module') || ($name == 'action')
				|| ($name == 'cmpref') || ($name == 'lang')
				|| ($name == 'width') || ($name == 'height')
				|| ($name == 'format') || ($name == 'alt'))
				{
					continue;
				}
				$link->setQueryParameter($name, $value);
			}
		}

		return $link->getUrl();
	}

	/**
	 * @param string $filename
	 * @param array $format
	 * @param boolean $transient If set to TRUE, the file content will NOT be persited (in /media/formatted).
	 */
	public static function outputFile($filename, $document = null, $format = null, $transient = false, $forceDownload = false)
	{
		media_FormatterHelper::outputFile($filename, $document, $format, $transient, $forceDownload);
	}

	/**
	 * Output EXTERNAL (non-media) file as "raw" data with all the required headers.
	 *
	 * @param string $filename
	 * @param array $format
	 * @param boolean $forceDownload
	 */
	public static function outputExternalFile($filename, $format = null, $forceDownload = false)
	{
		if (is_readable($filename) && is_file($filename))
		{
			if (!is_null($format))
			{
				$resourcePath = self::getTempFormattedResource($filename, $format);
			}
			else
			{
				$resourcePath = $filename;
			}
			self::outputHeader($filename, null, $forceDownload);
			readfile($resourcePath);
			if (($resourcePath != $filename) && is_writeable($resourcePath))
			{
				unlink($resourcePath);
			}
		}
	}

	/**
	 * Return the TEMP path of a formatted EXTERNAL (non-media) file
	 *
	 * @param $string $filename
	 * @param array $format
	 * @return string
	 */
	public static function getTempFormattedResource($filename, $format = array())
	{
		$originalSize = self::getImageSize($filename);
		$srcWidth = $originalSize['width'];
		$srcHeight = $originalSize['height'];
		$formattedSize = self::getImageSize($filename, $format);
		$resourceWidth = $formattedSize['width'];
		$resourceHeight = $formattedSize['height'];
		if ($srcWidth && $srcHeight && $resourceWidth && $resourceHeight)
		{
			if ($srcWidth != $resourceWidth || $srcHeight != $resourceHeight)
			{
				$resourcePath = f_util_FileUtils::getTmpFile();
				switch (self::getResourceType($filename))
				{
					case self::IMAGE_GIF:
						$imageSrc = imagecreatefromgif($filename);
						$colorTransparent = imagecolortransparent($imageSrc);
						$imageFormatted = imagecreate($resourceWidth, $resourceHeight);
						imagepalettecopy($imageFormatted, $imageSrc);
						imagefill($imageFormatted, 0, 0, $colorTransparent);
						imagecolortransparent($imageFormatted, $colorTransparent);
						imagecopyresized($imageFormatted, $imageSrc, 0, 0, 0, 0, $resourceWidth, $resourceHeight, $srcWidth, $srcHeight);
						imagegif($imageFormatted, $resourcePath);
						break;

					case self::IMAGE_JPEG:
						$imageSrc = imagecreatefromjpeg($filename);
						$imageFormatted = imagecreatetruecolor($resourceWidth, $resourceHeight);
						imagecopyresampled($imageFormatted, $imageSrc, 0, 0, 0, 0, $resourceWidth, $resourceHeight, $srcWidth, $srcHeight);
						imagejpeg($imageFormatted, $resourcePath, self::JPEG_QUALITY);
						break;

					case self::IMAGE_PNG:
						$imageSrc = imagecreatefrompng($filename);
						$imageFormatted = imagecreatetruecolor($resourceWidth, $resourceHeight);
						imageAlphaBlending($imageFormatted, false);
						imageSaveAlpha($imageFormatted, true);
						imagecopyresampled($imageFormatted, $imageSrc, 0, 0, 0, 0, $resourceWidth, $resourceHeight, $srcWidth, $srcHeight);
						imagepng($imageFormatted, $resourcePath);
						break;
				}
				imagedestroy($imageSrc);
				imagedestroy($imageFormatted);
				return $resourcePath;
			}
		}
		return $filename;
	}

	private static $realImageSizeArray;

	private static function getRealImageSize($filename)
	{
		if (self::$realImageSizeArray == null)
		{
			self::$realImageSizeArray = array();
		}
		if (!array_key_exists($filename, self::$realImageSizeArray))
		{
			$imageSizeInfo = getimagesize($filename);
			self::$realImageSizeArray[$filename] = $imageSizeInfo;
		}
		return self::$realImageSizeArray[$filename];
	}

	public static function computeImageSize($orginalWidth, $originalHeight, $format)
	{

		$resourceWidth = $orginalWidth;
		$resourceHeight = $originalHeight;
		$srcRatio = $orginalWidth / $originalHeight;

		if (isset($format['width']))
		{
			$resourceWidth = intval($format['width']);
			$resourceHeight = $resourceWidth / $srcRatio;
		}

		if (isset($format['height']))
		{
			$resourceHeight = intval($format['height']);
			$resourceWidth = $resourceHeight * $srcRatio;
		}

		if (isset($format['min-width'])
		&& ($resourceWidth < intval($format['min-width'])))
		{
			$resourceWidth = intval($format['min-width']);
			$resourceHeight = $resourceWidth / $srcRatio;
		}

		if (isset($format['min-height'])
		&& ($resourceHeight < intval($format['min-height'])))
		{
			$resourceHeight = intval($format['min-height']);
			$resourceWidth = $resourceHeight * $srcRatio;
		}

		if (isset($format['max-width'])
		&& ($resourceWidth > intval($format['max-width'])))
		{
			$resourceWidth = intval($format['max-width']);
			$resourceHeight = $resourceWidth / $srcRatio;
		}

		if (isset($format['max-height'])
		&& ($resourceHeight > intval($format['max-height'])))
		{
			$resourceHeight = intval($format['max-height']);
			$resourceWidth = $resourceHeight * $srcRatio;
		}

		$resourceWidth = round($resourceWidth);
		$resourceHeight = round($resourceHeight);

		return  array('width' =>  min($resourceWidth, $orginalWidth), 'height' => min($resourceHeight, $originalHeight));
	}

	/**
	 * Compute the size of a formatted media file
	 *
	 * @param $string $filename
	 * @param array $size
	 * @return string
	 */
	public static function getImageSize($filename, $format = array())
	{
		if (!is_readable($filename) || !is_file($filename))
		{
			return array('width' => null, 'height' => null);
		}

		$imageSizeInfo = self::getRealImageSize($filename);
		if (!$imageSizeInfo)
		{
			return array('width' => null, 'height' => null);
		}

		list($srcWidth, $srcHeight, ) = $imageSizeInfo;
		$size = array('width' => $srcWidth, 'height' => $srcHeight);

		if (f_util_ArrayUtils::isEmpty($format))
		{
			return $size;
		}

		switch (strtolower(f_util_FileUtils::getFileExtension($filename)))
		{
			case 'png':
			case 'jpg':
			case 'jpeg':
				break;
			case 'gif':
				if (self::isGifAnim($filename))
				{
					return $size;
				}
				break;
			default:
				return $size;
		}
		return self::computeImageSize($srcWidth, $srcHeight, $format);
	}

	private static function getResourceType($filename)
	{
		$extension = f_util_FileUtils::getFileExtension($filename);
		switch (strtolower($extension))
		{
			case 'gif':
				return self::IMAGE_GIF;
			case 'png':
				return self::IMAGE_PNG;
			case 'jpg':
			case 'jpeg':
				return self::IMAGE_JPEG;
		}
		return null;
	}

	/**
	 * Output the required headers.
	 *
	 * @param string $filename
	 */
	public static function outputHeader($filename, $document = null, $forceDownload = false)
	{
		if (!headers_sent())
		{
			$headers = self::getHeader($filename, $document, $forceDownload);
			foreach ($headers as $header)
			{
				header($header);
			}
		}
	}

	/**
	 * Get the required headers.
	 *
	 * @param string $filename
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param boolean $forceDownload
	 * @return array
	 */
	public static function getHeader($filename, $document = null, $forceDownload = false)
	{
		$header = array();
		$inline = !$forceDownload;

		if (strpos($filename, '.') === false)
		{
			$extension = $filename;
		}
		else
		{
			$extension = f_util_FileUtils::getFileExtension($filename);
		}

		if ($forceDownload)
		{
			$header[] = 'Cache-Control: public, must-revalidate';
			$header[] = 'Pragma: hack';
			$header[] = 'Content-Transfer-Encoding: binary';
		}
		else
		{
			switch (strtolower($extension))
			{
				case 'flv':
					$header[] = "Expires: Mon, 26 Jul 1997 05:00:00 GMT";
					$header[] = "Cache-Control: no-store, no-cache, must-revalidate";
					$header[] = "Cache-Control: post-check=0, pre-check=0";
					$header[] = "Pragma: no-cache";
					break;
				case 'png':
				case 'gif':
				case 'jpg':
				case 'jpeg':
				case 'swf':
				case 'ico':
					$header[] = 'Accept-Ranges: bytes';
					break;
				case 'pdf':
					$header[] = 'Cache-Control: public, must-revalidate';
					$header[] = 'Pragma: hack';
					$header[] = 'Content-Transfer-Encoding: binary';
					// FIXME : correct inline value for PDF ?
					$inline = false;
					break;
			}
		}

		$header[] = 'Last-Modified: ' . gmdate("D, d M Y H:i:s", filemtime($filename)) . " GMT";
		$header[] = 'Content-Length: ' . filesize($filename);
		$header[] = 'Content-Type: '.f_util_FileUtils::getContentTypeFromExtension($extension);
		self::setFileNameHeader($document, $filename, $header, $inline);

		return $header;
	}

	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 */
	private function setFileNameHeader($document, $filename, &$header, $inline = true)
	{
		$disposition = ($inline) ? "inline" : "attachment";

		if ($document !== null)
		{
			if ($document->getFilename())
			{
				$fileName = $document->getFilename();
			}
			else
			{
				$fileName = $document->getVoFilename();
			}
			$fileName = f_util_StringUtils::convertEncoding($fileName, 'UTF-8', 'ISO-8859-1');
			$header[] = 'Content-Disposition: '.$disposition.'; filename="' . $fileName . '"';
		}
		else
		{
			$header[] = 'Content-Disposition: '.$disposition.'; filename="' . basename($filename) . '"';
		}
	}

	/**
	 * Get the internal media type related to the given filename.
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function getMediaTypeByFilename($filename)
	{
		$type = self::TYPE_GENERIC;

		switch (strtolower(f_util_FileUtils::getFileExtension($filename)))
		{
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'png':
			case 'bmp':
				$type = self::TYPE_IMAGE;
				break;

			case 'pdf':
				$type = self::TYPE_PDF;
				break;

			case 'doc':
				$type = self::TYPE_DOC;
				break;

			case 'swf':
				$type = self::TYPE_FLASH;
				break;

			case 'flv':
				$type = self::TYPE_VIDEO;
				break;
				
			case 'mp3':
			case 'wav':
			case 'aif':
			case 'ogg':	
			case 'aac':
				$type = self::TYPE_AUDIO;
				break;
			default:
				$type = self::TYPE_GENERIC;
				break;
		}

		return $type;
	}

	/**
	 * @param string $extension
	 * @return string
	 */
	public static function getMimeTypeByExtension($extension)
	{
		switch (strtolower($extension))
		{
			case 'gif': 
				return 'image/gif';
			case 'jpg':
			case 'jpeg':
				return 'image/jpeg';
			case 'png':
				return 'image/png';
			case 'pdf':
				return 'application/pdf';
			case 'flv':
				return 'video/x-flv';
			case 'swf':	
				return 'application/x-shockwave-flash';
			case 'mp3':
				return 'audio/mpeg';
		}
		return 'application/octet-stream';
	}
	/**
	 * Get the URL of a server icon.
	 *
	 * @param string $name
	 * @param string $format
	 * @param string $extension
	 * @param string $layout
	 * @return string
	 */
	public static function getIcon($name, $format = null, $extension = null, $layout = null)
	{
		if ($format === null)
		{
			$format = self::NORMAL;
		}

		if ($extension === null)
		{
			$extension = self::EXTENSION_PNG;
		}

		if ($layout === null)
		{
			$layout = self::LAYOUT_PLAIN;
		}
		return LinkHelper::getUIRessourceLink(self::ICON_PATH . '/' . $format .$layout . '/'. $name. $extension)->getUrl();
	}
	
	/**
	 * @return string
	 */
	public static function getIconBaseUrl()
	{
		return Framework::getUIBaseUrl() . self::ICON_PATH;
	}

	/**
	 * Get the URL of a webapp static media.
	 *
	 * @param string $filename
	 * @param string $contentType
	 * @return string
	 */
	public static function getStaticUrl($filename, $contentType = null)
	{
		$filename = self::expandStaticUrl($filename);
		if (RequestContext::getInstance()->getMode() == RequestContext::BACKOFFICE_MODE)
		{
			return LinkHelper::getUIRessourceLink($filename)->getUrl();	
		}
		return LinkHelper::getRessourceLink($filename)->getUrl();
	}
	
	/**
	 * @param string $filename
	 * @return string
	 */
	private static function expandStaticUrl($filename)
	{
		$pos = strpos($filename, 'front/');
		if ($pos === 0 || $pos === 1)
		{
			return str_replace('//', '/', self::FRONT_STATIC_PATH . substr($filename, 6 + $pos));
		}
		$pos = strpos($filename, 'back/');
		if ($pos === 0 || $pos === 1)
		{
			return str_replace('//', '/', self::BACK_STATIC_PATH . substr($filename, 5 + $pos));
		}
		$pos = strpos($filename, 'theme/');
		if ($pos === 0 || $pos === 1)
		{
			return str_replace('//', '/', self::THEME_PATH . substr($filename, 6 + $pos));
		}
		$pos = strpos($filename, 'icon/');
		if ($pos === 0 || $pos === 1)
		{
			return str_replace('//', '/', self::ICON_PATH . substr($filename, 4 + $pos));
		}
		return str_replace('//', '/', $filename);
	}

	
	

	/**
	 * Get the URL of a webapp BACKOFFICE static media.
	 *
	 * @param string $filename
	 * @param string $contentType
	 * @return string
	 */
	public static function getBackofficeStaticUrl($filename, $contentType = null)
	{
		return LinkHelper::getUIRessourceLink(self::getBackofficeStaticPath($filename))->getUrl();
	}

	/**
	 * Get the URL of a webapp FRONTOFFICE static media.
	 *
	 * @param string $filename
	 * @param string $contentType
	 * @return string
	 */
	public static function getFrontofficeStaticUrl($filename, $contentType = null)
	{
		return LinkHelper::getRessourceLink(self::getFrontofficeStaticPath($filename))->getUrl();
	}

	/**
	 * Get the PATH of a webapp BACKOFFICE static media.
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function getBackofficeStaticPath($filename)
	{
		return str_replace('//', '/', self::BACK_STATIC_PATH . $filename);
	}

	/**
	 * Get the PATH of a webapp FRONTOFFICE static media.
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function getFrontofficeStaticPath($filename)
	{
		return str_replace('//', '/', self::FRONT_STATIC_PATH . $filename);
	}

	/**
	 * Get the ABSOLUTE PATH of a webapp static media.
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function getAbsolutePath($filename)
	{
		$filename = str_replace('//', '/', self::ROOT_MEDIA_PATH . $filename);
		// corrects some ugly old paths
		$filename = str_replace('media/media', 'media', $filename);
		return f_util_FileUtils::buildWebeditPath($filename);
	}

	public static function getFormatPropertiesByName($format, $id = null)
	{
		list($stylesheet, $formatName) = explode('/', $format);
		return MediaHelper::getFormatProperties($stylesheet, $formatName, $id);
	}

	/**
	 * Get the properties of the given format.
	 *
	 * @param string $stylesheet
	 * @param string $format
	 * @param integer $id
	 * @return array
	 */
	public static function getFormatProperties($stylesheet, $formatName, $id = null)
	{
		$key = $stylesheet.$formatName.$id;
		if (isset(self::$formats[$key]))
		{
			return self::$formats[$key];
		}
		$cacheFile = f_util_FileUtils::buildCachePath('mediaformat', basename($key));
		f_util_FileUtils::mkdir(dirname($cacheFile));
		if (!file_exists($cacheFile))
		{
			$formats = StyleService::getInstance()->getImageFormats($stylesheet);
			if (isset($formats[$formatName]))
			{
				$format = $formats[$formatName];
			}
			else 
			{
				$format = array();
			}
			f_util_FileUtils::write($cacheFile, '<?php $format = '.var_export($format, true).';', f_util_FileUtils::OVERRIDE);
		}
		else
		{
			include($cacheFile);
		}
		self::$formats[$key] = $format;
		return $format;
	}

	private static $gifAnimList = array();

	/**
	 * Return TRUE if the given file is an animated GIF.
	 *
	 * @param string $filePath
	 * @return boolean
	 */
	public static function isGifAnim($filePath)
	{
		if (isset(self::$gifAnimList[$filePath]))
		{
			return self::$gifAnimList[$filePath];
		}

		$isGifAnim = false;
		if (is_readable($filePath))
		{
			$gifContent = file_get_contents($filePath);

			$contentPosition = 0;
			$frameCount = 0;

			while ($frameCount < 2)
			{
				$firstHeader = strpos($gifContent, "\x00\x21\xF9\x04", $contentPosition);

				if ($firstHeader === false)
				{
					break;
				}
				else
				{
					$contentPosition = $firstHeader + 1;
					$secondHeader = strpos($gifContent, "\x00\x2C", $contentPosition);

					if ($secondHeader === false)
					{
						break;
					}
					else
					{
						if ($firstHeader + 8 == $secondHeader)
						{
							$frameCount++;
						}

						$contentPosition = $secondHeader + 1;
					}
				}
			}

			if ($frameCount > 1)
			{
				$isGifAnim = true;
			}
		}
		self::$gifAnimList[$filePath] = $isGifAnim;
		return $isGifAnim;
	}

	/**
	 * Parse arguments of "magic" methods.
	 *
	 * @param array $args
	 * @return array <'document' 'cmpref' 'lang' 'width' 'height' 'format' 'alt' 'contentType' 'attributes'>
	 */
	public static function parseFuncArgs($args, $unsetNullValues = true)
	{
		$argsCount = count($args);

		if ($argsCount < 1)
		{
			return null;
		}

		$parameters = array(
		'document' => null,
		'cmpref' => null,
		'lang' => null,
		'width' => null,
		'height' => null,
		'format' => null,
		'alt' => null,
		'contentType' => K::HTML,
		'attributes' => array()
		);

		if ($args[0] instanceof f_persistentdocument_PersistentDocument)
		{
			$parameters['document'] = $args[0];
			$parameters['cmpref'] = $args[0]->getId();
		}
		else
		{
			$parameters['cmpref'] = $args[0];
			try
			{
				$parameters['document'] = DocumentHelper::getDocumentInstance(intval($args[0]));
			}
			catch (Exception $e)
			{
				Framework::exception($e);
				return null;
			}
		}

		if (isset($args[1]))
		{
			if (is_numeric($args[1]))
			{
				$parameters['width'] = $args[1];
			}
			else if (is_string($args[1]) && (strlen($args[1]) == 2))
			{
				$parameters['lang'] = $args[1];
			}
			else if (is_string($args[1]) && (strlen($args[1]) != 2))
			{
				if (f_util_StringUtils::beginsWith($args[1], 'modules.'))
				{
					$parameters['format'] = $args[1];
				}
				else if (($args[1] == K::HTML) || ($args[1] == K::XUL) || ($args[1] == K::XML))
				{
					$parameters['contentType'] = $args[1];
				}
				else {
					$parameters['alt'] = $args[1];
				}
			}
			else if (is_array($args[1]))
			{
				$parameters['attributes'] = array_merge($parameters['attributes'], $args[1]);
			}
		}

		for ($i = 2; $i < $argsCount; $i++)
		{
			if (isset($args[$i]))
			{
				if (is_numeric($args[$i]))
				{
					if ($parameters['width'])
					{
						$parameters['height'] = $args[$i];
					}
					else
					{
						$parameters['width'] = $args[$i];
					}
				}
				else if (is_string($args[$i]) && (strlen($args[$i]) != 2))
				{
					if (f_util_StringUtils::beginsWith($args[$i], 'modules.'))
					{
						$parameters['format'] = $args[$i];
					}
					else if (($args[$i] == K::HTML) || ($args[$i] == K::XUL) || ($args[$i] == K::XML))
					{
						$parameters['contentType'] = $args[$i];
					}
					else
					{
						$parameters['alt'] = $args[$i];
					}
				}
				else if (is_array($args[$i]))
				{
					foreach ($args[$i] as $key => $val)
					{
						if ($key == "alt")
						{
							$parameters['alt'] = $val;
						}
						else
						{
							$parameters['attributes'][$key] = $val;
						}
					}
					// $parameters['attributes'] = array_merge($parameters['attributes'], $args[$i]);
				}
			}
		}

		if (!$parameters['lang'])
		{
			$lang = RequestContext::getInstance()->getLang();
			if ($parameters['document']->isLangAvailable($lang))
			{
				$parameters['lang'] = $lang;
			}
			else
			{
				$parameters['lang'] = $parameters['document']->getLang();
			}
		}

		if ($unsetNullValues)
		{
			foreach($parameters as $name => $value)
			{
				if (is_null($value))
				{
					unset($parameters[$name]);
				}
			}
		}

		return $parameters;
	}

	/**
	 * @deprecated
	 */
	public static function getStaticImage($filename, $contentType = null)
	{
		return self::getStaticUrl($filename, $contentType);
	}

	/**
	 * @deprecated
	 */
	public static function getFrontofficeStaticImage($filename, $contentType = null)
	{
		return self::getFrontofficeStaticUrl($filename, $contentType);
	}

	/**
	 * @deprecated
	 */
	public static function getBackofficeStaticImage($filename, $contentType = null)
	{
		return self::getBackofficeStaticUrl($filename, $contentType);
	}

	/**
	 * @param string $fileName The file name.
	 * @param string $tmpFileName The real file name moved to the storage place.
	 * @param mixed $destination The destination folder in Media module (generic_persistentdocument_folder) or the name of the module to get the "system forlder" of (string).
	 *
	 * @return media_persistentdocument_media
	 */
	private static function mediaImportFromFile($fileName, $tmpFileName, $destination = null, $fileAlt = null)
	{
		if ($destination instanceof generic_persistentdocument_folder)
		{
			$destId = $destination->getId();
		}
		else if (is_string($destination) )
		{
			$destId = ModuleService::getInstance()->getSystemFolderId('media', $destination);
		}
		else
		{
			$destId = null;
		}

		// Create Media instance and feed it
		$mediaService = media_MediaService::getInstance();

		$media = $mediaService->getNewDocumentInstance();

		$fileExtension = f_util_FileUtils::getFileExtension($fileName, true);
		$cleanFileName = basename($fileName, $fileExtension);

		$media->setLabel(f_util_StringUtils::utf8Encode($cleanFileName));
		$media->setTitle($fileAlt);
		$media->setNewFileName($tmpFileName, f_util_StringUtils::utf8Encode($fileName));

		$mediaService->save($media, $destId);

		return $media;
	}

	/**
	 * Inserts an uploaded file into the Media module.
	 *
	 * @param string $fileName The file name.
	 * @param string $serverFilePath The path to the file.
	 * @param mixed $destination The destination folder in Media module (generic_persistentdocument_folder) or the name of the module to get the "system forlder" of (string).
	 *
	 * @return media_persistentdocument_media
	 *
	 * @throws IOException, Exception
	 */
	public static function addUploadedFile($fileName, $serverFilePath, $destination = null, $fileAlt = null)
	{
		$tm = f_persistentdocument_TransactionManager::getInstance();
		try
		{
			$tm->beginTransaction();
			$tmpFileName = f_util_FileUtils::getTmpFile('addUploadedFile');
			if (!@move_uploaded_file($serverFilePath, $tmpFileName) )
			{
				throw new IOException("Could not move uploaded file \"".$serverFilePath."\" to destination path \"".$tmpFileName."\".");
			}
			$media = self::mediaImportFromFile($fileName, $tmpFileName, $destination, $fileAlt);

			$tm->commit();
			return $media;
		}
		catch (Exception $e)
		{
			$tm->rollBack();
			throw $e;
		}
	}

	/**
	 * Inserts a file from the filesystem into the Media module.
	 *
	 * @param string $fileName The file name.
	 * @param string $serverFilePath The path to the file.
	 * @param mixed $destination The destination folder in Media module (generic_persistentdocument_folder) or the name of the module to get the "system forlder" of (string).
	 *
	 * @return media_persistentdocument_media
	 *
	 * @throws IOException, Exception
	 */
	public static function addServerFile($fileName, $serverFilePath, $destination = null)
	{
		$tm = f_persistentdocument_TransactionManager::getInstance();
		try
		{
			$tm->beginTransaction();
				
			$tmpFileName = f_util_FileUtils::getTmpFile('addServerFile');
			if (!@copy($serverFilePath, $tmpFileName) )
			{
				throw new IOException("Could not copy server file \"".$serverFilePath."\" to destination path \"".$tmpFileName."\".");
			}
			$media = self::mediaImportFromFile($fileName, $tmpFileName, $destination);

			$tm->commit();
			return $media;
		}
		catch (Exception $e)
		{
			$tm->rollBack();
			throw $e;
		}
	}
	
	private static $additionnalAttributesBuilders;

	/**
	 * @param media_persistentdocument_media $media
	 * @param String $class
	 * @return array
	 */
	static function getAdditionnalDownloadAttributes($media, $class)
	{
		if (self::$additionnalAttributesBuilders === null)
		{
			$builderNames = Framework::getConfiguration("modules/media/additionnalDownloadAttributesBuilders", false);
			$builders = array();
			if ($builderNames !== false)
			{
				foreach ($builderNames as $builderName)
				{
					if (f_util_ClassUtils::classExists($builderName))
					{
						$builders[] = new $builderName();
					}
					else
					{
						throw new ConfigurationException("Bad modules/media/additionnalDownloadAttributesBuilders : class $builderName does not exists");
					}
				}
			}
			
			self::$additionnalAttributesBuilders = $builders;
		}
		
		$additionnalAttributes = array();
		foreach (self::$additionnalAttributesBuilders as $builder)
		{
			$additionnalAttributes = array_merge($additionnalAttributes, $builder->getAttributes($media, $class));
		}
		return $additionnalAttributes;
	}
}

interface f_DownloadAttributeBuilder
{
	/**
	 * @param f_persistentdocument_PersistentDocument $media
	 * @param String $class
	 * @return array<String, String>
	 */
	function getAttributes($media, $class);
}
