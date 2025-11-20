<?php

namespace App\EventHandlers;

interface EventStrategyInterface
{
    public function validate(array $data): void;
    public function updateStats(array $data): void;
}