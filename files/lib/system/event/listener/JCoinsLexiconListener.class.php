<?php
namespace lexicon\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;
use wcf\system\WCF;

/**
 * JCoins listener for entries.
 *
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		de.wcflabs.lexicon.jcoins
 */
class JCoinsLexiconListener implements IParameterizedEventListener {
	/**
	 * @inheritdoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_JCOINS) return;
		
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
				
			case 'trash':	// Lexicon >= 7
				foreach ($eventObj->getObjects() as $object) {
					if (!$object->isDisabled && !$object->isDeleted && $object->userID) {
						UserJCoinsStatementHandler::getInstance()->revoke('de.wcflabs.jcoins.statement.lexicon.entry', $object->getDecoratedObject());
					}
				}
				break;
				
			case 'delete':	// Lexicon < 7
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
					if (!WCF::getUser()->userID) continue;
					
					UserJCoinsStatementHandler::getInstance()->create('de.wcflabs.jcoins.statement.lexicon.version', $object->getDecoratedObject(), ['userID' => WCF::getUser()->userID]);
				}
				break;
		}
	}
}
