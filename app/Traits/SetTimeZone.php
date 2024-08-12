<?php

namespace App\Traits;

use Carbon\Carbon;

trait SetTimeZone
{
    public $times = [
        //Asia
        'Asia/Gaza' => '+3',
        'Asia/Riyadh' => '+3',
        'Asia/Dubai' => '+4',
        'Asia/Qatar' => '+3',
        'Asia/Beirut' => '+3',
        'Asia/Damascus' => '+3',
        //Europe
        'Europe/Madrid' => '+2',
        'Europe/Istanbul' => '+3',
        'Europe/Berlin' => '+2',
        'Europe/London' => '+1',
        'Europe/Paris' => '+2',
        'Europe/Rome' => '+2',
        //Africa
        'Africa/Cairo' => '+2',
        //America
        'America/New_York' => '-4',
    ];

    public function getTimeDifference()
    {
        $thimZone = auth()->user()->timezone ?? env('DEFAULT_TIME_ZONE', 'Europe/Istanbul');

        return $this->times[$thimZone] ?? '0';
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->addHours($this->getTimeDifference());
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->addHours($this->getTimeDifference());
    }

    /**
     * to get any time value with user timezone
     */
    public function getTimezone($value)
    {
        if ($value) {
            return Carbon::parse($value)->addHours($this->getTimeDifference())->toDateTimeString();
        }
    }

    /**
     * get current time with user timezone
     */
    public function getCurrentTime()
    {
        return Carbon::now()->addHours($this->getTimeDifference());
    }
}
