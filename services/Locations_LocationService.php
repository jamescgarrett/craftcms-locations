<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_Location Service
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class Locations_LocationService extends BaseApplicationComponent
{

    /**
     * @param Locations_LocationModel|Locations_LocationModel[] $locations
     *
     * @return array
     */
    public function getMapDataFromLocation(Locations_LocationModel $model) {
        $formattedAddress = urlencode($model->address1 . ' ,' . $model->city . ' ,' . $model->state . $model->zipCode . ' ,' . $model->country);
        $url = 'http://maps.google.com/maps/api/geocode/json?address=' . $formattedAddress . '&sensor=false';
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
        if (!$response) 
        {
            return false;
        }
        $response = json_decode($response);
        if ($response->status !== 'OK') 
        {
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
    
    /**
     * @param Bool $json
     *
     * @return array
     */
    public function getAllLocations($json)
    {
        $criteria = craft()->elements->getCriteria('Locations_Location');
        $query = craft()->elements->buildElementsQuery($criteria);
        $query->order('title asc');
        $queryResults = $query->queryAll();
        $locations = Locations_LocationModel::populateModels($queryResults);

        if ($json)
        {
            return $this->prepForJson($locations);
        }
        else{
            return $locations;
        }
        
    }

    /**
     * @param array $locations
     *
     * @return array
     */
    public function prepForJson($locations)
    {
        $results = [];
        foreach ($locations as $location)
        {
            $results[] = array(
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
        }
        return $results;
    }

    /**
     * @param Number $locationId
     *
     * @return object Locations_Location model
     */
    public function getLocationById($locationId)
    {
        return craft()->elements->getElementById($locationId, 'Locations_Location');
    }

    /**
     * @param Locations_LocationModel|Locations_LocationModel[] $model
     *
     * @return Bool
     * @throws \CDbException
     * @throws \Exception
     */
    public function saveLocation(&$model)
    {
        $isNew = !$model->id;
        if (!$isNew)
        {
            $record = Locations_LocationRecord::model()->findById($model->id);
            if (!$record)
            {
                throw new Exception(Craft::t('No location exists with the ID “{id}”', array('id' => $model->id)));
            }
        }
        else
        {
            $record = new Locations_LocationRecord();
        }

        $record->setAttributes($model->getAttributes(), false);

        if (!$record->validate())
        {
            $model->addErrors($record->getErrors());

            if (!craft()->content->validateContent($model))
            {
                $model->addErrors($model->getContent()->getErrors());
            }
            
            return false;
        }

        if (!$model->hasErrors())
        {
            $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
            try
            {
                if (craft()->elements->saveElement($model))
                {
                    if ($isNew)
                    {
                        $record->id = $model->id;
                    }
                    $record->save(false);

                    if ($transaction !== null)
                    {
                        $transaction->commit();
                    }

                    return true;
                }
            }
            catch (\Exception $e)
            {
                if ($transaction !== null)
                {
                    $transaction->rollback();
                }

                throw $e;
            }
        }
    }

    /**
     * Delete All Locations
     * 
     * @return
     */
    public function deleteAllLocations()
    {
        $locations = $this->getAllLocations(false);
        foreach ($locations as $location)
        {
            $this->deleteLocation($location);
        }
    }

    /**
     * @param Locations_LocationModel|Locations_LocationModel[] $locations
     *
     * @return bool
     * @throws \CDbException
     * @throws \Exception
     */
    public function deleteLocation($locations)
    {
        if (!$locations)
        {
            return false;
        }
        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
        try
        {
            if (!is_array($locations))
            {
                $locations = [$locations];
            }
            $locationsIds = [];
            foreach ($locations as $location)
            {
                $event = new Event($this, [
                    'location' => $location
                ]);

                $this->onBeforeDeleteLocation($event);

                if ($event->performAction)
                {
                    $locationIds[] = $location->id;
                }
            }

            if ($locationIds)
            {
                $success = craft()->elements->deleteElementById($locationIds);
            }
            else
            {
                $success = false;
            }

            if ($transaction !== null)
            {
                $transaction->commit();
            }
        }
        catch (\Exception $e)
        {
            if ($transaction !== null)
            {
                $transaction->rollback();
            }
            throw $e;
        }

        if ($success)
        {
            foreach ($locations as $location)
            {
                $this->onDeleteLocation(new Event($this, [
                    'location' => $location
                ]));
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * This event is raised before a location is deleted
     *
     * @param \CEvent $event
     *
     * @throws \CException
     */
    public function onBeforeDeleteLocation(\CEvent $event)
    {
        $params = $event->params;
        if (empty($params['location']) || !($params['location'] instanceof Locations_LocationModel))
        {
            throw new Exception('onBeforeDeleteLocation event requires "location" param with Locations_LocationModel instance that is being deleted.');
        }

        $this->raiseEvent('onBeforeDeleteLocation', $event);
    }

    /**
     * This event is raised after a location has been successfully deleted
     *
     * @param \CEvent $event
     *
     * @throws \CException
     */
    public function onDeleteLocation(\CEvent $event)
    {
        $params = $event->params;
        if (empty($params['location']) || !($params['location'] instanceof Locations_LocationModel))
        {
            throw new Exception('onDeleteLocation event requires "location" param with Locations_LocationModel instance that is being deleted.');
        }

        $this->raiseEvent('onDeleteLocation', $event);
    }
}