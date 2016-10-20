<?php
/**
 * Locations plugin for Craft CMS
 *
 * LocationsService
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class LocationsService extends BaseApplicationComponent
{
    
    /**
     * Gets any front end scripts needed
     *
     * @return
     */
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

        $settings =  craft()->plugins->getPlugin('locations')->getSettings();

        craft()->templates->includeJsFile($protocol . 'maps.googleapis.com/maps/api/js?' . ($settings['googleMapsApiKey'] ? 'key='.$settings['googleMapsApiKey'] : ''));

        craft()->templates->includeJsFile(UrlHelper::getResourceUrl('locations/src/js/locations.js'));

        if (!$settings['useYourOwnInitScript'])
        {
            $this->addLocationsJavascript($settings);
        }

    }

    /**
     * @param Plugin Settings
     *
     * @return
     */
    public function addLocationsJavascript($settings)
    {
        craft()->templates->includeJs('document.addEventListener("DOMContentLoaded",function(){let locations = new Locations({
            data: "' . $settings['dataApiPath'] . '"
        });});');
    }

    /**
     * Checks if https is needed
     *
     * @return Bool
     */
    public function isSecureSite()
    {
      return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * displays locations on the front end
     *
     * @return Template
     */
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