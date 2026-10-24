<?php

namespace App\Http\Controllers;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use App\Http\Requests\ReportRequest;
use App\Models\Location;
use App\Models\Report;
use App\Support\CurrentLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Pelaporan pesisir: CRUD lengkap + pencarian, filter, dan pagination. */
class ReportController extends Controller
{
    public function __construct(
        private readonly CurrentLocation $current,
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $filters = [
            'q' => $this->textFilter($request->query('q')),
            'category' => $this->enumFilter($request->query('kategori'), ReportCategory::class),
            'severity' => $this->enumFilter($request->query('tingkat'), ReportSeverity::class),
            'status' => $this->enumFilter($request->query('status'), ReportStatus::class),
            'location_id' => $this->intFilter($request->query('lokasi')),
        ];

        $reports = Report::query()
            ->with(['location:id,name', 'user:id,name'])
            ->filter($filters)
            ->latest()->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('laporan.index', [
            'reports' => $reports,
            'filters' => $filters,
            'locations' => Location::query()->orderBy('name')->get(['id', 'name']),
            'categories' => ReportCategory::cases(),
            'severities' => ReportSeverity::cases(),
            'statuses' => ReportStatus::cases(),
            'hasFilter' => count(array_filter($filters, fn ($v) => $v !== null)) > 0,
        ]);
    }

    public function show(Report $report): View
    {
        abort_unless(request()->user()?->isAdmin(), 403);

        $report->load(['location', 'user:id,name']);

        return view('laporan.show', [
            'report' => $report,
            'statuses' => ReportStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('laporan.form', $this->formData(new Report([
            'location_id' => $this->current->get()?->id,
            'severity' => ReportSeverity::SEDANG,
        ])));
    }

    public function store(ReportRequest $request): RedirectResponse
    {
        $report = $request->user()->reports()->create($request->validated());
        $redirect = $request->user()->isAdmin()
            ? redirect()->route('laporan.show', $report)
            : redirect()->route('laporan.create');

        return $redirect->with(
            'success',
            'Laporan berhasil dikirim kepada admin untuk ditinjau.'
        );
    }

    public function edit(Report $report): View
    {
        abort_unless(request()->user()?->isAdmin(), 403);
        Gate::authorize('update', $report);

        return view('laporan.form', $this->formData($report));
    }

    public function update(ReportRequest $request, Report $report): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        Gate::authorize('update', $report);
        $report->update($request->validated());

        return redirect()->route('laporan.show', $report)->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(Report $report): RedirectResponse
    {
        abort_unless(request()->user()?->isAdmin(), 403);
        Gate::authorize('delete', $report);
        $report->delete();

        return redirect()->route('laporan.index')->with('success', 'Laporan dihapus.');
    }

    /** Admin mengubah status tindak lanjut (Baru -> Diproses -> Selesai). */
    public function updateStatus(Request $request, Report $report): RedirectResponse
    {
        Gate::authorize('updateStatus', $report);
        $data = $request->validate(
            ['status' => ['required', 'in:'.implode(',', ReportStatus::values())]],
            ['status.required' => 'Pilih status.', 'status.in' => 'Status tidak valid.'],
        );
        $report->update(['status' => $data['status']]);

        return redirect()->route('laporan.show', $report)->with('success', 'Status laporan diperbarui.');
    }

    private function formData(Report $report): array
    {
        return [
            'report' => $report,
            'locations' => Location::query()->orderBy('name')->get(['id', 'name']),
            'categories' => ReportCategory::cases(),
            'severities' => ReportSeverity::cases(),
        ];
    }
}
