<?php
/**
 * media_patch_0360
 * @package modules.media
 */
class media_patch_0360 extends patch_BasePatch
{

	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$this->execChangeCommand('update-autoload', array('modules/media'));
		$this->execChangeCommand('compile-listeners');	
		media_ModuleService::getInstance()->addRemoveTmpFilesTask();
	}
}