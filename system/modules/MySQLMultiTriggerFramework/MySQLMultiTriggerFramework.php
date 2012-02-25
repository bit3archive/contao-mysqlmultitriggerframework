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
 * Class MySQLMultiTriggerFramework
 *
 * @copyright  InfinitySoft 2012
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    MySQLMultiTriggerFramework
 */
class MySQLMultiTriggerFramework extends Backend
{
	public function hookSqlCompileCommands($return)
	{
		// build new triggers array
		$arrTrigger = array();

		// collect triggers sql
		if (isset($GLOBALS['TL_TRIGGER']) && is_array($GLOBALS['TL_TRIGGER'])) {
			foreach ($GLOBALS['TL_TRIGGER'] as $strTable => $arrTimes) {
				foreach ($arrTimes as $strTime => $arrEvents) {
					foreach ($arrEvents as $strEvent => $arrSql) {
						$strTrigger = 'CMT_' . $strTable . '_' . strtoupper($strTime[0]) . strtoupper($strEvent[0]);

						foreach ($arrSql as &$strSql) {
							$strSql = implode("\n", array_map('trim', explode("\n", $strSql)));
							if (substr($strSql, -1) != ';') {
								$strSql .= ';';
							}
						}

						$arrTrigger[$strTrigger]        = new stdClass();
						$arrTrigger[$strTrigger]->table = $strTable;
						$arrTrigger[$strTrigger]->time  = strtoupper($strTime);
						$arrTrigger[$strTrigger]->event = strtoupper($strEvent);
						$arrTrigger[$strTrigger]->sql   = implode("\n", $arrSql);
						$arrTrigger[$strTrigger]->hash  = substr(md5($arrTrigger[$strTrigger]->sql), 0, 16);
					}
				}
			}
		}

		// fetch defined triggers
		$arrExistingTriggers = $this->Database
			->execute('SHOW TRIGGERS IN `' . $GLOBALS['TL_CONFIG']['dbDatabase'] . '`')
			->fetchEach('Trigger');

		// triggers to drop
		$arrDropTriggers = array();

		// check existing triggers that needs to be droped
		foreach ($arrExistingTriggers as $strTrigger) {
			if (preg_match('#^(?<trigger>CMT_(?<table>.*)_(?<time>.)(?<event>.))_(?<hash>.*)$#', $strTrigger, $arrMatch)) {
				if (!isset($arrTrigger[$arrMatch['trigger']]) || $arrTrigger[$arrMatch['trigger']]->hash != $arrMatch['hash']) {
					$arrDropTriggers[] = $arrMatch[0];
				}
			}
		}

		// triggers to create
		$arrCreateTriggers = array();

		// check triggers that needs to be created
		foreach ($arrTrigger as $strTrigger => $objTrigger) {
			$strTriggerName = $strTrigger . '_' . $objTrigger->hash;
			if (!in_array($strTriggerName, $arrExistingTriggers)) {
				$arrCreateTriggers[$strTriggerName] = $objTrigger;
			}
		}

		// add drop trigger
		foreach ($arrDropTriggers as $strTriggerName) {
			$return['ALTER_ADD'][] = 'DROP TRIGGER `' . $strTriggerName . '`';

			// HOOK: add custom logic
			if (isset($GLOBALS['TL_HOOKS']['mysqlMultiTriggerDrop']) && is_array($GLOBALS['TL_HOOKS']['mysqlMultiTriggerDrop']))
			{
				foreach ($GLOBALS['TL_HOOKS']['mysqlMultiTriggerDrop'] as $callback)
				{
					$this->import($callback[0]);
					$return = $this->$callback[0]->$callback[1]($strTriggerName, $return);
				}
			}
		}

		// add create trigger
		foreach ($arrCreateTriggers as $strTriggerName => $objTrigger) {
			$return['ALTER_ADD'][] = sprintf('CREATE TRIGGER `%s` %s %s ON %s FOR EACH ROW BEGIN
%s
END',
				$strTriggerName,
				$objTrigger->time,
				$objTrigger->event,
				$objTrigger->table,
				$objTrigger->sql);

			// HOOK: add custom logic
			if (isset($GLOBALS['TL_HOOKS']['mysqlMultiTriggerCreate']) && is_array($GLOBALS['TL_HOOKS']['mysqlMultiTriggerCreate']))
			{
				foreach ($GLOBALS['TL_HOOKS']['mysqlMultiTriggerCreate'] as $callback)
				{
					$this->import($callback[0]);
					$return = $this->$callback[0]->$callback[1]($strTriggerName, $objTrigger, $return);
				}
			}
		}

		return $return;
	}
}
