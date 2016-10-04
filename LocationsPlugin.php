<?php
namespace Craft;

class LocationsPlugin extends BasePlugin
{
	public function getName()
	{
	    return 'Locations';
	}

	public function getVersion()
	{
	    return '1.1.0';
	}

	public function getDeveloper()
	{
	    return 'James C Garrett';
	}

	public function getDeveloperUrl()
	{
	    return 'http://www.jamescgarrett.com';
	}

    public function hasCpSection()
    {
        return true;
    }

    public function registerCpRoutes()
    {
        return array(
            'locations' 								=> array('action' => 'locations/location/index'),
            'locations/location/add' 					=> array('action' => 'locations/location/addView'),
            'locations/location/(?P<locationId>\d+)'	=> array('action' => 'locations/location/editView'),
            'locations/settings' 						=> array('action' => 'locations/settings/index'),
            'locations/export' 							=> array('action' => 'locations/export/index'),
            'locations/export/csv'                 		=> array('action' => 'locations/export/exportCsv'),
            'locations/import' 							=> array('action' => 'locations/import/index'),
            'locations/import/csv'                 		=> array('action' => 'locations/import/importCsv')
        );
    }

    public function registerSiteRoutes()
    {
        return array(
            'api/craft/locationsplugin/locations' => array('action' => 'locations/api/locations')
        );
    }
}
