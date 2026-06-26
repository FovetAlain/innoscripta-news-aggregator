<?php

namespace App\Http\Resources;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserPreference
 */
class PreferenceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'preferred_sources' => $this->preferred_sources ?? [],
            'preferred_categories' => $this->preferred_categories ?? [],
            'preferred_authors' => $this->preferred_authors ?? [],
        ];
    }
}
