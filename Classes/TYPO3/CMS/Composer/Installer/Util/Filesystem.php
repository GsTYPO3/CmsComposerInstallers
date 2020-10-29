<?php

/*
 * This file is part of the TYPO3 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Composer\Installer\Util;

/**
 * An additional wrapper around filesystem
 */
class Filesystem extends \Composer\Util\Filesystem
{
    /**
     * @param array $files
     * @return bool
     */
    public function allFilesExist(array $files)
    {
        foreach ($files as $file) {
            if (!file_exists($file)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $files
     * @return bool
     */
    public function someFilesExist(array $files)
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                return true;
            }
        }
        return false;
    }

    public function establishSymlinks(array $links)
    {
        foreach ($links as $source => $target) {
            $this->symlink($source, $target);
        }
    }

    public function removeSymlinks(array $links)
    {
        foreach ($links as $target) {
            $this->remove($target);
        }
    }

    /**
     * @param string $source
     * @param string $target
     * @param bool $copyOnFailure
     */
    public function symlink($source, $target, $copyOnFailure = true)
    {
        if (!file_exists($source)) {
            throw new \InvalidArgumentException('The symlink source "' . $source . '" is not available.');
        }
        if (file_exists($target)) {
            throw new \InvalidArgumentException('The symlink target "' . $target . '" already exists.');
        }
        $symlinkSuccessfull = @symlink($source, $target);
        if (!$symlinkSuccessfull && !$copyOnFailure) {
            throw new \RuntimeException('Symlinking target "' . $target . '" to source "' . $source . '" failed.');
        } elseif (!$symlinkSuccessfull && $copyOnFailure) {
            try {
                $this->copy($source, $target);
            } catch (\Exception $exception) {
                throw new \RuntimeException('Neiter symlinking nor copying target "' . $target . '" to source "' . $source . '" worked.');
            }
        }
    }

    /**
     * @param string $source
     * @param string $target
     *
     * @return void
     */
    public function copy($source, $target)
    {
        if (!file_exists($source)) {
            throw new \RuntimeException('The source "' . $source . '" does not exist and cannot be copied.');
        }
        if (is_file($source)) {
            $this->ensureDirectoryExists(dirname($target));
            $this->copyFile($source, $target);
            return;
        } elseif (is_dir($source)) {
            $this->copyDirectory($source, $target);
            return;
        }
        throw new \RuntimeException('The source "' . $source . '" is neither a file nor a directory.');
    }

    /**
     * @param string $source
     * @param string $target
     */
    protected function copyFile($source, $target)
    {
        $copySuccessful = @copy($source, $target);
        if (!$copySuccessful) {
            throw new \RuntimeException('The source "' . $source . '" could not be copied to target "' . $target . '".');
        }
    }

    /**
     * @param string $source
     * @param string $target
     */
    protected function copyDirectory($source, $target)
    {
        $iterator = new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
        $this->ensureDirectoryExists($target);

        foreach ($recursiveIterator as $file) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $recursiveIterator->getSubPathName();
            if ($file->isDir()) {
                $this->ensureDirectoryExists($targetPath);
            } else {
                $this->copyFile($file->getPathname(), $targetPath);
            }
        }
    }
}
