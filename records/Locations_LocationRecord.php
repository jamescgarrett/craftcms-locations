<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_Location Record
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class Locations_LocationRecord extends BaseRecord
{
    /**
     * Returns the name of the database table the model is associated with (sans table prefix). By convention,
     * tables created by plugins should be prefixed with the plugin name and an underscore.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'locations_location';
    }

    /**
     * Returns an array of attributes which map back to columns in the database table.
     *
     * @access protected
     * @return array
     */
   protected function defineAttributes()
    {
        return array(
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
        );
    }

    /**
     * If your record should have any relationships with other tables, you can specify them with the
     * defineRelations() function
     *
     * @return array
     */
    public function defineRelations()
    {
        return array(
        );
    }
}