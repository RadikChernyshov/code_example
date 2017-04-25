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
 * Interface LanguageBatchInterfaces
 * @package Interfaces\LanguageBatch
 */
interface LanguageBatchInterface
{
    /**
     *
     * Starts the language file generation.
     *
     * @return mixed
     */
    public static function generateLanguageFiles();

    /**
     *
     * Gets the language files for the applet and puts them into the cache.
     *
     * @return void
     */
    public static function generateAppletLanguageXmlFiles();
}
