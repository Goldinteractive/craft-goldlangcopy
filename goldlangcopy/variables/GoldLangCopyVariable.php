<?php
namespace Craft;

class GoldLangCopyVariable
{
	public function getLocaleInputOptions($locales = null, $exclude = [])
	{
		return craft()->goldLangCopy->getLocaleInputOptions($locales, $exclude);
	}
}
