<?php
/**
 * change:img
 * <img change:img="PATH" />
 * @example PATH : front/pixel.gif
 * @example PATH : /media/frontoffice/pixel.gif
 * 
 * @example PATH : back/firefox.png
 * @example PATH : /media/backoffice/firefox.png
 * 
 * 
 * @example PATH : icon/small/media.png
 * @example PATH : /changeicons/small/media.png
 * 
 * @example PATH : theme/webfactory/tplOne.png
 * @example PATH : /media/themes/webfactory/tplOne.png
 * 
 * @example PATH : http://www.rbschange.fr/media/frontoffice/logo.png
 * @package phptal.php.attribute
 */
class PHPTAL_Php_Attribute_CHANGE_img extends PHPTAL_Php_Attribute
{
	
	public function start()
	{
		switch (strtolower($this->tag->name))
		{
			case 'img' :
			case 'input' :
			case 'image' :
				$attribute = 'src';
				break;
			
			default :
				$attribute = 'image';
				break;
		}
		$this->tag->attributes[$attribute] = '<?php echo PHPTAL_Php_Attribute_CHANGE_img::renderImg(\'' . $this->expression . '\') ?>';
	}
	
	/**
	 * @param array $params
	 * @return String
	 */
	public static function renderImg($url)
	{
		if (strpos($url, 'http') === 0)
		{
			return $url;
		}
		return  MediaHelper::getStaticUrl($url);	
	}
	
	public function end()
	{
	}
}
