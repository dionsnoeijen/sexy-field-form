<?php

/*
 * This file is part of the SexyField package.
 *
 * (c) Dion Snoeijen <hallo@dionsnoeijen.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Tardigrades\SectionField\Form;

use Symfony\Component\Form\FormInterface as SymfonyFormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Tardigrades\SectionField\ValueObject\SectionFormOptions;

interface FormInterface
{
    public function buildFormForSection(
        string $forHandle,
        RequestStack $requestStack,
        SectionFormOptions $sectionFormOptions = null,
        bool $csrfProtection = true
    ): SymfonyFormInterface;
}
