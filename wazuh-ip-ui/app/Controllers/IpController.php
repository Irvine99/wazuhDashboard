<?php
namespace App\Controllers;

use App\Contracts\IpProvider;

class IpController
{
    public function __construct(
        private IpProvider $provider,
        private string $cacheFile,
        private int $ttl
    ) {}

    /** @return string[] */
    public function list(): array
    {
        try {
            $useCache = file_exists($this->cacheFile) && (time() - filemtime($this->cacheFile) < $this->ttl);
            if ($useCache) {
                return json_decode(file_get_contents($this->cacheFile), true) ?: [];
            }
            $ips = $this->provider->getIPs();
            if (!is_dir(dirname($this->cacheFile))) {
                mkdir(dirname($this->cacheFile), 0775, true);
            }
            file_put_contents($this->cacheFile, json_encode($ips));
            return $ips;
        } catch (\Throwable $e) {
            // log minimal
            @file_put_contents(dirname($this->cacheFile) . '/error.log', date('c') . ' ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            if (file_exists($this->cacheFile)) {
                return json_decode(file_get_contents($this->cacheFile), true) ?: [];
            }
            return [];
        }
    }
}