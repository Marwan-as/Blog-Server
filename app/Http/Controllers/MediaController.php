<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    private $mediaService;
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    public function getMedia()
    {
        $media = $this->mediaService->getMedia();
        return response()->json(['media' => $media], 200);
    }
}
