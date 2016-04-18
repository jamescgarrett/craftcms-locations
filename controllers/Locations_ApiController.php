<?php
namespace Craft;

class Locations_ApiController extends BaseController
{

    protected $allowAnonymous = true;

    public function actionLocations()
    {
        $variables['locations'] = craft()->locations_location->getAllLocationsForExport();
        $variables['settings'] = craft()->locations_settings->getSettings();

        // Get Templates Paths
        $sitePath = craft()->path->getTemplatesPath();
        $pluginPath = craft()->path->getPluginsPath() . 'locations/templates';

        // Temp Set Template Path to Plugin Templates
        craft()->path->setTemplatesPath($pluginPath);

        $this->renderTemplate('api/_locations', $variables);

        // Reset Template Path
        craft()->path->setTemplatesPath($sitePath);

    }

}