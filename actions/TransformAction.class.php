<?php
class media_TransformAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		if ($request->hasParameter('way'))
		{
			$documents= $this->getDocumentInstanceArrayFromRequest($request);
			switch ($request->getParameter('way'))
			{
				case "toSecureMedia":
					$this->transformToSecureMedia($documents);
					break;
					
				case "toMedia":
					$this->transformToMedia($documents);
					break;
			}
			
			return self::getSuccessView();
		}

		return self::getErrorView();
	}
	
	/**
	 * @param Array<media_persistentdocument_media> $documents
	 */	
	public function transformToSecureMedia($documents)
	{
		$documentService  = media_MediaService::getInstance(); 	
		foreach ($documents as $document)
		{
			if ($document->getDocumentModelname() == "modules_media/media")
			{
				$documentService->transform($document, "modules_media/securemedia");
				$this->logAction($document);
			}
		}		
	}
	
	/**
	 * @param Array<media_persistentdocument_securemedia> $documents
	 */	
	public function transformToMedia($documents)
	{
		$documentService  = media_MediaService::getInstance(); 	
		foreach ($documents as $document)
		{
			if ($document->getDocumentModelname() == "modules_media/securemedia")
			{
				$documentService->transform($document, "modules_media/media");
				$this->logAction($document);
			}
		}		
	}	
	
	/**
	 * @return Boolean true
	 */
	protected function isDocumentAction()
	{
		return true;
	}
}