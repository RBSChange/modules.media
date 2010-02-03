<?php
class media_BlockFlashAction extends website_BlockAction
{
	/**
	 * @return Array
	 */
	public function getCacheDependencies()
	{
		return array("modules_media/media");
	}

	/**
	 * @param website_BlockActionRequest $request
	 * @return Array
	 */
	public function getCacheKeyParameters($request)
	{
		$keys = array($this->findParameterValue(K::COMPONENT_ID_ACCESSOR), $this->findParameterValue('transparent'));
		$keys['lang'] = $this->getLang();
		return $keys;
	}

	/**
	 * @see website_BlockAction::execute()
	 *
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function execute($request, $response)
	{
		if ($this->isInBackoffice())
		{
			return $this->executeBackOffice($request, $response);
		}
		 
		$medias = array();
		$document = $this->getRequiredDocumentParameter();
		$parameters = $document->getInfo();
		$parameters['url'] = $document->getDocumentService()->generateUrl($document, null, array());
		if (isset($parameters['id']) && is_numeric($parameters['id']))
		{
			$parameters['id'] = 'media-' . $parameters['id'];
		}
		if ($document->getDescription())
		{
			$parameters['description'] = $document->getDescriptionAsHtml();
		}
		$medias[] = $parameters;
		$request->setAttribute('medias', $medias);

		return website_BlockView::SUCCESS;
	}

	/**
	 * @see website_BlockAction::execute()
	 *
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function executeBackOffice ($request, $response)
	{
		$parameters = array();
		try
		{
			$document = $this->getRequiredDocumentParameter();
			$styleAttributes = array();
			$mediaInfos = $document->getInfo();
			if (isset($mediaInfos['height']))
			{
				$styleAttributes['height'] = $mediaInfos['height'] . 'px';
			}
			if (isset($mediaInfos['width']))
			{
				$styleAttributes['width'] = $mediaInfos['width'] . 'px';
			}
			$parameters['style'] = f_util_HtmlUtils::buildStyleAttribute($styleAttributes);
			if ($document->getTitle())
			{
				$parameters['title'] = $document->getTitle();
			} else
			{
				$parameters['title'] = $document->getLabel();
			}
			$parameters['document'] = $document;
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		$request->setAttribute('media', $parameters);
		return website_BlockView::BACKOFFICE;
	}
}