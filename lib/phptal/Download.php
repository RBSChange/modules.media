<?php

class PHPTAL_Php_Attribute_CHANGE_download extends PHPTAL_Php_Attribute
{
	const LOCALE_PATH =  '&modules.media.download.';

	public function start()
	{
		$expressions = $this->tag->generator->splitExpression($this->expression);
		$media = 'null';
		$class = 'null';
		// foreach attribute
		foreach ($expressions as $exp)
		{
			list($attribute, $value) = $this->parseSetExpression($exp);
			switch ($attribute)
			{
				case 'class':
					$class = '"'.$this->evaluate($value, true).'"';
					break;
				case 'document':
					$media = $this->evaluate($value, true);
					break;
				default:
					if ($media == 'null' && is_null($value) && !is_null($attribute))
					{
						$media = $this->evaluate($attribute, true);
					}
					break;
			}
		}

		$this->tag->generator->doSetVar('$media', $media);
		$this->tag->generator->doSetVar('$class', $class);
		$this->tag->generator->doEcho('PHPTAL_Php_Attribute_CHANGE_download::render($media, $class)');
	}

	public function end()
	{
	}
	/**
	 *
	 *
	 * @param media_persistentdocument_media $media
	 */
	public function getLang($media)
	{
		$lang = RequestContext::getInstance()->getLang();
		if ($media->isLangAvailable($lang))
		{
			return $lang;
		}
		return $media->getI18nInfo()->getVo();
	}

	/**
	 * Enter description here...
	 *
	 * @param media_persistentdocument_media $media
	 * @param String $lang
	 */
	public static function getContent($media, $lang)
	{
		$rc = RequestContext::getInstance();

		$rc->beginI18nWork($lang);

		if ($media->getTitle())
		{
		    $filename = $media->getTitle();
		}
		elseif ($media->getFilename())
		{
		    $filename = $media->getFilename();
		}
		else
		{
		    $filename = $media->getVoFilename();
		}

		$path = MediaHelper::getOriginalPath($media, true);
		$type = $media->getMediatype();
		
		// #7824 - intcours - display extension for "unknown" files :
		if (strtolower($type) == 'media')
		{
    		if ($media->getFilename())
    		{
    		    $type = f_util_FileUtils::getFileExtension($media->getFilename());
    		}
    		else
    		{
    		    $type = f_util_FileUtils::getFileExtension($media->getVoFilename());
    		}    		
		}

		$rc->endI18nWork();

		$langString = null;
		if ($lang !=  $rc->getLang())
		{
			$langString = f_Locale::translate( '&modules.uixul.bo.languages.' . ucfirst($lang) . ';' ). ', ';
		}
		
		$res = $filename . ' - ' . strtoupper($type) . ' (' . $langString;
		$res .= self::getFileSize($path);
		$res .= ')';
		return $res;
	}


	public static function getFileSize($path)
	{
		if (is_readable($path))
		{
			$size = filesize($path);
			$i = 0;
			$iec = array("b", "kb", "mb", "gb", "tb", "pb", "eb", "zb", "yb");
			while ( ($size / 1024) > 1)
			{
				$size = $size / 1024;
				$i++;
			}
			$res = sprintf("%.2f", $size).' <acronym title="'.f_Locale::translate(self::LOCALE_PATH . ucfirst($iec[$i]) .'-long;').'">';
			$res .= f_Locale::translate(self::LOCALE_PATH .ucfirst($iec[$i]).'-acronym;');
			$res .= '</acronym>';
			return $res;
		}
		return null;

	}

	/**
	 * @param media_persistentdocument_media $media
	 * @param String $class
	 * @return String
	 */
	public static function render($media, $class, $addcmpref = false)
	{
		$lang = self::getLang($media);
		$title = f_util_StringUtils::ucfirst(self::getContent($media, $lang));
		$alt = f_Locale::translate(self::LOCALE_PATH.'Download;') . ' ' . htmlspecialchars(strip_tags($title));
		$html = '<a';
		
		$attrs = array("href" => '"' . self::getUrl($media, $lang) . '"');
		if ($addcmpref)
		{
			$attrs['cmpref'] = '"' . $media->getId() . '"';
		}

		if (f_util_StringUtils::isNotEmpty($class))
		{
			$attrs['class'] = '"link download ' . $class . '"';
		}
		else
		{
			$attrs['class'] = '"link download"';
		}
		
		$attrs['alt'] = '"' . $alt . '"';
		$attrs['title'] = '"' . $alt . '"';
		
		if (!$addcmpref)
		{
			$attrs = array_merge($attrs, self::getAdditionnalAttributes($media, $class));
		}
		
		foreach ($attrs as $attrName => $attrValue) 
		{
			$html .= ' ' . $attrName .'=' . $attrValue;
		}
		$html .= '>' . $title . "</a>";
		return $html;
	}
	
	private function getAdditionnalAttributes($media, $class)
	{
		$additionnalAttributes = array();
		foreach (MediaHelper::getAdditionnalDownloadAttributes($media, $class) as $attrName => $attrValue)
		{
			$additionnalAttributes[$attrName] = '"' .htmlspecialchars($attrValue, ENT_COMPAT, "UTF-8").'"';
		}
		return $additionnalAttributes;
	}


	public static function getUrl($media, $lang)
	{
		if (!($media instanceof media_persistentdocument_file))
		{
			return '#';
		}
		return htmlspecialchars(media_FileService::getInstance()->generateDownloadUrl($media, $lang), ENT_COMPAT, "UTF-8");
	}
}
