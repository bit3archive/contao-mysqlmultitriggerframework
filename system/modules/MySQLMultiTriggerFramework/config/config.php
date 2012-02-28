<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * MySQL Multi Trigger Framework
 * Copyright (C) 2010,2011,2012 Tristan Lins
 *
 * Extension for:
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  InfinitySoft 2012
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    MySQLMultiTriggerFramework
 * @license    LGPL
 * @filesource
 */


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['sqlCompileCommands'][]    = array('MySQLMultiTriggerFramework', 'hookSqlCompileCommands');


/**
 * Procedure examples
 */
// $GLOBALS['TL_PROCEDURE']['<name>'] = '<sql>';


/**
 * Trigger examples
 */
// $GLOBALS['TL_TRIGGER']['<table>']['<time>']['<event>'] = '<sql>';
// $GLOBALS['TL_TRIGGER']['tl_page']['before']['insert'] = 'callProcedure(OLD.id);';
