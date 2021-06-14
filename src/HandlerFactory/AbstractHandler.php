<?php


namespace App\HandlerFactory;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractHandler implements HandlerInterface
{
    /** @var FormFactoryInterface $formFactory */
    private $formFactory;

    /** @var FormInterface<FormInterface> $form */
    protected $form;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Configure required option 'form_type' to OptionResolver
     *
     * @param OptionsResolver $resolver
     */
    abstract protected function configure(OptionsResolver $resolver): void;

    /**
     * This method is call when form is submitted and valid
     */
    abstract protected function process(): void;

    public function handle(Request $request, $data = null, $options = []): bool
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired('form_type');
        $resolver->setDefault("form_options", []);

        $this->configure($resolver);
        $options = $resolver->resolve($options);

        $this->form = $this->formFactory->create(
            $options["form_type"],
            $data,
            $options["form_options"]
        )->handleRequest($request);

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            $this->process();
            return true;
        }
        return false;
    }

    public function createView(): FormView
    {
        return $this->form->createView();
    }
}