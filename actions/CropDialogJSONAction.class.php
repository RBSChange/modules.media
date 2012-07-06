<?php
class media_CropDialogJSONAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$media = $this->getDocumentInstanceFromRequest($request);
		$url = LinkHelper::getUIActionLink('media', 'BoDisplay')
		->setQueryParameter('cmpref', $media->getId())
		->setQueryParameter('lang', RequestContext::getInstance()->getLang())
		->setQueryParameter('time', date_Calendar::now()->getTimestamp())->getUrl();
		$filename = $media->getLabel(). ' ' . LocaleService::getInstance()->trans('m.media.bo.actions.cropped', array('ucf'));
		
		$result = array_merge($media->getInfo(), array('src' => $url, 'filename' => $filename));
		$this->sendJSON($result);
	}
	
	/**
	 * @return boolean true
	 */
	protected function isDocumentAction()
	{
		return true;
	}
}