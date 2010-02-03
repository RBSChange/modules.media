<?php
/**
 * @package modules.media
 */
class media_EditImageSuccessView extends f_view_BaseView
{
    /**
	 * @param Context $context
	 * @param Request $request
	 */
    public function _execute($context, $request)
    {
        $rq = RequestContext::getInstance();

        $rq->beginI18nWork($rq->getUILang());

        // Set our template
        $this->setTemplateName('Media-EditImage-Success', K::XUL);

        $modules = array();
        $availableModules = ModuleService::getInstance()->getModules();
        foreach ($availableModules as $availableModuleName)
        {
            $modules[] = substr($availableModuleName, strpos($availableModuleName, '_') + 1);
        }

        foreach ($modules as $module)
        {
            if (($module == 'website')
            || ($module == 'uixul')
            || ($module == K::GENERIC_MODULE_NAME))
            {
                $this->getStyleService()
                    ->registerStyle('modules.' . $module . '.backoffice')
	                ->registerStyle('modules.' . $module . '.bindings');
            }
        }
        $this->setAttribute('cssInclusion', $this->getStyleService()->execute(K::XUL));
		$this->getJsService()->registerScript('modules.uixul.lib.default');
        $this->setAttribute('scriptInclusion', $this->getJsService()->executeInline(K::XUL));

        $languages = array();
		foreach (RequestContext::getInstance()->getSupportedLanguages() as $lang)
		{
		    $languages[$lang] = array(
		        'label' => f_Locale::translateUI('&modules.uixul.bo.languages.' . ucfirst($lang) . ';'),
		        'enabled' => true
		    );
		}

		$rq->endI18nWork();

		$this->setAttribute('infos', 'null');

        try
        {
            if ($request->hasParameter(K::COMPONENT_ACCESSOR))
            {
                if (preg_match('/lang="([^"]+)"/i', $request->getParameter(K::COMPONENT_ACCESSOR), $langMatch))
                {
                    $language = strtolower(trim($langMatch[1]));
                }
                else
                {
                    $language = RequestContext::getInstance()->getLang();
                }

                if (preg_match('/cmpref="([^"]+)"/i', $request->getParameter(K::COMPONENT_ACCESSOR), $cmprefMatch))
                {
                    $documentId = intval($cmprefMatch[1]);

                    $rq->beginI18nWork($language);

                    $document = DocumentHelper::getDocumentInstance($documentId);

                    $infos = $document->getInfo();
                    $this->setAttribute('infos', f_util_StringUtils::php_to_js($infos, true));

                    $langs = $document->getI18nInfo()->getLangs();

                    foreach ($languages as $lang => $language)
                    {
                        if (!in_array($lang, $langs))
                        {
                            $languages[$lang]['enabled'] = false;
                        }
                    }

                    $rq->endI18nWork();
                }
            }
        }
        catch (Exception $e)
        {
            Framework::exception($e);
        }

        $rq->beginI18nWork($rq->getUILang());

        $jsLanguages = array();
        foreach ($languages as $lang => $language)
        {
            if ($language['enabled'])
            {
                $jsLanguages[] = $lang . ': "' . $language['label'] . '"';
            }
            else
            {
                $jsLanguages[] = $lang . ': "!' . $language['label'] . '"';
            }
        }
        $this->setAttribute('languages', implode(', ', $jsLanguages));

        $rq->endI18nWork();
    }
}