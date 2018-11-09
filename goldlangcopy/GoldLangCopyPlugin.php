<?php

namespace Craft;

class GoldLangCopyPlugin extends BasePlugin
{
    /**
     * @return mixed
     */
    public function getName()
    {
        return Craft::t('GoldLangCopy');
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return Craft::t('Copy content to locales on element save.');
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '0.0.1';
    }

    public function getDeveloper()
    {
        return 'Gold Interactive';
    }

    public function getDeveloperUrl()
    {
        return 'http://www.goldinteractive.ch';
    }

    /**
     * @return mixed
     */
    public function init()
    {
        craft()->templates->hook('cp.entries.edit.right-pane', function (&$context) {
            return craft()->goldLangCopy->getElementOptionsHtml($context['entry']);
        });

        craft()->on('entries.onBeforeSaveEntry', function (Event $event) {
            return craft()->goldLangCopy->syncElementContent($event, craft()->request->getPost('goldLangCopy'));
        });
    }
}
