<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_Location Model
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class Locations_LocationModel extends BaseElementModel
{

    protected $elementType = 'Locations_Location';

    /**
     * Defines this model's attributes.
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'elementId'                 => array(AttributeType::Number, 'default' => 0),
            'priority'                  => array(AttributeType::Number, 'default' => 0),
            'name'                      => array(AttributeType::String, 'default' => '', 'required' => true),
            'address1'                  => array(AttributeType::String, 'default' => '', 'required' => true),
            'address2'                  => array(AttributeType::String, 'default' => ''),
            'city'                      => array(AttributeType::String, 'default' => '', 'required' => true),
            'state'                     => array(AttributeType::String, 'default' => '', 'required' => true),
            'zipCode'                   => array(AttributeType::String, 'default' => '', 'required' => true),
            'country'                   => array(AttributeType::String, 'default' => '', 'required' => true),
            'longitude'                 => array(AttributeType::String, 'default' => ''),
            'latitude'                  => array(AttributeType::String, 'default' => ''),
            'phone'                     => array(AttributeType::String, 'default' => ''),
            'website'                   => array(AttributeType::String, 'default' => '')
        ));
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * Returns the element's CP edit URL.
     *
     * @return string|false
     */
    public function getCpEditUrl()
    {
        if ($this->id)
        {
            return UrlHelper::getCpUrl('locations/location/' . $this->id);
        }
    }
}