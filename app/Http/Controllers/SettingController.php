<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use anlutro\LaravelSettings\Facade as Setting;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:update-settings');
        $this->middleware('permission:view-activity-log', ['only' => ['activity']]);
    }
    public function index() {
        activity('settings')
            ->causedBy(Auth::user())
            ->log('view');
        $title =  'Settings';
        $roles = Role::pluck('name', 'id');
        return view('settings.edit', compact('roles', 'title'));
    }

    public function update(Request $request) {
        $request->validate([
            'utilization_effective_work_hours' => ['nullable', 'numeric', 'min:1', 'max:24'],
            'report_period_reminder_enabled' => ['nullable', 'boolean'],
            'report_period_reminder_time' => ['nullable', 'date_format:H:i'],
            'report_period_reminder_to' => ['nullable', 'string', 'max:2000'],
            'report_period_reminder_subject' => ['nullable', 'string', 'max:255'],
            'report_period_reminder_body' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = $request->except(['_token']);
        
        foreach ($data as $key => $value) {
            // Handle logo specifically if it exists in the loop
            if ($key == 'company_logo' && !empty($value)) {
                $settingValue = parse_url($value, PHP_URL_PATH);
            } elseif ($key == 'ticketing_url') {
                $settingValue = $this->normalizeTicketingUrl($value);
            } else {
                $settingValue = $value;
            }

            // Skip saving if value is null to avoid DB constraint violation
            if ($settingValue !== null) {
                Setting::set($key, $settingValue);
            }
        }

        Setting::save();

        activity('settings')
            ->causedBy(Auth::user())
            ->withProperties($data)
            ->log('updated');

        flash('Settings updated successfully!')->success();
        return back();
    }

    private function normalizeTicketingUrl($value)
    {
        $url = trim((string) $value);

        if ($url === '') {
            return config('settings.defaults.ticketing_url');
        }

        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }

        return rtrim($url, '/');
    }

    public function activity(Request $request){
        $title= 'Activity Logs';
        activity('activity')
        ->causedBy(Auth::user())
        ->log('view');
        $activities = Activity::latest()->paginate(setting('record_per_page', 15));
        return view('settings.activity', compact('activities', 'title'));
    }
}
