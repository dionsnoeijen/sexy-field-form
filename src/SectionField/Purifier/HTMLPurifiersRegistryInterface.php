<?php

namespace Tardigrades\SectionField\Purifier;

interface HTMLPurifiersRegistryInterface
{
    /**
     * @param string $profile
     *
     * @return bool
     */
    public function has(string $profile): bool;

    /**
     * @param string $profile
     *
     * @return \HTMLPurifier
     */
    public function get(string $profile): \HTMLPurifier;
}
