<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace spec\EcPhp\CasLib\Configuration;

use EcPhp\CasLib\Configuration\Properties;
use JsonSerializable;
use PhpSpec\ObjectBehavior;
use spec\EcPhp\CasLib\Cas;

class PropertiesSpec extends ObjectBehavior
{
    public function it_can_be_json_encoded()
    {
        $this->beConstructedWith(['foo' => 'bar']);

        $this
            ->jsonSerialize()
            ->shouldReturn([
                'foo' => 'bar',
                'protocol' => [
                ],
            ]);
    }

    public function it_is_initializable()
    {
        $properties = Cas::getTestProperties()->jsonSerialize();

        $this->beConstructedWith($properties);

        $this->shouldHaveType(Properties::class);
        $this->shouldImplement(JsonSerializable::class);
    }
}
