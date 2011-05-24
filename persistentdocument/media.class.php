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
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
	{
		if ($treeType == tree_parser_TreeParser::TYPE_LIST)
		{
			$nodeAttributes['countreferences'] =  $this->countReferences();
		}
		
		switch ($this->getMediatype())
		{
			case MediaHelper::TYPE_IMAGE:
				$nodeAttributes['actualtype'] = 'modules_media_image';
				$nodeAttributes['hasPreviewImage'] = true;
				if ($treeType == tree_parser_TreeParser::TYPE_MULTI_LIST)
				{
					$lang = RequestContext::getInstance()->getLang();
					$alt = htmlspecialchars($this->getTitle(), ENT_COMPAT, 'UTF-8');
					$src = MediaHelper::getUrl($this, K::XUL);
					$nodeAttributes[f_tree_parser_AttributesBuilder::HTMLLINK_ATTRIBUTE] = '<img class="image" src="' . $src . '" cmpref="' . $this->getId() . '" alt="' . $alt . '" lang="' . $lang . '" xml:lang="' . $lang . '" />';
					$nodeAttributes[f_tree_parser_AttributesBuilder::BLOCK_ATTRIBUTE] = $nodeAttributes['actualtype'];
				}
				if ($treeType == 'wlist')
				{
			    	$nodeAttributes['thumbnailsrc'] = LinkHelper::getUIActionLink('media', 'BoDisplay')
						->setQueryParameter('cmpref', $this->getId())
						->setQueryParameter('format', 'modules.uixul.backoffice/thumbnaillistitem')
						->setQueryParameter('lang', RequestContext::getInstance()->getLang())
						->setQueryParameter('time', date_Calendar::now()->getTimestamp())->getUrl();
				}				
				break;
			
			case MediaHelper::TYPE_PDF:
				$nodeAttributes['actualtype'] = 'modules_media_pdf';
				if ($treeType == tree_parser_TreeParser::TYPE_MULTI_LIST)
				{
					$nodeAttributes[f_tree_parser_AttributesBuilder::HTMLLINK_ATTRIBUTE] = PHPTAL_Php_Attribute_CHANGE_download::render($this, null, true);
					$nodeAttributes[f_tree_parser_AttributesBuilder::BLOCK_ATTRIBUTE] = $nodeAttributes['actualtype'];
				}
				break;
			
			case MediaHelper::TYPE_DOC:
				$nodeAttributes['actualtype'] = 'modules_media_doc';
				if ($treeType == tree_parser_TreeParser::TYPE_MULTI_LIST)
				{
					$nodeAttributes[f_tree_parser_AttributesBuilder::HTMLLINK_ATTRIBUTE] = PHPTAL_Php_Attribute_CHANGE_download::render($this, null, true);
					$nodeAttributes[f_tree_parser_AttributesBuilder::BLOCK_ATTRIBUTE] = $nodeAttributes['actualtype'];
				}
				break;
			
			case MediaHelper::TYPE_FLASH:
				$nodeAttributes['actualtype'] = 'modules_media_flash';
				if ($treeType == tree_parser_TreeParser::TYPE_MULTI_LIST)
				{

				    $styleAttributes = array();
		            $mediaInfos = $this->getInfo();
		            if (isset($mediaInfos['height']))
		            {
		                $styleAttributes['height'] = $mediaInfos['height'] . 'px';
		            }
		            if (isset($mediaInfos['width']))
		            {
		                $styleAttributes['width'] = $mediaInfos['width'] . 'px';
		            }
		            $title = htmlspecialchars($this->getTitle());
		            
		            $style = f_util_HtmlUtils::buildStyleAttribute($styleAttributes);
		            $link = '<a rel="cmpref:' . $this->getId() . '" href="#" class="media-flash-dummy" title="' . $title . '" lang="' . RequestContext::getInstance()->getLang() . '" style="' . $style . '">' . $title . '&#160;</a>';
					$nodeAttributes[f_tree_parser_AttributesBuilder::HTMLLINK_ATTRIBUTE] = $link;
					$nodeAttributes[f_tree_parser_AttributesBuilder::BLOCK_ATTRIBUTE] = $nodeAttributes['actualtype'];
				}
				break;
			
			case MediaHelper::TYPE_VIDEO:
				$nodeAttributes['actualtype'] = 'modules_media_video';
				if ($treeType == tree_parser_TreeParser::TYPE_MULTI_LIST)
				{
					$nodeAttributes[f_tree_parser_AttributesBuilder::HTMLLINK_ATTRIBUTE] = '';
					$nodeAttributes[f_tree_parser_AttributesBuilder::BLOCK_ATTRIBUTE] = $nodeAttributes['actualtype'];
				}
				break;
			default:
				if ($treeType == tree_parser_TreeParser::TYPE_MULTI_LIST)
				{
					$nodeAttributes[f_tree_parser_AttributesBuilder::HTMLLINK_ATTRIBUTE] = PHPTAL_Php_Attribute_CHANGE_download::render($this, null, true);
				}
				break;
		}
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

	// Deprecated method.
	
	/**
	 * @deprecated no front indexation on medias. 
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