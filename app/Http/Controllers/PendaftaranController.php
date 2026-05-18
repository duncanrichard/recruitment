<?php

namespace App\Http\Controllers;

use App\Models\DataRiwayatDiri;

class PendaftaranController extends Controller
{
    public function index()
    {
        return view('pages.pendaftaran.index', [
            'title' => 'Pendaftaran',
            'token' => null,
            'pelamar' => null,
        ]);
    }

    public function show(string $token)
    {
        $pelamar = DataRiwayatDiri::query()
            ->with([
                'pendidikan',
                'agama',
                'kewarganegaraan',
                'statusPernikahan',
                'posisi',
                'perusahaan',
                'sosialMedia',
                'sumberInformasi',
            ])
            ->where('token', $token)
            ->firstOrFail();

        return view('pages.pendaftaran.index', [
            'title' => 'Pendaftaran',
            'token' => $token,
            'pelamar' => $pelamar,
        ]);
    }
}