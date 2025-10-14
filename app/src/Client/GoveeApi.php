<?php

declare(strict_types=1);

namespace App\Client;

use App\Enum\OnOff;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class GoveeApi
{
    private const string URL = 'https://openapi.api.govee.com';

    public function __construct(
        #[Autowire('%env(GOVEE_API_KEY)%')]
        private string $apiKey,
        private HttpClientInterface $httpClient
    )
    {
    }

    public function getDevices(): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                self::URL . '/router/api/v1/user/devices',
                [
                    'headers' => $this->getDefaultHeaders()
                ]
            );
            return json_decode($response->getContent(), true);
        } catch (
            ClientExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface |
            TransportExceptionInterface $e
        ) {
            throw new RuntimeException('Failed to fetch devices from the API', 0, $e);
        }
    }

    public function getDeviceState(string $sku, string $deviceId): array
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                self::URL . '/router/api/v1/device/state',
                array_merge(
                    [
                        'headers' => $this->getDefaultHeaders()
                    ],
                    [
                        'json' => [
                            'requestId' => uniqid('', true),
                            'payload' => [
                                'sku' => $sku,
                                'device' => $deviceId
                            ]
                        ]
                    ]
                )
            );
            return json_decode($response->getContent(), true);
        } catch (
            ClientExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface |
            TransportExceptionInterface $e
        ) {
            throw new RuntimeException('Failed to fetch device state from the API', 0, $e);
        }
    }

    public function turnOnDevice(string $sku, string $deviceId): array
    {
        return $this->setDevicePowerState($sku, $deviceId, OnOff::On);
    }

    public function turnOffDevice(string $sku, string $deviceId): array
    {
        return $this->setDevicePowerState($sku, $deviceId, OnOff::Off);
    }

    private function setDevicePowerState(
        string $sku, string $deviceId, OnOff $state): array
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                self::URL . '/router/api/v1/device/control',
                array_merge(
                    [
                        'headers' => $this->getDefaultHeaders()
                    ],
                    [
                        'json' => [
                            'requestId' => uniqid('', true),
                            'payload' => [
                                'sku' => $sku,
                                'device' => $deviceId,
                                'capability' => [
                                    'type' => 'devices.capabilities.on_off',
                                    'instance' => 'powerSwitch',
                                    'value' => $state->value
                                ]
                            ]
                        ]
                    ]
                )
            );
            return json_decode($response->getContent(), true);
        } catch (
            ClientExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface |
            TransportExceptionInterface $e
        ) {
            throw new RuntimeException('Failed to change device power state', 0, $e);
        }
    }

    private function getDefaultHeaders(): array
    {
        return [
            'Govee-API-Key' => $this->apiKey
        ];
    }
}
