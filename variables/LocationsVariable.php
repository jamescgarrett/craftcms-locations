<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations Variable
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class LocationsVariable
{
    /**
     *
     *  Usage: {{ craft.locations.getLocations }}
     */
     public function getLocations()
    {
        return craft()->locations->displayLocations();
    }
}