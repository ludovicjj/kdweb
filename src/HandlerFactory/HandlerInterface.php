<?php

namespace App\HandlerFactory;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

interface HandlerInterface
{
    /**
     * Handle form
     *
     * @param Request $request
     * @param mixed|null $data Form model class (DTO)
     * @param mixed|null $entity Entity to update
     * @param array<string, array> $options
     * @return bool
     */
    public function handle(Request $request, $data = null, $entity = null, $options = []): bool;

    /**
     * @return FormView The view
     */
    public function createView(): FormView;
}