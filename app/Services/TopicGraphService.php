<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TopicGraphService
{
    private const CACHE_KEY_PREFIX = 'project:';

    public function createProject(array $payload): array
    {
        $projectId = (string) Str::uuid();
        $seed = $payload['seed'];
        $locale = $payload['locale'] ?? 'en-US';
        $depth = (int) ($payload['depth'] ?? 3);
        $maxSpokes = (int) ($payload['maxSpokes'] ?? 4);
        $constraints = $payload['constraints'] ?? [];

        $nodes = $this->generateNodes($seed, $depth, $maxSpokes, $constraints);
        $links = $this->generateLinks($nodes);

        $project = [
            'projectId' => $projectId,
            'seed' => $seed,
            'locale' => $locale,
            'depth' => $depth,
            'maxSpokes' => $maxSpokes,
            'constraints' => $constraints,
            'nodes' => $nodes,
            'links' => $links,
            'createdAt' => now()->toISOString(),
        ];

        $this->storeProject($projectId, $project);

        return $project;
    }

    public function getProject(string $projectId): array
    {
        $project = Cache::get($this->cacheKey($projectId));

        if (!$project) {
            abort(404, 'Project not found.');
        }

        return $project;
    }

    public function regenerateBrief(string $projectId, string $nodeId): array
    {
        $project = $this->getProject($projectId);

        $this->mutateNode($project['nodes'], $nodeId, function (array $node) use ($project) {
            $node['brief'] = $this->makeBrief($node['topic'], $node['kind'], $project['locale']);
            return $node;
        });

        $this->storeProject($projectId, $project);

        return $project;
    }

    public function promote(string $projectId, string $nodeId): array
    {
        $project = $this->getProject($projectId);

        $this->mutateNode($project['nodes'], $nodeId, function (array $node) {
            if ($node['kind'] === 'subspoke') {
                $node['kind'] = 'spoke';
            } else {
                $node['kind'] = 'hub';
            }
            return $node;
        });

        $project['links'] = $this->generateLinks($project['nodes']);
        $this->storeProject($projectId, $project);

        return $project;
    }

    public function demote(string $projectId, string $nodeId): array
    {
        $project = $this->getProject($projectId);

        $this->mutateNode($project['nodes'], $nodeId, function (array $node) {
            if ($node['kind'] === 'hub') {
                $node['kind'] = 'spoke';
            } else {
                $node['kind'] = 'subspoke';
            }
            return $node;
        });

        $project['links'] = $this->generateLinks($project['nodes']);
        $this->storeProject($projectId, $project);

        return $project;
    }

    public function merge(string $projectId, string $primaryId, string $secondaryId): array
    {
        $project = $this->getProject($projectId);

        $secondaryNode = null;
        $this->mutateNode($project['nodes'], $secondaryId, function (array $node) use (&$secondaryNode) {
            $secondaryNode = $node;
            return $node;
        });

        if (!$secondaryNode) {
            abort(404, 'Secondary node not found.');
        }

        $this->mutateNode($project['nodes'], $primaryId, function (array $node) use ($secondaryNode) {
            $node['topic'] = $node['topic'] . ' & ' . $secondaryNode['topic'];
            $node['children'] = array_merge($node['children'] ?? [], $secondaryNode['children'] ?? []);
            foreach ($node['children'] as &$child) {
                $child['parentId'] = $node['id'];
            }
            return $node;
        });

        $this->removeNode($project['nodes'], $secondaryId);

        $project['links'] = $this->generateLinks($project['nodes']);
        $this->storeProject($projectId, $project);

        return $project;
    }

    public function split(string $projectId, string $nodeId): array
    {
        $project = $this->getProject($projectId);

        $locale = $project['locale'];

        $this->mutateNode($project['nodes'], $nodeId, function (array $node) use ($locale) {
            $children = $node['children'] ?? [];
            if (count($children) < 2) {
                $newChild = $this->makeNode($node['topic'] . ' Deep Dive', 'subspoke', $node['primaryKeyword'] . ' guide', $node['id'], $locale);
                $children[] = $newChild;
            } else {
                $chunks = array_chunk($children, 2);
                $node['children'] = array_map(function (array $chunk, int $index) use ($node, $locale) {
                    $topic = $node['topic'] . ' Part ' . ($index + 1);
                    $newNode = $this->makeNode($topic, 'subspoke', $node['primaryKeyword'] . ' ' . ($index + 1), $node['id'], $locale);
                    $newNode['children'] = $chunk;
                    foreach ($newNode['children'] as &$child) {
                        $child['parentId'] = $newNode['id'];
                    }
                    return $newNode;
                }, $chunks, array_keys($chunks));
                return $node;
            }
            $node['children'] = $children;
            return $node;
        });

        $project['links'] = $this->generateLinks($project['nodes']);
        $this->storeProject($projectId, $project);

        return $project;
    }

    private function storeProject(string $projectId, array $project): void
    {
        Cache::put($this->cacheKey($projectId), $project, now()->addHours(2));
    }

    private function cacheKey(string $projectId): string
    {
        return self::CACHE_KEY_PREFIX . $projectId;
    }

    private function generateNodes(string $seed, int $depth, int $maxSpokes, array $constraints): array
    {
        $hubs = max(2, min($maxSpokes, 4));

        $nodes = [];
        for ($i = 0; $i < $hubs; $i++) {
            $hub = $this->makeNode(
                ucfirst($seed) . ' Hub ' . ($i + 1),
                'hub',
                $seed . ' strategy ' . ($i + 1),
                null,
                $locale
            );

            if ($depth > 1) {
                $hub['children'] = $this->generateChildren($hub, 'spoke', $depth - 1, $maxSpokes, $constraints, $locale);
            }

            $nodes[] = $hub;
        }

        return $nodes;
    }

    private function generateChildren(array $parent, string $kind, int $remainingDepth, int $maxSpokes, array $constraints, string $locale): array
    {
        $count = max(2, min($maxSpokes, 5));
        $children = [];

        for ($i = 0; $i < $count; $i++) {
            $topic = $parent['topic'] . ' ' . ucfirst($kind) . ' ' . ($i + 1);
            $keyword = $parent['primaryKeyword'] . ' ' . $kind . ' ' . ($i + 1);
            $node = $this->makeNode($topic, $kind, $keyword, $parent['id'], $locale);

            if ($remainingDepth > 1) {
                $nextKind = $kind === 'spoke' ? 'subspoke' : 'subspoke';
                $node['children'] = $this->generateChildren($node, $nextKind, $remainingDepth - 1, $maxSpokes, $constraints, $locale);
            }

            $children[] = $node;
        }

        return $children;
    }

    private function makeNode(string $topic, string $kind, string $keyword, ?string $parentId, string $locale): array
    {
        $metrics = $this->makeMetrics($topic, $kind);

        return [
            'id' => (string) Str::uuid(),
            'parentId' => $parentId,
            'kind' => $kind,
            'topic' => $topic,
            'primaryKeyword' => $keyword,
            'metrics' => $metrics,
            'children' => [],
            'brief' => $this->makeBrief($topic, $kind, $locale),
        ];
    }

    private function makeMetrics(string $topic, string $kind): array
    {
        $hash = hexdec(substr(md5($topic . $kind), 0, 6));
        $msv = 500 + ($hash % 5000);
        $cpc = round(($hash % 500) / 100, 2);
        $competition = round((($hash >> 4) % 100) / 100, 2);
        $breadth = round((($hash >> 8) % 100) / 100, 2);
        $geo = round((($hash >> 12) % 100) / 100, 2);
        $viability = $geo > 0.66 ? 'high' : ($geo > 0.33 ? 'med' : 'low');

        return [
            'msv' => $msv,
            'cpc' => $cpc,
            'competition' => $competition,
            'breadthScore' => $breadth,
            'geoScore' => $geo,
            'viability' => $viability,
        ];
    }

    private function makeBrief(string $topic, string $kind, string $locale): array
    {
        $intent = $kind === 'hub' ? 'Informational' : ($kind === 'spoke' ? 'Commercial' : 'Transactional');

        return [
            'title' => $topic . ' Playbook',
            'searchIntent' => $intent,
            'summary' => 'Comprehensive guidance covering ' . strtolower($topic) . ' for ' . $locale . ' audiences.',
            'outline' => [
                'Introduction to ' . $topic,
                'Key Considerations',
                'Implementation Steps',
                'Examples and Case Studies',
                'Next Actions',
            ],
            'faqs' => [
                'What is ' . strtolower($topic) . '?',
                'How do I get started with ' . strtolower($topic) . '?',
            ],
            'entities' => [
                'Industry benchmarks',
                'Tools',
                'Frameworks',
            ],
            'geoPrompts' => [
                'Tailor messaging for ' . $locale . ' market nuances',
                'Highlight regional case studies in ' . $locale,
            ],
            'schemaHints' => ['Article', 'FAQPage'],
            'wordCountRange' => [1200, 1800],
        ];
    }

    private function mutateNode(array &$nodes, string $nodeId, callable $callback): bool
    {
        foreach ($nodes as &$node) {
            if ($node['id'] === $nodeId) {
                $node = $callback($node);
                return true;
            }

            if (!empty($node['children'])) {
                if ($this->mutateNode($node['children'], $nodeId, $callback)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function removeNode(array &$nodes, string $nodeId): bool
    {
        foreach ($nodes as $index => &$node) {
            if ($node['id'] === $nodeId) {
                array_splice($nodes, $index, 1);
                return true;
            }

            if (!empty($node['children'])) {
                if ($this->removeNode($node['children'], $nodeId)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function generateLinks(array $nodes, array $parents = []): array
    {
        $links = [];

        foreach ($nodes as $node) {
            foreach ($parents as $parent) {
                $links[] = [
                    'from' => $parent['topic'],
                    'to' => $node['topic'],
                    'direction' => $parent['kind'] . ' → ' . $node['kind'],
                    'anchorText' => 'Learn more about ' . strtolower($node['topic']),
                    'confidence' => round($node['metrics']['breadthScore'], 2),
                ];
            }

            if (!empty($node['children'])) {
                $links = array_merge($links, $this->generateLinks($node['children'], array_merge($parents, [$node])));
            }
        }

        return $links;
    }
}
