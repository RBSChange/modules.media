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
