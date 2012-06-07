<?php
/**
 * @package modules.media
 */
class media_RemoveTmpFilesTask  extends task_SimpleSystemTask
{
	
	/**
	 * @see task_SimpleSystemTask::execute()
	 */
	protected function execute()
	{
		$chunkSize = 100;		
		$startId = 0;
		$batchPath = 'modules/media/lib/bin/batchCleanTmpFile.php';
		do 
		{
			if ($startId > 0)
			{
				$this->plannedTask->ping();
			}
			
			$result = f_util_System::execScript($batchPath, array($chunkSize, $startId));			
			if (is_numeric($result))
			{
				$startId = intval($result);
			}
			else
			{
				throw new Exception($result);
			}
		}
		while ($startId > 0);
	}
}