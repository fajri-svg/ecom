<?php

if (!function_exists('format_rupiah')) {
    /**
     * Format a currency value with Indonesian Rupiah symbol.
     *
     * @param float $number
     * @return string
     */
    function format_rupiah($number)
    {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
