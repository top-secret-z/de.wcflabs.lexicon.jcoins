<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace lexicon\system\event\listener;

use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;
use wcf\system\WCF;

/**
 * JCoins listener for entries.
 */
class JCoinsLexiconListener implements IParameterizedEventListener
{
    /**
     * @inheritdoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!MODULE_JCOINS) {
            return;
        }

        switch ($eventObj->getActionName()) {
            case 'triggerPublication':
                foreach ($eventObj->getObjects() as $object) {
                    if (!$object->isDisabled && $object->userID) {
                        UserJCoinsStatementHandler::getInstance()->create('de.wcflabs.jcoins.statement.lexicon.entry', $object->getDecoratedObject());
                    }
                }
                break;

                // 'enable' calls triggerPublication

            case 'disable':
                foreach ($eventObj->getObjects() as $object) {
                    if ($object->userID) {
                        UserJCoinsStatementHandler::getInstance()->revoke('de.wcflabs.jcoins.statement.lexicon.entry', $object->getDecoratedObject());
                    }
                }
                break;

            case 'trash':    // Lexicon >= 7
                foreach ($eventObj->getObjects() as $object) {
                    if (!$object->isDisabled && !$object->isDeleted && $object->userID) {
                        UserJCoinsStatementHandler::getInstance()->revoke('de.wcflabs.jcoins.statement.lexicon.entry', $object->getDecoratedObject());
                    }
                }
                break;

            case 'delete':    // Lexicon < 7
                foreach ($eventObj->getObjects() as $object) {
                    if (!$object->isDisabled && !$object->isDeleted && $object->userID) {
                        UserJCoinsStatementHandler::getInstance()->revoke('de.wcflabs.jcoins.statement.lexicon.entry', $object->getDecoratedObject());
                    }
                }
                break;

            case 'restore':
                foreach ($eventObj->getObjects() as $object) {
                    if (!$object->isDisabled && $object->userID) {
                        UserJCoinsStatementHandler::getInstance()->create('de.wcflabs.jcoins.statement.lexicon.entry', $object->getDecoratedObject());
                    }
                }
                break;

            case 'update':
                foreach ($eventObj->getObjects() as $object) {
                    // user ...
                    if (!WCF::getUser()->userID) {
                        continue;
                    }

                    UserJCoinsStatementHandler::getInstance()->create('de.wcflabs.jcoins.statement.lexicon.version', $object->getDecoratedObject(), ['userID' => WCF::getUser()->userID]);
                }
                break;
        }
    }
}
