<?php
namespace Craft;

class Locations_LocationService extends BaseApplicationComponent
{

	public function getLocationById($locationId)
	{

        $record = Locations_LocationRecord::model()->findById($locationId);

        if ($record) 
        {
            return Locations_LocationModel::populateModel($record);
        } 

	}

	public function getAllLocations($indexBy = null)
	{
		$records = Locations_LocationRecord::model()->ordered()->findAll();
		return Locations_LocationModel::populateModels($records, $indexBy);
	}

    public function getAllLocationsForExport()
    {
        $records = Locations_LocationRecord::model()->ordered()->findAll();

        if (!$records)
        {
            return false;
        } else {
            foreach ($records as $record) {
                $results[] = $record->attributes;
            }
            
            return $results;
        }
    }

	public function addLocation(Locations_LocationModel $model)
    {

        if (!$model)
        {
            return false;
        }

        $record = new Locations_LocationRecord();

        $record->setAttributes($model->getAttributes(), false);

        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
        try
        {
            $record->validate();
            $model->addErrors($record->getErrors());

            if (!$model->hasErrors())
            {
                $record->save(false);

                if ($transaction !== null)
                {
                    $transaction->commit();
                }

                $model->setAttribute('id', $record->getAttribute('id'));

                return true;
            }
            else
            {
                if ($transaction !== null)
                {
                    $transaction->rollback();
                }

                return false;
            }
        }
        catch (\Exception $ex)
        {
            if ($transaction !== null)
            {
                $transaction->rollback();
            }

            throw $ex;
        }

    }

    public function editLocation(Locations_LocationModel $model)
    {

        if (!$model)
        {
            return false;
        }

        $record = Locations_LocationRecord::model()->findById($model->id);
        if (!$record)
        {
            $record = new Locations_LocationRecord();
        }

        $record->setAttributes($model->getAttributes(), false);

        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
        try
        {
            
            $record->validate();
            $model->addErrors($record->getErrors());

            if (!$model->hasErrors())
            {
                $record->save(false);

                if ($transaction !== null)
                {
                    $transaction->commit();
                }

                $model->setAttribute('id', $record->getAttribute('id'));

                return true;
            }
            else
            {
                if ($transaction !== null)
                {
                    $transaction->rollback();
                }

                return false;
            }
        }
        catch (\Exception $ex)
        {
            if ($transaction !== null)
            {
                $transaction->rollback();
            }

            throw $ex;
        }
    }

    public function deleteLocationById($locationId)
    {
        $location = $this->getLocationById($locationId);

        return $this->deleteLocation($location);
    }


    public function deleteLocation(Locations_LocationModel $model)
    {
        if (!$model || !$model->id)
        {
            return false;
        }

        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
        try
        {
            $rows = craft()->db->createCommand()->delete('locations_location', array(
                'id' => $model->id
            ));

            if ($transaction !== null)
            {
                $transaction->commit();
            }

            return (bool) $rows;
        }
        catch (\Exception $ex)
        {
            if ($transaction !== null)
            {
                $transaction->rollback();
            }
        }
    }

    public function deleteAllLocations()
    {
        
        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
        try
        {
            $rows = craft()->db->createCommand()->delete('locations_location');

            if ($transaction !== null)
            {
                $transaction->commit();
            }

            return (bool) $rows;
        }
        catch (\Exception $ex)
        {
            if ($transaction !== null)
            {
                $transaction->rollback();
            }
        }
    }

    public function getMapDataFromLocation(Locations_LocationModel $model)
    {
        $zip = urlencode($model->zipCode);
        $url = 'http://maps.google.com/maps/api/geocode/json?address=' . $zip . '&sensor=false';
        $ch = curl_init();
        $options = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
        );
        
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        if (!$response) {
            return false;
        }
        $response = json_decode($response);
        if ($response->status !== 'OK') {
            return false;
        }

        $longitude = $response->results[0]->geometry->location->lng;
        $latitude  = $response->results[0]->geometry->location->lat;

        $coords = array(
            'longitude' => $longitude,
            'latitude' => $latitude
        );

        return $coords;
    }

}
