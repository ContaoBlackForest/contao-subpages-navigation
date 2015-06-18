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

$GLOBALS['TL_DCA']['tl_module']['metapalettes']['subpages_navigation'] = array(

    'title' => array('name', 'type'),
    'config' => array('showProtected',),
    'image' => array(':hide','useNavImage'),
    'template' => array(':hide', 'navigationTpl', 'customTpl'),
    'protected' => array(':hide', 'protected'),
    'expert' => array(':hide', 'guests', 'cssID', 'space'),
);

$GLOBALS['TL_DCA']['tl_module']['metasubpalettes']['useNavImage'] = array(
    'imgSize'
);

$GLOBALS['TL_DCA']['tl_module']['fields']['useNavImage'] = array(

    'label' => &$GLOBALS['TL_LANG']['tl_module']['useNavImage'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'w50 m12','submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''",
);