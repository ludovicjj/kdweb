<?php

namespace App\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslationExtension extends AbstractExtension
{
    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var UrlGeneratorInterface $urlGenerator */
    private $urlGenerator;

    public function __construct(
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction("changeLocale", [$this, "displayLocalButton"], ["is_safe" => ["html"]])
        ];
    }

    public function displayLocalButton(): string
    {
        $label = $this->translator->trans("articles.read.label", [], "articles_content");
        return "<a href=\"{$this->getUrlWithUpdatedLocal()}\" class=\"btn btn-primary my-3\">{$label}</a>";

    }

    private function getUrlWithUpdatedLocal(): string
    {
        return $this->urlGenerator->generate("app_locale_update");
    }
}