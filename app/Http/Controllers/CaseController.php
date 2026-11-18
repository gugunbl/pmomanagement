<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\MCase;
use App\CaseDocument;
use App\Project;
use App\Perangkat;
use App\User;
use App\Mail\CaseMentionNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CaseDocumentStorage;
use App\Services\NextcloudWebDav;
use App\Services\NextcloudWebDavException;

class CaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-case');
        $this->middleware('permission:create-case', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-case', ['only' => ['edit', 'update']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $title = 'Daftar Case';

        $userProjects = $user->projects->pluck('id_project')->toArray();
        $query = MCase::with(['project.teamSupports', 'user', 'perangkat']);

        // Base query for summaries (respects project access and project filter, but not type/search)
        $summaryQuery = MCase::query();

        // Base project access filter
        if (count($userProjects) > 0) {
            $query->whereIn('id_project', $userProjects);
            $summaryQuery->whereIn('id_project', $userProjects);
            $projects = Project::whereIn('id_project', $userProjects)->pluck('nama_project', 'id_project');
        } else {
            $projects = Project::pluck('nama_project', 'id_project');
        }

        $selectedProjects = array_values(array_filter((array) $request->input('id_project', []), function ($idProject) {
            return $idProject !== null && $idProject !== '';
        }));

        // Project filter from request
        if (!empty($selectedProjects)) {
            $query->whereIn('id_project', $selectedProjects);
            $summaryQuery->whereIn('id_project', $selectedProjects);
        }

        // Calculate summaries
        $openCasesCount = (clone $summaryQuery)->whereIn('status', ['Open', 'In Progress'])->count();
        $todayCasesCount = (clone $summaryQuery)->whereDate('open_date', \Carbon\Carbon::today())->count();
        $yesterdayCasesCount = (clone $summaryQuery)->whereDate('open_date', \Carbon\Carbon::yesterday())->count();

        // Type filter from request
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search by subject or location
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%$search%")
                  ->orWhere('location', 'like', "%$search%");
            });
        }

        // Custom sorting as requested
        $cases = $query->orderByRaw("
            CASE WHEN status IN ('Open', 'In Progress') THEN 1 ELSE 2 END ASC,
            CASE WHEN status IN ('Open', 'In Progress') THEN open_date ELSE '1970-01-01' END DESC,
            CASE WHEN status = 'Closed' THEN closed_date ELSE '1970-01-01' END DESC
        ")->paginate(setting('record_per_page', 15))->withQueryString();

        $lokasis = \App\Lokasi::pluck('nama_lokasi', 'nama_lokasi'); // Using name as value for the text field in MCase

        return view('cases.index', compact('title', 'cases', 'projects', 'lokasis', 'openCasesCount', 'todayCasesCount', 'yesterdayCasesCount'));
    }

    public function create()
    {
        $user = Auth::user();
        $title = 'Tambah Case Baru';

        $userProjects = $user->projects->pluck('id_project')->toArray();

        // Check if user has specific assigned projects
        if (count($userProjects) > 0) {
            $projects = Project::whereIn('id_project', $userProjects)->where('status_maintenance', 1)->pluck('nama_project', 'id_project');
            $fixedProject = count($userProjects) === 1;
        } else {
            // Admin or user with all project access
            $projects = Project::where('status_maintenance', 1)->pluck('nama_project', 'id_project');
            $fixedProject = false;
        }

        $lokasis = \App\Lokasi::all();

        return view('cases.create', compact('title', 'projects', 'fixedProject', 'lokasis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'type' => 'required|in:Problem/Incident,Request/Kegiatan,Relokasi/Reposisi',
            'open_date' => 'required|date',
            'closed_date' => 'nullable|date',
            'subject' => 'required|string|max:255',
            'detail' => 'required|string',
            'severity' => 'required|in:Minor,Major,Critical',
            'affected_sn' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_contact' => 'nullable|string|max:255',
            'ncm_customer' => 'nullable|string|max:255',
            'principal_ticket_no' => 'nullable|string|max:255',
            'resolution_detail' => 'nullable|string',
            'status' => 'required|in:Open,In Progress,Closed',
            'evidence_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'rca_file' => 'nullable|mimes:pdf|max:10240',
            'mop_file' => 'nullable|mimes:pdf|max:10240',
        ]);

        $validated['user_id'] = Auth::id();

        $uploads = $this->caseDocumentUploads($request);
        unset($validated['evidence_photo'], $validated['rca_file'], $validated['mop_file']);

        $case = MCase::create($validated);

        $failedDocuments = $this->storeCaseDocuments($case, $uploads);

        if (empty($failedDocuments)) {
            flash('Case dan dokumen berhasil disimpan!')->success();
        } else {
            flash('Case berhasil disimpan, tetapi upload ' . implode(', ', $failedDocuments) . ' gagal. Dokumen dapat diunggah kembali dari halaman edit.')->warning();
        }

        if ($case->type === 'Relokasi/Reposisi') {
            return redirect()->route('cases.show', $case->id_case)
                ->with('ask_relocation_ba', true);
        }

        return redirect()->route('cases.index');
    }

    public function downloadRelocationBa($case)
    {
        $case = MCase::with(['project', 'perangkat.jenisPerangkat', 'perangkat.lokasi'])->findOrFail($case);

        abort_unless($case->type === 'Relokasi/Reposisi', 404);

        $pdf = Pdf::loadView('cases.relocation-ba-pdf', compact('case'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Draft-BA-Relokasi-Reposisi-Case-'.$case->id_case.'.pdf');
    }

    public function getSerialNumbers($id_project)
    {
        $devices = \App\Perangkat::with('jenisPerangkat')
            ->where('id_project', $id_project)
            ->whereNotNull('serialnumber')
            ->get();

        $sns = [];
        foreach ($devices as $device) {
            $jenis = optional($device->jenisPerangkat)->jns_perangkat ?? '-';
            $merk = $device->merk ?? '-';
            $sn = $device->serialnumber;
            $hostname = $device->hostname ?? '-';
            $lantai = $device->lantai ?? '-';
            $sns[$sn] = "{$jenis} - {$merk} - {$sn} - {$hostname} - {$lantai}";
        }
            
        return response()->json($sns);
    }

    public function show($case)
    {
        $case = MCase::with(['project', 'user', 'documents'])->findOrFail($case);
        $title = 'Detail Case';

        // Fetch affected devices based on SN
        $affectedPerangkats = [];
        if ($case->affected_sn) {
            // Split by common delimiters (comma, semicolon, space, newline)
            $sns = preg_split('/[,\s;]+/', $case->affected_sn, -1, PREG_SPLIT_NO_EMPTY);
            $affectedPerangkats = Perangkat::whereIn('serialnumber', $sns)->get();
        }

        return view('cases.show', compact('title', 'case', 'affectedPerangkats'));
    }

    public function edit($case)
    {
        $case = MCase::with('documents')->findOrFail($case);
        $title = 'Edit Case';
        $user = Auth::user();
        
        $userProjects = $user->projects->pluck('id_project')->toArray();
        if (count($userProjects) > 0) {
            $projects = Project::whereIn('id_project', $userProjects)->pluck('nama_project', 'id_project');
        } else {
            $projects = Project::pluck('nama_project', 'id_project');
        }

        $lokasis = \App\Lokasi::all();

        // Fetch affected devices based on current SN for the replacement button
        $affectedPerangkats = [];
        if ($case->affected_sn) {
            $sns = preg_split('/[,\s;]+/', $case->affected_sn, -1, PREG_SPLIT_NO_EMPTY);
            $affectedPerangkats = Perangkat::whereIn('serialnumber', $sns)->get();
        }

        $mentionUsers = User::select('id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->orderBy('name')
            ->get();

        return view('cases.edit', compact('title', 'case', 'projects', 'lokasis', 'affectedPerangkats', 'mentionUsers'));
    }

    public function update(Request $request, $case)
    {
        $caseModel = MCase::findOrFail($case);

        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'type' => 'required|in:Problem/Incident,Request/Kegiatan,Relokasi/Reposisi',
            'open_date' => 'required|date',
            'closed_date' => 'nullable|date',
            'subject' => 'required|string|max:255',
            'detail' => 'required|string',
            'severity' => 'required|in:Minor,Major,Critical',
            'affected_sn' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_contact' => 'nullable|string|max:255',
            'ncm_customer' => 'nullable|string|max:255',
            'principal_ticket_no' => 'nullable|string|max:255',
            'resolution_detail' => 'nullable|string',
            'ba_number' => 'nullable|string|max:100',
            'ba_date' => 'nullable|date',
            'ba_origin_location' => 'nullable|string|max:255',
            'ba_destination_location' => 'nullable|string|max:255',
            'ba_customer_pic_name' => 'nullable|string|max:255',
            'ba_customer_pic_title' => 'nullable|string|max:255',
            'ba_executor_pic_name' => 'nullable|string|max:255',
            'ba_executor_pic_title' => 'nullable|string|max:255',
            'status' => 'required|in:Open,In Progress,Closed',
            'evidence_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'rca_file' => 'nullable|mimes:pdf|max:10240',
            'mop_file' => 'nullable|mimes:pdf|max:10240',
        ]);

        $uploads = $this->caseDocumentUploads($request);
        unset($validated['evidence_photo'], $validated['rca_file'], $validated['mop_file']);

        $caseModel->update($validated);

        $failedDocuments = $this->storeCaseDocuments($caseModel, $uploads);

        if (empty($failedDocuments)) {
            flash('Case berhasil diupdate!')->success();
        } else {
            flash('Case berhasil diupdate, tetapi upload ' . implode(', ', $failedDocuments) . ' gagal. File sebelumnya tetap aktif.')->warning();
        }
        return redirect()->route('cases.index');
    }

    public function document($case, $document)
    {
        $caseModel = MCase::findOrFail($case);
        $this->authorizeCaseAccess($caseModel);

        $caseDocument = CaseDocument::where('case_id', $caseModel->id_case)
            ->whereKey($document)
            ->where('status', 'available')
            ->firstOrFail();

        $fileName = $caseDocument->original_name ?: $caseDocument->stored_name ?: basename($caseDocument->path);
        $headers = [
            'Content-Type' => $caseDocument->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . str_replace(['"', "\r", "\n"], '', $fileName) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($caseDocument->disk === 'nextcloud') {
            try {
                $response = app(NextcloudWebDav::class)->download($caseDocument->path);
            } catch (NextcloudWebDavException $e) {
                abort($e->getStatusCode() === 404 ? 404 : 502, $e->getMessage());
            }

            $stream = $response->getBody();
            return response()->stream(function () use ($stream) {
                while (!$stream->eof()) {
                    echo $stream->read(8192);
                }
            }, 200, $headers);
        }

        abort_unless(Storage::disk($caseDocument->disk)->exists($caseDocument->path), 404);
        $stream = Storage::disk($caseDocument->disk)->readStream($caseDocument->path);

        return response()->stream(function () use ($stream) {
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, $headers);
    }

    public function storeComment(Request $request, $case)
    {
        $request->validate([
            'comment' => 'required|string'
        ]);

        $caseModel = MCase::with('project')->findOrFail($case);

        $comment = \App\CaseComment::create([
            'case_id' => $case,
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);

        $mentionedUsers = $this->findMentionedUsers($request->comment);

        if ($mentionedUsers->isNotEmpty()) {
            $comment->load('user');
            $caseUrl = route('cases.show', $caseModel->id_case);

            foreach ($mentionedUsers as $mentionedUser) {
                try {
                    Mail::to($mentionedUser->email)->send(
                        new CaseMentionNotification($caseModel, $comment, $mentionedUser, $caseUrl)
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send case mention notification', [
                        'case_id' => $caseModel->id_case,
                        'comment_id' => $comment->id,
                        'mentioned_user_id' => $mentionedUser->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'notified_count' => $mentionedUsers->count(),
            ]);
        }

        flash('Comment added successfully!')->success();
        return back();
    }

    private function findMentionedUsers($comment)
    {
        $commentLower = mb_strtolower($comment);

        return User::select('id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->get()
            ->filter(function ($user) use ($commentLower) {
                $mentionName = '@' . mb_strtolower($user->name);
                $mentionEmailName = '@' . mb_strtolower(strtok($user->email, '@'));

                return strpos($commentLower, $mentionName) !== false
                    || strpos($commentLower, $mentionEmailName) !== false;
            })
            ->unique('id')
            ->values();
    }

    private function caseDocumentUploads(Request $request)
    {
        return array_filter([
            CaseDocument::TYPE_EVIDENCE => $request->file('evidence_photo'),
            CaseDocument::TYPE_RCA => $request->file('rca_file'),
            CaseDocument::TYPE_MOP => $request->file('mop_file'),
        ]);
    }

    private function storeCaseDocuments(MCase $case, array $uploads)
    {
        $labels = [
            CaseDocument::TYPE_EVIDENCE => 'Foto Evidence',
            CaseDocument::TYPE_RCA => 'Dokumen RCA',
            CaseDocument::TYPE_MOP => 'Dokumen MOP',
        ];
        $failed = [];

        foreach ($uploads as $type => $upload) {
            $document = app(CaseDocumentStorage::class)->store($case, $type, $upload, Auth::id());
            if (!$document || $document->status !== 'available') {
                $failed[] = $labels[$type] ?? $type;
            }
        }

        return $failed;
    }

    private function authorizeCaseAccess(MCase $case)
    {
        $projectIds = Auth::user()->projects()->pluck('m_project.id_project');

        if ($projectIds->isNotEmpty()) {
            abort_unless($projectIds->contains((int) $case->id_project), 403);
        }
    }
}
