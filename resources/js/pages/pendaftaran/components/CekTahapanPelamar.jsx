import React, { useEffect, useMemo, useState } from "react";
import InfoItem from "./InfoItem";

export default function CekTahapanPelamar({
    errors = {},
    hasil,
    loading = false,
    onBack,
}) {
    const [hasilTahapan, setHasilTahapan] = useState(hasil || null);
    const [localErrors, setLocalErrors] = useState(errors || {});
    const [localLoading, setLocalLoading] = useState(false);

    const token = useMemo(() => {
        return getTokenFromUrl() || hasil?.token || null;
    }, [hasil]);

    useEffect(() => {
        let cancelled = false;

        async function fetchTahapan() {
            if (!token) {
                setHasilTahapan(hasil || null);
                return;
            }

            setLocalLoading(true);

            try {
                const response = await fetch(
                    `/pendaftaran/api/token/${encodeURIComponent(token)}/cek-tahapan`,
                    {
                        method: "GET",
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    }
                );

                const json = await response.json();

                if (cancelled) {
                    return;
                }

                if (!response.ok || json?.success === false) {
                    setLocalErrors(
                        json?.errors || {
                            token:
                                json?.message ||
                                "Data tahapan tidak dapat ditampilkan.",
                        }
                    );
                    setHasilTahapan(hasil || null);
                    return;
                }

                setLocalErrors({});
                setHasilTahapan(json?.data || hasil || null);
            } catch (error) {
                if (cancelled) {
                    return;
                }

                setLocalErrors({
                    token: "Gagal mengambil data tahapan seleksi.",
                });

                setHasilTahapan(hasil || null);
            } finally {
                if (!cancelled) {
                    setLocalLoading(false);
                }
            }
        }

        fetchTahapan();

        return () => {
            cancelled = true;
        };
    }, [token, hasil]);

    const isLoading = loading || localLoading;
    const errorData = localErrors || {};
    const dataHasil = hasilTahapan || hasil;

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

                    {isLoading && (
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

                    {!isLoading && errorData?.token && (
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
                                            {errorData.token}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {!isLoading && !errorData?.token && !dataHasil && (
                        <div className="p-6 sm:p-8">
                            <div className="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                                <h3 className="font-black text-amber-900">
                                    Data Tahapan Belum Tersedia
                                </h3>

                                <p className="mt-1 text-sm font-semibold leading-6 text-amber-800">
                                    Data tahapan seleksi belum tersedia atau masih dalam proses pemuatan.
                                </p>
                            </div>
                        </div>
                    )}

                    {!isLoading && !errorData?.token && dataHasil && (
                        <HasilTahapan
                            hasil={dataHasil}
                            token={token}
                            onUpdated={setHasilTahapan}
                        />
                    )}

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

function HasilTahapan({ hasil, token, onUpdated }) {
    const jadwalTest = getJadwalTestFromHasil(hasil);
    const isTerjadwal = Boolean(jadwalTest);
    const tahapan = buildTahapanTampil(jadwalTest);

    const statusUtama = isTerjadwal ? "Jadwal Test Tersedia" : "Administrasi";
    const keteranganUtama = isTerjadwal
        ? "Kandidat sudah mendapatkan jadwal test."
        : "Status seleksi kandidat saat ini berada pada tahap Administrasi.";
    const saranUtama = isTerjadwal
        ? "Silakan mengikuti test sesuai jadwal yang sudah ditentukan."
        : "Silakan pantau halaman ini secara berkala untuk melihat perkembangan proses seleksi.";
    const tahapTerakhir = isTerjadwal ? "Jadwal Test" : "Administrasi";

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
                                isTerjadwal ? "text-blue-700" : "text-teal-700"
                            }`}
                        >
                            {statusUtama}
                        </h3>

                        <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            {keteranganUtama}
                        </p>
                    </div>

                    <span
                        className={`w-fit rounded-full px-4 py-2 text-xs font-bold ${
                            isTerjadwal
                                ? "bg-blue-100 text-blue-700"
                                : "bg-teal-100 text-teal-700"
                        }`}
                    >
                        Tahap Terakhir: {tahapTerakhir}
                    </span>
                </div>

                <div className="grid grid-cols-1 gap-4 rounded-3xl border border-slate-100 bg-slate-50 p-5 md:grid-cols-4">
                    <InfoItem
                        label="Nama Pelamar"
                        value={hasil?.nama_pelamar || hasil?.nama_lengkap || "-"}
                    />

                    <InfoItem
                        label="Posisi Dilamar"
                        value={
                            hasil?.posisi_dilamar ||
                            hasil?.posisi_yang_dilamar ||
                            "-"
                        }
                    />

                    <InfoItem
                        label="Perusahaan"
                        value={hasil?.perusahaan_dilamar || "-"}
                    />

                    <InfoItem
                        label="Token Pelamar"
                        value={hasil?.token || "-"}
                    />
                </div>

                <div
                    className={`mt-6 rounded-3xl border p-5 ${
                        isTerjadwal
                            ? "border-blue-200 bg-blue-50"
                            : "border-teal-200 bg-teal-50"
                    }`}
                >
                    <div className="flex items-start gap-4">
                        <div
                            className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg font-black text-white ${
                                isTerjadwal ? "bg-blue-600" : "bg-teal-600"
                            }`}
                        >
                            !
                        </div>

                        <div>
                            <h4
                                className={`font-black ${
                                    isTerjadwal ? "text-blue-900" : "text-teal-800"
                                }`}
                            >
                                Informasi Seleksi
                            </h4>

                            <p
                                className={`mt-2 text-sm leading-6 ${
                                    isTerjadwal ? "text-blue-800" : "text-teal-700"
                                }`}
                            >
                                {saranUtama}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="mt-8">
                    <h4 className="mb-5 text-lg font-black text-slate-950">
                        Tahapan Seleksi Pelamar
                    </h4>

                    <div className="relative">
                        <div className="absolute left-5 top-8 hidden h-[calc(100%-4rem)] w-1 rounded-full bg-slate-200 md:block" />

                        <div className="space-y-5">
                            {tahapan.map((item, index) => (
                                <TahapanItem
                                    key={`${item.nama}-${index}`}
                                    item={item}
                                    index={index}
                                    token={token}
                                    onUpdated={onUpdated}
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

function TahapanItem({ item, index, token, onUpdated }) {
    const status = String(item?.status || "").toLowerCase();

    const isLolos = status.includes("lolos");
    const isProses = status.includes("proses");
    const isTerjadwal = status.includes("jadwal") || status.includes("terjadwal");

    return (
        <div
            className={`relative flex gap-4 rounded-3xl border p-4 shadow-sm ${
                isTerjadwal
                    ? "border-blue-200 bg-blue-50"
                    : isLolos
                    ? "border-emerald-200 bg-emerald-50"
                    : isProses
                    ? "border-teal-200 bg-teal-50"
                    : "border-slate-200 bg-white"
            }`}
        >
            <div
                className={`z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-black text-white shadow-lg ${
                    isTerjadwal
                        ? "bg-blue-600 shadow-blue-100"
                        : isLolos
                        ? "bg-emerald-500 shadow-emerald-100"
                        : isProses
                        ? "bg-teal-600 shadow-teal-100"
                        : "bg-slate-400 shadow-slate-100"
                }`}
            >
                {isTerjadwal ? "📅" : isLolos ? "✓" : index + 1}
            </div>

            <div className="min-w-0 flex-1">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h5
                        className={`font-black ${
                            isTerjadwal ? "text-blue-950" : "text-slate-950"
                        }`}
                    >
                        {item?.nama || "-"}
                    </h5>

                    <span
                        className={`w-fit rounded-full px-3 py-1 text-xs font-bold ${
                            isTerjadwal
                                ? "bg-blue-100 text-blue-700"
                                : isLolos
                                ? "bg-emerald-100 text-emerald-700"
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
                        isTerjadwal ? "text-blue-800" : "text-slate-500"
                    }`}
                >
                    {item?.keterangan || "-"}
                </p>

                {item?.jadwal_test && (
                    <JadwalTestDalamTahapan
                        jadwalTest={item.jadwal_test}
                        token={token}
                        onUpdated={onUpdated}
                    />
                )}
            </div>
        </div>
    );
}

function JadwalTestDalamTahapan({ jadwalTest, token, onUpdated }) {
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState("");
    const [localJadwalTest, setLocalJadwalTest] = useState(
        normalizeJadwalTest(jadwalTest)
    );

    useEffect(() => {
        setLocalJadwalTest(normalizeJadwalTest(jadwalTest));
    }, [jadwalTest]);

    const tanggal = getJadwalTanggal(localJadwalTest);
    const jam = getJadwalJam(localJadwalTest);
    const bolehIsiKehadiran = isJadwalHariIni(localJadwalTest);
    const kehadiran = normalizeKehadiran(localJadwalTest?.kehadiran);
    const sudahMengisiKehadiran = Boolean(kehadiran);

    async function submitKehadiran(value) {
        const currentKehadiran = normalizeKehadiran(localJadwalTest?.kehadiran);

        if (currentKehadiran) {
            setMessage("Status kehadiran sudah tersimpan dan tidak dapat diubah.");
            return;
        }

        if (saving) {
            return;
        }

        if (!token || !localJadwalTest?.id) {
            setMessage("Data jadwal tidak lengkap.");
            return;
        }

        const pilihan = value === "hadir" ? "hadir" : "tidak_hadir";

        setSaving(true);
        setMessage("");

        try {
            const response = await fetch(
                `/pendaftaran/api/token/${encodeURIComponent(token)}/jadwal-test/${encodeURIComponent(localJadwalTest.id)}/kehadiran`,
                {
                    method: "PATCH",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                    body: JSON.stringify({
                        kehadiran: pilihan,
                    }),
                }
            );

            const json = await parseJsonResponse(response);

            if (!response.ok || json?.success === false) {
                const serverJadwalTest = getJadwalTestFromHasil(json?.data);

                if (serverJadwalTest?.kehadiran) {
                    setLocalJadwalTest(serverJadwalTest);

                    onUpdated?.((previousData) =>
                        injectUpdatedJadwalTest(previousData, serverJadwalTest)
                    );
                }

                setMessage(
                    json?.message ||
                        "Gagal menyimpan kehadiran. Silakan coba lagi."
                );

                return;
            }

            const serverJadwalTest =
                getJadwalTestFromHasil(json?.data) ||
                normalizeJadwalTest({
                    ...localJadwalTest,
                    kehadiran: pilihan,
                });

            const updatedJadwalTest = normalizeJadwalTest({
                ...localJadwalTest,
                ...serverJadwalTest,
                kehadiran: serverJadwalTest?.kehadiran || pilihan,
            });

            setLocalJadwalTest(updatedJadwalTest);

            setMessage(
                updatedJadwalTest.kehadiran === "hadir"
                    ? "Kehadiran berhasil disimpan: Hadir."
                    : "Kehadiran berhasil disimpan: Tidak Hadir."
            );

            if (json?.data) {
                onUpdated?.(injectUpdatedJadwalTest(json.data, updatedJadwalTest));
            } else {
                onUpdated?.((previousData) =>
                    injectUpdatedJadwalTest(previousData, updatedJadwalTest)
                );
            }
        } catch (error) {
            setMessage("Gagal menyimpan kehadiran.");
        } finally {
            setSaving(false);
        }
    }

    return (
        <div className="mt-4 rounded-2xl border border-blue-200 bg-white p-4">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p className="text-sm font-black text-blue-900">
                        Jadwal Test: {tanggal}
                    </p>

                    <p className="mt-1 text-sm font-semibold text-blue-700">
                        Pukul {jam} WIB
                    </p>

                    {sudahMengisiKehadiran && (
                        <div className="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p className="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Status Kehadiran
                            </p>

                            <p
                                className={`mt-1 text-lg font-black ${
                                    kehadiran === "hadir"
                                        ? "text-emerald-700"
                                        : "text-red-700"
                                }`}
                            >
                                {kehadiran === "hadir" ? "Hadir" : "Tidak Hadir"}
                            </p>
                        </div>
                    )}

                    {!bolehIsiKehadiran && !sudahMengisiKehadiran && (
                        <p className="mt-3 text-sm font-semibold text-blue-700">
                            Pilihan kehadiran hanya muncul pada tanggal jadwal test.
                        </p>
                    )}

                    {bolehIsiKehadiran && !sudahMengisiKehadiran && (
                        <p className="mt-3 text-sm font-semibold text-blue-700">
                            Silakan pilih status kehadiran Anda.
                        </p>
                    )}

                    {sudahMengisiKehadiran && (
                        <p className="mt-3 text-sm font-semibold text-blue-700">
                            Status kehadiran sudah tersimpan dan tidak dapat diubah.
                        </p>
                    )}

                    {message && (
                        <p className="mt-3 text-sm font-bold text-blue-900">
                            {message}
                        </p>
                    )}
                </div>

                {bolehIsiKehadiran && !sudahMengisiKehadiran && (
                    <div className="flex flex-wrap gap-2">
                        <button
                            type="button"
                            disabled={saving}
                            onClick={() => submitKehadiran("hadir")}
                            className="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {saving ? "Menyimpan..." : "Hadir"}
                        </button>

                        <button
                            type="button"
                            disabled={saving}
                            onClick={() => submitKehadiran("tidak_hadir")}
                            className="rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {saving ? "Menyimpan..." : "Tidak Hadir"}
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}

function buildTahapanTampil(jadwalTest) {
    const tahapan = [
        {
            nama: "Administrasi",
            status: jadwalTest ? "Lolos" : "Proses",
            keterangan: jadwalTest
                ? "Tahap Administrasi sudah selesai."
                : "Kandidat sedang berada pada tahap Administrasi.",
            saran: jadwalTest
                ? null
                : "Silakan pantau halaman ini secara berkala untuk melihat perkembangan proses seleksi.",
        },
    ];

    if (jadwalTest) {
        tahapan.push({
            nama: "Jadwal Test",
            status: "Terjadwal",
            keterangan: "Jadwal test kandidat sudah tersedia.",
            saran: "Silakan mengikuti test sesuai jadwal yang sudah ditentukan.",
            jadwal_test: jadwalTest,
        });
    }

    return tahapan;
}

function getJadwalTestFromHasil(hasil) {
    if (!hasil) {
        return null;
    }

    const direct =
        hasil?.jadwal_test ||
        hasil?.jadwalTest ||
        hasil?.jadwal_test_zoom ||
        hasil?.jadwalTestZoom ||
        hasil?.tahapan_seleksi?.jadwal_test ||
        hasil?.tahapanSeleksi?.jadwal_test ||
        null;

    if (direct && hasValidJadwal(direct)) {
        return normalizeJadwalTest(direct);
    }

    const tahapan =
        Array.isArray(hasil?.tahapan)
            ? hasil.tahapan
            : Array.isArray(hasil?.tahapan_seleksi?.tahapan)
            ? hasil.tahapan_seleksi.tahapan
            : Array.isArray(hasil?.tahapanSeleksi?.tahapan)
            ? hasil.tahapanSeleksi.tahapan
            : [];

    const itemDenganJadwal = tahapan.find((item) => {
        return (
            item?.jadwal_test ||
            item?.jadwalTest ||
            item?.jadwal_test_zoom ||
            item?.jadwalTestZoom
        );
    });

    const nested =
        itemDenganJadwal?.jadwal_test ||
        itemDenganJadwal?.jadwalTest ||
        itemDenganJadwal?.jadwal_test_zoom ||
        itemDenganJadwal?.jadwalTestZoom ||
        null;

    if (nested && hasValidJadwal(nested)) {
        return normalizeJadwalTest(nested);
    }

    return null;
}

function normalizeJadwalTest(jadwalTest) {
    if (!jadwalTest) {
        return null;
    }

    const rawKehadiran =
        jadwalTest?.kehadiran ??
        jadwalTest?.status_kehadiran ??
        jadwalTest?.statusKehadiran ??
        jadwalTest?.konfirmasi_kehadiran ??
        jadwalTest?.konfirmasiKehadiran ??
        jadwalTest?.is_hadir ??
        jadwalTest?.isHadir ??
        jadwalTest?.hadir ??
        null;

    return {
        id: jadwalTest?.id || null,
        jadwal:
            jadwalTest?.jadwal ||
            jadwalTest?.tanggal_jadwal ||
            jadwalTest?.tanggalJadwal ||
            null,
        tanggal: jadwalTest?.tanggal || null,
        jam: jadwalTest?.jam || null,
        keterangan: jadwalTest?.keterangan || null,
        kehadiran: normalizeKehadiran(rawKehadiran),
    };
}

function normalizeKehadiran(value) {
    if (value === undefined || value === null || value === "") {
        return null;
    }

    const normalized = String(value)
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "_")
        .replace(/-/g, "_");

    if (
        normalized === "hadir" ||
        normalized === "1" ||
        normalized === "true" ||
        normalized === "ya" ||
        normalized === "yes"
    ) {
        return "hadir";
    }

    if (
        normalized === "tidak_hadir" ||
        normalized === "tidakhadir" ||
        normalized === "tidak" ||
        normalized === "0" ||
        normalized === "false" ||
        normalized === "no"
    ) {
        return "tidak_hadir";
    }

    return null;
}

function injectUpdatedJadwalTest(data, updatedJadwalTest) {
    if (!data) {
        return data;
    }

    const normalizedUpdatedJadwalTest = normalizeJadwalTest(updatedJadwalTest);

    if (!normalizedUpdatedJadwalTest) {
        return data;
    }

    const fixedData = {
        ...data,
        jadwal_test: {
            ...(data?.jadwal_test || {}),
            ...normalizedUpdatedJadwalTest,
        },
    };

    if (Array.isArray(fixedData.tahapan)) {
        fixedData.tahapan = fixedData.tahapan.map((item) => {
            if (!item?.jadwal_test) {
                return item;
            }

            return {
                ...item,
                jadwal_test: {
                    ...item.jadwal_test,
                    ...normalizedUpdatedJadwalTest,
                },
            };
        });
    }

    if (fixedData?.tahapan_seleksi?.jadwal_test) {
        fixedData.tahapan_seleksi = {
            ...fixedData.tahapan_seleksi,
            jadwal_test: {
                ...fixedData.tahapan_seleksi.jadwal_test,
                ...normalizedUpdatedJadwalTest,
            },
        };
    }

    if (Array.isArray(fixedData?.tahapan_seleksi?.tahapan)) {
        fixedData.tahapan_seleksi = {
            ...fixedData.tahapan_seleksi,
            tahapan: fixedData.tahapan_seleksi.tahapan.map((item) => {
                if (!item?.jadwal_test) {
                    return item;
                }

                return {
                    ...item,
                    jadwal_test: {
                        ...item.jadwal_test,
                        ...normalizedUpdatedJadwalTest,
                    },
                };
            }),
        };
    }

    if (fixedData?.tahapanSeleksi?.jadwal_test) {
        fixedData.tahapanSeleksi = {
            ...fixedData.tahapanSeleksi,
            jadwal_test: {
                ...fixedData.tahapanSeleksi.jadwal_test,
                ...normalizedUpdatedJadwalTest,
            },
        };
    }

    if (Array.isArray(fixedData?.tahapanSeleksi?.tahapan)) {
        fixedData.tahapanSeleksi = {
            ...fixedData.tahapanSeleksi,
            tahapan: fixedData.tahapanSeleksi.tahapan.map((item) => {
                if (!item?.jadwal_test) {
                    return item;
                }

                return {
                    ...item,
                    jadwal_test: {
                        ...item.jadwal_test,
                        ...normalizedUpdatedJadwalTest,
                    },
                };
            }),
        };
    }

    return fixedData;
}

function hasValidJadwal(jadwalTest) {
    return Boolean(
        jadwalTest &&
            (jadwalTest.jadwal ||
                jadwalTest.tanggal ||
                jadwalTest.jam ||
                jadwalTest.tanggal_jadwal ||
                jadwalTest.tanggalJadwal)
    );
}

function getJadwalTanggal(jadwalTest) {
    if (!jadwalTest) {
        return "-";
    }

    if (jadwalTest.tanggal) {
        return jadwalTest.tanggal;
    }

    if (!jadwalTest.jadwal) {
        return "-";
    }

    const date = parseJadwalDate(jadwalTest.jadwal);

    if (!date) {
        return "-";
    }

    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
}

function getJadwalJam(jadwalTest) {
    if (!jadwalTest) {
        return "-";
    }

    if (jadwalTest.jam) {
        return jadwalTest.jam;
    }

    if (!jadwalTest.jadwal) {
        return "-";
    }

    const date = parseJadwalDate(jadwalTest.jadwal);

    if (!date) {
        return "-";
    }

    return date.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    });
}

function isJadwalHariIni(jadwalTest) {
    const jadwalDate = parseJadwalDate(jadwalTest?.jadwal);

    if (!jadwalDate) {
        return false;
    }

    const today = new Date();

    return (
        jadwalDate.getFullYear() === today.getFullYear() &&
        jadwalDate.getMonth() === today.getMonth() &&
        jadwalDate.getDate() === today.getDate()
    );
}

function parseJadwalDate(value) {
    if (!value) {
        return null;
    }

    const normalizedValue =
        typeof value === "string" && value.includes(" ") && !value.includes("T")
            ? value.replace(" ", "T")
            : value;

    const date = new Date(normalizedValue);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return date;
}

async function parseJsonResponse(response) {
    const text = await response.text();

    if (!text) {
        return {};
    }

    try {
        return JSON.parse(text);
    } catch (error) {
        return {
            success: false,
            message: "Response server bukan JSON.",
        };
    }
}

function getTokenFromUrl() {
    const path = window.location.pathname || "";
    const match = path.match(/\/pendaftaran\/([^/]+)/);

    if (!match?.[1]) {
        return null;
    }

    return decodeURIComponent(match[1]);
}

function getCsrfToken() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || ""
    );
}