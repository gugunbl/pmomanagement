<?php

namespace App;

use Spatie\Activitylog\Models\Activity as BaseActivity;

class Activity extends BaseActivity
{
    /**
     * The existing activity_log table predates Activitylog v4. Ignoring these
     * optional attributes keeps the package compatible without altering the
     * production database schema.
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, ['event', 'batch_uuid'], true)) {
            return $this;
        }

        return parent::setAttribute($key, $value);
    }
}
