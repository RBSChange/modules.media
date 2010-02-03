<?php
/**
 * @package modules.media
 */
class media_PreferencesService extends f_persistentdocument_DocumentService
{
	/**
	 * @var media_PreferencesService
	 */
	private static $instance;

	/**
	 * @return media_PreferencesService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

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
		return $this->pp->createQuery('modules_media/preferences');
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
	 * @return Boolean
	 */
	public function useFileNameAsAlt()
	{
	    return $this->getPreferenceDocument()->getUsefilenameasalt();
	}
	
	/**
	 * @param media_persistentdocument_preferences $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$document->setLabel('&modules.media.bo.general.Module-name;');
	}
}