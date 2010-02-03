<?php
class media_BlockImageListAction extends website_BlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 *
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function execute($request, $response)
	{
		$cmprefs = $this->findParameterValue(K::COMPONENT_ID_ACCESSOR);
		$images = array();
		if (is_array($cmprefs) && f_util_ArrayUtils::isNotEmpty($cmprefs))
		{
			foreach ($cmprefs as $cmpref)
			{
				$image = DocumentHelper::getDocumentInstance($cmpref);
				if ($image instanceof media_persistentdocument_media)
				{
					$images[] = $image;
				}
			}
		}

		$request->setAttribute("images", $images);
		return website_BlockView::SUCCESS;
	}
}