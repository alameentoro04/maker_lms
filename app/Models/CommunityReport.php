<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityReport extends Model
{
    protected $fillable = ['community_post_id', 'community_comment_id', 'reported_by', 'reason', 'status'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(CommunityComment::class, 'community_comment_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
