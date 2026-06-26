<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuthorController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        return AuthorResource::collection(Author::orderBy('name')->get());
    }
}
