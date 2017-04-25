<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace Interfaces;

use Applications\Application;

/**
 * Interface ApplicationsInterface
 * @package Interfaces
 */
interface ApplicationsInterface
{
    /**
     * @return Application[]
     */
    public function getTranslatedAppsList();
}