<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace LanguageFiles;

use Applications\Application;
use ErrorException;
use Generators\FileGenerator;
use Interfaces\LanguageFileInterface;
use Language\ApiCall;
use Language\Config;

/**
 * Class Language
 * @package Language
 */
class ApplicationLanguageFile implements LanguageFileInterface
{
    /**
     * @const string
     */
    const FILE_EXTENSION = 'php';

    /**
     * @var string
     */
    protected $code;

    /**
     * @var Application
     */
    protected $application;

    /**
     * Language constructor.
     *
     * @param Application $application
     * @param string $code
     */
    public function __construct(Application $application, $code = '')
    {
        $this->setCode($code);
        $this->setApplication($application);
    }
    
    /**
     * @return mixed
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
        $this->code = strtolower(trim($code));
    }

    /**
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }
    
    /**
     * @return bool
     * @throws ErrorException
     */
    public function generateFile() {
        return (new FileGenerator())->generate($this);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->code . '.' . self::FILE_EXTENSION;
    }
    
    /**
     * @return string
     * @throws ErrorException
     */
    public function getFolderPath()
    {
        return Config::get('system.paths.root') . '/cache/' . $this->application->getId();
    }

    /**
     * @return array
     */
    public function getFileContent() {
        $languageData = [];
        $response = ApiCall::call(
            'system_api',
            'language_api',
            [
                'system' => 'LanguageFiles',
                'action' => 'getLanguageFile'
            ],
            ['language' => $this->code]
        );
        if(is_array($response) && array_key_exists('data', $response)) {
            $languageData = $response['data'];
        }
        return $languageData;
    }
}
