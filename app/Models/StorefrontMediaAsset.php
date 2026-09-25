<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class StorefrontMediaAsset extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    public const MAX_PUBLIC_BYTES = 1_048_576;

    protected $fillable = ['title', 'alt_text', 'usage', 'status', 'notes'];

    protected static function booted(): void
    {
        static::saving(function (self $asset): void {
            if ($asset->isDirty('status') && $asset->status === 'ready' && ! $asset->isReady()) {
                throw new \DomainException('نسخه WebP باید آماده، عمومی و حداکثر ۱ مگابایت باشد.');
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('source')->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->performOnCollections('source')
            ->fit(Fit::Crop, 240, 240)->format('webp')->quality(78)->nonQueued();
        $this->addMediaConversion('preview')->performOnCollections('source')
            ->fit(Fit::Max, 1200, 1200)->format('webp')->quality(80)->nonQueued();
    }

    public function sourceMedia(): ?Media
    {
        return $this->getFirstMedia('source');
    }

    public function isReady(): bool
    {
        $media = $this->sourceMedia();
        if (! $media || ! $media->hasGeneratedConversion('preview')) {
            return false;
        }
        if (($media->conversions_disk ?: $media->disk) !== 'public') {
            return false;
        }
        $path = $media->getPath('preview');

        return is_file($path) && filesize($path) <= self::MAX_PUBLIC_BYTES;
    }

    public function previewUrl(): ?string
    {
        return $this->isReady() ? $this->sourceMedia()?->getFullUrl('preview') : null;
    }

    public function previewPath(): ?string
    {
        return $this->isReady() ? $this->sourceMedia()?->getPathRelativeToRoot('preview') : null;
    }
}
