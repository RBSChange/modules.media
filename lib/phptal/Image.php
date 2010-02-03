<?php
// change:image
//
//   <img change:image="[attributeName ][front/]image_name[ width][ height][ format]" />

/**
 * @package phptal.php.attribute
 * @author INTbonjF
 * 2007-04-19
 */
class PHPTAL_Php_Attribute_CHANGE_image extends PHPTAL_Php_Attribute
{
	private $pageId;
	private $lang;

	public function start()
	{
		$exp = trim($this->expression);
		$p = strpos($exp, ' ');
		$contentType = null;
		switch (strtolower($this->tag->name))
		{
			case 'img':
			case 'input':
				$contentType = K::HTML;
				break;

			default:
				$contentType = K::XUL;
				break;
		}

		if ($p !== false)
		{
			$attribute = substr($exp, 0, $p);
			$properties = explode(' ', trim(substr($exp, $p+1)));
		}
		else
		{
			$attribute = 'src';
			$properties = explode(' ', $exp);
		}

		if (is_numeric($properties[0]))
		{
			$args = array(intval(trim($properties[0])));

			for ($i = 1; $i < count($properties); $i++)
			{
				if (isset($properties[$i]))
				{
					$args[] = trim($properties[$i]);

					if (strtoupper(trim($properties[$i])) == K::XML)
					{
						$contentType = K::XUL;
					}
				}
			}

			$args[] = $contentType;

			$this->tag->attributes[$attribute] = f_util_ClassUtils::callMethodArgs('MediaHelper', 'getUrl', $args);
		}
		else
		{

			$this->tag->generator->pushCode('$__prop__changeimg__ = str_replace("{lang}", RequestContext::getInstance()->getLang(),' . "'$properties[0]'" . ');');
			$properties[0] = str_replace('{lang}', RequestContext::getInstance()->getLang(), $properties[0]);

			if (f_util_StringUtils::beginsWith($properties[0], 'front/'))
			{
				for ($i = 1; $i < count($properties); $i++)
				{
					if (isset($properties[$i]) && (strtoupper(trim($properties[$i])) == K::XML))
					{
						$contentType = K::XUL;
						break;
					}
				}
				
				if (!is_null($contentType) && is_string($contentType))
				{
					$argContentType = "'$contentType'";
				}
				else
				{
					$argContentType = 'null';
				}

				$this->tag->attributes[$attribute] = '<?php echo MediaHelper::getFrontofficeStaticUrl( substr($__prop__changeimg__  , 6), ' . $argContentType . '); ?>';
			}
			else if (f_util_StringUtils::beginsWith($properties[0], '/front/'))
			{
				for ($i = 1; $i < count($properties); $i++)
				{
					if (isset($properties[$i]) && (strtoupper(trim($properties[$i])) == K::XML))
					{
						$contentType = K::XUL;
						break;
					}
				}

				if (!is_null($contentType) && is_string($contentType))
				{
					$argContentType = "'$contentType'";
				}
				else
				{
					$argContentType = 'null';
				}

				$this->tag->attributes[$attribute] = '<?php echo MediaHelper::getFrontofficeStaticUrl( substr($__prop__changeimg__  , 7), ' . $argContentType . '); ?>';
			}
			else
			{
				for ($i = 1; $i < count($properties); $i++)
				{
					if (isset($properties[$i]) && (strtoupper(trim($properties[$i])) == K::XML))
					{
						$contentType = K::XUL;
						break;
					}
				}

				$this->tag->attributes[$attribute] = MediaHelper::getBackofficeStaticUrl($properties[0], $contentType);
			}
		}
	}

	public function end()
	{
	}
}
