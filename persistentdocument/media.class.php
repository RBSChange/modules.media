<?php
/**
 * media_persistentdocument_media
 * @package modules.media
 */
class media_persistentdocument_media extends media_persistentdocument_mediabase
{
	/**
	 * @see media_persistentdocument_mediabase::getBackofficeIndexedDocument()
	 * @return indexer_IndexedDocument
	 */
	public function getBackofficeIndexedDocument()
	{
		$indexedDoc = parent::getBackofficeIndexedDocument();
		$indexedDoc->setText($indexedDoc->getText() . "\n" . $this->getTextForIndexer());
		return $indexedDoc;
	}

	/**
	 * @return unknown
	 */
	private function getTextForIndexer()
	{
		return media_MediaService::getInstance()->getTextForIndexer($this);
	}

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