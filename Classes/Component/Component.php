<?php

/**
 * A component whose template a package provides
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Component;

/**
 * A component whose template a package provides
 *
 * The template lives in "Resources/Private/Components" of the package, its resources under the same path in
 * "Resources/Public/Components" and its examples next to the template.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class Component
{
    /**
     * Folder of the templates in a package
     *
     * @var string
     */
    public const string TEMPLATES = 'Resources/Private/Components/';

    /**
     * Folder of the resources in a package
     *
     * @var string
     */
    public const string RESOURCES = 'Resources/Public/Components/';

    /**
     * Constructor
     *
     * @param string $namespace   Namespace of the collection, such as "Tollwerk\Components"
     * @param string $name        Name within the collection, such as "event.teaser"
     * @param string $template    Absolute path of the template that applies
     * @param string $packageKey  Key of the package the template belongs to, such as "tw_base"
     * @param string $packagePath Absolute path of the package, with a trailing slash
     * @param string $path        Path of the template within the folder of the templates, without the extension, such
     *                            as "Event/Teaser/Teaser"
     */
    public function __construct(
        public string $namespace,
        public string $name,
        public string $template,
        public string $packageKey,
        public string $packagePath,
        public string $path,
    ) {}

    /**
     * Whether the component is the page of a site, such as "a11yclub.page"
     *
     * Its resources hold what every component of the site builds on, so they load before those of the others.
     *
     * @return boolean
     */
    public function isPage(): bool
    {
        return 1 === preg_match('/^[^.]+\.page$/', $this->name);
    }

    /**
     * Absolute path of the file with the examples, which may not exist
     *
     * @return string
     */
    public function examplesFile(): string
    {
        return $this->packagePath . self::TEMPLATES . $this->path . '.examples.php';
    }

    /**
     * Absolute path of a resource, which may not exist
     *
     * @param string $suffix Suffix of the file, such as ".css"
     *
     * @return string
     */
    public function resourceFile(string $suffix): string
    {
        return $this->packagePath . self::RESOURCES . $this->path . $suffix;
    }

    /**
     * Resource as a path with "EXT:"
     *
     * @param string $suffix Suffix of the file, such as ".css"
     *
     * @return string
     */
    public function resource(string $suffix): string
    {
        return 'EXT:' . $this->packageKey . '/' . self::RESOURCES . $this->path . $suffix;
    }
}
