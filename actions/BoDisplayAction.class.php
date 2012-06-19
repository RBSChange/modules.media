<?php
/**
 * media_BoDisplayAction
 * @param modules.media
 */
class media_BoDisplayAction extends media_DisplayAction
{
	/**
	 * @param media_persistentdocument_file $media
	 * @param change_Context $context
	 * @param change_Request $request
	 * @return boolean true if the display is permitted (true by default)
	 */
	protected function hasAccess($media, $context, $request)
	{
		return $media->getDocumentService()->hasBoAccess($media);
	}

	public function isSecure()
	{
		return true;
	}
}