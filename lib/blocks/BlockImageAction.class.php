<?php
class media_BlockImageAction extends website_BlockAction
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
		$keys = array('cmpref' => $this->findParameterValue(K::COMPONENT_ID_ACCESSOR));
		$cfg = $this->getConfiguration();
		$params = array("format", "customWidth", "customHeight", "zoom", "url");
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