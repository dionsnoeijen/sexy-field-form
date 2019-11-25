<?php
declare (strict_types=1);

namespace Tardigrades\SectionField\Form;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Mockery as M;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Tardigrades\Entity\Field;
use Tardigrades\Entity\FieldTypeInterface;
use Tardigrades\Entity\Section;
use Tardigrades\Entity\SectionEntityInterface;
use Tardigrades\SectionField\Generator\CommonSectionInterface;
use Tardigrades\SectionField\Service\ReadSectionInterface;
use Tardigrades\SectionField\Service\SectionManagerInterface;
use Tardigrades\SectionField\ValueObject\FieldConfig;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;
use Tardigrades\SectionField\ValueObject\Id;
use Tardigrades\SectionField\ValueObject\SectionConfig;
use Tardigrades\SectionField\ValueObject\Slug;

/**
 * @coversDefaultClass Tardigrades\SectionField\Form\Form
 * @covers ::<private>
 */
class FormTest extends TestCase
{
    use M\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @var SectionManagerInterface|M\Mock */
    private $sectionManager;

    /** @var FormFactory|M\Mock */
    private $formFactory;

    /** @var ContainerInterface|M\Mock */
    private $container;

    /** @var ReadSectionInterface|M\Mock */
    private $readSection;

    /** @var Form */
    private $form;

    public function setUp()
    {
        $this->sectionManager = M::mock(SectionManagerInterface::class);
        $this->formFactory = M::mock(FormFactory::class);
        $this->container = M::mock(ContainerInterface::class);
        $this->readSection = M::mock(ReadSectionInterface::class);
        $this->form = new Form(
            $this->sectionManager,
            $this->readSection,
            $this->container,
            $this->formFactory
        );
    }

    /**
     * @test
     */
    public function it_should_have_one_test()
    {
        $this->assertTrue(true);
    }

    /**
     * @covers ::buildFormForSection
     * @covers ::__construct
     * @runInSeparateProcess
     */
    public function it_builds_form_for_section()
    {
        $sectionFormOptions = M::mock('alias:Tardigrades\SectionField\ValueObject\SectionFormOptions')->makePartial();
        $sectionFormOptions->shouldReceive('getSlug')
            ->andReturn(Slug::fromString('snails'));
        $sectionFormOptions->shouldReceive('getId')
            ->andReturn(Id::fromInt(222));

        $mockedRequestStack = M::mock(RequestStack::class)->makePartial();

        $mockedSectionManagerInterface = M::mock(Section::class)->makePartial();

        $configSextion = SectionConfig::fromArray(
            [
                'section' => [
                    'name' => 'sexyName',
                    'handle' => 'sexyHandles',
                    'fields' => [
                        's' => 'e',
                        'x' => 'y'
                    ],
                    'default' => 'Sexy per Default',
                    'namespace' => 'sexy space',
                    'generator' => 'Generator of awesome sexyness'
                ]
            ]
        );

        $fieldType = M::mock(FieldTypeInterface::class)->makePartial();
        $fieldType->shouldReceive('getFullyQualifiedClassName')
            ->once()
            ->andReturn(FullyQualifiedClassName::fromString('Tardigrades\FieldType\TextInput\TextInput'));

        $field = M::mock(new Field())->makePartial();
        $field->shouldReceive('getFieldType')
            ->andReturn($fieldType);
        $field->shouldReceive('getConfig')
            ->andReturn(
                FieldConfig::fromArray(
                    [
                        'field' =>
                            [
                                'name' => 'sexyname',
                                'handle' => 'lovehandles'
                            ]
                    ]
                )
            );

        $mockedSectionManagerInterface->shouldReceive('getConfig')
            ->once()
            ->andReturn($configSextion);
        $mockedSectionManagerInterface->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$field]));

        $this->sectionManager->shouldReceive('readByHandle')
            ->twice()
            ->andReturn($mockedSectionManagerInterface);

        $sexyEntity = M::mock(CommonSectionInterface::class)->makePartial();

        $this->readSection->shouldReceive('read')
            ->once()
            ->andReturn(new \ArrayIterator([$sexyEntity]));

        $formbuilderInterface = M::mock(FormBuilderInterface::class)->makePartial();

        $this->formFactory->shouldReceive('createBuilder')
            ->once()
            ->andReturn($formbuilderInterface);

        $formbuilderInterface->shouldReceive('add')
            ->once()
            ->andReturn($formbuilderInterface);

        $formbuilderInterface->shouldReceive('add')
            ->once()
            ->with('save', SubmitType::class)
            ->andReturn($formbuilderInterface);

        $mockedForm = M::mock(\Symfony\Component\Form\Form::class)->shouldDeferMissing();

        $formbuilderInterface->shouldReceive('getForm')
            ->once()
            ->andReturn($mockedForm);

        $endresult = $this->form->buildFormForSection(
            'handle',
            $mockedRequestStack,
            $sectionFormOptions,
            true
        );
        $this->assertInstanceOf(\Symfony\Component\Form\FormInterface::class, $endresult);
    }
}
