<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TopicGraphService;
use Illuminate\Http\JsonResponse;

class LinkController extends Controller
{
    public function index(string $projectId, TopicGraphService $service): JsonResponse
    {
        $project = $service->getProject($projectId);

        return response()->json([
            'projectId' => $project['projectId'],
            'links' => $project['links'],
        ]);
    }
}
