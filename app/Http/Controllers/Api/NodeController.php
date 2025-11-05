<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TopicGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function regenerate(string $nodeId, Request $request, TopicGraphService $service): JsonResponse
    {
        $data = $request->validate([
            'projectId' => ['required', 'string'],
        ]);

        $project = $service->regenerateBrief($data['projectId'], $nodeId);

        return response()->json([
            'projectId' => $project['projectId'],
            'nodes' => $project['nodes'],
            'links' => $project['links'],
        ]);
    }

    public function promote(string $nodeId, Request $request, TopicGraphService $service): JsonResponse
    {
        $data = $request->validate([
            'projectId' => ['required', 'string'],
        ]);

        $project = $service->promote($data['projectId'], $nodeId);

        return response()->json([
            'nodes' => $project['nodes'],
            'links' => $project['links'],
        ]);
    }

    public function demote(string $nodeId, Request $request, TopicGraphService $service): JsonResponse
    {
        $data = $request->validate([
            'projectId' => ['required', 'string'],
        ]);

        $project = $service->demote($data['projectId'], $nodeId);

        return response()->json([
            'nodes' => $project['nodes'],
            'links' => $project['links'],
        ]);
    }

    public function merge(Request $request, TopicGraphService $service): JsonResponse
    {
        $data = $request->validate([
            'projectId' => ['required', 'string'],
            'primaryId' => ['required', 'string'],
            'secondaryId' => ['required', 'string'],
        ]);

        $project = $service->merge($data['projectId'], $data['primaryId'], $data['secondaryId']);

        return response()->json([
            'nodes' => $project['nodes'],
            'links' => $project['links'],
        ]);
    }

    public function split(Request $request, TopicGraphService $service): JsonResponse
    {
        $data = $request->validate([
            'projectId' => ['required', 'string'],
            'nodeId' => ['required', 'string'],
        ]);

        $project = $service->split($data['projectId'], $data['nodeId']);

        return response()->json([
            'nodes' => $project['nodes'],
            'links' => $project['links'],
        ]);
    }
}
