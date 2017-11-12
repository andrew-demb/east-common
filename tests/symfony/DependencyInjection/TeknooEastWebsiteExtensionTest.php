<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\WebsiteBundle\DependencyInjection\TeknooEastWebsiteExtension;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\DependencyInjection\TeknooEastWebsiteExtension
 */
class TeknooEastWebsiteExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @return ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainerBuilderMock()
    {
        if (!$this->container instanceof ContainerBuilder) {
            $this->container = $this->createMock(ContainerBuilder::class);
        }

        return $this->container;
    }

    /**
     * @return TeknooEastWebsiteExtension
     */
    private function buildExtension(): TeknooEastWebsiteExtension
    {
        return new TeknooEastWebsiteExtension();
    }

    /**
     * @return string
     */
    private function getExtensionClass(): string
    {
        return TeknooEastWebsiteExtension::class;
    }

    public function testLoad()
    {
        self::assertInstanceOf(
            $this->getExtensionClass(),
            $this->buildExtension()->load([], $this->getContainerBuilderMock())
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testLoadErrorContainer()
    {
        $this->buildExtension()->load([], new \stdClass());
    }

    /**
     * @expectedException \TypeError
     */
    public function testLoadErrorConfig()
    {
        $this->buildExtension()->load(new \stdClass(), $this->getContainerBuilderMock());
    }
}
