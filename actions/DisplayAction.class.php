<?php
class media_DisplayAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute ($context, $request)
	{
		$mediaId = $request->getParameter(K::COMPONENT_ID_ACCESSOR);
		if ($mediaId === null)
		{
			$mediaId = $request->getModuleParameter('media', K::COMPONENT_ID_ACCESSOR);
		}
		
		try 
		{
			$media = DocumentHelper::getDocumentInstance($mediaId);
		}
		catch (Exception $e)
		{
			//Invalid Media
        	Framework::exception($e);
        	$HTTP_Header= new HTTP_Header();
			$HTTP_Header->sendStatusCode(404);
			return View::NONE;
		}
		
		if (!$this->hasAccess($media, $context, $request))
		{
			$controller = $context->getController();
			$user = $context->getUser();
			$user->setAttribute('illegalAccessPage', $_SERVER["REQUEST_URI"]);
			$controller->forward('website', 'Error401');
			return View::NONE;
		}

		return $this->displayMedia($media, $context, $request);
	}
	
	/**
	 * @param media_persistentdocument_media $media
	 * @param Context $context
	 * @param Request$request
	 * @return String
	 */
	protected function displayMedia($media, $context, $request)
	{
		$filename = $this->getFilename($media->getId());
		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__ . ' filename :' . $filename);
		}

		if ($filename === null)
		{
			$controller = $context->getController();
			$controller->forward('website', 'Error404');
			return View::NONE;
		}

		if (!$request->hasParameter(MediaHelper::FORCEDOWNLOAD_ATTRIBUTE))
		{
			$apacheHeaders = apache_request_headers();
			if (isset($apacheHeaders['If-Modified-Since']))
			{
				$ifModifiedSince = preg_replace('/;.*$/', '', $apacheHeaders['If-Modified-Since']);
				if ($ifModifiedSince && ($ifModifiedSince == gmdate("D, d M Y H:i:s", filemtime($filename)) . " GMT"))
				{
					header('HTTP/1.1 304 Not Modified');
					$this->dispatchDownloadEvent($media);
					return View::NONE;
				}
			}
		}

		if ($request->hasParameter(MediaHelper::FORMAT_ATTRIBUTE) && f_util_StringUtils::beginsWith($request->getParameter(MediaHelper::FORMAT_ATTRIBUTE), 'modules.'))
		{
			list ($stylesheet, $formatName) = explode('/', $request->getParameter(MediaHelper::FORMAT_ATTRIBUTE));
			$format = MediaHelper::getFormatProperties($stylesheet, $formatName);
		}
		else
		{
			$parameters = $request->getParameters();
			$format = array();
			if (isset($parameters['max-height']) && intval($parameters['max-height']) > 0)
			{
				$format['max-height'] = $parameters['max-height'];
			}
			if (isset($parameters['max-width']) && intval($parameters['max-width']) > 0)
			{
				$format['max-width'] = $parameters['max-width'];
			}
			if (isset($parameters['min-height']) && intval($parameters['min-height']) > 0)
			{
				$format['min-height'] = $parameters['min-height'];
			}
			if (isset($parameters['min-width']) && intval($parameters['min-width']) > 0)
			{
				$format['min-width'] = $parameters['min-width'];
			}
			if (isset($parameters['height']) && intval($parameters['height']) > 0)
			{
				$format['height'] = $parameters['height'];
			}
			if (isset($parameters['width']) && intval($parameters['width']) > 0)
			{
				$format['width'] = $parameters['width'];
			}
			if (Framework::isDebugEnabled())
			{
				Framework::debug(var_export($format, true));
			}
		}

		if (f_util_ArrayUtils::isEmpty($format))
		{
			$format = null;
		}

		media_FormatterHelper::outputFile($filename, $media, $format, $request->hasParameter(MediaHelper::TRANSIENT_ATTRIBUTE), $request->hasParameter(MediaHelper::FORCEDOWNLOAD_ATTRIBUTE));
		$this->dispatchDownloadEvent($media);
		return View::NONE;
	}

	protected function getFilename ($mediaId)
	{
		if (is_numeric($mediaId))
		{
			try
			{
				return MediaHelper::getOriginalPathById($mediaId, true);
			}
			catch (Exception $e)
			{
				Framework::exception($e);
			}
		}
		return null;
	}

	/**
	 * @param media_persistentdocument_file $media
	 */
	private function dispatchDownloadEvent($media)
	{
		f_event_EventManager::dispatchEvent(// event name
        'mediaDownloaded', // sender
		$this, // event parameters
		array('document' => $media , 'mediaId' => $media->getId()));
	}

	/**
	 * @param media_persistentdocument_file $media
	 * @param Context $context
	 * @param Request $request
	 * @return Boolean true if the display is permitted (true by default)
	 */
	protected function hasAccess($media, $context, $request)
	{
		return $media->getDocumentService()->hasAccess($media);
	}

	public function isSecure()
	{
		return false;
	}

	public function getRequestMethods()
	{
		return Request::GET | Request::POST;
	}
}