<?php
namespace Craft;

class Locations_SettingsModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            'recordId'			=> AttributeType::String,
            'googleMapsApiKey'  => AttributeType::String,
            'notFoundText'      => AttributeType::String,
            'defaultZip' 		=> AttributeType::String,
            'defaultRadius' 	=> AttributeType::String,
            'useGeoLocation'	=> AttributeType::Bool
        );
    }
}