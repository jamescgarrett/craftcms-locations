<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_ImportService
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

class Locations_ImportService extends BaseApplicationComponent
{

    /**
     * Cache import file data
     *
     * @var array
     */
    private $_data = array();

    /**
     * Read CSV columns.
     *
     * @param string $file
     *
     * @return array
     */
    public function columns($file)
    {
        // Open CSV file
        $data = $this->_open($file);

        // Return only column names
        return array_shift($data);
    }

    /**
     * Get CSV data.
     *
     * @param string $file
     *
     * @return array
     */
    public function data($file)
    {
        // Open CSV file
        $data = $this->_open($file);

        // Skip first row
        array_shift($data);

        // Return all data
        return $data;
    }

    /**
     * Special function that handles csv delimiter detection.
     *
     * @param string $file
     *
     * @return array
     */
    protected function _open($file)
    {
        if (!count($this->_data)) {

            // Turn asset into a file
            $asset = craft()->assets->getFileById($file);
            $source = $asset->getSource();
            $sourceType = $source->getSourceType();
            $file = $sourceType->getLocalCopy($asset);

            // Check if file exists in the first place
            if (file_exists($file)) {

                // Automatically detect line endings
                @ini_set('auto_detect_line_endings', true);

                // Open file into rows
                $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                // Detect delimiter from first row
                $delimiters = array();
                $delimiters[Locations_ImportModel::DelimiterSemicolon] = substr_count($lines[0], Locations_ImportModel::DelimiterSemicolon);
                $delimiters[Locations_ImportModel::DelimiterComma] = substr_count($lines[0], Locations_ImportModel::DelimiterComma);
                $delimiters[Locations_ImportModel::DelimiterPipe] = substr_count($lines[0], Locations_ImportModel::DelimiterPipe);

                // Sort by delimiter with most occurences
                arsort($delimiters, SORT_NUMERIC);

                // Give me the keys
                $delimiters = array_keys($delimiters);

                // Use first key -> this is the one with most occurences
                $delimiter = array_shift($delimiters);

                // Open file and parse csv rows
                $handle = fopen($file, 'r');
                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

                    // Add row to data array
                    $this->_data[] = $row;
                }
                fclose($handle);
            }
        }

        // Return data array
        return $this->_data;
    }

    /**
     * Import row.
     *
     * @param @param int   $row
     * @param array        $data
     * @param array|object $settings
     *
     * @throws Exception
     */
    public function row($row, array $data, $settings)
    {
        $fields = array_combine($settings['map'], $data);
        $model = new Locations_LocationModel();

        try {
            foreach ($fields as $key => $value)
            {
                switch ($key)
                {
                    case "longitude":
                        if (empty($value))
                        {
                            $mapCoords = craft()->locations_location->getMapDataFromLocation($model);
                            $model->$key = $mapCoords['longitude'];
                        }
                        else
                        {
                            $model->$key = $value;
                        }
                        break;
                    case "latitude":
                        if (empty($value))
                        {
                            $mapCoords = craft()->locations_location->getMapDataFromLocation($model);
                            $model->$key = $mapCoords['longitude'];
                        }
                        else
                        {
                            $model->$key = $value;
                        }
                        break;
                    default:
                        $model->$key = $value;
                        break;
                }

                if ($key == "name")
                {
                    $model->getContent()->title = $value;
                }

            }
        } catch (Exception $e) {
            LocationsPlugin::log(print_r($e->getMessage(), true));
        }

        if (!craft()->locations_location->saveLocation($model)) 
        {
           LocationsPlugin::log(print_r("Error saving location on import. Check if all required fields are available in the CSV", true));
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * Fires an "onImportStart" event.
     *
     * @param Event $event
     */
    public function onImportStart(Event $event)
    {
        $this->raiseEvent('onImportStart', $event);
    }

    /**
     * @codeCoverageIgnore
     *
     * Fires an "onImportFinish" event.
     *
     * @param Event $event
     */
    public function onImportFinish(Event $event)
    {
        $this->raiseEvent('onImportFinish', $event);
    }
    
}