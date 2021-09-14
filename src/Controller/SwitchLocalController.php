<?php


namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\String\u;

class SwitchLocalController extends AbstractController
{
    /** @var RouterInterface $router */
    private $router;

    /** @var TranslatorInterface $translator */
    private $translator;

    public function __construct(
        RouterInterface $router,
        TranslatorInterface $translator
    )
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @Route(
     *     {
     *          "en": "/locale/update",
     *          "fr": "/locale/modifier"
     *     },
     *     name="app_locale_update",
     *     methods={"GET"},
     *     defaults={"_public_access": false}
     * )
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateLocale(Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $locale = $this->translator->getLocale();

        $referer = $request->headers->get("referer");

        if (
            $referer === null ||
            u($referer)->ignoreCase()->startsWith($request->getSchemeAndHttpHost()) === false
        ) {
            return $this->redirectToRoute("app_home");
        }

        $path = parse_url($referer, PHP_URL_PATH);

        if (!is_string($path)) {
            throw new BadRequestHttpException();
        }

        try {
            $routeParams = $this->router->match($path);
        } catch (Exception $exception) {
            return $this->redirectToRoute("app_home", [
                "_locale" => $locale
            ]);
        }
        $updatedLocale = $locale === "fr" ? "en" : "fr";

        return $this->redirectToRoute($routeParams['_route'], [
            "_locale" => $updatedLocale,
            "id" => $routeParams['id'] ?? null
        ]);
    }
}