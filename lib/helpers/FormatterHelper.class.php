<?php
class media_FormatterHelper
{
	/**
	 * @param string $encodedformat for example:
	 * - 126/0/fr/Deux%20jours%20%C3%A0%20tuer;maxh=128,maxw=128.jpg
	 * - 126/0/fr/Deux%20jours%20%C3%A0%20tuer;h=578,w=434.jpg
	 * @throws Exception
	 */
	public static function outputFormatedMedia($encodedformat)
	{
		$mediaId = 0;
		$format = array();
		$lang = '';
		$formattedFileName = PROJECT_HOME . MediaHelper::FORMATTED_PATH . $encodedformat;
		
		self::decodeFormat($encodedformat, $mediaId, $lang, $format);
		try
		{
			RequestContext::getInstance()->beginI18nWork($lang);
			$media = DocumentHelper::getDocumentInstance($mediaId);
			
			if (!($media instanceof media_persistentdocument_file))
			{
				throw new Exception("Document is not an instance of file");
			}
			
			$fileName = $media->getDocumentService()->getOriginalPath($media, false);
			if (! is_readable($fileName))
			{
				throw new Exception("Unable to read : $fileName");
			}
			
			media_ResizerFormatter::getInstance()->resize($fileName, $formattedFileName, $format);
			
			MediaHelper::outputHeader($formattedFileName, $media, false);
			readfile($formattedFileName);
			
			RequestContext::getInstance()->endI18nWork();
		}
		catch (Exception $e)
		{
			RequestContext::getInstance()->endI18nWork($e);
			throw $e;
		}
	}
	
	public static function resize($inputFileName, $width, $height)
	{
		$format = array('width' => $width, 'height' => $height);
		$formattedFileName = self::buildFormattedResourcePath($inputFileName, $format);
		if ($inputFileName !== $formattedFileName)
		{
			media_ResizerFormatter::getInstance()->resize($inputFileName, $formattedFileName, $format);
		}
		return $formattedFileName;
	}
	
	/**
	 * Output media file as "raw" data with all the required headers.
	 *
	 * @param string $filename
	 * @param media_persistentdocument_file $document
	 * @param array $format
	 * @param boolean $transient If set to TRUE, the file content will NOT be persited (in /media/formatted).
	 * @param boolean $forceDownload
	 */
	public static function outputFile($filename, $document = null, $format = null, $transient = false, $forceDownload = false)
	{
		if (is_readable($filename))
		{
			if ($format !== null)
			{
				$resourcePath = self::buildFormattedResourcePath($filename, $format);
				if ($resourcePath !== $filename && (!file_exists($resourcePath) || filemtime($filename) > filemtime($resourcePath)))
				{
					media_ResizerFormatter::getInstance()->resize($filename, $resourcePath, $format);
				}
			}
			else
			{
				$resourcePath = $filename;
			}
			
			MediaHelper::outputHeader($resourcePath, $document, $forceDownload);
			readfile($resourcePath);
			
			if ($transient && $resourcePath !== $filename)
			{
				unlink($resourcePath);
			}
		}
	}
	
	private static function decodeFormat($encodedformat, &$mediaId, &$lang, &$format)
	{
		$datas = explode('/', $encodedformat);
		$partId = '';
		$i = 0;
		while (is_numeric($datas[$i]))
		{
			$partId .= $datas[$i];
			$i ++;
		}
		$mediaId = intval($partId);
		$lang = $datas[$i];
		
		$mediaFileName = $datas[$i + 1];
		$matches = array();
		if (! preg_match('/^.*;(([a-z]+=[0-9]+){1}(,[a-z]+=[0-9]+)*)\.[a-zA-Z]+$/', $mediaFileName, $matches))
		{
			throw new Exception("Unknown formatted media format: $mediaFileName");
		}
		$stringFormat = $matches[1];
		foreach (explode(',', $stringFormat) as $formatInfo)
		{
			list ($key, $value) = explode('=', $formatInfo);
			switch ($key)
			{
				case 'maxh' :
					$format['max-height'] = intval($value);
					break;
				case 'maxw' :
					$format['max-width'] = intval($value);
					break;
				case 'minh' :
					$format['min-height'] = intval($value);
					break;
				case 'minw' :
					$format['min-width'] = intval($value);
					break;
				case 'h' :
					$format['height'] = intval($value);
					break;
				case 'w' :
					$format['width'] = intval($value);
					break;
			}
		}
	}
	
	public static function buildFormattedResourcePath($filename, $format)
	{
		$extension = f_util_FileUtils::getFileExtension($filename);
		switch (strtolower($extension))
		{
			case 'gif' :
				$resourceExtension = MediaHelper::EXTENSION_GIF;
				break;
			case 'png' :
				$resourceExtension = MediaHelper::EXTENSION_PNG;
				break;
			case 'jpg' :
			case 'jpeg' :
				$resourceExtension = MediaHelper::EXTENSION_JPEG;
				break;
			default :
				$resourceExtension = '.' . $extension;
				break;
		}
		
		$resourceId = self::generateFormatKey($format);
		$resourceDir = str_replace('/original/', '/formatted/', dirname($filename));
		$resourcePath = $resourceDir . '/' . str_replace('&', '_', basename($filename, '.' . $extension)) . ';' . $resourceId . $resourceExtension;
		return $resourcePath;
	}
	
	public static function getFormatKey($format)
	{
		if (f_util_ArrayUtils::isNotEmpty($format))
		{
			$key = self::generateFormatKey($format);
			if (! f_util_StringUtils::isEmpty($key))
			{
				return $key;
			}
		}
		return null;
	}
	
	private static function generateFormatKey($format)
	{
		$keys = array();
		if (isset($format['max-height']))
		{
			$keys[] = 'maxh=' . intval($format['max-height']);
		}
		
		if (isset($format['max-width']))
		{
			$keys[] = 'maxw=' . intval($format['max-width']);
		}
		
		if (isset($format['min-height']))
		{
			$keys[] = 'minh=' . intval($format['min-height']);
		}
		
		if (isset($format['min-width']))
		{
			$keys[] = 'minw=' . intval($format['min-width']);
		}
		
		if (isset($format['height']))
		{
			$keys[] = 'h=' . intval($format['height']);
		}
		
		if (isset($format['width']))
		{
			$keys[] = 'w=' . intval($format['width']);
		}
		
		return implode(',', $keys);
	}
	
	private static function linkToOriginal($originalFileName, $formattedFileName)
	{
		f_util_FileUtils::mkdir(dirname($formattedFileName));
		symlink($originalFileName, $formattedFileName);
	}
}

class media_ResizerFormatter
{
	protected function __construct()
	{
	}
	
	/**
	 * the singleton instance
	 * @var media_ResizerFormatter
	 */
	private static $instance = null;
	
	/**
	 * @return media_ResizerFormatter
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			if (extension_loaded('imagick'))
			{
					$finalClassName = 'media_ImagickResizerFormatter';
			}
			else if (extension_loaded('gd'))
			{
					$finalClassName = 'media_GDResizerFormatter';
			}
			else
			{
				$finalClassName = get_class();
			}
			self::$instance = new $finalClassName();
		}
		return self::$instance;
	}
	
	/**
	 * @param string $inputFileName
	 * @param string $formattedFileName
	 * @param array $formatSizeInfo
	 * @return boolean true if resized
	 */
	public function resize($inputFileName, $formattedFileName, $formatSizeInfo)
	{
		if (Framework::isDebugEnabled())
		{
			Framework::debug("NO library installed for resizing $inputFileName to " . str_replace("\n", "", var_export($formatSizeInfo, true)));
		}
		$this->linkToOriginal($inputFileName, $formattedFileName);
		return false;
	}
	
	protected function linkToOriginal($originalFileName, $formattedFileName)
	{
		f_util_FileUtils::mkdir(dirname($formattedFileName));
		f_util_FileUtils::symlink($originalFileName, $formattedFileName, f_util_FileUtils::OVERRIDE);
	}
	
	protected function computeImageSize($orginalWidth, $originalHeight, $format)
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
		
		if (isset($format['min-width']) && ($resourceWidth < intval($format['min-width'])))
		{
			$resourceWidth = intval($format['min-width']);
			$resourceHeight = $resourceWidth / $srcRatio;
		}
		
		if (isset($format['min-height']) && ($resourceHeight < intval($format['min-height'])))
		{
			$resourceHeight = intval($format['min-height']);
			$resourceWidth = $resourceHeight * $srcRatio;
		}
		
		if (isset($format['max-width']) && ($resourceWidth > intval($format['max-width'])))
		{
			$resourceWidth = intval($format['max-width']);
			$resourceHeight = $resourceWidth / $srcRatio;
		}
		
		if (isset($format['max-height']) && ($resourceHeight > intval($format['max-height'])))
		{
			$resourceHeight = intval($format['max-height']);
			$resourceWidth = $resourceHeight * $srcRatio;
		}
		
		$resourceWidth = round($resourceWidth);
		$resourceHeight = round($resourceHeight);
		
		return array(min($resourceWidth, $orginalWidth), min($resourceHeight, $originalHeight));
	}
}

class media_ImagickResizerFormatter extends media_ResizerFormatter
{
	/**
	 * @param string $inputFileName
	 * @param string $formattedFileName
	 * @param array $formatSizeInfo
	 * @return boolean true if resized
	 */
	public function resize($inputFileName, $formattedFileName, $formatSizeInfo)
	{
		try
		{
			$formatable = false;
			$imagik = new Imagick($inputFileName);
			switch ($imagik->getImageFormat())
			{
				case 'PNG' :
				case 'JPG' :
				case 'JPEG' :
				case 'GIF' :
					$formatable = true;
					break;
			}
			
			if ($formatable === true && $imagik->getImageWidth() > 0)
			{			
				list ($width, $height) = $this->computeImageSize($imagik->getImageWidth(), $imagik->getImageHeight(), $formatSizeInfo);
				if ($width != $imagik->getImageWidth() || $height != $imagik->getImageHeight())
				{
					if ($imagik->getNumberImages() > 1)
					{
						$vi = $imagik->getVersion();
						if ($vi['versionNumber'] >= 1591) //ImageMagick 6.3.7 
						{
							$imagik = $imagik->coalesceImages();
							foreach ($imagik as $frame) 
							{
							   $frame->thumbnailImage($width, $height, true);
							}
							f_util_FileUtils::mkdir(dirname($formattedFileName));
							$imagik->writeImages($formattedFileName, true);
							return true;
						}
						else
						{
							if (Framework::isInfoEnabled())
							{
								Framework::info(__METHOD__ . ' Unable to resize animated gif with ' . $vi['versionString']);
							}
						}
					}
					else
					{
						$imagik->thumbnailImage($width, $height, true);	
						f_util_FileUtils::mkdir(dirname($formattedFileName));
						$imagik->writeImage($formattedFileName);
						return true;
					}
				}
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		$this->linkToOriginal($inputFileName, $formattedFileName);
		return false;
	}
}

class media_GDResizerFormatter extends media_ResizerFormatter
{
	/**
	 * @param string $inputFileName
	 * @param string $formattedFileName
	 * @param array $formatSizeInfo
	 * @return boolean true if resized
	 */
	public function resize($inputFileName, $formattedFileName, $formatSizeInfo)
	{
		try
		{
			$extension = f_util_FileUtils::getFileExtension($inputFileName);
			switch (strtolower($extension))
			{
				case 'gif' :
					$sizeInfo = getimagesize($inputFileName);
					if ($sizeInfo[0] > 0)
					{
						list ($width, $height) = $this->computeImageSize($sizeInfo[0], $sizeInfo[1], $formatSizeInfo);
						if ($width == $sizeInfo[0] && $height == $sizeInfo[1])
						{
							break;
						}
						if ($this->isGifAnim($inputFileName))
						{
							break;
						}
						$imageSrc = imagecreatefromgif($inputFileName);
						$colorTransparent = imagecolortransparent($imageSrc);
						$imageFormatted = imagecreate($width, $height);
						imagepalettecopy($imageFormatted, $imageSrc);
						imagefill($imageFormatted, 0, 0, $colorTransparent);
						imagecolortransparent($imageFormatted, $colorTransparent);
						imagecopyresized($imageFormatted, $imageSrc, 0, 0, 0, 0, $width, $height, $sizeInfo[0], $sizeInfo[1]);
						
						f_util_FileUtils::mkdir(dirname($formattedFileName));
						imagegif($imageFormatted, $formattedFileName);
						return true;
					}
					break;
				case 'png' :
					$sizeInfo = getimagesize($inputFileName);
					if ($sizeInfo[0] > 0)
					{
						list ($width, $height) = $this->computeImageSize($sizeInfo[0], $sizeInfo[1], $formatSizeInfo);
						if ($width == $sizeInfo[0] && $height == $sizeInfo[1])
						{
							break;
						}
						$imageSrc = imagecreatefrompng($inputFileName);
						$imageFormatted = imagecreatetruecolor($width, $height);
						imageAlphaBlending($imageFormatted, false);
						imageSaveAlpha($imageFormatted, true);
						imagecopyresampled($imageFormatted, $imageSrc, 0, 0, 0, 0, $width, $height, $sizeInfo[0], $sizeInfo[1]);
						
						f_util_FileUtils::mkdir(dirname($formattedFileName));
						imagepng($imageFormatted, $formattedFileName);
						return true;
					}
					break;
				case 'jpg' :
				case 'jpeg' :
					$sizeInfo = getimagesize($inputFileName);
					if ($sizeInfo[0] > 0)
					{
						list ($width, $height) = $this->computeImageSize($sizeInfo[0], $sizeInfo[1], $formatSizeInfo);
						if ($width == $sizeInfo[0] && $height == $sizeInfo[1])
						{
							break;
						}
						$imageSrc = imagecreatefromjpeg($inputFileName);
						$imageFormatted = imagecreatetruecolor($width, $height);
						imagecopyresampled($imageFormatted, $imageSrc, 0, 0, 0, 0, $width, $height, $sizeInfo[0], $sizeInfo[1]);
						
						f_util_FileUtils::mkdir(dirname($formattedFileName));
						imagejpeg($imageFormatted, $formattedFileName, 90);
						return true;
					}
					break;
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		$this->linkToOriginal($inputFileName, $formattedFileName);
		return false;
	}
	
	/**
	 * Return TRUE if the given file is an animated GIF.
	 * @param string $filePath
	 * @return boolean
	 */
	private function isGifAnim($filePath)
	{
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
							$frameCount ++;
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
		
		return $isGifAnim;
	}
}

