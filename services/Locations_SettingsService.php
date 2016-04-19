<?php
namespace Craft;

class Locations_SettingsService extends BaseApplicationComponent
{

    public function getSettingsModel()
    {
        $record = Locations_SettingsRecord::model()->findByAttributes(array("recordId" => "settings"));

        if ($record) 
        {
            $model =  Locations_SettingsModel::populateModel($record);
        } 
        else 
        {
            $record = new Locations_SettingsRecord();
            $model =  Locations_SettingsModel::populateModel($record);
        }

        return $model;
    }

    public function getSettings()
    {
        $model = $this->getSettingsModel();

        if (!$model)
        {
            return false;
        } 
        else 
        {
            $result = $model->attributes;

            if ($result)
            {
                unset($result['id']);
                unset($result['dateCreated']);
                unset($result['dateUpdated']);
                unset($result['uid']);
            }

            return $result;
        }
    }

    public function saveSettings(Locations_SettingsModel $model)
    {

        if (!$model)
        {
            return false;
        }

        $record = Locations_SettingsRecord::model()->findByAttributes(array('recordId' => 'settings'));
        if (!$record)
        {
            $record = new Locations_SettingsRecord();
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
}
