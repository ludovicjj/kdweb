<?php

namespace App\Handler;

use App\Form\CreateArticleType;
use App\HandlerFactory\AbstractHandler;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateArticleHandler extends AbstractHandler
{

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", CreateArticleType::class);
    }

    protected function process(): void
    {
        $dto = $this->form->getData();
        dd($dto);
    }
}