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

array_insert(
    $GLOBALS['FE_MOD']['navigationMenu'],
    count($GLOBALS['FE_MOD']['navigationMenu']),
    array(
        'subpages_navigation' => '\ContaoBlackForest\SubPagesNavigation\Module\ModuleSubPagesNavigation'
    )
);