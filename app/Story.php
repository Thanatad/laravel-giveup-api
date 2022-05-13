<?php

namespace App;

use App\StoryLog;
use Auth;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $guarded = [];
    protected $appends = ['is_seen'];

    public function getIsSeenAttribute()
    {
        return $this->seen(Auth::user()->id, $this->id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    private function seen($userId, $id)
    {
        $sl = StoryLog::where('user_id', $userId)->where('story_id', $id)->first();
        return $sl ? 1 : 0;
    }
}
