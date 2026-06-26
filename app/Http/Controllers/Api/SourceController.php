<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource;
use App\Models\Source;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SourceController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        return SourceResource::collection(Source::orderBy('name')->get());
    }
}
