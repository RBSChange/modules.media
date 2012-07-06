<?php
/**
 * @package modules.media
 * @method media_ListFormatsService getInstance()
 */
class media_ListFormatsService extends change_BaseService implements list_ListItemsService
{
	/**
	 * Returns an array of available formats for the media module.
	 * @return list_Item[]
	 */
	public function getItems()
	{
		$items = array();
		$formats = website_StyleService::getInstance()->getImageFormats('modules.media.frontoffice');
		foreach (array_keys($formats) as $formatName)
		{
			$items[] = new list_Item(LocaleService::getInstance()->trans('m.media.bo.general.format.' . strtolower($formatName), array('ucf')), 'modules.media.frontoffice/' . $formatName);
		}
		return $items;
	}
}