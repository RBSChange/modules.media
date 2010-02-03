<?php
class media_CropDialogJSONAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$media = $this->getDocumentInstanceFromRequest($request);
		$url = LinkHelper::getUIActionLink('media', 'BoDisplay')
		->setQueryParameter('cmpref', $media->getId())
		->setQueryParameter('lang', RequestContext::getInstance()->getLang())
		->setQueryParameter('time', date_Calendar::now()->getTimestamp())->getUrl();
		$filename = $media->getLabel(). ' ' . f_Locale::translateUI('&modules.media.bo.actions.Cropped;');
		
		$result = array_merge($media->getInfo(), array('src' => $url, 'filename' => $filename));
		$this->sendJSON($result);
	}
	
	/**
	 * @return Boolean true
	 */
	protected function isDocumentAction()
	{
		return true;
	}
}