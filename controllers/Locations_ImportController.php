<?php
namespace Craft;

class Locations_ImportController extends BaseController
{
    public function actionIndex()
    {
        $assetources = craft()->assetSources->getViewableSources();
        if ($assetources)
        {
            foreach ($assetources as $source)
            {
                
                $variables['options'][$source->id] = $source->name;
            }
        } 
        else 
        {
            $variables['nooptions']['message'] = 'Please create an asset source in Craft before using the import feature.';
        }
        
        $this->renderTemplate('locations/import/_index/', $variables);
    }

    public function actionUploadFile()
    {

        $this->requirePostRequest();

        $import = array(
            'type' => craft()->request->getPost('importType'),
            'assetSource' => craft()->request->getPost('importAssetSource'),
            'file' => craft()->request->getPost('importFile')
        );

        $file = \CUploadedFile::getInstanceByName('importFile');

        if (!is_null($file)) {
            // Determine folder
            $folder = craft()->path->getStoragePath().'import/';
            // Ensure folder exists
            IOHelper::ensureFolderExists($folder);
            // Get filepath - save in storage folder
            $path = $folder.$file->getName();
            // Save file to Craft's temp folder for later use
            $file->saveAs($path);
            // Get source
            $source = craft()->assetSources->getSourceTypeById($import['assetSource']);
            // Get folder to save to
            $folderId = craft()->assets->getRootFolderBySourceId($import['assetSource']);
            // Move the file by source type implementation
            $response = $source->insertFileByPath($path, $folderId, $file->getName(), true);
            // Prevent sensitive information leak. Just in case.
            $response->deleteDataItem('filePath');
            // Get file id
            $fileId = $response->getDataItem('fileId');
            // Put vars in model
            $model = new Locations_ImportModel();
            $model->filetype = $file->getType();
            // Validate filetype
            if ($model->validate()) {
                $file = craft()->assets->getFileById($fileId);
                craft()->locations_import->importLocations($file, $import['type']);

            } else {
                // Not validated, show error
                craft()->userSession->setError(Craft::t('This filetype is not valid').': '.$model->filetype);
            }
        } else {
            // No file uploaded
            craft()->userSession->setError(Craft::t('Please upload a file.'));
        }
    }

}