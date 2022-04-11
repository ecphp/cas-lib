<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\CasLib\Configuration;

use function array_key_exists;

/**
 * Class Properties.
 */
final class Properties implements PropertiesInterface
{
    /**
     * @var array<string, mixed>
     */
    private $properties;

    /**
     * Properties constructor.
     *
     * @param array<string, mixed> $properties
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

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->properties);
    }

    /**
     * @param mixed $offset
     *
     * @return array<string, mixed>|string|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->properties[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->properties[$offset] = $value;
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->properties[$offset]);
    }
}
