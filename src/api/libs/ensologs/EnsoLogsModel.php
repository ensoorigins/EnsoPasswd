<?php

class EnsoLogsModel
{

	private static $ENSO_LOGS_VERSION = "5.0.0";

	/**
	 * System is unusable.
	 */
	public static $EMERGENCY = 0;		// System is unusable.
	/**
	 * Action must be taken immediately.
	 */
	public static $ALERT = 1;			// Action must be taken immediately.
	/**
	 * Critical conditions.
	 */
	public static $CRITICAL = 2;			// Critical conditions.
	/**
	 * Error conditions.
	 */
	public static $ERROR = 3;			// Error conditions.
	/**
	 * Warning conditions.
	 */
	public static $WARNING = 4;			// Warning conditions.
	/**
	 * Normal but significant condition.
	 */
	public static $NOTICE = 5;			// Normal but significant condition.
	/**
	 * Informational messages.
	 */
	public static $INFORMATIONAL = 6;	// Informational messages.
	/**
	 * Debug-level messages.
	 */
	public static $DEBUG = 7;			// Debug-level messages.
	
	//add log
	public static function addEnsoLog($ownerid, $action, $severity, $facility)
	{
		$sql = "INSERT INTO enso_logs (inserted_timestamp, severity_level, facility, ownerid, action) " .
			"VALUES (:inserted_timestamp, :severity_level, :facility, :ownerid, :action)";

		$values = array();
		$values[':inserted_timestamp'] = EnsoShared::now();
		$values[':severity_level'] = $severity;
		$values[':facility'] = $facility;
		$values[':ownerid'] = $ownerid;
		$values[':action'] = $action;

		try {
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute($values);

			$row = $db->fetchAll();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public static function getAvailableFacilities()
	{
		$sql = "SELECT DISTINCT(facility) " .
			"FROM enso_logs";

		try {
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute();

			return $db->fetchAll(PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			return false;
		}
	}

	public static function getLogs($facility, $startTime, $endTime, $severity, $userSearch, $startIndex, $advance, $searchString, $caseSensitive = false)
	{
		$sql = "SELECT * " .
			"FROM enso_logs " .
			"WHERE facility = :facility " .
			"AND severity_level = :severity " .
			"AND ownerid = :ownerid ";

		$values = array();

		$values[':search'] = $searchString;

		if ($caseSensitive === true)
			$sql .= "AND action LIKE :search ";
		else
			$sql .= "AND LCASE(action) LIKE LCASE(:search) ";

		if ($facility !== "")
			$values[':facility'] = $facility;
		else
			$sql = str_replace(':facility', 'facility', $sql);

		if ($severity !== "")
			$values[':severity'] = $severity;
		else
			$sql = str_replace(':severity', 'severity_level', $sql);

		if ($userSearch !== "")
			$values[':ownerid'] = $userSearch;
		else
			$sql = str_replace(':ownerid', 'ownerid', $sql);

		if ($startTime != null && $endTime != null) {
			$sql .= " AND inserted_timestamp BETWEEN :startTime AND :endTime ";
			$values[':startTime'] = $startTime;
			$values[':endTime'] = $endTime;
		}

		$sql .= " ORDER BY inserted_timestamp DESC ";

		if (!empty($advance) && !empty($startIndex)) {

			$sql .= " LIMIT :size OFFSET :start ";

			$values[':size'] = intval($advance);
			$values[':start'] = intval($startIndex);
		}

		EnsoDebug::var_error_log($values);
		EnsoDebug::var_error_log($sql);

		try {
			$db = new EnsoDB();
			$db->prepare($sql);
			$query = $db->getQuery();

			foreach ($values as $key => $value) {
				if ($key === ":size" || $key === ":start") {
					$query->bindValue($key, $value, PDO::PARAM_INT);
				} else {
					$query->bindValue($key, $value, PDO::PARAM_STR);
				}
			}

			$query->execute();

			return $query->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			return false;
		}
	}

	public static function getUsedSeverityLevels()
	{
		$sql = "SELECT DISTINCT(severity_level) " .
			"FROM enso_logs";

		try {
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute();

			return $db->fetchAll(PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			return false;
		}
	}

	public static function getUsersPresentInLogs()
	{
		$sql = "SELECT DISTINCT(ownerid) " .
			"FROM enso_logs";

		try {
			$db = new EnsoDB();
			$db->prepare($sql);
			$db->execute();

			return $db->fetchAll(PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			return false;
		}
	}
}
?>