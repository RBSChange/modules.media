<?php
class media_BlockImageAction extends website_BlockAction
{
	/**
	 * @return array
	 */	
	public function getCacheDependencies()
	{
		$deps = array($this->getDocumentIdParameter());
		$docUrlId = $this->getConfiguration()->getDocumentUrlId();
		if (f_util_StringUtils::isNotEmpty($docUrlId))
		{
			$deps[] = $docUrlId;
		}
		return $deps;
	}

	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return string
	 */
	public function execute($request, $response)
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
		
		$cmprefs = $this->getDocumentIds();
		if (count($cmprefs) == 0)
		{
			return website_BlockView::NONE;
		}

		if (count($cmprefs) > 1)
		{
			$request->setAttribute('cmpref', $cmprefs);
			$request->setAttribute('imageConf', $this->getConfiguration());
			return $this->forward('media', 'ImageList');
		}
		
		try 
		{
			$image = DocumentHelper::getDocumentInstance($cmprefs[0], 'modules_media/media');
			if ($image->getMediatype() !== MediaHelper::TYPE_IMAGE)
			{
				return website_BlockView::NONE;
			}
			$request->setAttribute("image", $image);
		}
		catch (Exception $e)
		{
			Framework::warn(__METHOD__ . ' Invalid media ' . $e->getMessage()); 
			return website_BlockView::NONE;
		}
        return website_BlockView::SUCCESS;
	}
	
	/**
	 * @return integer[]
	 */
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