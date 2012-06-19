<?php
class media_DropFileAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$parentNode = $this->getDocumentInstanceFromRequest($request);
		
		$tmpFileName = $this->getFilePathFromRequest($request);
		$fileName = $request->getFileName('filename');
		$fileExtension = f_util_FileUtils::getFileExtension($fileName, true);
		$cleanFileName = basename($fileName, $fileExtension);
		
		$media = media_MediaService::getInstance()->getNewDocumentInstance();
		$media->setLabel(f_util_StringUtils::utf8Encode($cleanFileName));

		$media->setNewFileName($tmpFileName, f_util_StringUtils::utf8Encode($fileName));
		$media->save($parentNode->getId());
		if ($request->hasParameter('beforeid') ||  $request->hasParameter('afterid'))
		{
			media_MediaService::getInstance()->moveTo($media, $parentNode->getId(), $request->getParameter('beforeid'), $request->getParameter('afterid'));
			
		}
		$this->logAction($media, array('destinationlabel' => $parentNode->getLabel()));
		return $this->sendJSON(array('dropfileid' => $media->getId(), 'label' => $media->getLabel()));
	}
	
	private function getFilePathFromRequest($request)
	{
		if (! $request->hasFile('filename'))
		{
			throw new IOException('no-file');
		}
		
		if ($request->hasFileError('filename'))
		{
			switch ($request->getFileError('filename'))
			{
				case UPLOAD_ERR_INI_SIZE :
					throw new ValidationException('ini-size');
					break;
				
				case UPLOAD_ERR_FORM_SIZE :
					throw new ValidationException('form-size');
					break;
				
				case UPLOAD_ERR_PARTIAL :
					throw new IOException('partial-file');
					break;
				
				case UPLOAD_ERR_NO_FILE :
					throw new IOException('no-file');
					break;
				
				case UPLOAD_ERR_NO_TMP_DIR :
				case UPLOAD_ERR_CANT_WRITE :
					throw new IOException('cannot-write');
					break;
				
				default :
					throw new IOException('unknown');
					break;
			}
		}
				
		$filePath = $request->getFilePath('filename');
		if (! is_uploaded_file($filePath))
		{
			throw new IOException('no-file');
		}
		
		$tmpFileName = f_util_FileUtils::getTmpFile('upload');
		try
		{
			if (! $request->moveFile('filename', $tmpFileName))
			{
				throw new IOException('cannot-move');
			}
		}
		catch (FileException $e)
		{
			Framework::exception($e);
			throw new IOException('cannot-move');
		}
		
		return $tmpFileName;
	}
	
	/**
	 * @return boolean
	 */
	protected function isDocumentAction()
	{
		return true;
	}
}