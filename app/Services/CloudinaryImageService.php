<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class CloudinaryImageService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        if (! $cloudName || ! $apiKey || ! $apiSecret || $apiSecret === '**********') {
            throw new InvalidArgumentException('Cloudinary is not configured. Set CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, and the real CLOUDINARY_API_SECRET in your .env file.');
        }

        $this->cloudinary = new Cloudinary(
            "cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}"
        );
    }

    public function upload(UploadedFile $file): array
    {
        $result = $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => 'laptop-store/laptops',
                'resource_type' => 'image',
            ]
        );

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'],
        ];
    }

    public function delete(?string $publicId): void
    {
        if (! $publicId) {
            return;
        }

        $this->cloudinary->uploadApi()->destroy($publicId);
    }
}
