<?php
class media_UpdateUsageListener
{
	public function onPersistentDocumentUpdated($sender, $params)
	{
		if ($params['document'] instanceof f_persistentdocument_PersistentDocument)
		{
			$document = $params['document'];
			$mfus = media_FileusageService::getInstance();
			if ($mfus->hasFileTypeProperties($document))
			{
				$usagesArray = $mfus->buildDocumentUsage($document);
				media_FileusageService::getInstance()->updateUsagesByDocument($document, $usagesArray);
			}			
		}
	}
	
	public function onPersistentDocumentCreated($sender, $params)
	{
		if ($params['document'] instanceof f_persistentdocument_PersistentDocument)
		{
			$document = $params['document'];
			
			$mfus = media_FileusageService::getInstance();
			if ($mfus->hasFileTypeProperties($document))
			{
				$usagesArray = $mfus->buildDocumentUsage($document);
				media_FileusageService::getInstance()->updateUsagesByDocument($document, $usagesArray);
			}			
		}

	}	
	
	public function onPersistentDocumentActivated($sender, $params)
	{
		if ($params['document'] instanceof f_persistentdocument_PersistentDocument)
		{
			$document = $params['document'];
			
			$mfus = media_FileusageService::getInstance();
			if ($mfus->hasFileTypeProperties($document))
			{				
				$usagesArray = $mfus->buildDocumentUsage($document);
				media_FileusageService::getInstance()->updateUsagesByDocument($document, $usagesArray);
			}			
		}
	}
	
	public function onPersistentDocumentDeleted($sender, $params)
	{
		if ($params['document'] instanceof f_persistentdocument_PersistentDocument)
		{
			$document = $params['document'];
			$mfus = media_FileusageService::getInstance();
			if ($mfus->hasFileTypeProperties($document))
			{
				if ($document->isDeleted())
				{
					$usagesArray = array();
				}
				else
				{
					$usagesArray = $mfus->buildDocumentUsage($document);
				}
				media_FileusageService::getInstance()->updateUsagesByDocument($document, $usagesArray);
			}				
		}		
	}
	
	public function onPersistentDocumentDeprecated($sender, $params)
	{
		if ($params['document'] instanceof f_persistentdocument_PersistentDocument)
		{
			$document = $params['document'];
			$mfus = media_FileusageService::getInstance();
			if ($mfus->hasFileTypeProperties($document))
			{
				$usagesArray = array();
				media_FileusageService::getInstance()->updateUsagesByDocument($document, $usagesArray);
			}				
		}			
	}
}