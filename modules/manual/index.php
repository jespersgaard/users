<?php

/**
 * @package StartupAPI
 * @subpackage Subscriptions
 */

/**
 * Manual payment engine used for consierge subscription
 */
class ManualPaymentEngine extends PaymentEngine {

	/**
	 * @var boolean Singleton flag
	 */
	private static $loaded = false;

	public function __construct() {

		$this->slug = 'manual';
		if (!self::$loaded) {
			parent::__construct();
			self::$loaded = true;
		}
	}

	public static function getModulesTitle() {
		return "Manual Payment Processing";
	}

	public static function getModulesDescription() {
		return "<p>Manual payment engine used for invoicing or consierge service subscriptions</p>";
	}

	public function getDescription() {
		return self::getModulesDescription();
	}

	/**
	 * Called when subscription changes
	 *
	 * @param int $account_id Account ID
	 * @param string $plan_slug Plan slug
	 * @param string $schedule_slug Payment schedule slug
	 *
	 * @return true
	 */
	public function changeSubscription($account_id, $plan_slug, $schedule_slug) {

		// Okay
		return TRUE;
	}

	/**
	 * Records transaction details
	 *
	 * Following keys are accepted in $details array:
	 * - operator_id - User ID of operator who entered the transaction
	 * - funds_source - Source of funds, e.g. cash or invoice paid
	 * - comment
	 *
	 * @param int $transaction_id Transaction ID
	 * @param mixed[] $details Transaction details
	 *
	 * @return boolean True if transaction details were successfully recorded
	 *
	 * @throws DBException
	 */
	public function storeTransactionDetails($transaction_id, $details) {

		if (is_null($transaction_id)) {
			return FALSE;
		}

		// Extract data from array
		foreach (array('operator_id', 'funds_source', 'comment') as $i) {
			$$i = isset($details[$i]) ? $details[$i] : NULL;
		}

		$db = UserConfig::getDB();

		if (!($stmt = $db->prepare('INSERT INTO ' . UserConfig::$mysql_prefix .
				'transaction_details_' . $this->getSlug() .
				' (transaction_id, operator_id, funds_source, comment) VALUES(?,?,?,?)'))) {
			throw new DBPrepareStmtException($db);
		}

		if (!$stmt->bind_param('iiss', $transaction_id, $operator_id, $funds_source, $comment)) {
			throw new DBBindParamException($db, $stmt);
		}

		if (!$stmt->execute()) {
			throw new DBExecuteStmtException($db, $stmt);
		}

		return TRUE;
	}

	/**
	 * Retrieves transaction details
	 *
	 * @param int $transaction_id Transaction ID
	 *
	 * @return mixed[]|false Retrieves array of transaction details or false if none available
	 *
	 * @throws DBException
	 */
	public function expandTransactionDetails($transaction_id) {
		if (is_null($transaction_id)) {
			return FALSE;
		}

		$db = UserConfig::getDB();

		if (!($stmt = $db->prepare('SELECT operator_id, funds_source, comment FROM ' .
				UserConfig::$mysql_prefix . 'transaction_details_' . $this->getSlug() .
				' WHERE transaction_id = ?'))) {
			throw new DBPrepareStmtException($db);
		}

		if (!$stmt->bind_param('i', $transaction_id)) {
			throw new DBBindParamException($db, $stmt);
		}

		if (!$stmt->execute()) {
			throw new DBExecuteStmtException($db, $stmt);
		}

		if (!$stmt->bind_result($operator_id, $funds_source, $comment)) {
			throw new DBBindResultException($db, $stmt);
		}

		$details = array();
		if ($stmt->fetch() === TRUE) {
			foreach (array('operator_id', 'funds_source', 'comment') as $i) {
				$details[$i] = stripslashes($$i);
			}
			return $details;
		}

		return FALSE;
	}

	/**
	 * Returns HTML representing transaction details for this engine
	 *
	 * @param int $transaction_id Transaction ID
	 *
	 * @return string HTML rendering of transaction details
	 */
	public function renderTransactionLogDetails($transaction_id) {
		$details = $this->expandTransactionDetails($transaction_id);
		$operator = User::getUser($details['operator_id']);
		$name = is_null($operator) ? 'Unknown' : $operator->getName();
		$source = is_null($details['funds_source']) ? 'Unknown' : $details['funds_source'];
		$comment = is_null($details['comment']) ? '-' : $details['comment'];
		return "<div>Operator: <b>$name</b>, Source: <b>$source</b>, Comment: $comment</div>";
	}

}
