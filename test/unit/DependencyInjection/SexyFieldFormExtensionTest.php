<?php
declare (strict_types=1);

namespace Tardigrades\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mockery as M;

/**
 * @coversDefaultClass Tardigrades\DependencyInjection\SexyFieldFormExtension
 * @covers ::<private>
 */
class SexyFieldFormExtensionTest extends TestCase
{
    use M\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @test
     * @covers ::load
     */
    public function it_loads()
    {
        $container = M::mock(ContainerBuilder::class)->makePartial();
        $mock = M::mock('overload:Symfony\Component\DependencyInjection\Loader\YamlFileLoader');
        $mock->shouldReceive('load')
            ->once()
            ->with('services.yml');

        $loader = new SexyFieldFormExtension();
        $loader->load([], $container);
        $this->assertInstanceOf(SexyFieldFormExtension::class, $loader);
    }
}
