<?php
class media_BlockImageAction extends website_BlockAction
{
	/**
	 * @return Array
	 */	
	public function getCacheDependencies()
	{
		$deps = array($this->findParameterValue("cmpref"));
		$docUrlId = $this->getConfiguration()->getDocumentUrlId();
		if (f_util_StringUtils::isNotEmpty($docUrlId))
		{
			$deps[] = $docUrlId;
		}
		return $deps;
	}
	
	/**
	 * @param website_BlockActionRequest $request
	 * @return Array
	 */
	public function getCacheKeyParameters($request)
	{
		$keys = array('cmpref' => $this->findParameterValue("cmpref"));
		$cfg = $this->getConfiguration();
		$params = array("format", "customWidth", "customHeight", "zoom", "url", "documenturl");
		foreach ($params as $paramName)
		{
			$keys[$paramName] = $cfg->getConfigurationParameter($paramName);
		}
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
		$cmpref = $this->findParameterValue(K::COMPONENT_ID_ACCESSOR);
		if (count($cmpref) > 1)
		{
			//$request->setAttribute(K::COMPONENT_ID_ACCESSOR, explode(' ', $cmpref));
			$request->setAttribute(K::COMPONENT_ID_ACCESSOR, $cmpref);
			$request->setAttribute('imageConf', $this->getConfiguration());
			return $this->forward('media', 'ImageList');
		}
		$request->setAttribute("image", $this->getDocumentParameter());
        return website_BlockView::SUCCESS;
	}
}