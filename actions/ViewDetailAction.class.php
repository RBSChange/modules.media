<?php
/**
 * media_ViewDetailAction
 * @package modules.media.actions
 */
class media_ViewDetailAction extends change_Action
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
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