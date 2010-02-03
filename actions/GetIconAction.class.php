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
		$expectedPath = f_util_FileUtils::buildWebappPath('www', 'icons', $icon);
		if (file_exists($expectedPath))
		{
			$iconPath = $expectedPath; 
		}
		else 
		{
			$iconPath = f_util_FileUtils::buildAbsolutePath('/usr/share/pear/rbs/webedit4/libs/icons-1.0',  $icon);
		}
		MediaHelper::outputHeader($iconPath);
		readfile($iconPath);
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