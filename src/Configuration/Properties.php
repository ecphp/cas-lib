<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Configuration;

use EcPhp\CasLib\Contract\Configuration\PropertiesInterface;

final class Properties implements PropertiesInterface
{
    /**
     * @var array<array-key, mixed>
     */
    private array $properties;

    /**
     * @param array<array-key, mixed> $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties + ['protocol' => []];

        foreach (array_keys((array) $this->properties['protocol']) as $key) {
            $this->properties['protocol'][$key] += ['default_parameters' => []];
        }
    }

    public function jsonSerialize(): array
    {
        return $this->properties;
    }
}
