<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace Applets;

use Exceptions\UndefinedException;
use Interfaces\AppletInterface;
use Interfaces\ApplicationLanguageInterface;
use Language\ApiCall;

/**
 * Class Applet
 * @package Applets
 */
class Applet implements AppletInterface, ApplicationLanguageInterface
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $title;

    /**
     * @param string $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     * @throws \Exceptions\UndefinedException
     */
    public function getLanguages()
    {
        $response = ApiCall::call(
            'system_api',
            'language_api',
            [
                'system' => 'LanguageFiles',
                'action' => 'getAppletLanguages'
            ],
            ['applet' => $this->id]
        );
        if (is_array($response) && array_key_exists('data', $response)) {
            $languageList = $response['data'];
        } else {
            throw new UndefinedException('There is no available languages for the ' . $this->id . ' applet.');
        }
        return $languageList;
    }
}