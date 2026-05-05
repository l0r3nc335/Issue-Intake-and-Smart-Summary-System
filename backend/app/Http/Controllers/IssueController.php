<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Services\IssueInsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class IssueController extends Controller
{
    public function __construct(private readonly IssueInsightService $insightService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $issues = Issue::query()
            ->filter($request->only(['status', 'category', 'priority']))
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        return response()->json($issues);
    }

    public function store(StoreIssueRequest $request): JsonResponse
    {
        $data = $request->validated();
        $insight = $this->insightService->generate(
            $data['title'],
            $data['description'],
            $data['priority']
        );

        $data['summary'] = $insight['summary'];
        $data['suggested_next_action'] = $insight['next_action'];
        $data['is_escalated'] = $this->shouldEscalate($data['priority'], $data['due_at'] ?? null);

        $issue = Issue::query()->create($data);

        return response()->json([
            'data' => $issue,
            'meta' => ['insight_source' => $insight['source']],
        ], 201);
    }

    public function show(Issue $issue): JsonResponse
    {
        return response()->json(['data' => $issue]);
    }

    public function update(UpdateIssueRequest $request, Issue $issue): JsonResponse
    {
        $data = $request->validated();
        $refreshInsight = isset($data['title']) || isset($data['description']) || isset($data['priority']);

        if ($refreshInsight) {
            $insight = $this->insightService->generate(
                $data['title'] ?? $issue->title,
                $data['description'] ?? $issue->description,
                $data['priority'] ?? $issue->priority
            );

            $data['summary'] = $insight['summary'];
            $data['suggested_next_action'] = $insight['next_action'];
        }

        $priority = $data['priority'] ?? $issue->priority;
        $dueAt = $data['due_at'] ?? $issue->due_at?->toISOString();
        $data['is_escalated'] = $this->shouldEscalate($priority, $dueAt);

        $issue->update($data);

        return response()->json(['data' => $issue->fresh()]);
    }

    private function shouldEscalate(string $priority, mixed $dueAt): bool
    {
        if (in_array($priority, ['high', 'critical'], true)) {
            return true;
        }

        if ($dueAt === null || $dueAt === '') {
            return false;
        }

        return now()->greaterThan(Carbon::parse($dueAt));
    }
}
