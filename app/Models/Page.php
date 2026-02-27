<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'is_active',
        'show_in_footer',
        'footer_section',
        'footer_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_footer' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            $source = trim((string) $page->slug);
            if ($source === '') {
                $source = trim((string) $page->title);
            }

            $page->slug = static::generateUniqueSlug($source, $page->id);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($source);
        if ($baseSlug === '') {
            $baseSlug = 'page';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = static::where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
