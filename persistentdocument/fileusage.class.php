<?php
/**
 * @package modules.media
 */
class media_persistentdocument_fileusage extends media_persistentdocument_fileusagebase 
{
	public function getUsages()
	{
		$data  = $this->getUsageinfo();
		if (!empty($data))
		{
			return unserialize($data);
		}
		return array();	
	}
	
	public function setUsages($usagesArray)
	{
		if (f_util_ArrayUtils::isEmpty($usagesArray))
		{
			$this->setUsageinfo(null);
		}
		else
		{
			$this->setUsageinfo(serialize($usagesArray));
		}
	}
}