<?php
/**
 * media_patch_0300
 * @package modules.media
 */
class media_patch_0300 extends patch_BasePatch
{
//  by default, isCodePatch() returns false.
//  decomment the following if your patch modify code instead of the database structure or content.
    /**
     * Returns true if the patch modify code that is versionned.
     * If your patch modify code that is versionned AND database structure or content,
     * you must split it into two different patches.
     * @return Boolean true if the patch modify code that is versionned.
     */
//	public function isCodePatch()
//	{
//		return true;
//	}
 
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$this->log('Add node property to file document');
		$newPath = f_util_FileUtils::buildWebeditPath('modules/media/persistentdocument/file.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'media', 'file');
		$newProp = $newModel->getPropertyByName('node');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('media', 'file', $newProp);	
		
		
		$this->log('update media publication status');
		$ids = media_MediaService::getInstance()->createQuery()
			->setProjection(Projections::property('id'))->findColumn('id');
			
		foreach (array_chunk($ids, 100) as $chunk) 
		{
			$this->log(f_util_System::execHTTPScript('modules/media/patch/0300/updatePublicationStatus.php', $chunk));
		}
	
	}

	/**
	 * @return String
	 */
	protected final function getModuleName()
	{
		return 'media';
	}

	/**
	 * @return String
	 */
	protected final function getNumber()
	{
		return '0300';
	}
}