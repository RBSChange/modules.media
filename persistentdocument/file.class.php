<?php
/**
 * media_persistentdocument_file
 * @package modules.media
 */
class media_persistentdocument_file extends media_persistentdocument_filebase 
{
	private $info;
	
	private function loadInfoForLang($lang)
	{
		if (!isset($this->info[$lang]))
		{
			$infoData = parent::getInfo();
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
	 * @return Array
	 */
	public function getInfo()
	{
		$lang = RequestContext::getInstance()->getLang();
		$this->loadInfoForLang($lang);
		return $this->info[$lang];
	}
	/**
	 * @param Array $info
	 */
	public function setInfo($info)
	{
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
    
    protected function countReferences()
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
}