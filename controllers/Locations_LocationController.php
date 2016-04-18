<?php
namespace Craft;

class Locations_LocationController extends BaseController
{

    public function actionIndex()
    {
        $variables['locations'] = craft()->locations_location->getAllLocations();

        $this->renderTemplate('locations/location/_index', $variables);
    }

    public function actionEditView(array $variables = array())
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
            throw new HttpException(404);
        }

        $this->renderTemplate('locations/location/_edit', $variables);
    }

    public function actionAddView()
    {
        $this->renderTemplate('locations/location/_add');
    }
    
    public function actionAddLocation()
    {
        $this->requirePostRequest();

        $model = new Locations_LocationModel();

        $model->priority = craft()->request->getPost('priority');
        $model->name = craft()->request->getPost('name');
        $model->address1 = craft()->request->getPost('address1');
        $model->address2 = craft()->request->getPost('address2');
        $model->city = craft()->request->getPost('city');
        $model->state = craft()->request->getPost('state');
        $model->zipCode = craft()->request->getPost('zipCode');
        $model->country = craft()->request->getPost('country');
        $model->phone = craft()->request->getPost('phone');
        $model->website = craft()->request->getPost('website');
        $model->demoDealer = craft()->request->getPost('demoDealer');
        $model->rentalBikesAndTours = craft()->request->getPost('rentalBikesAndTours');
        $model->products = craft()->request->getPost('products');

        $mapCoords = craft()->locations_location->getMapDataFromLocation($model);
        $model->longitude = $mapCoords['longitude'];
        $model->latitude = $mapCoords['latitude'];

        if (craft()->locations_location->addLocation($model)) 
        {
            craft()->userSession->setNotice(Craft::t('Location saved.'));
        } 
        else 
        {
            craft()->userSession->setError(Craft::t('Couldn’t Save Location.'));
        }
    }

    public function actionEditLocation()
    {
        $this->requirePostRequest();

        $locationId = craft()->request->getPost('id');
        $model = craft()->locations_location->getLocationById($locationId);

        $model->priority = craft()->request->getPost('priority');
        $model->name = craft()->request->getPost('name');
        $model->address1 = craft()->request->getPost('address1');
        $model->address2 = craft()->request->getPost('address2');
        $model->city = craft()->request->getPost('city');
        $model->state = craft()->request->getPost('state');
        $model->zipCode = craft()->request->getPost('zipCode');
        $model->country = craft()->request->getPost('country');
        $model->phone = craft()->request->getPost('phone');
        $model->website = craft()->request->getPost('website');
        $model->demoDealer = craft()->request->getPost('demoDealer');
        $model->rentalBikesAndTours = craft()->request->getPost('rentalBikesAndTours');
        $model->products = craft()->request->getPost('products');

        $mapData = craft()->locations_location->getMapDataFromLocation($model);
        $model->longitude = mapData.longitude;
        $model->latitude = mapData.latitude;

        if (craft()->locations_location->editLocation($model)) 
        {
            craft()->userSession->setNotice(Craft::t('Location saved.'));
        } 
        else 
        {
            craft()->userSession->setError(Craft::t('Couldn’t Save Location.'));
        }
    }

    public function actionDeleteLocation()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $locationId = craft()->request->getRequiredPost('id');

        craft()->locations_location->deleteLocationById($locationId);

        $this->returnJson(array('success' => true));
    }
}