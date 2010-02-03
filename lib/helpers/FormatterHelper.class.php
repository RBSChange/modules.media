<?php
class media_FormatterHelper
{
	/**
	 * @param String $encodedformat
	 * @example 126/0/fr/Deux%20jours%20%C3%A0%20tuer;maxh=128,maxw=128.jpg
	 * @example 126/0/fr/Deux%20jours%20%C3%A0%20tuer;h=578,w=434.jpg
	 */
	public static function outputFormatedMedia($encodedformat)
	{
		$mediaId = 0;
		$format = array();
		$lang = '';
		$formattedFileName = WEBEDIT_HOME . MediaHelper::FORMATTED_PATH . $encodedformat;
		
		self::decodeFormat($encodedformat, $mediaId, $lang, $format);
		try
		{
			RequestContext::getInstance()->beginI18nWork($lang);
			$media = DocumentHelper::getDocumentInstance($mediaId);
			
			$fileName = $media->getDocumentService()->getOriginalPath($media, false);
			if (! is_readable($fileName))
			{
				throw new Exception("Unable to read : $fileName");
			}
			
			if (self::isFormatable($fileName))
			{
				$image = self::imagickResize($fileName, $format);
				self::saveImagik($image, $formattedFileName);
			}
			else
			{
				self::linkToOriginal($fileName, $formattedFileName);
			}
			
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
			if (self::isFormatable($inputFileName))
			{
				$image = self::imagickResize($inputFileName, $format);
				self::saveImagik($image, $formattedFileName);
			}
			else
			{
				self::linkToOriginal($inputFileName, $formattedFileName);
			}
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
			if (!is_null($format) && self::isFormatable($filename))
			{
				$resourcePath = self::buildFormattedResourcePath($filename, $format);
				$image = self::imagickResize($filename, $format);
				self::saveImagik($image, $resourcePath);
			}
			else
			{
				$resourcePath = $filename;
			}
			
			MediaHelper::outputHeader($resourcePath, $document, $forceDownload);
			
			readfile($resourcePath);
			
			if ($resourcePath !== $filename)
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
	
	/**
	 * 
	 * @param String $fileName
	 */
	private static function isFormatable($fileName)
	{
		try
		{
			$image = new Imagick($fileName);
			switch ($image->getImageFormat())
			{
				case 'PNG' :
				case 'JPG' :
				case 'JPEG' :
					return true;
				case 'GIF' :
					return $image->getNumberImages() == 1;
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
			//Invalid image format
		}
		return false;
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
		
	/**
	 * @param string $fileName
	 * @return Imagick
	 */
	private static function imagickResize($fileName, $sizeInfo)
	{
		$image = new Imagick($fileName);
		$finalSize = self::computeImageSize($image->getImageWidth(), $image->getImageHeight(), $sizeInfo);
		$width = $finalSize['width'];
		$height = $finalSize['height'];
		$image->thumbnailImage($width, $height, true);
		return $image;
	}
	
	private static function linkToOriginal($originalFileName, $formattedFileName)
	{
		f_util_FileUtils::mkdir(dirname($formattedFileName));
		symlink($originalFileName, $formattedFileName);
	}
	
	private static function saveImagik($imagik, $formattedFileName)
	{
		f_util_FileUtils::mkdir(dirname($formattedFileName));
		$imagik->writeImage($formattedFileName);
	}
	
	private static function computeImageSize($orginalWidth, $originalHeight, $format)
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
		
		return array('width' => min($resourceWidth, $orginalWidth), 'height' => min($resourceHeight, $originalHeight));
	}
}