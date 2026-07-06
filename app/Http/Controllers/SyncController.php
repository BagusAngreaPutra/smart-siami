<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AuditAssignment;
use App\Models\Clarification;
use App\Models\Evaluation;
use App\Models\Evidence;
use App\Models\Finding;
use App\Models\FollowUp;
use App\Models\Notification;
use App\Models\SelfAssessment;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Notification::archiveExpired();

        $user = $request->user();
        $latestNotifications = Notification::query()
            ->where('user_id', $user->id)
            ->active()
            ->latest()
            ->limit(10)
            ->get();

        $response = response()->json([
            'version' => $this->version($request),
            'unread_count' => Notification::query()
                ->where('user_id', $user->id)
                ->active()
                ->unread()
                ->count(),
            'notifications' => $latestNotifications->map(fn (Notification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->judul,
                'body' => str($notification->isi)->limit(90)->toString(),
                'time' => $notification->created_at->format('d/m H:i'),
                'is_read' => $notification->is_read,
                'url' => route('notifications.open', $notification, absolute: false),
            ])->values(),
        ]);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    private function version(Request $request): string
    {
        $timestamps = [
            Notification::query()
                ->where('user_id', $request->user()->id)
                ->max('updated_at'),
            Notification::query()
                ->where('user_id', $request->user()->id)
                ->max('created_at'),
        ];

        foreach ($this->scopedTables($request) as [$model, $scope]) {
            /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
            $updatedQuery = $model::query();
            $createdQuery = $model::query();
            $scope($updatedQuery);
            $scope($createdQuery);

            $timestamps[] = $updatedQuery->max('updated_at');
            $timestamps[] = $createdQuery->max('created_at');
        }

        return collect($timestamps)
            ->filter()
            ->sortDesc()
            ->first() ?: now()->toDateTimeString();
    }

    /**
     * @return array<int, array{0: class-string<\Illuminate\Database\Eloquent\Model>, 1: callable(Builder): void}>
     */
    private function scopedTables(Request $request): array
    {
        $user = $request->user();

        if ($user->role === UserRole::Admin) {
            $all = static fn (Builder $query): Builder => $query;

            return [
                [AuditAssignment::class, $all],
                [SelfAssessment::class, $all],
                [Evidence::class, $all],
                [Evaluation::class, $all],
                [Clarification::class, $all],
                [Visit::class, $all],
                [Finding::class, $all],
                [FollowUp::class, $all],
            ];
        }

        if ($user->role === UserRole::Auditor) {
            $assignmentScope = function (Builder $query) use ($user): void {
                $query->where(function (Builder $query) use ($user): void {
                    $query->where('lead_auditor_id', $user->id)
                        ->orWhereHas('auditors', fn (Builder $query) => $query->where('users.id', $user->id));
                });
            };
            $byAssignment = fn (Builder $query): Builder => $query->whereHas('assignment', $assignmentScope);

            return [
                [AuditAssignment::class, $assignmentScope],
                [SelfAssessment::class, $byAssignment],
                [Evidence::class, fn (Builder $query): Builder => $query->whereHas('selfAssessment.assignment', $assignmentScope)],
                [Evaluation::class, $byAssignment],
                [Clarification::class, $byAssignment],
                [Visit::class, $byAssignment],
                [Finding::class, $byAssignment],
                [FollowUp::class, $byAssignment],
            ];
        }

        $unitScope = fn (Builder $query): Builder => $query->where('unit_id', $user->unit_id);
        $byAssignment = fn (Builder $query): Builder => $query->whereHas('assignment', $unitScope);

        return [
            [AuditAssignment::class, $unitScope],
            [SelfAssessment::class, $byAssignment],
            [Evidence::class, fn (Builder $query): Builder => $query->whereHas('selfAssessment.assignment', $unitScope)],
            [Evaluation::class, $byAssignment],
            [Clarification::class, $byAssignment],
            [Visit::class, $byAssignment],
            [Finding::class, $byAssignment],
            [FollowUp::class, $byAssignment],
        ];
    }
}
