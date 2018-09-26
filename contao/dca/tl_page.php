<?php
/**
 * Subpages Navigation Module
 *
 * PHP version 5
 *
 * @copyright  ContaoBlackForest <https://github.com/ContaoBlackforest/>
 * @author     Dominik Tomasi <dominik.tomasi@gmail.com>
 * @author     Sven Baumann <baumannsv@gmail.com>
 * @package    contao-subpages-navigation
 * @license    LGPL
 * @filesource
 */


foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $name => $palette) {

    if ($name == '__selector__') {
        continue;
    }

    \Bit3\Contao\MetaPalettes\MetaPalettes::appendAfter('tl_page', $name , 'title', array(
            'nav_image' => array('navImage')
        )
    );
}

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['navImage'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_page']['navImage'],
    'inputType' => 'fileTree',
    'exclude' => true,
    'eval' => array('fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'jpg,jpeg,gif,png'),
    'sql' => "blob NULL",
);
