import React from "react";
import InfoItem from "./InfoItem";

export default function CekTahapanPelamar({
    errors = {},
    hasil,
    loading = false,
    onBack,
}) {
    return (
        <main className="min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-cyan-50 px-4 py-10">
            <div className="mx-auto max-w-5xl">
                <div className="mb-8 text-center">
                    <span className="inline-flex rounded-full bg-cyan-50 px-4 py-1 text-xs font-bold uppercase tracking-wide text-teal-700 ring-1 ring-cyan-100">
                        Cek Tahapan Seleksi
                    </span>

                    <h1 className="mt-4 text-3xl font-black text-slate-950">
                        Status Pendaftaran Kandidat
                    </h1>

                    <p className="mt-3 text-sm leading-6 text-slate-500">
                        Data tahapan seleksi muncul otomatis berdasarkan token pada link pendaftaran.
                    </p>
                </div>

                <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl shadow-slate-200/70">
                    <div className="border-b border-white/10 bg-gradient-to-r from-slate-950 via-blue-950 to-teal-900 px-6 py-6 text-white sm:px-8">
                        <h2 className="text-xl font-black">
                            Informasi Tahapan Seleksi
                        </h2>

                        <p className="mt-2 text-sm text-slate-200">
                            Kandidat tidak perlu memasukkan token lagi karena sistem membaca token otomatis dari URL.
                        </p>
                    </div>

                    {loading && (
                        <div className="p-6 sm:p-8">
                            <div className="rounded-3xl border border-cyan-200 bg-cyan-50 p-5">
                                <div className="flex items-center gap-4">
                                    <div className="h-5 w-5 animate-spin rounded-full border-2 border-cyan-600 border-t-transparent" />

                                    <div>
                                        <h3 className="font-black text-cyan-800">
                                            Memuat Tahapan Seleksi
                                        </h3>

                                        <p className="mt-1 text-sm font-semibold text-cyan-700">
                                            Sistem sedang mengambil data seleksi kandidat.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {!loading && errors?.token && (
                        <div className="p-6 sm:p-8">
                            <div className="rounded-3xl border border-red-200 bg-red-50 p-5">
                                <div className="flex items-start gap-4">
                                    <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-500 text-lg font-black text-white">
                                        !
                                    </div>

                                    <div>
                                        <h3 className="font-black text-red-800">
                                            Data Tahapan Tidak Dapat Ditampilkan
                                        </h3>

                                        <p className="mt-1 text-sm font-semibold leading-6 text-red-700">
                                            {errors.token}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {!loading && !errors?.token && !hasil && (
                        <div className="p-6 sm:p-8">
                            <div className="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                                <div className="flex items-start gap-4">
                                    <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-400 text-lg font-black text-slate-950">
                                        !
                                    </div>

                                    <div>
                                        <h3 className="font-black text-amber-900">
                                            Data Tahapan Belum Tersedia
                                        </h3>

                                        <p className="mt-1 text-sm font-semibold leading-6 text-amber-800">
                                            Data tahapan seleksi belum tersedia atau masih dalam proses pemuatan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {!loading && hasil && <HasilTahapan hasil={hasil} />}

                    <div className="border-t border-slate-100 bg-white px-6 py-5 sm:px-8">
                        <button
                            type="button"
                            onClick={onBack}
                            className="rounded-2xl bg-slate-100 px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200"
                        >
                            Kembali ke Pendaftaran
                        </button>
                    </div>
                </div>
            </div>
        </main>
    );
}

function HasilTahapan({ hasil }) {
    const isGagal = String(hasil?.status || "").toLowerCase().includes("gagal");
    const isDiterima = String(hasil?.status || "").toLowerCase().includes("diterima");

    return (
        <div className="border-t border-slate-100 bg-slate-50 p-6 sm:p-8">
            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-wide text-teal-700">
                            Hasil Pengecekan
                        </p>

                        <h3
                            className={`mt-2 text-2xl font-black ${
                                isGagal
                                    ? "text-red-600"
                                    : isDiterima
                                    ? "text-emerald-600"
                                    : "text-teal-700"
                            }`}
                        >
                            {hasil?.status || "-"}
                        </h3>

                        <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            {hasil?.keterangan || "-"}
                        </p>
                    </div>

                    <span
                        className={`w-fit rounded-full px-4 py-2 text-xs font-bold ${
                            isGagal
                                ? "bg-red-100 text-red-700"
                                : isDiterima
                                ? "bg-emerald-100 text-emerald-700"
                                : "bg-teal-100 text-teal-700"
                        }`}
                    >
                        Tahap Terakhir: {hasil?.tahapan_terakhir || "-"}
                    </span>
                </div>

                <div className="grid grid-cols-1 gap-4 rounded-3xl border border-slate-100 bg-slate-50 p-5 md:grid-cols-4">
                    <InfoItem label="Nama Pelamar" value={hasil?.nama_pelamar} />
                    <InfoItem label="Posisi Dilamar" value={hasil?.posisi_dilamar} />
                    <InfoItem label="Perusahaan" value={hasil?.perusahaan_dilamar} />
                    <InfoItem label="Token Pelamar" value={hasil?.token} />
                </div>

                {hasil?.saran && (
                    <div
                        className={`mt-6 rounded-3xl border p-5 ${
                            isGagal
                                ? "border-red-200 bg-red-50"
                                : "border-teal-200 bg-teal-50"
                        }`}
                    >
                        <div className="flex items-start gap-4">
                            <div
                                className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg font-black text-white ${
                                    isGagal ? "bg-red-500" : "bg-teal-600"
                                }`}
                            >
                                !
                            </div>

                            <div>
                                <h4
                                    className={`font-black ${
                                        isGagal ? "text-red-800" : "text-teal-800"
                                    }`}
                                >
                                    Informasi Seleksi
                                </h4>

                                <p
                                    className={`mt-2 text-sm leading-6 ${
                                        isGagal ? "text-red-700" : "text-teal-700"
                                    }`}
                                >
                                    {hasil.saran}
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                <div className="mt-8">
                    <h4 className="mb-5 text-lg font-black text-slate-950">
                        Tahapan Seleksi Pelamar
                    </h4>

                    <div className="relative">
                        <div className="absolute left-5 top-8 hidden h-[calc(100%-4rem)] w-1 rounded-full bg-slate-200 md:block" />

                        <div className="space-y-5">
                            {(hasil?.tahapan || []).map((item, index) => {
                                const status = String(item?.status || "").toLowerCase();
                                const isLolos = status.includes("lolos");
                                const isItemGagal = status.includes("gagal");
                                const isProses = status.includes("proses");

                                return (
                                    <div
                                        key={`${item?.nama || "tahap"}-${index}`}
                                        className={`relative flex gap-4 rounded-3xl border p-4 shadow-sm ${
                                            isItemGagal
                                                ? "border-red-200 bg-red-50"
                                                : isProses
                                                ? "border-teal-200 bg-teal-50"
                                                : "border-slate-200 bg-white"
                                        }`}
                                    >
                                        <div
                                            className={`z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-black text-white shadow-lg ${
                                                isLolos
                                                    ? "bg-emerald-500 shadow-emerald-100"
                                                    : isItemGagal
                                                    ? "bg-red-500 shadow-red-100"
                                                    : isProses
                                                    ? "bg-teal-600 shadow-teal-100"
                                                    : "bg-slate-400 shadow-slate-100"
                                            }`}
                                        >
                                            {isLolos ? "✓" : isItemGagal ? "!" : index + 1}
                                        </div>

                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                <h5
                                                    className={`font-black ${
                                                        isItemGagal
                                                            ? "text-red-800"
                                                            : "text-slate-950"
                                                    }`}
                                                >
                                                    {item?.nama || "-"}
                                                </h5>

                                                <span
                                                    className={`w-fit rounded-full px-3 py-1 text-xs font-bold ${
                                                        isLolos
                                                            ? "bg-emerald-100 text-emerald-700"
                                                            : isItemGagal
                                                            ? "bg-red-100 text-red-700"
                                                            : isProses
                                                            ? "bg-teal-100 text-teal-700"
                                                            : "bg-slate-100 text-slate-700"
                                                    }`}
                                                >
                                                    {item?.status || "Menunggu"}
                                                </span>
                                            </div>

                                            <p
                                                className={`mt-2 text-sm leading-6 ${
                                                    isItemGagal
                                                        ? "text-red-700"
                                                        : "text-slate-500"
                                                }`}
                                            >
                                                {item?.keterangan || "-"}
                                            </p>

                                            {item?.saran && (
                                                <div className="mt-3 rounded-2xl border border-red-200 bg-white p-4">
                                                    <p className="text-sm font-semibold leading-6 text-red-700">
                                                        {item.saran}
                                                    </p>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
