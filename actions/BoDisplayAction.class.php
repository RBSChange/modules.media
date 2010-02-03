<?php
/**
 * media_BoDisplayAction
 * @param modules.media
 */
class media_BoDisplayAction extends media_DisplayAction
{
	/**
	 * @param media_persistentdocument_file $media
	 * @param Context $context
	 * @param Request $request
	 * @return Boolean true if the display is permitted (true by default)
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