<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace Interfaces;

use Applets\Applet;

/**
 * Interface ApplicationsInterface
 * @package Interfaces
 */
interface AppletsInterface
{
    /**
     * @return Applet[]
     */
    public function getTranslatedAppletsList();
}
