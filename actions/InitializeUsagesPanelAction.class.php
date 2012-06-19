<?php
class media_InitializeUsagesPanelAction extends change_JSONAction
{
	
	/**
	 * @param change_Request $request
	 * @return media_persistentdocument_file
	 */
	private function getFileFromRequest($request)
	{
		return $this->getDocumentInstanceFromRequest($request);
	}
	
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$file = $this->getFileFromRequest($request);
		//$mediaId, $mediaLang, $documentId, $documentLang
		$pp = f_persistentdocument_PersistentProvider::getInstance();
		$docs = array();
		$ls = LocaleService::getInstance();
		$usages = $file->getAllUsages();
		$offset = $request->getParameter("offset", 0);
		$length = $request->getParameter("length", 10);
		$docEditorService = uixul_DocumentEditorService::getInstance();
		foreach (array_slice($usages, $offset, $length) as $usage)
		{
			$doc = $pp->getDocumentInstance($usage[2]);
			$lang = isset($usage[3]) ? $usage[3] : null;
			$modelName = $doc->getDocumentModelName();
			$matches = null;
			preg_match('/^modules_(.*)\/(.*)$/', $modelName, $matches);
			$moduleName = $matches[1];
			$docName = $matches[2];
			$docInfo = array('l' => $doc->getLabel(), 'i' => $doc->getId(),
				'm' => $modelName, 'e' => $docEditorService->getEditModuleName($doc),
				't' => $ls->trans('m.'.$moduleName.'.document.'.$docName.'.document-name').' ('.$ls->trans('m.'.$moduleName.'.bo.general.module-name').')');
			if ($lang != "")
			{
				$docInfo['l'] = $doc->getLabelForLang($lang);
				$docInfo['L'] = $lang;
			}
			else
			{
				$docInfo['l'] = $doc->getLabel();
			}
			
			$docs[] = $docInfo;
		}
		
		$data = array('fileId' => $file->getId(), 'total' => count($usages),
			'offset' => $offset, 'length' => $length, 'docs' => $docs);
		
		return $this->sendJSON($data);
	}
}