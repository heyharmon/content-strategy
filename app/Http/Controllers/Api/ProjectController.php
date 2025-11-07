<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TopicGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function store(Request $request, TopicGraphService $service): JsonResponse
    {
        $validated = $request->validate([
            'seed' => ['required', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:10'],
            'depth' => ['nullable', 'integer', 'between:2,3'],
            'maxSpokes' => ['nullable', 'integer', 'min:2', 'max:8'],
            'constraints' => ['nullable', 'array'],
            'constraints.minMsv' => ['nullable', 'integer', 'min:0'],
            'constraints.minCpc' => ['nullable', 'numeric', 'min:0'],
            'industry' => ['nullable', 'string', 'max:255'],
            'competitors' => ['nullable', 'array'],
            'competitors.*' => ['string', 'max:255'],
        ]);

        $project = $service->createProject($validated);

        $metadata = [
            'seed' => $validated['seed'],
            'locale' => $validated['locale'] ?? 'en-US',
            'depth' => $project['depth'],
            'maxSpokes' => $project['maxSpokes'],
            'constraints' => $project['constraints'],
            'industry' => $validated['industry'] ?? null,
            'competitors' => $validated['competitors'] ?? [],
            'createdAt' => $project['createdAt'] ?? now()->toISOString(),
        ];

        return response()->json([
            'projectId' => $project['projectId'],
            'nodes' => $project['nodes'],
            'links' => $project['links'],
            'metadata' => $metadata,
        ]);
    }

    public function show(string $projectId, TopicGraphService $service): JsonResponse
    {
        $project = $service->getProject($projectId);

        return response()->json([
            'projectId' => $project['projectId'],
            'nodes' => $project['nodes'],
            'links' => $project['links'],
            'metadata' => [
                'seed' => $project['seed'],
                'locale' => $project['locale'],
                'depth' => $project['depth'],
                'maxSpokes' => $project['maxSpokes'],
                'constraints' => $project['constraints'],
                'createdAt' => $project['createdAt'] ?? null,
            ],
        ]);
    }

    public function export(string $projectId, Request $request, TopicGraphService $service)
    {
        $project = $service->getProject($projectId);
        $format = $request->query('format', 'json');

        if ($format === 'csv') {
            $rows = $this->flattenNodes($project['nodes']);
            $csv = $this->toCsv($rows);

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="project-' . $projectId . '.csv"',
            ]);
        }

        if ($format === 'zip-md') {
            $files = $this->toMarkdownCollection($project['nodes']);

            return response()->json([
                'projectId' => $projectId,
                'files' => $files,
            ]);
        }

        return response()->json($project);
    }

    private function flattenNodes(array $nodes, ?string $parentTopic = null, ?string $parentId = null): array
    {
        $rows = [];

        foreach ($nodes as $node) {
            $rows[] = [
                'id' => $node['id'],
                'parentId' => $parentId,
                'parentTopic' => $parentTopic,
                'topic' => $node['topic'],
                'kind' => $node['kind'],
                'primaryKeyword' => $node['primaryKeyword'],
                'msv' => $node['metrics']['msv'],
                'cpc' => $node['metrics']['cpc'],
                'competition' => $node['metrics']['competition'],
                'breadthScore' => $node['metrics']['breadthScore'],
                'geoScore' => $node['metrics']['geoScore'],
                'viability' => $node['metrics']['viability'],
            ];

            if (!empty($node['children'])) {
                $rows = array_merge($rows, $this->flattenNodes($node['children'], $node['topic'], $node['id']));
            }
        }

        return $rows;
    }

    private function toCsv(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $headers = array_keys($rows[0]);
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv ?: '';
    }

    private function toMarkdownCollection(array $nodes): array
    {
        $documents = [];

        foreach ($nodes as $node) {
            $documents[] = [
                'filename' => Str::slug($node['topic']) . '.md',
                'content' => $this->toMarkdown($node),
            ];

            if (!empty($node['children'])) {
                $documents = array_merge($documents, $this->toMarkdownCollection($node['children']));
            }
        }

        return $documents;
    }

    private function toMarkdown(array $node): string
    {
        $brief = $node['brief'];
        $outline = collect($brief['outline'] ?? [])->map(function ($item, $index) {
            return ($index + 1) . '. ' . $item;
        })->implode("\n");

        $faqs = collect($brief['faqs'] ?? [])->map(function ($item) {
            return '- ' . $item;
        })->implode("\n");

        $entities = collect($brief['entities'] ?? [])->implode(', ');
        $prompts = collect($brief['geoPrompts'] ?? [])->map(function ($item) {
            return '- ' . $item;
        })->implode("\n");
        $schemas = collect($brief['schemaHints'] ?? [])->implode(', ');
        $wordRange = $brief['wordCountRange'] ?? [0, 0];

        return <<<MD
# {$node['topic']}
**Intent:** {$brief['searchIntent']}  
**Word Count:** {$wordRange[0]}–{$wordRange[1]}  
**Primary Keyword:** {$node['primaryKeyword']}

## Summary
{$brief['summary']}

## Outline
{$outline}

## FAQs
{$faqs}

## Entities to Mention
{$entities}

## GEO Prompts
{$prompts}

## Schema Suggestions
{$schemas}
MD;
    }
}
