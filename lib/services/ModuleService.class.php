<?php
/**
 * @package modules.media.lib.services
 */
class media_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var media_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return media_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
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