<?php

namespace App\Services;

class testService
{
    public function getAll(): array
    {
        return [];
    }

    public function getById(int|string $id): mixed
    {
        return null;
    }

    public function create(array $data): mixed
    {
        return $data;
    }

    public function update(int|string $id, array $data): mixed
    {
        return $data;
    }

    public function delete(int|string $id): bool
    {
        return true;
    }
}
