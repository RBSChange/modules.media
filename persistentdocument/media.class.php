<?php
/**
 * media_persistentdocument_media
 * @package modules.media
 */
class media_persistentdocument_media extends media_persistentdocument_mediabase
{
	/**
	 * @param array $info
	 */
	public function setInfo($info)
	{	
		$info['type'] = $this->getMediatype();
		$info['alt'] = $this->getTitle();		
		parent::setInfo($info);
	}
	
	public function getI18ntmpfile()
	{
		return null;
	}

	public function getROI18ntmpfile()
	{
		return $this->getFilename();
	}
	
	public function setI18ntmpfile($val)
	{
		$this->setTmpfile($val);
	}
}