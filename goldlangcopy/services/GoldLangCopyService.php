<?php

namespace Craft;

class GoldLangCopyService extends BaseApplicationComponent
{
    private $_elementBeforeSave;
    private $_element;
    private $_elementSettings;

    /**
     * Get the markup to render in the right pane of every element.
     *
     * @param BaseElementModel $element
     * @return string
     */
    public function getElementOptionsHtml(BaseElementModel $element)
    {
        $isNew = $element->id === null;
        $locales = array_keys($element->getLocales());

        if ($isNew || count($locales) < 2) {
            return;
        }

        return craft()->templates->render('goldlangcopy/_cp/entriesEditRightPane', [
            'localeId' => $element->locale,
        ]);
    }

    /**
     * Get list of locales to sync to.
     *
     * @param null  $locales
     * @param array $exclude
     * @return array
     */
    public function getLocaleInputOptions($locales = null, $exclude = [])
    {
        $locales = $locales ?: craft()->i18n->getSiteLocales();
        $locales = array_map(function ($locale) use ($exclude) {
            if (!$locale instanceof LocaleModel) {
                $locale = craft()->i18n->getLocaleById($locale);
            }

            if ($locale instanceof LocaleModel && !in_array($locale->id, $exclude)) {
                $locale = [
                    'label' => $locale->name,
                    'value' => $locale->id,
                ];
            } else {
                $locale = null;
            }

            return $locale;
        }, $locales);

        return array_filter($locales);
    }

    public function syncElementContent(Event $event, $elementSettings)
    {
        $this->_element = $event->params['entry'];
        $this->_elementSettings = $elementSettings;

        // elementSettings will be null in HUD, where we want to continue with defaults
        if ($this->_elementSettings !== null && ($event->params['isNewEntry'] || empty($this->_elementSettings['enabled']))) {
            return;
        }

        $this->_elementBeforeSave = craft()->elements->getElementById($this->_element->id, $this->_element->elementType,
            $this->_element->locale);
        $locales = $this->_element->getLocales();

        $targets = $this->_elementSettings['targets'];
        if (!is_array($targets)) {
            $targets = [$targets];
        }

        foreach ($locales as $localeId => $localeInfo) {
            $localizedElement = craft()->elements->getElementById($this->_element->id, $this->_element->elementType,
                $localeId);
            $matchingTarget = $targets === '*' || in_array($localeId, $targets);

            if ($localizedElement && $matchingTarget && $this->_element->locale !== $localeId) {
                $localizedElement->setContentFromPost('fields');
                craft()->entries->saveEntry($localizedElement);
            }
        }
    }
}
