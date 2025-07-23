<?php
namespace App\Services;

use App\Contracts\IpProvider;

class DashboardProxyClient implements IpProvider
{
    public function __construct(
        private string $baseUrl,
        private string $user,
        private string $pass,
        private bool $verifySsl = true
    ) {}

    private function request(array $body): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/api/console/proxy?path=_search&method=POST';
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $this->user . ':' . $this->pass,
            CURLOPT_HTTPHEADER     => [
                'kbn-xsrf: true',
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 5,
        ]);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \Exception($err);
        }
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $json = json_decode($resp, true);
        if ($code >= 400) {
            $msg = $json['error']['reason'] ?? $resp;
            throw new \Exception("HTTP $code: $msg");
        }
        return $json;
    }

    public function getIPs(int $size = 1000): array
    {
        $fieldsToTry = [
            'srcip.keyword',
            'source.ip.keyword',
            'data.srcip.keyword',
            'srcip',
            'source.ip',
            'data.srcip'
        ];

        foreach ($fieldsToTry as $field) {
            try {
                $body = [
                    'index' => 'wazuh-alerts-*',
                    'size'  => 0,
                    'aggs'  => [
                        'ips' => [
                            'terms' => [
                                'field' => $field,
                                'size'  => $size
                            ]
                        ]
                    ]
                ];
                $json = $this->request($body);
                $buckets = $json['aggregations']['ips']['buckets'] ?? null;
                if ($buckets && is_array($buckets)) {
                    return array_column($buckets, 'key');
                }
            } catch (\Throwable $e) {
                // On tente le champ suivant
            }
        }

        return [];
    }
}