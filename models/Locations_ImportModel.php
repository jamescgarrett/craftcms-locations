<?php
/**
 * Locations plugin for Craft CMS
 *
 * Locations_Import Model
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

class Locations_ImportModel extends BaseModel
{
    
    /**
     * Filetypes.
     */
    const TypeCSV = 'text/csv';
    const TypeCSVWin = 'text/comma-separated-values';
    const TypeCSVFF = 'text/x-comma-separated-values';
    const TypeCSVIE = 'text/plain';
    const TypeCSVApp = 'application/csv';
    const TypeCSVExc = 'application/excel';
    const TypeCSVOff = 'application/vnd.ms-excel';
    const TypeCSVOff2 = 'application/vnd.msexcel';
    const TypeCSVOth = 'application/octet-stream';

    /**
     * Statuses.
     */
    const StatusStarted = 'started';
    const StatusFinished = 'finished';
    const StatusReverted = 'reverted';

    /**
     * Delimiters.
     */
    const DelimiterSemicolon = ';';
    const DelimiterComma = ',';
    const DelimiterPipe = '|';

    /**
     * Defines this model's attributes.
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'filetype' => array(AttributeType::Enum,
                'required' => true,
                'label' => Craft::t('Filetype'),
                'values' => array(
                    self::TypeCSV,
                    self::TypeCSVWin,
                    self::TypeCSVFF,
                    self::TypeCSVIE,
                    self::TypeCSVApp,
                    self::TypeCSVExc,
                    self::TypeCSVOff,
                    self::TypeCSVOff2,
                    self::TypeCSVOth,
                ),
            ),
        );
    }
}