<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use PragmaRX\Countries\Package\Countries;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Countries::all()
            ->map(function ($country) {
                $dialCode = $country->dialling->calling_code[0] ?? null;

                if (!$dialCode) return null;

                return [
                    'name'      => $country->name->common ?? (string) $country->name,
                    'iso_code'  => $country->cca2,
                    'dial_code' => '+' . $dialCode,
                    'flag'      => $country->flag->emoji ?? null,
                ];
            })
            ->filter()
            ->sortBy('name')
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $countries
        ]);
    }
}
