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
 * Class FileGeneratorInterface
 * @package Interfaces
 */
interface FileGeneratorInterface
{
    /**
     * @return string
     */
    public function getFolderPath();

    /**
     * @return string
     */
    public function getFileContent();

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @return boolean
     */
    public function generateFile();
}
