<?php

namespace App\Form\FormExtension;

use App\EventSubscriber\HoneyPotSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class HoneyPotType extends AbstractType
{
    /** @var LoggerInterface $honeyPotLogger */
    private $honeyPotLogger;

    /** @var RequestStack $requestStack */
    private $requestStack;

    protected const FIRST_FIELD_FOR_BOT = "phone";

    protected const SECOND_FIELD_FOR_BOT = "faxNumber";

    public function __construct(
        LoggerInterface $honeyPotLogger,
        RequestStack $requestStack
    ) {
        $this->honeyPotLogger = $honeyPotLogger;
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(self::FIRST_FIELD_FOR_BOT, TextType::class, $this->setHoneyPotFieldConfig())
            ->add(self::SECOND_FIELD_FOR_BOT, TextType::class, $this->setHoneyPotFieldConfig())
            ->addEventSubscriber(new HoneyPotSubscriber($this->honeyPotLogger, $this->requestStack))
        ;
    }

    private function setHoneyPotFieldConfig(): array
    {
        return [
            'mapped' => false,
            'required' => false,
            'attr' => [
                'autocomplete' => 'off',
                'tabindex' => '-1'
            ]
        ];
    }
}