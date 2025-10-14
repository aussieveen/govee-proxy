<?php

declare(strict_types=1);

namespace App\Controller;

use App\Client\GoveeApi;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DeviceController extends AbstractController
{
    public function __construct(private readonly GoveeApi $goveeApi)
    {
    }

    #[Route('/devices/list', name: 'govee_device_list')]
    public function list(): JsonResponse
    {
        try{
            $response = $this->goveeApi->getDevices();
            $statusCode = $response['code'] ?? 200;
        } catch (RuntimeException $e) {
            return $this->json(['error' => 'Failed to fetch devices from the API'], 500);
        }

        $devices = [];
        foreach($response['data'] as $device){
            $devices[] = [
                'sku' => $device['sku'],
                'device' => $device['device'],
                'deviceName' => $device['deviceName'],
            ];
        }

        return $this->json($devices, $statusCode);
    }

    #[Route('/devices/state/{sku}/{deviceId}', name: 'govee_device_state')]
    public function state(string $sku, string $deviceId): JsonResponse
    {
        try {
            $response = $this->goveeApi->getDeviceState($sku, $deviceId);
            $statusCode = $response['code'] ?? 200;
        } catch (RuntimeException $e) {
            $response = ['error' => 'Failed to connect to the API'];
            $statusCode = 500;
        }

        return $this->json($response, $statusCode);
    }

    #[Route('/devices/control/{sku}/{deviceId}/on', name: 'govee_device_on')]
    public function deviceOn(string $sku, string $deviceId): JsonResponse
    {
        try {
            $response = $this->goveeApi->turnOnDevice($sku, $deviceId);
            $statusCode = $response['code'] ?? 200;
        } catch (RuntimeException $e) {
            $response = ['error' => 'Failed to connect to the API'];
            $statusCode = 500;
        }

        return $this->json($response, $statusCode);
    }

    #[Route('/devices/control/{sku}/{deviceId}/off', name: 'govee_device_off')]
    public function deviceOff(string $sku, string $deviceId): JsonResponse
    {
        try {
            $response = $this->goveeApi->turnOffDevice($sku, $deviceId);
            $statusCode = $response['code'] ?? 200;
        } catch (RuntimeException $e) {
            $response = ['error' => 'Failed to connect to the API'];
            $statusCode = 500;
        }

        return $this->json($response, $statusCode);
    }
}
