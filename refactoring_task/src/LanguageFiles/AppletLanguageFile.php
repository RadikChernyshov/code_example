<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace LanguageFiles;

use Applets\Applet;
use Generators\FileGenerator;
use Interfaces\LanguageFileInterface;
use Language\ApiCall;
use Language\Config;

/**
 * Class AppletLanguageFile
 * @package Language
 */
class AppletLanguageFile implements LanguageFileInterface
{
    /**
     * @const string
     */
    const FILE_EXTENSION = 'xml';

    /**
     * @var Applet
     */
    protected $applet;

    /**
     * @var string
     */
    protected $code;

    /**
     * AppletLanguageFile constructor.
     * @param Applet $applet
     * @param string $code
     */
    public function __construct(Applet $applet, $code = '')
    {
        $this->applet = $applet;
        $this->setCode($code);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code = '')
    {
        $this->code = $code;
    }

    /**
     * @inheritdoc
     */
    public function getFolderPath()
    {
        return Config::get('system.paths.root') . '/cache/flash';
    }

    /**
     * @return string
     */
    public function getFileContent()
    {
        $languageData = '';
        $response = ApiCall::call(
            'system_api',
            'language_api',
            [
                'system' => 'LanguageFiles',
                'action' => 'getAppletLanguageFile'
            ],
            [
                'applet' => $this->applet->getId(),
                'language' => $this->code
            ]
        );
        if (is_array($response) && array_key_exists('data', $response)) {
            $languageData = $response['data'];
        }
        return $languageData;
    }

    /**
     * @inheritdoc
     */
    public function getFileName()
    {
        return 'lang_' . $this->code . '.' . self::FILE_EXTENSION;
    }

    /**
     * @return bool
     * @throws \ErrorException
     */
    public function generateFile()
    {
        return (new FileGenerator)->generate($this);
    }
}
