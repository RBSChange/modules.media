<?php
$mediaIdArray = $_POST['argv'];
Controller::newInstance("controller_ChangeController");
$tm = f_persistentdocument_TransactionManager::getInstance();
$rc = RequestContext::getInstance();
try
{
	$tm->beginTransaction();
	foreach ($mediaIdArray as $mediaId)
	{
		try 
		{
			$media = DocumentHelper::getDocumentInstance($mediaId, 'modules_media/media');
			foreach ($media->getI18nInfo()->getLangs() as $lang) 
			{
				try 
				{
					$rc->beginI18nWork($lang);
					$media->getDocumentService()->publishIfPossible($media->getId());
					echo '+';
					$rc->endI18nWork();
				}
				catch (Exception $noloc)
				{
					$rc->endI18nWork();
					echo $mediaId . '!';
				}
			}	
		}
		catch (Exception $nodoc)
		{
			echo $mediaId . '-';
		}
	}
	$tm->commit();
}
catch (Exception $e)
{
	$tm->rollBack($e);
}