<?php
class media_InsertJSONAction extends generic_InsertJSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$modelName = $request->getParameter('modelname');
		if ($modelName !== 'modules_media/media' && $modelName !== 'modules_media/securemedia')
		{
			return parent::_execute($context, $request);
		}
		
		$tmpFileId = intval($request->getParameter('tmpfile'));
		$tmpDoc = DocumentHelper::getDocumentInstance($tmpFileId);
		$document = $tmpDoc->getDocumentService()->transform($tmpDoc, $modelName);
		$documentService = $document->getDocumentService();
		
		$propertiesNames = explode(',', $request->getParameter('documentproperties', ''));
		$propertiesValue = array();
		foreach ($propertiesNames as $propertyName)
		{
			if ($propertyName === 'tmpfile')
			{
				continue;
			}
			
			if ($request->hasParameter($propertyName))
			{
				$propertiesValue[$propertyName] = $request->getParameter($propertyName);
			}
		}
		uixul_DocumentEditorService::getInstance()->importFieldsData($document, $propertiesValue);
		$parentNodeId = intval($request->getParameter('parentref'));
		if ($parentNodeId <= 0)
		{
			$parentNodeId = null;
		}
		$document->setPublicationstatus('ACTIVE');
		$documentService->save($document);
		if ($parentNodeId)
		{
			TreeService::getInstance()->newLastChild($parentNodeId, $document->getId());
		}
		$this->logAction($document);
		return $this->sendJSON(array('id' => $document->getId(), 'lang' => $document->getLang(), 'label' => $document->getLabel()));
	}
}