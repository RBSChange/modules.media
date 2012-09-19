<?php
class media_Setup extends object_InitDataSetup
{
	public function install()
	{
		$this->executeModuleScript('init.xml');

		media_ModuleService::getInstance()->addRemoveTmpFilesTask();
	}
}