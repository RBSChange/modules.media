<?php
/**
 * media_persistentdocument_preferences
 * @package media
 */
class media_persistentdocument_preferences extends media_persistentdocument_preferencesbase 
{
	/**
	 * @see f_persistentdocument_PersistentDocumentImpl::getLabel()
	 *
	 * @return String
	 */
	public function getLabel()
	{
		return f_Locale::translateUI(parent::getLabel());
	}	
}