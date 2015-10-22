<?php

namespace RootXS;

use Exception;

class SudoBiblePassage {

	/**
	 * The original SudoBible object, for communicating back.
	 *
	 * @var SudoBible $oBible
	 */
	protected $oBible = null;

	/**
	 * The verses included in this passage.
	 *
	 * @var array $aVerses
	 */
	protected $aVerses = [];

	/**
	 * Do we have verses?
	 *
	 * @var bool $bEmpty
	 */
	protected $bEmpty = false;

	/**
	 * Flag to identify a single chapter.
	 *
	 * @var bool $bIsFullChapter
	 */
	protected $bIsFullChapter = false;

	/**
	 * Flag to determine whether to print verse numbers in the passage.
	 *
	 * @var bool $bNumberVerses
	 */
	protected $bNumberVerses = false;

	/**
	 * Flag to determine whether to use HTML or plain text.
	 *
	 * @var bool $bHTML
	 */
	protected $bHTML = false;

	/**
	 * Constructor - set up the passage object.
	 *
	 * @param array $aVerses An array of Bible verses from sudo_bible_verses
	 */
	public function __construct(array $aVerses, SudoBible $oBible)
	{
		$this->oBible = $oBible;

		if (count($aVerses) === 0) {
			$this->setEmpty();
		} elseif ($aVerses[0]->verse && $aVerses[0]->text && $aVerses[0]->book_name) {
			$this->aVerses = $aVerses;
		} else {
			throw new Exception('The first parameter for ' . __METHOD__
				. ' must be an array of records from sudo_bible_verses table'
				. ' (joined with sudo_bible_books).');
		}
	}

	/**
	 * Set the "empty" flag.
	 *
	 * @param bool $bFlag
	 * @return $this
	 */
	protected function setEmpty($bFlag = true)
	{
		$this->bEmpty = $bFlag;
		return $this;
	}

	/**
	 * Check for verses.
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		return $this->bEmpty;
	}

	/**
	 * Set the "full-chapter" flag.
	 *
	 * @param bool $bFlag
	 * @return $this
	 */
	public function setFullChapter($bFlag = true)
	{
		$this->bIsFullChapter = $bFlag;
		return $this;
	}

	/**
	 * Check if this passage contains one complete chapter.
	 *
	 * @return bool
	 */
	public function isFullChapter()
	{
		return $this->bIsFullChapter;
	}

	/**
	 * Set the "number-verses" flag.
	 *
	 * @param bool $bFlag
	 * @return $this
	 */
	public function numberVerses($bFlag = true)
	{
		$this->bNumberVerses = $bFlag;
		return $this;
	}

	/**
	 * Set the "HTML" flag.
	 *
	 * @param bool $bFlag
	 * @return $this
	 */
	public function useHTML($bFlag = true)
	{
		$this->bHTML = $bFlag;
		return $this;
	}

	/**
	 * Retrieve the first verse in the current set.
	 *
	 * @return stdClass
	 */
	protected function getFirstVerse()
	{
		return $this->aVerses[0];
	}

	/**
	 * Retrieve the last verse in the current set.
	 *
	 * @return stdClass
	 */
	protected function getLastVerse()
	{
		return $this->aVerses[count($this->aVerses) - 1];
	}

	/**
	 * Get the reference location, like "John 3:16"
	 *
	 * @param bool $bAbbreviate Use the book's abbreviation instead of full name?
	 * @return string
	 */
	public function getReference($bAbbreviate = false)
	{
		$oFirstVerse = $this->getFirstVerse();
		$oLastVerse = $this->getLastVerse();

		// John 3
		$strRef = ($bAbbreviate ? $oFirstVerse->book_abbr : $oFirstVerse->book_name)
			. ' ' . $oFirstVerse->chapter;

		if (!$this->isFullChapter()) {

			// John 3:16
			$strRef .= ':' . $oFirstVerse->verse;

			if (count($this->aVerses) > 1) {

				if ($oFirstVerse->book_id == $oLastVerse->book_id && $oFirstVerse->chapter == $oLastVerse->chapter) {

					// John 3:16-17
					$strRef .= '-' . $oLastVerse->verse;

				} else {

					$strRef .= ' - ';

					// John 3:16 - Acts 1:1
					if ($oFirstVerse->book_id !== $oLastVerse->book_id)
						$strRef .= ($bAbbreviate ? $oLastVerse->book_abbr : $oLastVerse->book_name) . ' ';

					// John 3:16 - 4:2
					$strRef .= $oLastVerse->chapter . ':' . $oLastVerse->verse;
				}

			}
		}

		// Add styling
		$strRef = $this->bHTML
			? '<i>(' . $strRef . ')</i>'
			: '(' . $strRef .')';

		return $strRef;
	}

	/**
	 * Return the next Bible verse after the current passage.
	 *
	 * @return SudoBiblePassage
	 */
	public function nextVerse()
	{
		if ($this->isEmpty())
			throw new Exception(__METHOD__ . ': Can\'t find next - current passage is invalid.');
		$oLastVerse = $this->getLastVerse();

		return $this->oBible->nextVerse(
			$oLastVerse->book_id,
			$oLastVerse->chapter,
			$oLastVerse->verse
		);
	}

	/**
	 * Convert the verses to one string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$strOutput = 'No verses selected.';

		if (!$this->isEmpty()) {
			$strOutput = '';

			foreach ($this->aVerses as $oVerse) {
				if ($this->bNumberVerses) {
					$strOutput .= $this->bHTML
						? '<sup>' . $oVerse->verse . '</sup>'
						: $oVerse->verse;
					$strOutput .= ' ';
				}
				$strOutput .= $oVerse->text . ' ';
			}

			$strOutput .= $this->getReference();
		}

		return $strOutput;
	}
}
