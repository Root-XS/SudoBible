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
		'db_pass' => '',
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
	 * Defines which folder under queries/ is used to create the database.
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
		$this->iTranslation = is_numeric($mTranslation)
			? $mTranslation : $this->getIdFor('translation', $mTranslation);
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
	 * Drop tables.
	 */
	public function uninstall()
	{
		$this->queryFiles('drop');
	}

	/**
	 * Drop & re-create tables, then insert data.
	 */
	public function reinstall()
	{
		$this->uninstall();
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

		$strPath = 'queries/' . $this->dbType . '/' . $strAction;
		foreach (scandir($strPath) as $strFilename) {

			// Only run SQL files (eliminates ., .., and subdirs)
			// Using in_array() in case DBs other than SQL are supported in the future.
			if (in_array(substr($strFilename, -4), ['.sql']) {
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
	 * Fluent alias to return a single verse.
	 *
	 * @param int|string $mBook Name or ID of the book.
	 * @param int $iChapter Chapter number.
	 * @param int $iVerse Verse number.
	 * @return array
	 */
	public function verse($mBook, $iChapter, $iVerse)
	{
		$aVerses = $this->ref($mBook, $iChapter, $iVerse);
		return $aVerses[0];
	}

	/**
	 * Fluent alias to return an entire chapter.
	 *
	 * @param int|string $mBook Name or ID of the book.
	 * @param int $iChapter Chapter number.
	 * @return array
	 */
	public function chapter($mBook, $iChapter)
	{
		return $this->ref($mBook, $iChapter);
	}

	/**
	 * Return all verses on a single topic.
	 *
	 * @param int|string $mTopic The topic ID or name.
	 * @return array
	 */
	public function topic($mTopic)
	{
		if (is_string($mTopic))
			$mTopic = $this->getIdFor('topic', $mTopic);

		$q = 'SELECT tv.*, verses.`text`, books.`name` AS book_name, books.`abbr` AS book_abbr, books.`ot`, books.`nt`'
			. ' FROM `sudo_bible_topic_verses` AS tv'
			. ' LEFT JOIN `sudo_bible_verses` AS verses ON verses.`book_id` = tv.`book_id`'
				. ' AND verses.`chapter` = tv.`chapter` AND verses.`verse` = tv.`verse`'
			. ' LEFT JOIN `sudo_bible_books` AS books ON books.`id` = tv.`book_id`'
			. ' WHERE verses.`translation_id` = ? AND tv.`topic_id` = ?'
			. ' ORDER BY `book_id`, `chapter_id`, `verse_id`';
		return $this->runPreparedQuery($q, [$this->iTranslation, $mTopic]);
	}

	/**
	 * Return a verse, passage, or chapter.
	 *
	 * @param int|string $mBook Name or ID of the book.
	 * @param int $iChapter Chapter number.
	 * @param int $iVerse Verse number.
	 * @param int|string $mEnd1 Ending verse, chapter, or book (name or ID).
	 * @param int $iEnd2 Ending verse or chapter.
	 * @param int $iEnd3 Ending verse.
	 * @throws Exception
	 * @return array
	 */
	public function ref($mBook, $iChapter, $iVerse = null, $mEnd1 = null, $iEnd2 = null, $iEnd3 = null)
	{
		// Sanitize input
		if (is_string($mBook))
			$mBook = $this->getIdFor('book', $mBook);
		if (is_string($mEnd1))
			$mEnd1 = $this->getIdFor('book', $mEnd1);

		// Validate input
		if ($iEnd3) {
			// Multiple verses, ending spills into the next book
			// @todo Is this necessary? Spanning books might be overkill!
			if ($mEnd1 > $mBook)
				throw new Exception('2nd book given comes before 1st.');
		} elseif ($iEnd2) {
			// Multiple verses, ending spills into the next chapter
			if ($mEnd1 > $iChapter)
				throw new Exception('2nd chapter given comes before 1st.');
		} elseif ($mEnd1) {
			// Multiple verses
			if ($mEnd1 > $iVerse)
				throw new Exception('2nd verse given comes before 1st.');

			// Must give a starting verse
			if (!$iVerse)
				throw new Exception('Missing starting verse.');
		}

		// Begin query
		$q = 'SELECT verses.*, books.`name` AS book_name, books.`abbr` AS book_abbr, books.`ot`, books.`nt`'
			. ' FROM `sudo_bible_verses` AS verses'
			. ' LEFT JOIN `sudo_bible_books` AS books ON books.`id` = verses.`book_id`'
			. ' WHERE `translation_id` = ?';
		$aParams = [$this->iTranslation];

		// Multiple verses, ending spills into the next book
		if ($iEnd3) {
			$q .= ' AND (';

			// End of the starting book
			$q .= ' (`book_id` = ? AND ((`chapter_id` = ? AND `verse` >= ?) OR `chapter_id` > ?))';
			$aParams = array_merge($aParams, [$mBook, $iChapter, $iVerse, $iChapter]);

			// Any books in between
			$q .= ' OR (`book_id` > ? AND `book_id` < ?)';
			$aParams = array_merge($aParams, [$mBook, $mEnd1]);

			// Beginning of the finishing book
			$q .= ' OR (`book_id` = ? AND (`chapter_id` < ? OR (`chapter_id` = ? AND `verse` <= ?)))';
			$aParams = array_merge($aParams, [$mEnd1, $iEnd2, $iEnd2, $iEnd3]);

			$q .= ' )';

		// Verses are contained in a single book
		} else {
			$q .= ' AND `book_id` = ?';
			$aParams[] = $mBook;
		}

		// Multiple verses, ending spills into the next chapter
		if ($iEnd2 && !$iEnd3) {
			$q .= ' AND (';

			// End of the starting chapter
			$q .= ' (`chapter_id` = ? AND `verse` >= ?)';
			$aParams = array_merge($aParams, [$iChapter, $iVerse]);

			// Any chapters in between
			$q .= ' OR (`chapter_id` > ? AND `chapter_id` < ?)';
			$aParams = array_merge($aParams, [$iChapter, $mEnd1]);

			// Beginning of the finishing chapter
			$q .= ' OR (`chapter_id` = ? AND `verse` <= ?)';
			$aParams = array_merge($aParams, [$mEnd1, $iEnd2])

			$q .= ')';

		// Verses are contained in a single chapter
		} elseif (!$iEnd3) {
			$q .= ' AND `chapter` = ?';
			$aParams[] = $iChapter;
		}

		// Multiple verses
		if ($mEnd1 && !$iEnd2 && !$iEnd3) {
			$q .= ' AND (`verse` >= ? AND `verse` <= ?)';
			$aParams = array_merge($aParams, [$iVerse, $mEnd1]);

		// A single verse
		} else {
			$q .= ' AND `verse` = ?';
			$aParams[] = $iVerse;
		}

		$q .= ' ORDER BY `book_id`, `chapter_id`, `verse_id`';

		return $this->runPreparedQuery($q, $aParams);
	}

	/**
	 * Get an ID, given the name.
	 *
	 * For now, we're using this instead of a JOIN in the main query
	 * so we can throw a useful error.
	 *
	 * @param string $strType translation, book, or topic
	 * @param string $strName Name of the translation or book.
	 * @throws Exception
	 * @return int
	 */
	protected function getIdFor($strType, $strName)
	{
		// Validate input
		if (!in_array($strType, ['book', 'translation', 'topic']))
			throw new Exception('Invalid entity type "' . $strType . '"');

		// Query the db to get the ID
		$strTableName = 'sudo_bible_' . $strType . 's';
		$aResult = $this->runPreparedQuery(
			'SELECT `id` FROM `' . $strTableName . '` WHERE `name` LIKE ?',
			[$strName]
		);
		$iId = $aResult[0]['id'];

		// Validate result
		if (!is_numeric($iId))
			throw new Exception('Invalid ' . $strType . ' "' .  $strName . '" given.');

		return $iId;
	}

	/**
	 * Run a "mysqli" prepared query, given the query string and params.
	 *
	 * @param string $strSql Query string with "?" placeholders.
	 * @param array $aParams Array of values to fill in for the placeholders.
	 * @return array Result set.
	 */
	protected function runPreparedQuery($strSql, array $aParams = [])
	{
		// Build data type string for bind_param
		$strDataTypes = '';
		foreach ($aParams as $mValue)
			$strDataTypes .= is_int($mValue) ? 'i' : 's';
		array_unshift($aParams, $strDataTypes);

		// Prepare & execute
		$stmt = $this->db->prepare($strSql);
		call_user_func_array(array($stmt, 'bind_param'), $aParams);
		$stmt->execute();
		$oResult = $stmt->get_result();

		// Build return array
		$aReturn = [];
		$i = 0;
		while ($oRow = $result->fetch_object())
			$aReturn[] = $oRow;
		$stmt->close();

		return $aReturn;
	}

}
