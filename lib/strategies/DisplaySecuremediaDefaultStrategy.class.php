<?php
/**
 * This strategy should always be the last strategy applied
 * @package modules.media
 */
class media_DisplaySecuremediaDefaultStrategy extends media_DisplaySecuremediaStrategy
{
	/**
	 * @param media_persistentdocument_securemedia $media
	 * @return integer
	 */
	public function canDisplayMedia($media)
	{
		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__ . ' : ' . $media->__toString());
		}
		
		$ps = change_PermissionService::getInstance();
		
		$user = users_UserService::getInstance()->getCurrentFrontEndUser();
		if ($user === null)
		{
			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__ . ' : User not authenticated');
			}
			return self::KO;
		}
		$permissionName = 'modules_media.SecureMediaDisplay';
		if (!$ps->hasPermission($user, $permissionName, $media->getId()))
		{
			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__ . 'permisson {' . $permissionName . '} is not defined for user {' . $user->__toString() . '}');
			}
			return self::KO;
		}
		
		return self::NOT_CONCERNED;
	}

}