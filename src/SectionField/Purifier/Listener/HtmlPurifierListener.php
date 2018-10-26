<?php

namespace Tardigrades\SectionField\Purifier\Listener;

use Tardigrades\SectionField\Purifier\HTMLPurifiersRegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HTMLPurifierListener implements EventSubscriberInterface
{
    /**
     * @var HTMLPurifiersRegistryInterface
     */
    private $registry;

    /**
     * @var string
     */
    private $profile;

    /**
     * @param HTMLPurifiersRegistryInterface $registry
     * @param string                         $profile
     */
    public function __construct(HTMLPurifiersRegistryInterface $registry, $profile)
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
            // Hope there is a view transformer, otherwise an error might happen
            return; // because we don't want to handle it here
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
            FormEvents::PRE_SUBMIT => ['purifySubmittedData', /* as soon as possible */ 1000000],
        ];
    }

    /**
     * @return \HTMLPurifier
     */
    private function getPurifier(): \HTMLPurifier
    {
        return $this->registry->get($this->profile);
    }
}
