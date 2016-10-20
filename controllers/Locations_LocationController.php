<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_Location Controller
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class Locations_LocationController extends BaseController
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = array('actionIndex');

    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/locations
     */
    public function actionIndex()
    {
        $this->renderTemplate('locations/location/index');
    }

    /**
     * Handle a request going to our plugin's add and edit action URL, e.g.: actions/locations/add & actions/locations/edit
     */
    public function actionEdit(array $variables = array()) 
    {
        if (!empty($variables['locationId']))
        {
            if (empty($variables['location']))
            {
                $variables['location'] = craft()->locations_location->getLocationById($variables['locationId']);

                if (!$variables['location'])
                {
                    throw new HttpException(404);
                }
            }
        }
        else
        {
            if (empty($variables['location']))
            {
                LocationsPlugin::log(print_r("Creating an NEW ID", true));
                $location = new Locations_LocationModel();
                $variables['location'] = $location;
            }
        }


        $this->renderTemplate('locations/location/_edit', $variables);
    }

    /**
     * Handle a request going to our plugin's save action
     */
    public function actionSave(array $variables = array()) 
    {
        $this->requirePostRequest();

        $locationId = craft()->request->getPost('locationId');

        if ($locationId)
        {
            $model = craft()->locations_location->getLocationById($locationId);
        }
        else
        {
            $model = new Locations_LocationModel();
        }

        $model->priority = craft()->request->getPost('priority', $model->priority);
        $model->name = craft()->request->getPost('name', $model->name);
        $model->address1 = craft()->request->getPost('address1', $model->address1);
        $model->address2 = craft()->request->getPost('address2', $model->address2);
        $model->city = craft()->request->getPost('city', $model->city);
        $model->state = craft()->request->getPost('state', $model->state);
        $model->zipCode = craft()->request->getPost('zipCode', $model->zipCode);
        $model->country = craft()->request->getPost('country', $model->country);
        $model->phone = craft()->request->getPost('phone', $model->phone);
        $model->website = craft()->request->getPost('website', $model->website);
        if (craft()->request->getPost('longitude') != null || craft()->request->getPost('latitude') != null)
        {
            $model->longitude = craft()->request->getPost('longitude', $model->longitude); 
            $model->latitude = craft()->request->getPost('latitude', $model->latitude);  
        }
        else
        {
            $mapCoords = craft()->locations_location->getMapDataFromLocation($model);
            $model->longitude = $mapCoords['longitude'];
            $model->latitude = $mapCoords['latitude'];
        }
        $model->getContent()->title = craft()->request->getPost('name', $model->name);

        if (craft()->locations_location->saveLocation($model)) 
        {
            craft()->userSession->setNotice(Craft::t('Location saved.'));
            $this->redirectToPostedUrl($model);
        } 
        else 
        {
            craft()->userSession->setError(Craft::t('Couldn’t Save Location.'));
            craft()->urlManager->setRouteVariables(array(
                'location' => $model
            ));
        }
    }

}