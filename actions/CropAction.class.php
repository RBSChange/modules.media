<?php
class media_CropAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$media = $this->getDocumentInstanceFromRequest($request);
		if ($media === null)
		{
			return $this->sendJSONError('Media not found', false);
		}
		$filePath = $media->getDocumentService()->getOriginalPath($media);
		$extension = f_util_FileUtils::getFileExtension($filePath);
		$sourceImage = null;
		switch (strtolower($extension))
		{
			case 'gif':
				$sourceImage = imagecreatefromgif($filePath);
				break;
			case 'png':
				$sourceImage = imagecreatefrompng($filePath);
				break;
			case 'jpg':
			case 'jpeg':
				$sourceImage = imagecreatefromjpeg($filePath);
				break;
			default:
				return $this->sendJSONError('Invalid media extension :' . $extension, false);
		}
		
		$targetHeight = $request->getParameter("h");
		$targetWidth = $request->getParameter("w");
		$targetX = $request->getParameter("x");
		$targetY = $request->getParameter("y");
		$destImage = ImageCreateTrueColor( $targetWidth, $targetHeight );
		imagecopy ($destImage,$sourceImage, 0, 0, $targetX, $targetY, $targetWidth,$targetHeight);
		
		$override = ($request->getParameter('override') == "true");
		
		$tmpFilePath = f_util_FileUtils::getTmpFile();
		
		switch (strtolower($extension))
		{
			case 'gif':
				$tmpFilePath .= '.gif';
				imagegif($destImage, $tmpFilePath);
				break;
			case 'png':
				$tmpFilePath .= '.png';
				imagepng($destImage, $tmpFilePath);
				break;
			case 'jpg':
			case 'jpeg':
				$tmpFilePath .= '.jpg';
				imagejpeg($destImage, $tmpFilePath);
				break;
		}

		if ($override)
		{
			$media->setNewFileName($tmpFilePath);
			$media->setModificationdate(null);
			$media->save();
			$this->logAction($media);
			return $this->sendJSON(array('id' => $media->getId()));
		}
		else 
		{
			$filename = trim($request->getParameter('filename'));
			if (f_util_StringUtils::isEmpty($filename))
			{
				$filename = $media->getLabel(). ' ' . LocaleService::getInstance()->trans('m.media.bo.actions.cropped', array('ucf'));
			}
			$ms = media_MediaService::getInstance();
			$newMedia = $ms->getNewDocumentInstance();
			$newMedia->setNewFileName($tmpFilePath, $media->getFilename());
			$newMedia->setLabel($filename);
			$newMedia->setTitle($media->getTitle());
			$ms->save($newMedia, $ms->getParentOf($media)->getId());
			$this->logAction($newMedia);
			return $this->sendJSON(array('id' => $newMedia->getId()));
		}
	}

	/**
	 * @return boolean true
	 */
	protected function isDocumentAction()
	{
		return true;
	}
}