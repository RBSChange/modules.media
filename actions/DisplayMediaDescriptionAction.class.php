<?php
/**
 * @package modules.media
 */
class media_DisplayMediaDescriptionAction extends media_DisplayAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	protected function displayMedia ($media, $context, $request)
	{
		$this->setContentType("text/html");
		$template = TemplateLoader::getInstance()->setPackageName("modules_media")->setDirectory("templates")->load("Media-MediaDescription");
		$template->setAttribute("media", $media);
		$website = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
		$template->setAttribute("website", $website);
		echo $template->execute();
		return View::NONE;
	}
}