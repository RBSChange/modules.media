<?php
class media_BlockMediaAction extends website_BlockAction
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
	    $keys = array($this->findParameterValue(change_Request::DOCUMENT_ID));
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
		$media = $this->getDocumentParameter();
		if ($media === null)
		{
			return website_BlockView::NONE;
		}
        $request->setAttribute("media", $media);
        return website_BlockView::SUCCESS;
    }
}