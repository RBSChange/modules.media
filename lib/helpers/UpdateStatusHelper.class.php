<?php
class media_UpdateStatusHelper
{
	/**
	 * @param array $Ids
	 * @param string $batchPath
	 */
	public static function batchSend($Ids, $batchPath)
	{
		if (f_util_ArrayUtils::isEmpty($Ids) == 1)
		{
			$datas = media_FileService::getInstance()->createQuery()->setProjection(Projections::property('id', 'id'))->find();
			$ids = array();
			foreach ($datas as $info)
			{
				$ids[] = $info['id'];
			}
			foreach (array_chunk($ids, 500) as $chunkIds)
			{
				$processHandle = popen('php ' . $batchPath . ' ' . implode(' ', $chunkIds), 'r');
				while ($string = fread($processHandle, 1024))
				{
					echo $string;
				}
				pclose($processHandle);
			}
		}
		else
		{
			foreach ($Ids as $id)
			{
				try
				{
					$document = DocumentHelper::getDocumentInstance($id);
					self::updateStatus($document);
				}
				catch (Exception $e)
				{
					echo $e->getMessage();
				}
			}
		}
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 */
	public static function updateStatus($document)
	{
		if ($document->isLocalized())
		{
			$rc = RequestContext::getInstance();
			foreach ($document->getI18nInfo()->getLangs() as $lang)
			{
				try
				{
					$rc->beginI18nWork($lang);
					$document->setPublicationstatus('PUBLICATED');
					$document->getDocumentService()->publishDocument($document, array());
					$rc->endI18nWork();
				}
				catch (Exception $e)
				{
					$rc->endI18nWork($e);
				}
			}
		}
		else
		{
			$document->setPublicationstatus('PUBLICATED');
			$document->getDocumentService()->publishDocument($document, array());
		}	
	}
}