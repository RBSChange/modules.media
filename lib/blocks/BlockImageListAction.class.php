<?php
class media_BlockImageListAction extends website_BlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 *
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return string
	 */
	public function execute($request, $response)
	{
		$cmprefs = $this->getDocumentIds();
		$images = array();
		foreach ($cmprefs as $cmpref)
		{
			try
			{
				$image = DocumentHelper::getDocumentInstance($cmpref, 'modules_media/media');
				if ($image->isPublished() && $image->getMediatype() === MediaHelper::TYPE_IMAGE)
				{
					$images[] = $image;
				}
			}
			catch (Exception $e)
			{
				Framework::warn(__METHOD__ . ' Invalid media ' . $e->getMessage()); 
			}
		}
		
		if (count($images) == 0)
		{
			return website_BlockView::NONE;
		}
		
		if (!$request->hasAttribute('imageConf'))
		{
			$configuration = $this->getConfiguration();
			$documentURL = $configuration->getDocumentUrlSafe();
			if ($documentURL != null)
			{
				$url = LinkHelper::getDocumentUrl($documentURL);
				$configuration->setConfigurationParameter("url", $url);
			}
			if (f_util_StringUtils::isNotEmpty($configuration->getUrl()))
			{
				$configuration->setConfigurationParameter("zoom", "false");
			}
			$request->setAttribute('imageConf', $configuration);
		}
		$request->setAttribute("images", $images);
		return website_BlockView::SUCCESS;
	}
	
	
	private function getDocumentIds()
	{
		$data = $this->findParameterValue('cmpref');
		if (is_array($data))
		{
			return $data;
		}
		
		if (is_integer($data))
		{
			return array($data);
		}
		
		if (is_string($data))
		{
			if (strpos($data, ',') !== false)
			{
				$cmprefs = explode(',', $data);
			}
			else
			{
				$cmprefs = explode(' ', $data);
			}
			$result = array();
			foreach ($cmprefs as $cmpref) 
			{
				if (intval($cmpref) > 0)
				{
					$result[] = intval($cmpref);
				}
			}
			return $result;
		}
		return array();
	}
}