<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace Applications;

use Exceptions\UndefinedException;
use Interfaces\ApplicationsInterface;
use Language\Config;

/**
 * Class Applications
 * @package Language
 */
class Applications implements ApplicationsInterface
{

    /**
     * @const string
     */
    const CONFIG_PARAM_TRANSLATED_APPLICATIONS = 'system.translated_applications';

    /**
     * @var array
     */
    protected $list;

    /**
     * @inheritdoc
     * @throws \Exceptions\UndefinedException
     */
    public function getTranslatedAppsList()
    {
        if (0 === count($this->list)) {
            $applications = (array)Config::get(self::CONFIG_PARAM_TRANSLATED_APPLICATIONS);
            if(0 === count($applications)) {
                throw new UndefinedException('Applications not found.');
            }
            foreach ($applications as $id => $languages) {
                $this->list[] = new Application($id, $languages);
            }
        }
        return $this->list;
    }
}
