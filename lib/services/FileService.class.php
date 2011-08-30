<?php
/**
 * @package modules.media
 */
class media_FileService extends f_persistentdocument_DocumentService
{
	/**
	 * @var media_FileService
	 */
	private static $instance;
	
	/**
	 * @return media_FileService
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
	 * @return media_persistentdocument_file
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_media/file');
	}
	
	/**
	 * Create a query based on 'modules_media/file' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_media/file');
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$tmpFileId = intval($document->getTmpfile());
		if ($tmpFileId > 0 && $tmpFileId != $document->getId())
		{
			$tmpFile = DocumentHelper::getDocumentInstance($tmpFileId);
			$newFileName = $tmpFile->getDocumentService()->getAbsoluteFilePath($tmpFile, $tmpFile->getLang());
			$document->setNewFileName($newFileName, $tmpFile->getVoFilename());
			$document->setTmpfile(null);
		}
		
		$tmpFileName = $document->getNewFileName();
		if ($tmpFileName !== null && is_file($tmpFileName))
		{
			if ($document->getFilename() === null)
			{
				$filename = basename($tmpFileName);
				$document->setFilename(f_util_StringUtils::utf8Encode($filename));
			}
			else
			{
				$filename = $document->getFilename();
			}
			if (defined('NODE_NAME'))
			{
				$document->setNode(NODE_NAME);
				if ($document->hasMeta('nodes'))
				{
					$prefix = RequestContext::getInstance()->getLang() . ':';
					$syncNodes = array();
					foreach ($document->getMeta('nodes') as $syncNode) 
					{
						if (strpos($syncNode, $prefix) !== 0)
						{
							$syncNodes[] = $syncNode;
						}
					}
					$document->setMeta('nodes', count($syncNodes) > 0 ? $syncNodes : null);
				}
			}
			$extension = f_util_FileUtils::getFileExtension($filename);
			$info = $this->getFileInfoByPath($tmpFileName, $extension);
			$info['extension'] = $extension;
			$info['filename'] = basename($filename, '.' . $extension);
			$document->setContentlength($info['filesize']);
			$document->setMimetype($info['mimetype']);
			$document->setInfo($info);
		}
	}
	
	/**
	 * @param string $path
	 * @param string $extension
	 * @return array <filesize, size, mimetype, isGifAnim, width, height>
	 */
	private function getFileInfoByPath($path, $extension = null)
	{
		clearstatcache();
		$info = array('filesize' => filesize($path), 'size' => f_util_FileUtils::getReadableFileSize($path), 'mimetype' => self::getMimetype($path, $extension));
		
		if ($info['mimetype'] === 'image/gif' && MediaHelper::isGifAnim($path))
		{
			$info['isGifAnim'] = true;
		}
		
		$info = array_merge($info, MediaHelper::getImageSize($path));
		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__ . ' ' . $path . ' : ' . var_export($info, true));
		}
		return $info;
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function postSave($document, $parentNodeId = null)
	{
		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__ . " : " . $document->__toString());
		}
		
		$tmpFileName = $document->getNewFileName();
		if ($tmpFileName !== null && is_file($tmpFileName))
		{
			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__ . " : Add new file " . $tmpFileName);
			}
			
			$lang = RequestContext::getInstance()->getLang();
			$path = $this->getAbsoluteFolder($document->getId(), $lang);
			
			//Remove old file
			if (is_dir($path))
			{
				f_util_FileUtils::clearDir($path);
				try
				{
					$this->deleteFormatedMedia($document, $lang);
				}
				catch (Exception $e)
				{
					Framework::exception($e);
				}
			}
			else
			{
				f_util_FileUtils::mkdir($path);
			}
			
			$originalFileName = $path . $document->getFilename();
			$fileSize = filesize($tmpFileName);
			
			if ($fileSize > 0)
			{
				if (!@copy($tmpFileName, $originalFileName))
				{
					throw new IOException('cannot-copy : ' . $tmpFileName . ' to ' . $originalFileName);
				}
			}
			else
			{
				if (Framework::isDebugEnabled())
				{
					Framework::debug(__METHOD__ . " : Null file size " . $tmpFileName);
				}
				@touch($originalFileName);
			}
			@unlink($tmpFileName);
			
			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__ . " : New file added " . $originalFileName);
			}
			
			$document->setNewFileName(null);
			$this->dispatchMediaFileContentUpdated($document, $lang);
		}
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @param string $lang
	 */
	public function dispatchMediaFileContentUpdated($document, $lang)
	{
		try 
		{
			f_event_EventManager::dispatchEvent('mediaFileContentUpdated', $this, array('media' => $document, 'lang' => $lang));
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
	}
	
	
	/**
	 * @var Boolean
	 */
	private static $hasFileInfo;
	private static $magicFile;
	
	/**
	 * @param string $path
	 * @param string $extension
	 */
	public static function getMimetype($path, $extension = null)
	{
		if (self::$hasFileInfo === null)
		{
			self::$hasFileInfo = function_exists('finfo_open');
			if (self::$hasFileInfo)
			{
				self::$magicFile = Framework::getConfigurationValue("modules/media/fileinfo_magic_file_path", "/usr/share/file/magic");				
			}
		}
		$mime = null;
		
		if (self::$hasFileInfo)
		{
			// FIXME: is FILEINFO_MIME_ENCODING something util for navigator ?
			// See RFC 2045 ?
			if (defined("FILEINFO_MIME_TYPE"))
			{
				$finfoMode = FILEINFO_MIME_TYPE;
			}
			else
			{
				$finfoMode = FILEINFO_MIME;
			}
			$finfo = @finfo_open($finfoMode, self::$magicFile);
			if (!$finfo)
			{
				Framework::info("Could not open magic file " . self::$magicFile . " . update your [modules/media/fileinfo_magic_file_path] configuration");
				self::$hasFileInfo = false;
			}
			else
			{
				$mime = finfo_file($finfo, $path);
				finfo_close($finfo);
			}
		}
		
	
		if ($mime === null && function_exists('mime_content_type'))
		{
			$mime = mime_content_type($path);
		}
			
		if (f_util_StringUtils::isEmpty($mime) || $mime === 'application/octet-stream')
		{
			$mime = MediaHelper::getMimeTypeByExtension($extension ? $extension : f_util_FileUtils::getFileExtension($path));
		}
		else
		{
			// FIXME: something strange appears one some platforms. Observed on MAC OS X, PHP5.3.
			// Probably an alternative implementation of mime_content_type based on fileinfo using FILEINFO_MIME instead
			// of FILEINFO_MIME_TYPE
			$charsetIndex = strpos($mime, "; charset=");
			if ($charsetIndex !== false)
			{
				$mime = substr($mime, 0, $charsetIndex);
			}
		}
		
		return $mime;
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @return void
	 */
	protected function postDeleteLocalized($document)
	{
		$lang = RequestContext::getInstance()->getLang();
		
		$path = $this->getAbsoluteFolder($document->getId(), $lang);
		f_util_FileUtils::rmdir($path);
		
		$this->deleteFormatedMedia($document, $lang);
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @return void
	 */
	protected function postDelete($document)
	{
		$path = $this->getAbsoluteFolder($document->getId(), null);
		f_util_FileUtils::rmdir($path);
		
		$this->deleteFormatedMedia($document);
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @param string $lang
	 * @return void
	 */
	public function deleteFormatedMedia($document, $lang = null)
	{
		$this->deleteFormatedMediaId($document->getId(), $lang);
		f_event_EventManager::dispatchEvent('deleteFormatedMedia', null, array('media' => $document));
	}
	
	/**
	 * @param integer $documentId
	 * @param string $lang$
	 * @return void
	 */
	public function deleteFormatedMediaId($documentId, $lang = null)
	{
		$path = $this->getFormattedAbsoluteFolder($documentId, $lang);
		f_util_FileUtils::rmdir($path);
	}
	
	/**
	 * @param Integer $id
	 * @param String lang
	 * @return String
	 */
	public final function getRelativeFolder($id, $lang = null)
	{
		$folder = "";
		$idStr = str_split((string) $id);
		
		$i = 1;
		foreach ($idStr as $c)
		{
			$folder .= $c;
			if ($i % 3 == 0)
			{
				$folder .= '/';
				$i = 1;
			}
			else
			{
				$i++;
			}
		}
		
		if (!f_util_StringUtils::endsWith($folder, "/"))
		{
			$folder .= '/';
		}
		return ($lang === null) ? $folder : $folder . $lang . '/';
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @return string
	 */
	public static function getOriginalFolder($document)
	{
		return $this->getAbsoluteFolder($document->getId(), RequestContext::getInstance()->getLang());
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @param boolean $mustExist
	 * @return string
	 */
	public function getOriginalPath($document, $mustExist = false)
	{
		if (!$document->getFilename())
		{
			if ($mustExist)
			{
				$lang = $document->getLang();
			}
			else
			{
				return null;
			}
		}
		else
		{
			$lang = RequestContext::getInstance()->getLang();
		}
		
		$mediaFolder = $this->getAbsoluteFolder($document->getId(), $lang);
		$filePath = $mediaFolder . $document->getFilenameForLang($lang);
		return $filePath;
	}
	
	/**
	 * @param Integer $id
	 * @param String $lang
	 * @return String
	 */
	protected function getAbsoluteFolder($id, $lang = null)
	{
		return WEBEDIT_HOME . '/media/original/' . $this->getRelativeFolder($id, $lang);
	}
	
	/**
	 * @param Integer $id
	 * @param String $lang
	 * @return String
	 */
	protected function getFormattedAbsoluteFolder($id, $lang = null)
	{
		return WEBEDIT_HOME . '/media/formatted/' . $this->getRelativeFolder($id, $lang);
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @param $lang
	 */
	protected function getAbsoluteFilePath($document, $lang = null, $urlencoded = false)
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
		$fileName = $document->getFilenameForLang($lang);
		if ($urlencoded)
			$fileName = rawurlencode($fileName);
		return $this->getAbsoluteFolder($document->getId(), $lang) . $fileName;
	}
	
	/**
	 * @param String $mediaFileName
	 * @return String
	 */
	protected function convertMediaFileNameToUrl($fileName)
	{
		
		$baseUrl = Framework::getBaseUrl();
		if (!$baseUrl)
		{
			$baseUrl = Framework::getUIBaseUrl();
		}
		return $baseUrl . $this->convertMediaFileNameToAbsoluteUrl($fileName);
	}
	
	/**
	 * @param String $mediaFileName
	 * @return String
	 */
	protected function convertMediaFileNameToAbsoluteUrl($fileName)
	{
		$completName = substr($fileName, strlen(WEBEDIT_HOME . MediaHelper::ROOT_MEDIA_PATH));
		if (strpos($completName, 'formatted/') === 0)
		{
			$completName = str_replace(array('&', '%26'), '_', $completName);
		}
		return '/publicmedia/' . $completName;
	}
	
	/**
	 * @param website_UrlRewritingService $urlRewritingService
	 * @param media_persistentdocument_file $document
	 * @param website_persistentdocument_website $website
	 * @param string $lang
	 * @param array $parameters
	 * @return f_web_Link | null
	 */
	public function getWebLink($urlRewritingService, $document, $website, $lang, $parameters)
	{
		if ($website === null)
		{
			f_util_ProcessUtils::printBackTrace();
		}
		
		$documentId = $document->getId();
		$fileName = $document->getFilenameForLang($lang);
		if (empty($fileName))
		{
			$fileName = $document->getVoFilename();
			$lang = $document->getLang();
		}
		
		if (isset($parameters['download']))
		{
			unset($parameters['download']);
			$parameters['lang'] = $lang;
			$parameters['cmpref'] = $documentId;
			return $urlRewritingService->getActionLinkForWebsite('media', 'Display', $website, $lang, $parameters);
		}
		
		$protocol = RequestContext::getInstance()->getProtocol();
		$host = $this->getHostForDocumentId($documentId, $lang);
			
		$formatKey = media_FormatterHelper::getFormatKey($parameters);
		if ($formatKey !== null)
		{
			$resourceExtension = $this->getFormatedExtension($document, $lang);
			if ($resourceExtension !== false)
			{
				$link = new f_web_ParametrizedLink($protocol, $host, 
					'/publicmedia/formatted/' . 
					$this->getRelativeFolder($documentId, $lang) . rawurlencode($fileName) . 
					';' . $formatKey . $resourceExtension);
				return $link;
			}
		}
		else
		{
			$link = new f_web_ParametrizedLink($protocol, $host, '/publicmedia/original/' . $this->getRelativeFolder($documentId, $lang) . rawurlencode($fileName));
			$link->setQueryParameters($parameters);
			return $link;
		}
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
	 * @param integer $id
	 * @param string $lang
	 */
	protected function getHostForDocumentId($id, $lang)
	{
		$webSite = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
		$domains = Framework::getConfigurationValue('modules/media/domains', array());
		if (f_util_ArrayUtils::isEmpty($domains))
		{
			return $webSite->isLangAvailable($lang) ? $webSite->getDomainForLang($lang) : Framework::getUIDefaultHost();
		}
		else
		{
			$domains = array_values($domains);
			return (count($domains) > 1) ?  $domains[$id % count($domains)] : $domains[0];
		}	
	}

	/**
	 * @param media_persistentdocument_file $document
	 * @param string $lang
	 * @param array $parameters
	 * @return String
	 */
	public function generateAbsoluteUrl($document, $lang, $parameters)
	{
		$path = $this->getAbsoluteFilePath($document, $lang, true);
		return $this->convertMediaFileNameToAbsoluteUrl($path);
	}
	
	/**
	 * @param media_persistentdocument_file $document
	 * @param string $lang
	 * @param array $parameters
	 * @return string
	 */
	public final function generateDownloadUrl($document, $lang = null, $parameters = array())
	{
		$lang = ($lang == null) ? RequestContext::getInstance()->getLang() : $lang;
		$parameters['download'] = true;
		return website_UrlRewritingService::getInstance()->getDocumentUrl($document, $lang, $parameters);
	}
	
	/**
	 * @param media_persistentdocument_file $file
	 * @return Boolean
	 */
	public function hasAccess($file)
	{
		return true;
	}
	
	/**
	 * @param media_persistentdocument_file $file
	 * @return Boolean
	 */
	public function hasBoAccess($file)
	{
		return true;
	}
	
	/**
	 * @see f_persistentdocument_DocumentService::getWebsiteId()
	 *
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return integer
	 */
	public function getWebsiteId($document)
	{
		return null;
	}
	
	/**
	 * @param Integer $fileId
	 * @return media_persistentdocument_file
	 */
	private function resolveFileDocumentInstance($fileId)
	{
		try
		{
			$id = intval($fileId);
			if ($id > 0)
			{
				$file = $this->getDocumentInstance($id);
				if ($file instanceof media_persistentdocument_file)
				{
					return $file;
				}
			}
		}
		catch (Exception $e)
		{
			if (Framework::isInfoEnabled())
			{
				Framework::info(__METHOD__ . ' : invalid media document id #' . $fileId);
			}
		}
		return null;
	}
	
	public function addUsageInfo($mediaId, $mediaLang, $documentId, $documentLang)
	{
		$media = $this->resolveFileDocumentInstance($mediaId);
		if ($media === null)
		{
			if (Framework::isInfoEnabled())
			{
				Framework::info(__METHOD__ . " : invalid document #$mediaId");
			}
			return;
		}
		try
		{
			$this->tm->beginTransaction();
			if ($media->addUsage($mediaLang, $documentId, $documentLang))
			{
				$this->pp->updateDocument($media);
			}
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}
	}
	
	public function removeUsageInfo($mediaId, $mediaLang, $documentId, $documentLang)
	{
		$media = $this->resolveFileDocumentInstance($mediaId);
		if ($media === null)
		{
			if (Framework::isInfoEnabled())
			{
				Framework::info(__METHOD__ . " : invalid document #$mediaId");
			}
			return;
		}
		
		try
		{
			$this->tm->beginTransaction();
			if ($media->removeUsage($mediaLang, $documentId, $documentLang))
			{
				$this->pp->updateDocument($media);
			}
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
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
		$attributes['href'] = media_FileService::getInstance()->generateDownloadUrl($document, $lang);
		$document->addDownloadAttributes($attributes);
		return f_util_HtmlUtils::buildLink($attributes, $content);
	}
}