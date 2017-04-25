<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace Interfaces;

/**
 * Interface LanguageFileInterface
 * @package Interfaces
 */
interface LanguageFileInterface extends FileGeneratorInterface
{
    /**
     * @param string $code
     */
    public function setCode($code = '');
}