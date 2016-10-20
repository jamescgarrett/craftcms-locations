<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_Import Controller
 *
 * @author https://github.com/boboldehampsink/import
 * Modified some code from boboldehampsink's git repo Import plugin for Craft CMS
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class Locations_ImportController extends BaseController
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = array('actionIndex');

    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/import
     */
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
        $this->renderTemplate('locations/import/index', $variables);
    }

    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/import/taskId
     */
    public function actiontask()
    {
        $this->renderTemplate('locations/import/_task');
    }

    /**
     * Handles the request when the import form is submitted
     */
    public function actionUploadFile()
    {
        $import = craft()->request->getRequiredPost('import');

        $file = \CUploadedFile::getInstanceByName('file');

        if (!is_null($file)) {

            if (isset($import['assetSource']) && !empty($import['assetSource']))
            {
                // Get source
                $source = craft()->assetSources->getSourceTypeById($import['assetSource']);

                // Get folder to save to
                $folderId = craft()->assets->getRootFolderBySourceId($import['assetSource']);

                // Save file to Craft's temp folder for later use
                $fileName = AssetsHelper::cleanAssetName($file->name);
                $filePath = AssetsHelper::getTempFilePath($file->extensionName);
                $file->saveAs($filePath);

                // Move the file by source type implementation
                $response = $source->insertFileByPath($filePath, $folderId, $fileName, true);

                // Prevent sensitive information leak. Just in case.
                $response->deleteDataItem('filePath');

                // Get file id
                $fileId = $response->getDataItem('fileId');

                // Put vars in model
                $model = new Locations_ImportModel();
                $model->filetype = $file->getType();

                // Validate filetype
                if ($model->validate())
                {
                 
                    // Get file
                    $file = craft()->assets->getFileById($fileId);

                    // Get rows/steps from file 
                    $rows = count(craft()->locations_import->data($file->id));

                    // Set up mapping
                    $map = [];
                    foreach (craft()->locations_import->columns($fileId) as $key => $value)
                    {
                        $map[] = $value;
                    }

                    // Proceed when atleast one row
                    if ($rows)
                    {
                        // Set more settings
                        $settings = array(
                            'file' => $file->id,
                            'rows' => $rows,
                            'map' => $map
                        );

                        // Delete all if replacing
                        if ($import['behavior'] == 1) {
                            craft()->locations_location->deleteAllLocations();
                        }

                        // Create the import task
                        $task = craft()->tasks->createTask('Locations_Import', Craft::t('Importing Locations'), $settings);

                        // Notify user
                        craft()->userSession->setNotice(Craft::t('Import process started.'));

                        // Redirect
                        $this->redirect(UrlHelper::getCpUrl('locations/import/'. $task->id));
                    } 
                    else 
                    {
                        $this->redirect(UrlHelper::getCpUrl('locations/import'));
                    }
                }
                else
                {
                    // Not validated, show error
                    craft()->userSession->setError(Craft::t('This filetype is not valid').': '.$model->filetype);
                }
            }
            else
            {
                // No asset source selected
                craft()->userSession->setError(Craft::t('Please select an asset source.'));
            }
        }
        else
        {
            // No file uploaded
            craft()->userSession->setError(Craft::t('Please upload a file.'));
        }
    }
}