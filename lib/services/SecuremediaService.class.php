<?php
/**
 * @package modules.media
 * @method media_SecuremediaService getInstance()
 */
class media_SecuremediaService extends media_MediaService
{
	/**
	 * @return media_persistentdocument_securemedia
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_media/securemedia');
	}

	/**
	 * @param integer $id
	 * @param string $lang
	 * @return string
	 */
	protected function getAbsoluteFolder($id, $lang = null)
	{
		return PROJECT_HOME.'/securemedia/original/'.$this->getRelativeFolder($id, $lang);
	}

	/**
	 * @param integer $id
	 * @param string $lang
	 * @return string
	 */
	protected function getFormattedAbsoluteFolder($id, $lang = null)
	{
		return PROJECT_HOME.'/securemedia/formatted/'.$this->getRelativeFolder($id, $lang);
	}

	/**
	 * @param website_UrlRewritingService $urlRewritingService
	 * @param media_persistentdocument_securemedia $document
	 * @param website_persistentdocument_website $website
	 * @param string $lang
	 * @param array $parameters
	 * @return f_web_Link | null
	 */
	public function getWebLink($urlRewritingService, $document, $website, $lang, $parameters)
	{
		$fileName = $document->getFilenameForLang($lang);
		if (empty($fileName)) 
		{
			$lang = $document->getLang();
		}
		$parameters['lang'] = $lang;
		$parameters['cmpref'] = $document->getId();		
		return $urlRewritingService->getActionLinkForWebsite('media', 'Display', $website, $lang, $parameters);
	}
	
	/**
	 * Compute access using the registered strategies.
	 * @param media_persistentdocument_securemedia $media
	 * @return boolean
	 */
	public function hasAccess($media)
	{
		foreach ($this->getStrategyArray() as $strategy)
		{
			switch ($strategy->canDisplayMedia($media))
			{
				case media_DisplaySecuremediaStrategy::OK:
					return true;
				case media_DisplaySecuremediaStrategy::KO:
					return false;
				case media_DisplaySecuremediaStrategy::NOT_CONCERNED:
					// continue
					break;
			}
		}
		return true;
	}

	/**
	 * @param media_persistentdocument_securemedia $media
	 * @return boolean
	 */
	public function hasBoAccess($media)
	{
		$ps = change_PermissionService::getInstance();

		$user = users_UserService::getInstance()->getCurrentBackEndUser();
		if ($user === null)
		{
			return false;
		}
		$permissionName = 'modules_media.BoDisplay';
		if (!$ps->hasPermission($user, $permissionName, $media->getId()))
		{
			return false;
		}
		return true;
	}
	 
	/**
	 * @var media_DisplaySecuremediaStrategy[]
	 */
	private $strategyArray;
	 
	/**
	 * @return media_DisplaySecuremediaStrategy[]
	 */
	private function getStrategyArray()
	{
		if ($this->strategyArray === null)
		{
			$this->strategyArray = array();
			if (Framework::hasConfiguration('modules/media/secureMediaStrategyClass'))
			{
				$strategyClassNameArray = Framework::getConfiguration('modules/media/secureMediaStrategyClass');
				foreach ($strategyClassNameArray as $key => $strategyClassName)
				{
					$this->strategyArray[$key] = new $strategyClassName();
				}
			}
		}
		return $this->strategyArray;
	}
}