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
			$items[] = new list_Item(f_Locale::translateUI('&modules.media.bo.general.format.' . ucfirst($formatName) .';'), 'modules.media.frontoffice/' . $formatName);
		}
		return $items;
	}
}