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

use function array_key_exists;

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

    public function all(): array
    {
        return $this->properties;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->properties);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->properties[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->properties[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->properties[$offset]);
    }
}
