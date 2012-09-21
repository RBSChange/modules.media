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
		
		$expressions = $this->tag->generator->splitExpression($this->expression);
		$url = array_shift($expressions);
		
		if (count($expressions) > 0)
		{
			Framework::info('More than url parameter are passed to change:img. Additional parameters are ignored');
		}
		
		$this->tag->attributes[$attribute] = '<?php echo PHPTAL_Php_Attribute_CHANGE_img::renderImg(\'' . $url . '\') ?>';
		
		// Always generate the alt atrtibute on img tags.
		if (strtolower($this->tag->name) == 'img' && !isset($this->tag->attributes['alt']))
		{
			$this->tag->attributes['alt'] = '';
		}
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
		return MediaHelper::getStaticUrl($url);
	}
	
	public function end()
	{
	}
}