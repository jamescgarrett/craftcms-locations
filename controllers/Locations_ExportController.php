<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_Export Controller
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class Locations_ExportController extends BaseController
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = array('actionIndex');

    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/export
     */
    public function actionIndex()
    {
        $this->renderTemplate('locations/export/index');
    }

    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/export/csv
     */
    public function actionExportCsv()
    {
        $locations = craft()->locations_location->getAllLocations(false);

        set_time_limit('1000');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . ('locations.csv'));
        header('Content-Transfer-Encoding: binary');
        $stream = fopen('php://output', 'w');
        
        $firstLoop = true;
        $count = 0;
        $length = count($locations);

        while ($count <= $length)
        {
            foreach ($locations as $location) 
            {
                $count++;
                $row = array(
                    'priority' => $location['priority'],
                    'name' => $location['name'],
                    'address1' => $location['address1'],
                    'address2' => $location['address2'],
                    'city' => $location['city'],
                    'state' => $location['state'],
                    'zipCode' => $location['zipCode'],
                    'country' => $location['country'],
                    'longitude' => $location['longitude'],
                    'latitude' => $location['latitude'],
                    'phone' => $location['phone'],
                    'website' => $location['website']
                );

                if ($firstLoop) 
                {
                    fputcsv($stream, array_keys($row));
                    $firstLoop = false;
                }
                fputcsv($stream, $row);
            }
        }
        fclose($stream);
    }
}