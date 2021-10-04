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

namespace TYPO3\CMS\Composer\Installer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use TYPO3\CMS\Composer\Plugin\Config;

/**
 * @author Thomas Maroschik <tmaroschik@dfau.de>
 * @author Helmut Hummel <info@helhum.io>
 */
class ExtensionInstaller extends LibraryInstaller
{
    /**
     * @var string
     */
    private $extensionDir;

    /**
     * @var string
     */
    private $systemExtensionDir;

    public function __construct(
        IOInterface $io,
        Composer $composer,
        Config $pluginConfig = null
    ) {
        parent::__construct($io, $composer);

        $pluginConfig = $pluginConfig ?: Config::load($composer);
        $rootDirectory = $this->filesystem->normalizePath($pluginConfig->get('root-dir'));
        $this->extensionDir = $rootDirectory . '/typo3conf/ext';
        $this->systemExtensionDir = $rootDirectory . '/typo3/sysext';
    }

    /**
     * Decides if the installer supports the given type
     *
     * @param  string $packageType
     * @return bool
     */
    public function supports($packageType)
    {
        return $packageType !== 'typo3-cms-core'
            && strncmp('typo3-cms-', $packageType, 10) === 0;
    }
}
