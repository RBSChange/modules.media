<?php
/**
 * change:img
 * <img change:img="PATH" />
 * 
 * Example de Path :
 * PATH : front/pixel.gif
 * PATH : /media/frontoffice/pixel.gif
 * 
 * PATH : back/firefox.png
 * PATH : /media/backoffice/firefox.png
 * 
 * PATH : icon/small/media.png
 * PATH : /changeicons/small/media.png
 * 
 * PATH : theme/webfactory/tplOne.png
 * PATH : /media/themes/webfactory/tplOne.png
 * 
 * PATH : http://www.rbschange.fr/media/frontoffice/logo.png
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
