<?php

namespace Tardigrades\SectionField\Purifier;

use Psr\Container\ContainerInterface;

class HTMLPurifiersRegistry implements HTMLPurifiersRegistryInterface
{
    /**
     * @var ContainerInterface
     */
    private $purifiersLocator;

    public function __construct(ContainerInterface $purifiersLocator)
    {
        $this->purifiersLocator = $purifiersLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $profile): bool
    {
        return $this->purifiersLocator->has($profile);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $profile): \HTMLPurifier
    {
        return $this->purifiersLocator->get($profile);
    }
}
