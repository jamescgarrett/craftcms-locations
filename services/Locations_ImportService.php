<?php
namespace Craft;

class Locations_ImportService extends BaseApplicationComponent
{

	public function data($file)
    {
        $data = $this->_open($file);

        return $data;
    }

    public function importLocations($file, $type)
    {

        if ($type == 1)
        {
            craft()->locations_location->deleteAllLocations();
        }

    	$rows = $this->data($file->id);

        if ($rows) {
                	
            foreach ($rows as $row)
    		{
                $continue = true;

                $model = new Locations_LocationModel();

		        $model->priority = $row['priority'];
		        $model->name = $row['name'];
		        $model->address1 = $row['address1'];
		        $model->address2 = $row['address2'];
		        $model->city = $row['city'];
		        $model->state = $row['state'];
		        $model->zipCode = $row['zipCode'];
		        $model->country = $row['country'];
		        $model->phone = $row['phone'];
		        $model->website = $row['website'];
		        $model->demoDealer = $row['demoDealer'];
		        $model->rentalBikesAndTours = $row['rentalBikesAndTours'];
		        $model->products = $row['products'];

                if ($row['longitude'] == '' || $row['latitude'] == '')
                {
                    $mapCoords = craft()->locations_location->getMapDataFromLocation($model);
                    $model->longitude = $mapCoords['longitude'];
                    $model->latitude = $mapCoords['latitude'];
                }
                else
                {
                    $model->longitude = $row['longitude'];
                    $model->latitude = $row['latitude'];
                }
		        

                if (craft()->locations_location->addLocation($model)) 
                {
                    craft()->userSession->setNotice(Craft::t('Locations saved.'));
                } 
                else 
                {
                    craft()->userSession->setError(Craft::t('Couldn’t Save Locations.'));
                }
			}
        }
        else 
        {
            craft()->userSession->setError(Craft::t('We can not find any data in that file!'));
        }

    }

    protected function _open($file)
    {
        $data = array();
        // Turn asset into a file
        $asset = craft()->assets->getFileById($file);
        $source = $asset->getSource();
        $sourceType = $source->getSourceType();
        $file = $sourceType->getLocalCopy($asset);
        // Check if file exists in the first place
        if (file_exists($file)) {
            // Automatically detect line endings
            @ini_set('auto_detect_line_endings', true);
            // Open file into rows
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            // Detect delimiter from first row
            $delimiters = array();
            $delimiters[Locations_ImportModel::DelimiterSemicolon] = substr_count($lines[0], Locations_ImportModel::DelimiterSemicolon);
            $delimiters[Locations_ImportModel::DelimiterComma] = substr_count($lines[0], Locations_ImportModel::DelimiterComma);
            $delimiters[Locations_ImportModel::DelimiterPipe] = substr_count($lines[0], Locations_ImportModel::DelimiterPipe);
            // Sort by delimiter with most occurences
            arsort($delimiters, SORT_NUMERIC);
            // Give me the keys
            $delimiters = array_keys($delimiters);
            // Use first key -> this is the one with most occurences
            $delimiter = array_shift($delimiters);
            // Open file and parse csv rows
			$header = null;
            $handle = fopen($file, 'r');
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) 
            {
    			if ($header === null) 
    			{
        			$header = $row;
        			continue;
    			}
    			$data[] = array_combine($header, $row);
			}
            fclose($handle);
        }
        // Return data array
        return $data;
    }

}