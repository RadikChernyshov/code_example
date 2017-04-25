<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

use Language\LanguageBatchBo;

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo 'Project dependencies not found. Use command line command \'composer install\' to install.';
    exit(1);
}
require __DIR__ . '/vendor/autoload.php';

LanguageBatchBo::generateLanguageFiles();
LanguageBatchBo::generateAppletLanguageXmlFiles();
exit(0);