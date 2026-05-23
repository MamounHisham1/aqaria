<?php

namespace App\Services\Telegram\Contracts;

interface ChatClient
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function create(array $params): array;
}
