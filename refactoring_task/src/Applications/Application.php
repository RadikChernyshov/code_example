<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace Applications;

use Interfaces\ApplicationInterface;
use Interfaces\ApplicationLanguageInterface;

/**
 * Class Applications
 * @package Language
 */
class Application implements ApplicationInterface, ApplicationLanguageInterface
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var array
     */
    protected $languages;

    /**
     * Application constructor.
     * @param string $id
     * @param array $languages
     */
    public function __construct($id, array $languages = [])
    {
        $this->setId($id);
        $this->setLanguages($languages);
    }


    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = trim($id);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $languages
     */
    public function setLanguages(array $languages = [])
    {
        $this->languages = $languages;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }
}
