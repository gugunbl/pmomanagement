<?php

namespace App\Services;

use App\ProjectReport;
use App\ProjectReportTeamFile;
use Illuminate\Support\Facades\DB;

class ProjectReportTeamFiles
{
    public function ensureSlots(ProjectReport $report)
    {
        return DB::transaction(function () use ($report) {
            $lockedReport = ProjectReport::query()->lockForUpdate()->findOrFail($report->id);
            $existing = ProjectReportTeamFile::query()
                ->where('project_report_id', $lockedReport->id)
                ->orderBy('id')
                ->get();

            // Once created, the rows are the historical team snapshot for this period.
            if ($existing->isNotEmpty()) {
                return $existing->load(['teamSupport', 'uploader']);
            }

            $teamIds = $lockedReport->project()
                ->firstOrFail()
                ->teamSupports()
                ->orderBy('m_team_supports.name')
                ->pluck('m_team_supports.id');

            foreach ($teamIds as $teamId) {
                ProjectReportTeamFile::firstOrCreate([
                    'project_report_id' => $lockedReport->id,
                    'team_support_id' => $teamId,
                ]);
            }

            return ProjectReportTeamFile::with(['teamSupport', 'uploader'])
                ->where('project_report_id', $lockedReport->id)
                ->orderBy('id')
                ->get();
        });
    }
}
