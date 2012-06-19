<?php

class PHPTAL_Php_Attribute_CHANGE_Gauge extends ChangeTalAttribute 
{
	/**
	 * @return string
	 */
	protected function getDefaultParameterName()
	{
		return 'value';
	}
	
	protected function getDefaultValues()
	{
		return array('value' => 0, 
			'baseName' => 'front/gauge-search-5-', 
			'imageExtension' => '.png', 
			'maxScore' => 5, 
			'baseClass' => 'solrsearch-results-',
			'baseLocalKey' => 'm.media.frontoffice.gauge-search-5-');
	}
	
	protected function getEvaluatedParameters()
	{
		return array('value', 'maxScore', 'class');
	}
	
	/**
	 * @param array $params
	 * @return string
	 */
	public static function renderGauge($params)
	{
		$value = strval(round(intval($params['maxScore']) * floatval($params['value'])));
		$name = $params['baseName'] . $value . $params['imageExtension'];
		$attributes = array('src' => MediaHelper::getStaticUrl($name));
		
		if (isset($params['alt']))
		{
			$attributes['alt'] = $params['alt'];
		}
		else
		{
			$attributes['alt'] = LocaleService::getInstance()->trans($params['baseLocalKey'] . $value, array('ucf'));
		}
		
		$attributes['title'] = $attributes['alt'];
		if (isset($params['class']))
		{
			$attributes['class'] = $params['class'];
		}
		else
		{
			$attributes['class'] = $params['baseClass'] . $value;
		}
		return '<img '.self::buildAttributes($attributes).'>';	
	}
}
