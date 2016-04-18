<?php
namespace Craft;

class LocationsVariable
{

    public function getLocations()
    {
        return craft()->locations->displayLocations();
    }

}