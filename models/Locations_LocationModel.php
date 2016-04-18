<?php
namespace Craft;

class Locations_LocationModel extends BaseModel
{

    protected function defineAttributes()
    {
        return array(
        'id' 						=> AttributeType::Number,
          'priority'          => AttributeType::String,
          'name'            => AttributeType::String,
          'address1'        => AttributeType::String,
          'address2'        => AttributeType::String,
          'city'            => AttributeType::String,
          'state'           => AttributeType::String,
          'zipCode'         => AttributeType::String,
          'country'         => AttributeType::String,
          'phone'           => AttributeType::String,
          'website'         => AttributeType::String,
          'demoDealer'      => AttributeType::String,
          'rentalBikesAndTours' => AttributeType::String,
          'products'          => AttributeType::String,
          'longitude'       => AttributeType::String,
          'latitude'       => AttributeType::String
        );
    }
}