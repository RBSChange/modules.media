<?php
/**
 * change:img
 * <img change:img="PATH" />
 * 
 * Examples de Paths:
 * PATH: front/pixel.gif
 * PATH: /media/frontoffice/pixel.gif
 * 
 * PATH: back/firefox.png
 * PATH: /media/backoffice/firefox.png
 * 
 * PATH: icon/small/media.png
 * PATH: /changeicons/small/media.png
 * 
 * PATH: theme/webfactory/tplOne.png
 * PATH: /media/themes/webfactory/tplOne.png
 * 
 * PATH: http://www.rbschange.fr/media/frontoffice/logo.png
 * @package phptal.php.attribute
 */
class PHPTAL_Php_Attribute_CHANGE_Img extends PHPTAL_Php_Attribute
{
    /**
     * Called before element printing.
     */
    public function before(PHPTAL_Php_CodeWriter $codewriter)
    {
        switch (strtolower($this->phpelement->getLocalName()))
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
		$attr = $this->phpelement->getOrCreateAttributeNode($attribute);
		$attr->setValueEscaped('<?php echo PHPTAL_Php_Attribute_CHANGE_Img::renderImg(\'' . $this->expression . '\') ?>');
    	
		// Always generate the alt atrtibute on img tags.
		if (strtolower($this->phpelement->getLocalName()) == 'img')
		{
			$this->phpelement->getOrCreateAttributeNode('alt');
		}
    }

    /**
     * Called after element printing.
     */
    public function after(PHPTAL_Php_CodeWriter $codewriter)
    {
       //NOTHING
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
}