<?php
declare (strict_types=1);

namespace Tardigrades\SectionField\Form;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Mockery as M;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Tardigrades\Entity\Field;
use Tardigrades\Entity\FieldTypeInterface;
use Tardigrades\Entity\Section;
use Tardigrades\Entity\SectionEntityInterface;
use Tardigrades\SectionField\Service\ReadSectionInterface;
use Tardigrades\SectionField\Service\SectionManagerInterface;
use Tardigrades\SectionField\ValueObject\FieldConfig;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;
use Tardigrades\SectionField\ValueObject\Id;
use Tardigrades\SectionField\ValueObject\JitRelationship;
use Tardigrades\SectionField\ValueObject\SectionConfig;
use Tardigrades\SectionField\ValueObject\Slug;

/**
 * @coversDefaultClass Tardigrades\SectionField\Form\Form
 * @covers ::<private>
 */
class FormTest extends TestCase
{
    /** @var SectionManagerInterface|M\Mock */
    private $sectionManager;

    /** @var FormFactory|M\Mock */
    private $formFactory;

    /** @var ReadSectionInterface|M\Mock */
    private $readSection;

    /** @var Form */
    private $form;

    public function setUp()
    {
        $this->sectionManager = M::mock(SectionManagerInterface::class);
        $this->formFactory = M::mock(FormFactory::class);
        $this->readSection = M::mock(ReadSectionInterface::class);
        $this->form = new Form($this->sectionManager, $this->readSection, $this->formFactory);
    }

    /**
     * @test
     * @covers ::hasRelationship
     * @covers ::__construct
     */
    public function it_has_relationships_with_string_data()
    {
        $formData =
            [
                'i have _id' => 'qualityName:88',
                'i do not have Id' => 'unqualified:1002'
            ];

        $relationships = $this->form->hasRelationship($formData);
        $this->assertInstanceOf(JitRelationship::class, $relationships[0]);
        $this->assertSame($relationships[0]->getId()->toInt(), 88);
        $this->assertSame((string)$relationships[0]->getFullyQualifiedClassName(), 'qualityName');
        $this->assertCount(1, $relationships);
    }

    /**
     * @test
     * @covers ::hasRelationship
     * @covers ::__construct
     */
    public function it_has_relationships_with_array_data()
    {
        $formData =
            [
                'i have _id' => ['qualityName:88', 'veryhighquality:1'],
                'i do not have Id' => 'unqualified:1002'
            ];

        $relationships = $this->form->hasRelationship($formData);
        $this->assertInstanceOf(JitRelationship::class, $relationships[0]);
        $this->assertSame($relationships[0]->getId()->toInt(), 88);
        $this->assertSame((string)$relationships[0]->getFullyQualifiedClassName(), 'qualityName');
        $this->assertSame($relationships[1]->getId()->toInt(), 1);
        $this->assertSame((string)$relationships[1]->getFullyQualifiedClassName(), 'veryhighquality');
        $this->assertCount(2, $relationships);
    }

    /**
     * @test
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
            ->once()
            ->andReturn($mockedSectionManagerInterface);

        $sexyEntity = M::mock(SectionEntityInterface::class)->makePartial();

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
