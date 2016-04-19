<?php
namespace Craft;

class LocationsService extends BaseApplicationComponent
{

	public function log($dataToLog)
	{
		LocationsPlugin::log(print_r($dataToLog, true));
	}

    public function getScripts()
    {
        if ( $this->isSecureSite() )
        {
            $protocol = 'https://';
        }
        else 
        {
            $protocol = 'http://';
        }

        $settings =  craft()->locations_settings->getSettings();

        craft()->templates->includeJsFile($protocol . 'maps.googleapis.com/maps/api/js?' . ($settings['googleMapsApiKey'] ? 'key='.$settings['googleMapsApiKey'] : ''));

        craft()->templates->includeJsFile(UrlHelper::getResourceUrl('locations/src/js/locations.js'));

        if (!$settings['useYourOwnJavascriptFile'])
        {
            $this->addLocationsJavascript($settings);
        }

    }

    public function addLocationsJavascript($settings)
    {
        craft()->templates->includeJs('document.addEventListener("DOMContentLoaded",function(){const locations = new LocationLocator({
            data: "' . $settings['dataApiPath'] . '"
        });});');
    }

    public function isSecureSite()
    {
      return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    public function displayLocations()
    {
        $this->getScripts();

        foreach (craft()->config->get('defaultTemplateExtensions') as $extension) 
        {
            if ( IOHelper::fileExists( craft()->path->getTemplatesPath() . 'plugin_locations/_locations' . "." . $extension) ) 
            {
                $html = craft()->templates->render('plugin_locations/_locations');
            }
            else
            {
                $sitePath = craft()->path->getTemplatesPath();
                $pluginPath = craft()->path->getPluginsPath() . 'locations/templates';

                craft()->path->setTemplatesPath($pluginPath);

                $html = craft()->templates->render('frontend/_locations');

                // Reset Template Path
                craft()->path->setTemplatesPath($sitePath);
            }
        }

    
        return TemplateHelper::getRaw($html);

    }

}