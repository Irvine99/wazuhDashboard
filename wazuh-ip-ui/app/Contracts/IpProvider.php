<?php
namespace App\Contracts;

interface IpProvider
{
    /** @return string[] */
    public function getIPs(int $size = 1000): array;
}