<?php
/**
 * @package modules.media
 */
class media_RemoveTmpFilesListener
{
	/**
	 * @param f_persistentdocument_DocumentService $sender
	 * @param array $params
	 */
	public function onDayChange($sender, $params)
	{
		$date = $params['date'];
		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__ . "($date)");
		}

		$start = date_Calendar::getInstance($date)->sub(date_Calendar::HOUR, 1)->toString();
		media_TmpfileService::getInstance()->cleanOldFiles($start);
	}
}