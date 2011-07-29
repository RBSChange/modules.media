<?php
/**
 * @package modules.media
 */
class media_ListFormatsService extends BaseService implements list_ListItemsService
{
	/**
	 * @var media_ListFormatsService
	 */
	private static $instance;
	
	/**
	 * @return media_ListFormatsService
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
	 * Returns an array of available formats for the media module.
	 *
	 * @return array
	 */
	public function getItems()
	{
		$items = array();
		$formats = website_StyleService::getInstance()->getImageFormats('modules.media.frontoffice');
		foreach (array_keys($formats) as $formatName)
		{
			$items[] = new list_Item(f_Locale::translateUI('&modules.media.bo.general.format.' . ucfirst($formatName) .';'), 'modules.media.frontoffice/' . $formatName);
		}
		return $items;
	}
}