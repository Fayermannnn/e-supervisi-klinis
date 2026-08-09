<?php

namespace App\Modules\Observasi\Services;

use App\Events\HasilObservasiDifinalisasi;
use App\Exceptions\ButirTidakSesuaiInstrumenException;
use App\Exceptions\HasilObservasiBelumLengkapException;
use App\Exceptions\InstrumenAktifTidakTersediaException;
use App\Exceptions\SesiSupervisiNotFoundException;
use App\Exceptions\SkorDiLuarRentangException;
use App\Models\ButirObservasi;
use App\Models\HasilObservasi;
use App\Models\InstrumenObservasi;
use App\Models\SesiSupervisi;

class HasilObservasiService
{
    /**
     * simpanHasil() menangani draft & final dalam satu method (Sprint 5
     * backlog): setiap panggilan meng-upsert skor yang dikirim (bisa
     * sebagian, untuk draft), lalu bila status_akhir:true memvalidasi
     * seluruh butir sudah terisi dan memindahkan status sesi ke
     * "dianalisis".
     *
     * instrumen_id sesi (dideferred sejak Sprint 3) diisi otomatis di sini
     * dari instrumen yang sedang aktif/terkunci - SDD tidak menyebutkan
     * langkah eksplisit "pilih instrumen", dan hanya satu instrumen yang
     * semestinya aktif dipakai kabupaten-wide pada satu waktu.
     *
     * @param  array<string, mixed>  $data  butir: array<{butir_id, skor}>, status_akhir: bool
     *
     * @throws SesiSupervisiNotFoundException
     * @throws InstrumenAktifTidakTersediaException
     * @throws ButirTidakSesuaiInstrumenException
     * @throws SkorDiLuarRentangException
     * @throws HasilObservasiBelumLengkapException
     */
    public function simpanHasil(string $sesiId, array $data): SesiSupervisi
    {
        $sesi = SesiSupervisi::find($sesiId);

        if (! $sesi) {
            throw new SesiSupervisiNotFoundException;
        }

        if ($sesi->instrumen_id === null) {
            $instrumenAktif = InstrumenObservasi::where('terkunci', true)->latest('versi')->first();

            if (! $instrumenAktif) {
                throw new InstrumenAktifTidakTersediaException;
            }

            $sesi->instrumen_id = $instrumenAktif->id;
        }

        $instrumen = $sesi->instrumen()->first() ?? InstrumenObservasi::findOrFail($sesi->instrumen_id);
        $butirValid = ButirObservasi::where('instrumen_id', $instrumen->id)->pluck('id');

        foreach ($data['butir'] as $item) {
            if (! $butirValid->contains($item['butir_id'])) {
                throw new ButirTidakSesuaiInstrumenException;
            }

            if ($item['skor'] < $instrumen->skor_min || $item['skor'] > $instrumen->skor_maks) {
                throw new SkorDiLuarRentangException($instrumen->skor_min, $instrumen->skor_maks);
            }
        }

        foreach ($data['butir'] as $item) {
            HasilObservasi::updateOrCreate(
                ['sesi_id' => $sesi->id, 'butir_id' => $item['butir_id']],
                ['skor' => $item['skor']],
            );
        }

        $status = 'observasi';

        if (! empty($data['status_akhir'])) {
            $jumlahTerisi = HasilObservasi::where('sesi_id', $sesi->id)->count();

            if ($jumlahTerisi < $butirValid->count()) {
                $sesi->save();

                throw new HasilObservasiBelumLengkapException;
            }

            $status = 'dianalisis';
        }

        $sesi->status = $status;
        $sesi->save();

        $sesi = $sesi->fresh(['hasilObservasi']);

        if ($status === 'dianalisis') {
            HasilObservasiDifinalisasi::dispatch($sesi);
        }

        return $sesi;
    }
}
