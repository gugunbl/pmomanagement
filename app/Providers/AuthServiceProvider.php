<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability, $arguments = []) {
            if ($user && $user->isSuperAdmin()) {
                return true;
            }

            if ($ability === 'update-project' && !empty($arguments)) {
                $project = $arguments[0] ?? null;

                if ($project instanceof \App\Project && method_exists($user, 'canEditProject')) {
                    return $user->canEditProject($project) ? true : false;
                }
            }

            return null;
        });
    }
}
