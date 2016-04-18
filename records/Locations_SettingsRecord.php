<?php
namespace Craft;

class Locations_SettingsRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'locations_settings';
    }

    protected function defineAttributes()
    {
         return array(
            'recordId'          => AttributeType::String,
            'googleMapsApiKey'  => AttributeType::String,
            'notFoundText'      => AttributeType::String,
            'defaultZip'        => AttributeType::String,
            'defaultRadius'     => AttributeType::String,
            'useGeoLocation'    => AttributeType::Bool
        );
    }
}