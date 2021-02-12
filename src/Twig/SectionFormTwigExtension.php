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

namespace Tardigrades\Twig;

use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Tardigrades\SectionField\Form\FormInterface;
use Tardigrades\SectionField\Service\CreateSectionInterface;
use Tardigrades\SectionField\Service\SectionManagerInterface;
use Tardigrades\SectionField\ValueObject\SectionFormOptions;
use Twig\TwigFunction;

class SectionFormTwigExtension extends TwigExtension
{
    /** @var SectionManagerInterface */
    private $sectionManager;

    /** @var FormInterface */
    private $form;

    /** @var CreateSectionInterface */
    private $createSection;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        SectionManagerInterface $sectionManager,
        CreateSectionInterface $createSection,
        FormInterface $form,
        RequestStack $requestStack
    ) {
        $this->sectionManager = $sectionManager;
        $this->createSection = $createSection;
        $this->form = $form;
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return array(
            new TwigFunction(
                'sectionForm',
                [ $this, 'sectionForm' ]
            )
        );
    }

    /**
     * This method returns a form to be rendered with Twig.
     * It will return a view, validate and save the form data.
     *
     * @param string $forHandle
     * @param array $sectionFormOptions
     * @return FormView
     */
    public function sectionForm(
        string $forHandle,
        array $sectionFormOptions = []
    ): FormView {

        $sectionFormOptions = SectionFormOptions::fromArray($sectionFormOptions);

        $form = $this->form->buildFormForSection(
            $forHandle,
            $this->requestStack,
            $sectionFormOptions
        );
        $form->handleRequest();

        if ($form->isSubmitted() &&
            $form->isValid()
        ) {
            $data = $form->getData();
            $this->createSection->save($data);

            try {
                $redirect = $sectionFormOptions->getRedirect();
            } catch (\Exception $exception) {
                $redirect = '/';
            }
            header('Location: ' . $redirect);
            exit;
        }

        return $form->createView();
    }
}
