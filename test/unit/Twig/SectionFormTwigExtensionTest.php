<?php
declare (strict_types=1);

namespace Tardigrades\Twig;

use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Tardigrades\SectionField\Service\CreateSectionInterface;
use Tardigrades\SectionField\Service\SectionManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Tardigrades\SectionField\Form\FormInterface;

/**
 * @coversDefaultClass Tardigrades\Twig\SectionFormTwigExtension
 * @covers ::<private>
 */
class SectionFormTwigExtensionTest extends TestCase
{
    /** @var SectionManagerInterface|Mockery\Mock */
    private $sectionManager;

    /** @var CreateSectionInterface|Mockery\Mock */
    private $createSection;

    /** @var FormInterface|Mockery\Mock */
    private $form;

    /** @var RequestStack|Mockery\Mock */
    private $requestStack;

    /** @var SectionFormTwigExtension */
    private $twigExtension;

    public function setUp()
    {
        $this->sectionManager = Mockery::mock(SectionManagerInterface::class);
        $this->createSection = Mockery::mock(CreateSectionInterface::class);
        $this->form = Mockery::mock(FormInterface::class);
        $this->requestStack = Mockery::mock(RequestStack::class);

        $this->twigExtension = new SectionFormTwigExtension(
            $this->sectionManager,
            $this->createSection,
            $this->form,
            $this->requestStack
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getFunctions
     */
    public function it_should_construct_and_get_functions()
    {
        $result = $this->twigExtension->getFunctions();
        $this->assertInternalType('array', $result);
        $this->assertInstanceOf(\Twig_Function::class, $result[0]);
        $this->assertInstanceOf(\Twig_Function::class, $result[1]);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::sectionForm
     * @runInSeparateProcess
     */
    public function it_should_redirect_if_form_is_valid_and_submitted()
    {
        $handleString = 'sexyHandle';
        $formOptionsArray = [
            'id' => 1,
            'slug' => 'snail',
            'redirect' => '/myPlace/'
        ];

        $dummyData = '';

        $mockedForm = Mockery::mock(Form::class)->makePartial();
        $mockedCurrentRequest = Mockery::mock(Request::class)->makePartial();

        $this->requestStack->shouldReceive('getCurrentRequest')->once()->andReturn($mockedCurrentRequest);

        $this->form->shouldReceive('buildFormForSection')
            ->once()
            ->andReturn($mockedForm);

        $mockedCurrentRequest->shouldReceive('get')->once()->with('form')
            ->andReturn(['nothing to see here']);

        $this->form->shouldReceive('hasRelationship')->once()
            ->with(['nothing to see here'])
            ->andReturn(['nope']);

        $this->createSection->shouldReceive('save')->once()
            ->with($dummyData, ['nope']);

        $mockedForm->shouldReceive('handleRequest')->once();
        $mockedForm->shouldReceive('isSubmitted')->once()->andReturn(true);
        $mockedForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockedForm->shouldReceive('getData')->once()->andReturn($dummyData);

        $formView = $this->twigExtension->sectionForm($handleString, $formOptionsArray);

        $this->assertNull($formView);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::sectionForm
     */
    public function it_should_return_form_view_if_form_is_not_valid_or_submitted()
    {
        $handleString = 'sexyHandle';
        $formOptionsArray = [
            'id' => 10000,
            'slug' => 'snail',
            'redirect' => '/myPlace/'
        ];

        $dummyData = 'trololo';

        $mockedForm = Mockery::mock(Form::class)->makePartial();

        $this->form->shouldReceive('buildFormForSection')
            ->once()
            ->andReturn($mockedForm);

        $this->form->shouldReceive('hasRelationship')->never();

        $this->createSection->shouldReceive('save')->once()
            ->with($dummyData, ['nope']);

        $mockedForm->shouldReceive('handleRequest')->once();
        $mockedForm->shouldReceive('isSubmitted')->once()->andReturn(false);
        $mockedForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockedForm->shouldReceive('getData')->never();
        $mockedFormView = Mockery::mock(FormView::class)->makePartial();
        $mockedForm->shouldReceive('createView')->once()->andReturn($mockedFormView);

        $formView = $this->twigExtension->sectionForm($handleString, $formOptionsArray);

        $this->assertInstanceOf(FormView::class, $formView);
    }
}
