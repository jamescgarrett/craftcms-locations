<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_Location ElementType
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class Locations_LocationElementType extends BaseElementType
{
    /**
     * Returns this element type's name.
     *
     * @return mixed
     */
    public function getName()
    {
        return Craft::t('Location');
    }

    /**
     * Returns whether this element type has content.
     *
     * @return bool
     */
    public function hasContent()
    {
        return true;
    }

    /**
     * Returns whether this element type has titles.
     *
     * @return bool
     */
    public function hasTitles()
    {
        return true;
    }

    /**
     * Returns whether this element type can have statuses.
     *
     * @return bool
     */
    public function hasStatuses()
    {
        return false;
    }

    /**
     * Returns whether this element type is localized.
     *
     * @return bool
     */
    public function isLocalized()
    {
        return false;
    }

    /**
     * Returns this element type's sources.
     *
     * @param string|null $context
     * @return array|false
     */
    public function getSources($context = null)
    {
       $sources = array(
            '*' => array(
                'label'    => Craft::t('All Locations')
            )
        );
       return $sources;
    }

    /**
     * @inheritDoc IElementType::getAvailableActions()
     *
     * @param string|null $source
     *
     * @return array|null
     */
    public function getAvailableActions($source = null)
    {
        $actions = [];
        $deleteAction = craft()->elements->getAction('Locations_DeleteLocation');
        $deleteAction->setParams([
            'confirmationMessage' => Craft::t('Are you sure you want to delete the selected location?'),
            'successMessage' => Craft::t('Locations deleted.'),
        ]);
        $actions[] = $deleteAction;
        
        return $actions;
    }

    /**
     * Returns the attributes that can be shown/sorted by in table views.
     *
     * @param string|null $source
     * @return array
     */
    public function defineTableAttributes($source = null)
    {
        return array(
            'name'  => Craft::t('Name'),
            'state'  => Craft::t('State'),
            'zipCode'  => Craft::t('Zip Code'),
            'country'  => Craft::t('Country')
        );
    }

    /**
     * Returns the table view HTML for a given attribute.
     *
     * @param BaseElementModel $element
     * @param string $attribute
     * @return string
     */
    public function getTableAttributeHtml(BaseElementModel $element, $attribute)
    {
        switch ($attribute)
        {
            default:
            {
                return parent::getTableAttributeHtml($element, $attribute);
            }
        }
    }

    /**
     * Defines any custom element criteria attributes for this element type.
     *
     * @return array
     */
    public function defineCriteriaAttributes()
    {
        return array(
            'name' => AttributeType::String,
            'state' => AttributeType::String,
            'zipCode' => AttributeType::String,
            'country' => AttributeType::String
        );
    }

    /**
     * Modifies an element query targeting elements of this type.
     *
     * @param DbCommand $query
     * @param ElementCriteriaModel $criteria
     * @return mixed
     */
    public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
    {
       $query
            ->addSelect('locations_location.*')
            ->join('locations_location locations_location', 'locations_location.id = elements.id');

        if ($criteria->name)
        {
            $query->andWhere(DbHelper::parseParam('locations_location.name', $criteria->name, $query->params));
        }

        if ($criteria->state)
        {
            $query->andWhere(DbHelper::parseParam('locations_location.state', $criteria->state, $query->params));
        }

        if ($criteria->zipCode)
        {
            $query->andWhere(DbHelper::parseParam('locations_location.zipCode', $criteria->zipCode, $query->params));
        }

        if ($criteria->country)
        {
            $query->andWhere(DbHelper::parseParam('locations_location.country', $criteria->country, $query->params));
        }
    }

    /**
     * Populates an element model based on a query result.
     *
     * @param array $row
     * @return array
     */
    public function populateElementModel($row)
    {
        return Locations_LocationModel::populateModel($row);
    }

    /**
     * Returns the HTML for an editor HUD for the given element.
     *
     * @param BaseElementModel $element
     * @return string
     */
    public function getEditorHtml(BaseElementModel $element)
    {
    }
}