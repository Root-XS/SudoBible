<?php

namespace RootXS;

use Exception;

class SudoBible {

	/**
	 * Constants.
	 *
	 * @var DB_CREDS
	 */
	const DB_CREDS = [
		'db_host' => 'localhost',
		'db_user' => 'sudobible',
		'db_pass' => 'sudobible',
		'db_name' => 'sudobible',
		'db_port' => 3306,
	];

	/**
	 * DB connection.
	 *
	 * @var mysqli $db
	 */
	protected $db;

	/**
	 * DB type.
	 *
	 * @var $dbType
	 */
	protected $dbType = 'mysql';

	/**
	 * Translation.
	 *
	 * @var $iTranslation
	 */
	protected $iTranslation = 1;

	/**
	 * Init Sudo Bible.
	 *
	 * @param array $aOptions
	 */
	public function __construct(array $aOptions)
	{
		$this->dbConnect($Options);
		if (isset($aOptions['translation']))
			$this->setTranslation($aOptions['translation']);
	}

	/**
	 * Connect to the DB.
	 *
	 * @param array $aOptions
	 * @throws Exception
	 */
	protected dbConnect(array $aOptions)
	{
		$aOptions = array_merge(Bible::DB_CREDS, $aOptions);
		$this->db = new mysqli(
			$aOptions['db_host'],
			$aOptions['db_user'],
			$aOptions['db_pass'],
			$aOptions['db_name'],
			$aOptions['db_port']
		);
		if ($this->db->connect_errno)
			throw new Exception('DB connectin failed - ' . $mysqli->connect_error);
	}

	/**
	 * Set the translation preference.
	 *
	 * @param int|string $mTranslation
	 */
	public function setTranslation($mTranslation)
	{
		if (is_numeric($mTranslation)) {
			$this->iTranslation = $mTranslation;
		} elseif (is_string($mTranslation)) {
			// query the db to get the ID
			$stmt = $this->db->prepare('SELECT `id` FROM `sudo_bible_translations` WHERE `name` LIKE ?');
			$stmt->bind_param('s', $mTranslation);
			$stmt->execute();
			$stmt->bind_result($iTranslation);
			$stmt->fetch();
			$stmt->close();

			// check result
			if (is_numeric($iTranslation))
				$this->iTranslation = $iTranslation;
			else
				throw new Exception('Invalid translation "' .  $mTranslation . '" given.');
		}
	}

	/**
	 * Create tables & insert data.
	 */
	public function install()
	{
		$this->queryFiles('create');
		$this->queryFiles('insert');
	}

	/**
	 * Drop & re-create tables, then insert data.
	 */
	public function reinstall()
	{
		$this->queryFiles('drop');
		$this->install();
	}

	/**
	 * Run the query files in a given directory.
	 *
	 * @param string $strAction create, insert, or drop
	 * @throws Exception
	 */
	protected function queryFiles($strAction)
	{
		if (!in_array($strAction, ['create', 'insert', 'drop']))
			throw new Exception('Invalid parameter "' . $strAction . '" sent to SudoBible::queryFiles()');

		$strPath = 'sql/' . $this->dbType . '/' . $strAction;
		foreach (scandir($strPath) as $strFilename) {

			// Only run SQL files (eliminates ., .., and subdirs)
			if (substr($strFilename, -4) === '.sql') {
				$mResult = $this->db->query(file_get_contents($strPath . '/' . $strFilename));

				// Check success if creating or dropping
				if ('insert' !== $strAction && true !== $mResult) {
					throw new Exception('Unable to ' . $strAction . ' table in ' . $strFilename
						. ' - please ensure your DB user has the right permissions.');
				}
			}
		}
	}

	/**
	 *
	 */
	public function verse()
	{

	}

	/**
	 *
	 */
	public function topic()
	{

	}

	/**
	 *
	 */
	public function chapter()
	{

	}

}
