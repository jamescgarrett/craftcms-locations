<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_DeleteLocationElementAction
 *
 *
 * @author    James C Garrett
 * @copyright Copyright (c) 2016 James C Garrett
 * @link      http://jamescgarrett.com
 * @package   Locations
 * @since     1.0.0
 */

namespace Craft;

class Locations_DeleteLocationElementAction extends BaseElementAction
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc IComponentType::getName()
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Delete');
    }

    /**
     * @inheritDoc IElementAction::isDestructive()
     *
     * @return bool
     */
    public function isDestructive()
    {
        return true;
    }

    /**
     * @inheritDoc IElementAction::getConfirmationMessage()
     *
     * @return string|null
     */
    public function getConfirmationMessage()
    {
        return $this->getParams()->confirmationMessage;
    }

    /**
     * @inheritDoc IElementAction::performAction()
     *
     * @param ElementCriteriaModel $criteria
     *
     * @return bool
     */
    public function performAction(ElementCriteriaModel $criteria)
    {
        foreach($criteria->find() as $location)
        {
            craft()->locations_location->deleteLocation($location);
        }

        $this->setMessage($this->getParams()->successMessage);

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc BaseElementAction::defineParams()
     *
     * @return array
     */
    protected function defineParams()
    {
        return array(
            'confirmationMessage' => array(AttributeType::String),
            'successMessage'      => array(AttributeType::String),
        );
    }
}
