<?php

namespace App\Http\Resources\Follower;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FollowerCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'TY' => 'follower-list',
            'results' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'total' => $this->total(),
                'per_page' => $this->perPage() >= 99999999999999 ? 0 : $this->perPage(),
                'count' => $this->count(),
                'total_pages' => $this->lastPage(),
            ],
            'links' => [
                'next' => $this->nextPageUrl() ?? '',
                'prev' => $this->previousPageUrl() ?? '',

            ],
        ];
    }
}
