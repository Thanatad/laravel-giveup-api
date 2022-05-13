<?php

namespace App\Http\Resources\UserSetting;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserSettingCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'TY' => 'user-setting-list',
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
