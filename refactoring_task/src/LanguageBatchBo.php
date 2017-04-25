<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace Language;

use Applets\Applets;
use Applications\Applications;
use Interfaces\LanguageBatchInterface;
use Interfaces\LanguageFileInterface;
use LanguageFiles\AppletLanguageFile;
use LanguageFiles\ApplicationLanguageFile;

/**
 * Class LanguageBatchBo
 * @package Language
 */
class LanguageBatchBo implements LanguageBatchInterface
{
	/**
	 * @inheritdoc
	 *
	 * @throws \Exception
	 */
	public static function generateLanguageFiles()
	{
		$applications = (new Applications())->getTranslatedAppsList();
		foreach ($applications as $application) {
			self::generate(new ApplicationLanguageFile($application), $application->getLanguages());
		}
		return true;
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \Exception
	 */
	public static function generateAppletLanguageXmlFiles()
	{
		$applets = (new Applets)->getTranslatedAppletsList();
		foreach ($applets as $applet) {
			self::generate(new AppletLanguageFile($applet), $applet->getLanguages());
		}
		return true;
	}

	/**
	 * @param LanguageFileInterface $languageFile
	 * @param array $languageCodes
	 * @return bool
	 */
	protected static function generate(LanguageFileInterface $languageFile, array $languageCodes = [])
	{
		foreach ($languageCodes as $code) {
			$languageFile->setCode($code);
			if ($languageFile->generateFile()) {
				echo 'Successful: language cache file [' . $languageFile->getFileName() . '] generated. At ',
				$languageFile->getFolderPath(),
				PHP_EOL;
			} else {
				echo 'Error: can\'t generate language file.', PHP_EOL;
			}
		}
	}
}
