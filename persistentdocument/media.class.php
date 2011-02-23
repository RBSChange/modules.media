<?php
/**
 * media_persistentdocument_media
 * @package modules.media
 */
class media_persistentdocument_media extends media_persistentdocument_mediabase implements indexer_IndexableDocument
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

	// Deprecated
	
	/**
	 * @deprecated (will be removed in 4.0) no front indexation on medias. 
	 */
	public function getIndexedDocument()
	{
		$indexedDoc = new indexer_IndexedDocument();
		$indexedDoc->setId($this->getId());
		$indexedDoc->setDocumentModel('modules_media/media');
		$indexedDoc->setLabel($this->getLabel());
		$indexedDoc->setLang(RequestContext::getInstance()->getLang());
		$indexedDoc->setText($this->getTextForIndexer());
		$indexedDoc->setStringField('mediaType', $this->getMediatype());
		return $indexedDoc;
	}
}