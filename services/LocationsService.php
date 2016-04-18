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

        $googleMapsKey =  craft()->locations_settings->getGoogleMapsApiKey();

        craft()->templates->includeJsFile($protocol . 'maps.googleapis.com/maps/api/js?' . ($googleMapsKey ? 'key='.$googleMapsKey : ''));

        craft()->templates->includeJsFile(UrlHelper::getResourceUrl('locations/src/js/app.js'));
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
            if ( IOHelper::fileExists( craft()->path->getTemplatesPath() . 'locationsPlugin/_locations' . "." . $extension) ) 
            {
                $html = craft()->templates->render('locationsPlugin/_locations');
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