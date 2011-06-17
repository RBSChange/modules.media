<?php
/**
 * media_persistentdocument_file
 * @package modules.media
 */
class media_persistentdocument_file extends media_persistentdocument_filebase 
{
	private $info;
	
	/**
	 * @param string $lang
	 */
	private function loadInfoForLang($lang)
	{
		if (!isset($this->info[$lang]))
		{
			$infoData = parent::getInfoForLang($lang);
			if ($infoData == null)
			{
				$this->info[$lang] = array();
			}
			else
			{
				$this->info[$lang] = unserialize($infoData);
				$this->info[$lang]['id'] = $this->getId();
			}
		}
	}
	
	/**
	 * @return array
	 */	
	public function getCommonInfo()
	{
		$info = $this->getInfo();
		if (!$this->getFilename())
		{
			$info = array_merge($this->getInfoForLang($this->getLang()), $info);
		}
		return $info;
	}
	
	/**
	 * @return array
	 */
	public function getInfo()
	{
		return $this->getInfoForLang(RequestContext::getInstance()->getLang());
	}
	
	/**
	 * @param string $lang
	 * @return array
	 */
	public function getInfoForLang($lang)
	{
		$this->loadInfoForLang($lang);
		$infos = $this->info[$lang];		
		if (!isset($infos['type']))
		{
			$infos['type'] = MediaHelper::getMediaTypeByFilename($this->getFilename());
		}
		return $infos;
	}	
		
	/**
	 * @param array $info
	 */
	public function setInfo($info)
	{
		if (!isset($info['type']))
		{
			$info['type'] = MediaHelper::getMediaTypeByFilename($this->getFilename());
		}
		$lang = RequestContext::getInstance()->getLang();
		$this->loadInfoForLang($lang);
		$this->info[$lang] = $info;
		if (isset($this->info[$lang]['url']))
		{
			$this->info[$lang]['url'] = preg_replace('#http://[^/]*#i', '', $this->info[$lang]['url']);
		}
		parent::setInfo(serialize($this->info[$lang]));
	}
	
    /**
     * @var String
     */
    private $newFileName;

    /**
     * @param String $tempFilePath
     * @param String $realFileName
     */
    public function setNewFileName($tempFilePath, $realFileName = null)
    {
        $this->newFileName = $tempFilePath;
        if ($realFileName !== null)
        {
            $this->setFilename($realFileName);
        }
    }

    /**
     * @return String
     */
    public function getNewFileName ()
    {
        return $this->newFileName;
    }
    
    public function setAllUsages($referenceInfos)
    {
    	if (f_util_ArrayUtils::isEmpty($referenceInfos))
    	{
    		$this->setUsageinfo(null);
    	}
    	else
    	{
    		$this->setUsageinfo(serialize($referenceInfos));
    	}
    }
    
    public function getAllUsages()
    {
    	$data = $this->getUsageinfo();
    	if (!is_null($data))
    	{
    		return unserialize($data);
    	}
    	return array();
    }
    
    /**
     * @return integer
     */
    public function countReferences()
    {
    	return count($this->getAllUsages());
    }

    /**
     * @param String $mediaLang
     * @param Integer $documentId
     * @param String $documentLang
     * @return boolean
     */
    public function addUsage($mediaLang, $documentId, $documentLang)
    {
    	$key = media_MediaUsageHelper::buildKey($this->getId(), $mediaLang, $documentId, $documentLang);
    	$usages = $this->getAllUsages();
    	if (!isset($usages[$key]))
    	{
    		if (Framework::isInfoEnabled())
    		{
    			Framework::info(__METHOD__ . ' : ' . $key);
    		}
    		$usages[$key] = media_MediaUsageHelper::buildValue($this->getId(), $mediaLang, $documentId, $documentLang);
    		$this->setAllUsages($usages);
    		return true;
    	}
    	return false;
    }

    /**
     * @param String $mediaLang
     * @param Integer $documentId
     * @param String $documentLang
     * @return boolean
     */
    public function removeUsage($mediaLang, $documentId, $documentLang)
    {
    	$key = media_MediaUsageHelper::buildKey($this->getId(), $mediaLang, $documentId, $documentLang);
    	$usages = $this->getAllUsages();
    	if (isset($usages[$key]))
    	{
    	    if (Framework::isInfoEnabled())
    		{
    			Framework::info(__METHOD__ . ' : ' . $key);
    		}
    		
    		$newUsage = array();
    		foreach ($usages as $oldkey => $value) 
    		{
    			if ($oldkey !== $key) {$newUsage[$oldkey] = $value;}
    		}
    		$this->setAllUsages($newUsage);
    		return true;
    	}
    	return false;
    }

    public function addDownloadAttributes(&$attributes)
    {
    	$class = isset($attributes['class']) ? $attributes['class'] : null;
    	$attrs = MediaHelper::getAdditionnalDownloadAttributes($this, $class);
    	$attributes = array_merge($attributes, $attrs);
    }
    
 	/**
 	 * @return string
 	 */
    public function getPath()
    {
    	return $this->getDocumentService()->getOriginalPath($this);
    }
    
	private $tmpFileId;
	
	public function getTmpfile()
	{
		return $this->tmpFileId;
	}
	
	public function setTmpfile($tmpFileId)
	{
		$val = intval($tmpFileId);
		if ($val > 0)
		{
			$this->setModificationdate(null);
		}
		$this->tmpFileId = ($val > 0) ? $val : null;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return null;
	}

	/**
	 * @param string $lang
	 * @return string
	 */
	public function getDescriptionForLang($lang)
	{
		return null;
	}
}