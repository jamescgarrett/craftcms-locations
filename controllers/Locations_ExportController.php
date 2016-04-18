<?php
namespace Craft;

class Locations_ExportController extends BaseController
{
    public function actionIndex()
    {
        $this->renderTemplate('locations/export/_index/', array());
    }

    public function actionExportCsv()
	{

		$locations = craft()->locations_location->getAllLocationsForExport();
		
		set_time_limit('1000');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . ('locator-locations.csv'));
		header('Content-Transfer-Encoding: binary');
		$stream = fopen('php://output', 'w');
		$firstLoop = true;

		foreach ($locations as $location) 
		{

			$row = array(
				'priority' => $location['priority'],
				'name' => $location['name'],
				'address1' => $location['address1'],
				'address2' => $location['address2'],
				'city' => $location['city'],
				'state' => $location['state'],
				'zipCode' => $location['zipCode'],
				'country' => $location['country'],
				'phone' => $location['phone'],
				'website' => $location['website'],
				'demoDealer' => $location['demoDealer'],
				'rentalBikesAndTours' => $location['rentalBikesAndTours'],
				'products' => $location['products'],
				'longitude' => $location['longitude'],
				'latitude' => $location['latitude']
			);

            if ($firstLoop) 
            {
                fputcsv($stream, array_keys($row));
                $firstLoop = false;
            }

            fputcsv($stream, $row);

		}
        fclose($stream);
	}
}