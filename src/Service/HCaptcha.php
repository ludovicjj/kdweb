<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HCaptcha
{
    const VERIFY_URL = "https://hcaptcha.com/siteverify";

    /** @var HttpClientInterface $client */
    private $client;

    /** @var RequestStack $requestStack */
    private $requestStack;

    /** @var string $privateKey */
    private $privateKey;

    public function __construct(
        string $privateKey,
        HttpClientInterface $client,
        RequestStack $requestStack
    )
    {
        $this->privateKey = $privateKey;
        $this->client = $client;
        $this->requestStack = $requestStack;
    }

    /**
     * @return bool
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function isHCaptchaValid(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }
        $options = [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded"
            ],
            "body" => [
                "secret" => $this->privateKey,
                "response" => $request->request->get('h-captcha-response')
            ]
        ];
        $response = $this->client->request("POST", self::VERIFY_URL, $options);
        $data = $response->toArray();

        if (!array_key_exists('success', $data)) {
            return false;
        }

        return $data['success'];
    }
}