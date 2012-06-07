<?php
class media_Setup extends object_InitDataSetup
{
	public function install()
	{
		try
		{
			$scriptReader = import_ScriptReader::getInstance();
			$scriptReader->executeModuleScript('media', 'init.xml');
		}
		catch (Exception $e)
		{
			echo "ERROR: " . $e->getMessage() . "\n";
			Framework::exception($e);
		}

		media_ModuleService::getInstance()->addRemoveTmpFilesTask();
	}
}