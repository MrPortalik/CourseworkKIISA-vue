<?php

namespace App\Http\Controllers;

use App\Models\PlatformReport;
use App\Notifications\ReportResponseNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = PlatformReport::with(['user', 'article', 'reportedUser', 'respondedBy'])
            ->when($request->type, fn ($query) => $query->where('type', $request->type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Reports', [
            'reports' => $reports,
            'pendingCount' => PlatformReport::where('status', PlatformReport::STATUS_PENDING)->count(),
            'filters' => ['type' => $request->type],
        ]);
    }

    public function respond(Request $request, PlatformReport $report)
    {
        $validated = $request->validate([
            'admin_reply' => 'required|string|min:5|max:5000',
        ]);

        $report->update([
            'admin_reply' => $validated['admin_reply'],
            'responded_by_id' => $request->user()->id,
            'responded_at' => now(),
            'status' => PlatformReport::STATUS_RESOLVED,
        ]);

        $report->load(['article', 'respondedBy']);
        $report->user?->notify(new ReportResponseNotification($report));

        return back()->with('status', 'Ответ отправлен пользователю.');
    }

    public function destroy(Request $request, PlatformReport $report)
    {
        $validated = $request->validate([
            'deletion_reason' => 'required|string|min:5|max:2000',
        ]);

        $report->update([
            'deletion_reason' => $validated['deletion_reason'],
            'deleted_by_id' => $request->user()->id,
            'deleted_at' => now(),
        ]);

        return back()->with('status', 'Жалоба удалена.');
    }
}
