<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_ImportTask
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

class Locations_ImportTask extends BaseTask
{
   /**
     * Defines the settings.
     *
     * @access protected
     * @return array
     */
    protected function defineSettings()
    {
        return array(
            'file' => AttributeType::Name,
            'rows' => AttributeType::Number,
            'map' => AttributeType::Mixed,
            'behavior' => AttributeType::String
        );
    }

    /**
     * Returns the default description for this task.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Importing Locations';
    }

    /**
     * Gets the total number of steps for this task.
     *
     * @return int
     */
    public function getTotalSteps()
    {
        $settings = $this->getSettings();
        craft()->templateCache->deleteCachesByElementType('Locations_Location');
        return $settings->rows;
    }

    /**
     * Runs a task step.
     *
     * @param int $step
     * @return bool
     */
    public function runStep($step)
    {
        $settings = $this->getSettings();
        $data = craft()->locations_import->data($settings->file);

        if (!$step) {
            $event = new Event($this, array('settings' => $settings));
            craft()->locations_import->onImportStart($event);
        }
        if (isset($data[$step])) {
            craft()->locations_import->row($step, $data[$step], $settings);
        }
        if ($step == ($settings->rows - 1)) {
            $event = new Event($this, array('settings' => $settings));
            craft()->locations_import->onImportFinish($event);
        }
        return true;
    }
}
