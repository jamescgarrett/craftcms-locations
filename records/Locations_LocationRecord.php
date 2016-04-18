<?php
namespace Craft;

class Locations_LocationRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'locations_location';
	}

	protected function defineAttributes()
	{
            return array(
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

	public function scopes()
	{
		return array(
			'ordered' => array('order' => 'name'),
		);
	}
}
