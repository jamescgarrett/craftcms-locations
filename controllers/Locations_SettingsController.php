<?php
namespace Craft;

class Locations_SettingsController extends BaseController
{

    public function actionIndex()
    {
        $this->renderTemplate('locations/_settings/', array(
            'settings' => craft()->locations_settings->getSettings()
        ));
    }
    
    public function actionSaveSettings()
    {
        $this->requirePostRequest();
        
        $model = craft()->locations_settings->getSettingsModel();

        $model->recordId = "settings";
        $model->googleMapsApiKey = craft()->request->getPost('googleMapsApiKey');
        $model->notFoundText = craft()->request->getPost('notFoundText');
        $model->defaultZip = craft()->request->getPost('defaultZip');
        $model->defaultRadius = craft()->request->getPost('defaultRadius');
        $model->showMap = craft()->request->getPost('showMap');
        $model->useGeoLocation = craft()->request->getPost('useGeoLocation');
        $model->useYourOwnJavascriptFile = craft()->request->getPost('useYourOwnJavascriptFile');
        $model->dataApiPath = craft()->request->getPost('dataApiPath');

        if (craft()->locations_settings->saveSettings($model)) 
        {
            craft()->userSession->setNotice(Craft::t('Settings saved.'));
        } 
        else 
        {
            craft()->userSession->setError(Craft::t('Couldn’t save settings.'));
        }
    }

}