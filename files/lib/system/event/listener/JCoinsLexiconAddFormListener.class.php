<?php
namespace lexicon\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\NamedUserException;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;
use wcf\system\WCF;

/**
 * Checks whether the user has enough JCoins to add a new lexicon entry.
 *
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		de.wcflabs.lexicon.jcoins
 */
class JCoinsLexiconAddFormListener implements IParameterizedEventListener {
	/**
	 * @inheritdoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_JCOINS || JCOINS_ALLOW_NEGATIVE) return;
		if (!WCF::getSession()->getPermission('user.jcoins.canEarn') || !WCF::getSession()->getPermission('user.jcoins.canUse')) return;
		
		$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('de.wcflabs.jcoins.statement.lexicon.entry');
		
		if ($statement->calculateAmount() < 0 && ($statement->calculateAmount() * -1) > WCF::getUser()->jCoinsAmount) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.jcoins.amount.tooLow'));
		}
	}
}