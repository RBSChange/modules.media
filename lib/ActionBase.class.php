<?php
class media_ActionBase extends f_action_BaseAction
{
	
	/**
	 * Returns the media_TmpfileService to handle documents of type "modules_media/tmpfile".
	 *
	 * @return media_TmpfileService
	 */
	public function getTmpfileService()
	{
		return media_TmpfileService::getInstance();
	}
	
	/**
	 * Returns the media_PreferencesService to handle documents of type "modules_media/preferences".
	 *
	 * @return media_PreferencesService
	 */
	public function getPreferencesService()
	{
		return media_PreferencesService::getInstance();
	}
	
	/**
	 * Returns the media_SecuremediaService to handle documents of type "modules_media/securemedia".
	 *
	 * @return media_SecuremediaService
	 */
	public function getSecuremediaService()
	{
		return media_SecuremediaService::getInstance();
	}
	
	/**
	 * Returns the media_FileService to handle documents of type "modules_media/file".
	 *
	 * @return media_FileService
	 */
	public function getFileService()
	{
		return media_FileService::getInstance();
	}
	
	/**
	 * Returns the media_MediaService to handle documents of type "modules_media/media".
	 *
	 * @return media_MediaService
	 */
	public function getMediaService()
	{
		return media_MediaService::getInstance();
	}
	
	/**
	 * Returns the media_FileusageService to handle documents of type "modules_media/fileusage".
	 *
	 * @return media_FileusageService
	 */
	public function getFileusageService()
	{
		return media_FileusageService::getInstance();
	}
	
}