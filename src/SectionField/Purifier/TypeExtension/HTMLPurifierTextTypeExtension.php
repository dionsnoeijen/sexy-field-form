<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\Purifier\TypeExtension;

use Psr\Container\ContainerInterface;
use Tardigrades\SectionField\Purifier\Listener\HTMLPurifierListener;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HTMLPurifierTextTypeExtension extends AbstractTypeExtension
{
    /**
     * @var ContainerInterface
     */
    private $purifiersRegistry;

    public function __construct(ContainerInterface $registry)
    {
        $this->purifiersRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'purify_html' => false,
                'purify_html_profile' => 'default',
            ])
            ->setAllowedTypes('purify_html', 'bool')
            ->setAllowedTypes('purify_html_profile', ['string', 'null'])
            ->setNormalizer('purify_html_profile', function (Options $options, $profile) {
                if (!$options['purify_html']) {
                    return null;
                }
                if ($this->purifiersRegistry->has('sexy_field.'.$profile)) {
                    return $profile;
                }
                throw new InvalidOptionsException(sprintf('The profile "%s" is not registered.', $profile));
            })
            ->setNormalizer('trim', function (Options $options, $trim) {
                // trim is done in the HTMLPurifierListener
                return $options['purify_html'] ? false : $trim;
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['purify_html']) {
            $builder->addEventSubscriber(new HTMLPurifierListener($this->purifiersRegistry, $options['purify_html_profile']));
        }
    }
}
