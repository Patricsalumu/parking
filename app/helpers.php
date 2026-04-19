<?php

use Carbon\Carbon;

if (!function_exists('format_dt')) {
    /**
     * Format a date/time for display using entreprise timezone (IANA) and append a short zone label.
     * Returns empty string when $dt is falsy.
     */
    function format_dt($dt, $format = 'Y-m-d H:i', $tz = null)
    {
        if (!$dt) return '';

        try {
            if (is_string($dt)) {
                $c = Carbon::parse($dt);
            } elseif (method_exists($dt, 'copy')) {
                $c = $dt->copy();
            } elseif (method_exists($dt, 'format')) {
                $c = Carbon::parse($dt->format('Y-m-d H:i:s'));
            } else {
                $c = Carbon::parse($dt);
            }
        } catch (\Exception $e) {
            return '';
        }

        // determine timezone: explicit, or entreprise->timezone, or app.timezone, or fallback
        if (!$tz) {
            try {
                $ent = App\Models\Entreprise::first();
                $tz = $ent?->timezone ?: config('app.timezone') ?: 'Africa/Kinshasa';
            } catch (\Exception $e) {
                $tz = config('app.timezone') ?: 'Africa/Kinshasa';
            }
        }

        try {
            $c->setTimezone($tz);
        } catch (\Exception $e) {
            // if invalid tz, fallback to UTC
            $c->setTimezone('UTC');
            $tz = 'UTC';
        }

        // return formatted datetime in entreprise timezone (no timezone label)
        return $c->format($format);
    }
}
