<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Entreprise;
use Illuminate\Support\Facades\View;

class InjectTimezone
{
    /**
     * Share a short display timezone label with all views as $display_timezone
     */
    public function handle($request, Closure $next)
    {
        $tz = config('app.timezone') ?: 'Africa/Kinshasa';
        try {
            $ent = Entreprise::first();
            if ($ent && !empty($ent->timezone)) {
                $tz = $ent->timezone;
            }
        } catch (\Exception $e) {
            // ignore and fallback to config
        }

        // short label from IANA name
        $label = str_contains($tz, '/') ? explode('/', $tz)[1] : $tz;
        View::share('display_timezone', $label);

        return $next($request);
    }
}
