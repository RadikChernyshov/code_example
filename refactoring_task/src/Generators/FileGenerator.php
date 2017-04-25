<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace Generators;

use Interfaces\FileGeneratorInterface;

/**
 * Class FileGenerator
 * @package Generators
 */
class FileGenerator
{
    /**
     * @param FileGeneratorInterface $generator
     * @return boolean
     * @throws \ErrorException
     */
    public function generate(FileGeneratorInterface $generator)
    {
        $isGenerated = false;
        $folderPath = $generator->getFolderPath();
        if ($this->makeSourceFolder($folderPath)) {
            $sourcePath = rtrim($folderPath, '/') . '/' . $generator->getFileName();
            $sourceContent = $generator->getFileContent();
            if('' === $sourcePath) {
                throw new \ErrorException('Can\'t generate a file, it\'s path is empty.');
            }
            if('' === $sourceContent) {
                throw new \ErrorException('Can\'t generate a file, it\'s empty.');
            }
            $isGenerated = (bool)file_put_contents($sourcePath, $sourceContent);
        }
        return $isGenerated;
    }

    /**
     * @param $path
     * @return bool
     * @throws \ErrorException
     */
    protected function makeSourceFolder($path = '')
    {
        if (!@mkdir($path, 0755, true) && !is_dir($path)) {
            throw new \ErrorException('Can\'t create source folder. ' . '['. $path .']');
        }
        return true;
    }
}
