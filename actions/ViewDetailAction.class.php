<?php
/**
 * media_ViewDetailAction
 * @package modules.media.actions
 */
class media_ViewDetailAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		return $context->getController()->forward('media', 'Display');
	}
	/**
	 * @see f_action_BaseAction::isSecure()
	 */
	public function isSecure()
	{
		return false;
	}
}