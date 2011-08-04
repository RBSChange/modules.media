<?php
/**
 * @package modules.media
 */
class media_SecuremediaService extends media_MediaService
{
	/**
	 * @var media_SecuremediaService
	 */
	private static $instance;

	/**
	 * @return media_SecuremediaService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return media_persistentdocument_securemedia
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_media/securemedia');
	}

	/**
	 * @param Integer $id
	 * @param String $lang
	 * @return String
	 */
	protected function getAbsoluteFolder($id, $lang = null)
	{
		return PROJECT_HOME.'/securemedia/original/'.$this->getRelativeFolder($id, $lang);
	}

	/**
	 * @param Integer $id
	 * @param String $lang
	 * @return String
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
	 * @return Boolean
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
	 * @return Boolean
	 */
	public function hasBoAccess($media)
	{
		$ps = f_permission_PermissionService::getInstance();

		$user = users_UserService::getInstance()->getCurrentBackEndUser();
		if ($user === null)
		{
			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__ . ' : User not authenticated');
			}
			return false;
		}
		$permissionName = 'modules_media.BoDisplay';
		if (!$ps->hasPermission($user, $permissionName, $media->getId()))
		{
			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__ . 'permisson {' . $permissionName . '} is not defined for user {' . $user->__toString() . '}');
			}
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