<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Mail\SendAccessCredentials;
use App\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {

        $this->middleware('permission:view-user')->except(['profile', 'profileUpdate']);
        $this->middleware('permission:create-user', ['only' => ['create', 'store']]);
        $this->middleware('permission:update-user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:destroy-user', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {

        if ($request->has('search')) {
            $users = User::where('name', 'like', '%'.$request->search.'%')->paginate(setting('record_per_page', 15));
        } else {
            $users = User::paginate(setting('record_per_page', 15));
        }
        $title = 'Manage Users';

        return view('users.index', compact('users', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'Create user';
        $roles = Role::pluck('name', 'id');
        $projects = Project::where('status_maintenance', '<', 2)->orderBy('nama_project')->pluck('nama_project', 'id_project');

        return view('users.create', compact('roles', 'title', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(UserStoreRequest $request)
    {
        $userData = $request->except(['role', 'profile_photo', 'id_project']);
        if ($request->profile_photo) {
            $userData['profile_photo'] = parse_url($request->profile_photo, PHP_URL_PATH);
        }
        $user = User::create($userData);
        $user->assignRole((int) $request->role);

        if ($request->has('id_project') && is_array($request->id_project)) {
            $user->projects()->sync($request->id_project);
        }

        if ($request->has('team_support') && is_array($request->team_support)) {
            $user->teamSupports()->sync($request->team_support);
        }

        flash('User created successfully!')->success();

        return redirect()->route('users.index');

    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(User $user)
    {
        $title = 'User Details';
        $roles = Role::pluck('name', 'id');

        return view('users.show', compact('user', 'title', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(User $user)
    {
        $title = 'User Details';
        $roles = Role::pluck('name', 'id');
        $projects = Project::where('status_maintenance', '<', 2)->orderBy('nama_project')->pluck('nama_project', 'id_project')->toArray();

        return view('users.edit', compact('user', 'title', 'roles', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $userData = $request->except(['role', 'profile_photo', 'id_project']);
        if ($request->profile_photo) {
            $userData['profile_photo'] = parse_url($request->profile_photo, PHP_URL_PATH);
        }

        if (! $request->filled('password')) {
            unset($userData['password']);
        }

        $user->update($userData);
        $user->syncRoles((int) $request->role);

        if ($request->has('id_project') && is_array($request->id_project)) {
            $user->projects()->sync($request->id_project);
        } else {
            $user->projects()->sync([]);
        }

        if ($request->has('team_support') && is_array($request->team_support)) {
            $user->teamSupports()->sync($request->team_support);
        } else {
            $user->teamSupports()->sync([]);
        }

        flash('User updated successfully!')->success();

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(User $user)
    {
        if ($user->id == Auth::user()->id || $user->id == 1) {
            flash('You can not delete logged in user!')->warning();

            return back();
        }
        $user->delete();
        flash('User deleted successfully!')->info();

        return back();

    }

    public function profile(User $user)
    {
        $title = 'Edit Profile';

        return view('users.profile', compact('title', 'user'));
    }

    public function profileUpdate(UserUpdateRequest $request, User $user)
    {
        $userData = $request->except('profile_photo');
        if ($request->profile_photo) {
            $userData['profile_photo'] = parse_url($request->profile_photo, PHP_URL_PATH);
        }

        if (! $request->filled('password')) {
            unset($userData['password']);
        }

        $user->update($userData);
        flash('Profile updated successfully!')->success();

        return back();
    }

    /**
     * Send access credentials email to user.
     */
    public function sendAccessCredentials(User $user)
    {
        \Log::info('Attempting to send access credentials to user ID: '.$user->id);
        try {
            Mail::to($user->email)
                ->send(new SendAccessCredentials($user));

            $message = 'Email akses berhasil dikirim ke '.$user->email;
            flash($message)->success();
            session()->flash('success', $message);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

        } catch (\Exception $e) {
            $errorMsg = 'Gagal mengirim email: '.$e->getMessage();
            flash($errorMsg)->error();
            session()->flash('error', $errorMsg);
            \Log::error('Mail Error: '.$e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                ], 500);
            }
        }

        return back();
    }
}
