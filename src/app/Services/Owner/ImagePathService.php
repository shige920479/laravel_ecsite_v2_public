<?php
namespace App\Services\Owner;

class ImagePathService
{
    public function tmp(string $filename): string
    {
        return "tmp/{$filename}";
    }

    public function uploads(string $subDir, string $filename): string
    {
        return "uploads/{$subDir}/{$filename}";
    }
}