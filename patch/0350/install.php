<?php
/**
 * media_patch_0350
 * @package modules.media
 */
class media_patch_0350 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$this->executeSQLQuery('ALTER TABLE `m_media_doc_file` CHANGE `info` `info` MEDIUMTEXT;');
		$this->executeSQLQuery('ALTER TABLE `m_media_doc_file` CHANGE `usageinfo` `usageinfo` MEDIUMTEXT;');
		$this->executeSQLQuery('ALTER TABLE `m_media_doc_file_i18n` CHANGE `info_i18n` `info_i18n` MEDIUMTEXT;');
	}
}