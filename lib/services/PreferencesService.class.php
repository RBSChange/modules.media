<?php
/**
 * @package modules.media
 * @method media_PreferencesService getInstance()
 */
class media_PreferencesService extends f_persistentdocument_DocumentService
{
	/**
	 * @return media_persistentdocument_preferences
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_media/preferences');
	}

	/**
	 * Create a query based on 'modules_media/preferences' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_media/preferences');
	}
	
	/**
	 * @var media_persistentdocument_preferences
	 */
	private $preference;
	
	/**
	 * @return media_persistentdocument_preferences
	 */
	private function getPreferenceDocument()
	{
		if ($this->preference === null)
		{
			$preference = $this->createQuery()->findUnique();
			if ($preference === null)
			{
				$preference = $this->getNewDocumentInstance();
				$this->save($preference);
			}
			$this->preference = $preference;
		}
		return $this->preference;
	}
	
	/**
	 * @return boolean
	 */
	public function useFileNameAsAlt()
	{
		return $this->getPreferenceDocument()->getUsefilenameasalt();
	}
	
	/**
	 * @param media_persistentdocument_preferences $document
	 * @param integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$document->setLabel('media');
	}
}