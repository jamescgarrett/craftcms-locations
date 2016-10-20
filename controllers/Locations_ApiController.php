<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_Api Controller
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class Locations_ApiController extends BaseController
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = true;

    /**
     * Handle request for the front end's api
     */
    public function actionLocations()
    {
        $settings = craft()->plugins->getPlugin('locations')->getSettings();

        $variables['locations'] = craft()->locations_location->getAllLocations(true);

        $variables['settings'] = array(
            'notFoundText' => $settings->notFoundText,
            'defaultZip' => $settings->defaultZip,
            'defaultRadius' => $settings->defaultRadius,
            'showMap' => $settings->showMap,
            'useGeoLocation' => $settings->useGeoLocation,
            'useYourOwnJavascriptFile' => $settings->useYourOwnJavascriptFile
        );

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