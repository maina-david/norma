<?php

namespace App\Services\System\Antivirus;

use Exception;
use Socket\Raw\Factory;
use Xenolope\Quahog\Client;

/**
 * @codeCoverageIgnore
 */
class ClamAVClient
{
    /**
     * Get the socket or tcp address.
     *
     * @return string
     */
    protected function getSocket(): string
    {
        if (config('antivirus.preferred_socket') === 'unix_socket') {
            $unixSocket = config('antivirus.unix_socket');

            if (file_exists($unixSocket)) {
                return 'unix://' . $unixSocket;
            }
        }

        return config('antivirus.tcp_socket');
    }

    /**
     * Create a new quahog ClamAV scanner client.
     *
     * @return Client
     */
    protected function createClient(): Client
    {
        $socket = $this->getSocket();

        $client = (new Factory())->createClient($socket, config('antivirus.socket_connect_timeout'));

        return new Client($client, config('antivirus.socket_read_timeout'), PHP_NORMAL_READ);
    }

    /**
     * Scan the provided filepath.
     *
     * @param string $filePath
     *
     * @return AntivirusResponse
     */
    public function scan(string $filePath): AntivirusResponse
    {
        if (!filter_var(config('antivirus.enabled'), FILTER_VALIDATE_BOOLEAN)) {
            return new AntivirusResponse(true, '');
        }

        if (!is_readable($filePath)) {
            return new AntivirusResponse(false, 'Uploaded file is not readable', true);
        }

        try {
            /** @var resource $resource */
            $resource = fopen($filePath, 'rb');
            $result = $this->createClient()->scanResourceStream($resource);
        } catch (Exception $exception) {
            return new AntivirusResponse(false, $exception->getMessage(), true);
        }

        $reason = $result->getReason() ?? '';

        if ($result->isError()) {
            return new AntivirusResponse(false, $reason, true);
        }

        return new AntivirusResponse($result->isOk(), $reason);
    }
}
