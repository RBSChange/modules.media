<?php
/**
 * media_GetIconAction
 * @package modules.media.actions
 */
class media_GetIconAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$icon = $request->getParameter('icon');
		if ( Framework::hasConfiguration('modules/media/icons/library') && f_util_StringUtils::isNotEmpty($icon))
		{
			$iconPath = f_util_FileUtils::buildAbsolutePath(Framework::getConfiguration('modules/media/icons/library'),  $icon);
			if (is_readable($iconPath))
			{
				/*
				$to = f_util_FileUtils::buildWebeditPath('changeicons' , $icon);
				f_util_FileUtils::mkdir(dirname($to));
				f_util_FileUtils::cp($iconPath, $to);
				*/
				MediaHelper::outputHeader($iconPath);
				readfile($iconPath);
				return; 
			}
		}
		if (Framework::isInfoEnabled())
		{
			Framework::info(__METHOD__ . ' ICON not found : ' . $_SERVER['REQUEST_URI']);
		}
		f_web_http_Header::setStatus(404);
	 	return; 
	}
	
	/**
	 * @see f_action_BaseAction::isSecure()
	 *
	 * @return boolean
	 */
	public function isSecure() 
	{
		return false;
	}
}