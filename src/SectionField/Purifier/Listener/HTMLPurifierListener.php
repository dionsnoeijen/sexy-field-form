<?php

namespace Tardigrades\SectionField\Purifier\Listener;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HTMLPurifierListener implements EventSubscriberInterface
{
    /** @var ContainerInterface */
    private $registry;

    /** @var string */
    private $profile;

    /**
     * @param ContainerInterface $registry
     * @param string $profile
     */
    public function __construct(ContainerInterface $registry, string $profile)
    {
        $this->registry = $registry;
        $this->profile = $profile;
    }

    /**
     * @param FormEvent $event
     */
    public function purifySubmittedData(FormEvent $event): void
    {
        if (!is_scalar($data = $event->getData())) {
            return;
        }


        if (0 === strlen($submittedData = trim($data))) {
            return;
        }

        $event->setData($this->getPurifier()->purify($submittedData));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => ['purifySubmittedData', 1000000],
        ];
    }

    /**
     * @return \HTMLPurifier
     */
    private function getPurifier(): \HTMLPurifier
    {
        return $this->registry->get('sexy_field.'.$this->profile);
    }
}
