<?php

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    /**
     * Format integer rupiah tanpa desimal: Rp 1.250.000
     */
    function rupiah(int|float|null $amount): string
    {
        return 'Rp '.number_format((int) round((float) $amount), 0, ',', '.');
    }
}

if (! function_exists('tanggal_indonesia')) {
    /**
     * Format tanggal Indonesia: 27 Okt 2026, 14:30
     */
    function tanggal_indonesia(CarbonInterface|string|null $date, bool $withTime = false): string
    {
        if (blank($date)) {
            return '-';
        }

        $date = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        $bulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $result = $date->day.' '.$bulan[$date->month].' '.$date->year;

        return $withTime ? $result.', '.$date->format('H:i') : $result;
    }
}
