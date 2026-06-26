<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePreferencesRequest;
use App\Http\Resources\PreferenceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PreferenceController extends Controller
{
    public function show(Request $request): PreferenceResource
    {
        // firstOrNew keeps the read side-effect free; absent values fall back to [].
        return new PreferenceResource($request->user()->preference()->firstOrNew([]));
    }

    public function update(UpdatePreferencesRequest $request): JsonResponse
    {
        $preference = $request->user()->preference()->firstOrNew([]);
        $preference->fill($request->validated())->save();

        return PreferenceResource::make($preference)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
