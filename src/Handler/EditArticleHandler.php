<?php


namespace App\Handler;


use App\Form\EditArticleType;
use App\HandlerFactory\AbstractHandler;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditArticleHandler extends AbstractHandler
{
    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", EditArticleType::class);
    }

    protected function process(): void
    {
        $dto = $this->form->getData();
        dd($dto);
    }
}