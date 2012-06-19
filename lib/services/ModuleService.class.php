<?php
/**
 * @package modules.media
 * @method media_ModuleService getInstance()
 */
class media_ModuleService extends ModuleBaseService
{
	/**
	 * @return void
	 */
	public function addRemoveTmpFilesTask()
	{
		$tasks = task_PlannedtaskService::getInstance()->getBySystemtaskclassname('media_RemoveTmpFilesTask');
		if (count($tasks) == 0)
		{
			$task = task_PlannedtaskService::getInstance()->getNewDocumentInstance();
			$task->setSystemtaskclassname('media_RemoveTmpFilesTask');
			$task->setLabel('media_RemoveTmpFilesTask');
			$task->setMaxduration(2);
			$task->setMinute(-1);
			$task->save(ModuleService::getInstance()->getSystemFolderId('task', 'media'));
		}
	}
}