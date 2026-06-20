<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResellerTutorial extends Model
{
    use SoftDeletes;

    protected $table = 'reseller_tutorials';

    protected $fillable = [
        'title', 'description', 'youtube_url', 'sort_order', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * যেকোনো YouTube URL format (watch?v=, youtu.be/, embed/) থেকে
     * video ID বের করে embeddable URL রিটার্ন করে।
     */
    public function getEmbedUrlAttribute(): ?string
    {
        $url = $this->youtube_url;
        $videoId = null;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
    }

    public function getThumbnailAttribute(): ?string
    {
        $url = $this->youtube_url;
        $videoId = null;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg" : null;
    }
}
