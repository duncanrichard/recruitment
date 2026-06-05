import React, { useEffect, useMemo, useState } from "react";

const PESAN_LOLOS_SELEKSI = `SELAMAT!

Anda dinyatakan lolos tahap psikotes dan dapat melanjutkan ke tahap seleksi berikutnya.

Untuk informasi dan proses selanjutnya, silakan melakukan pengecekan secara berkala melalui website ini.

Terima kasih dan semoga sukses pada tahap berikutnya.`;

const PESAN_LOLOS_OFFERING_LETTER = `Selamat, Anda lolos tahap interview dan saat ini sedang dalam proses offering letter.

Jadwal penyampaian offering letter akan kami informasikan lebih lanjut. Jika ada pertanyaan, silakan hubungi kami melalui WhatsApp.`;

const PESAN_OFFERING_LETTER_MENERIMA = `Selamat, Anda telah menerima offering letter.

Saat ini Anda dinyatakan siap untuk bekerja. Silakan mempersiapkan diri dan mengikuti arahan dari tim rekrutmen atau HR untuk proses onboarding dan informasi mulai bekerja.

Terima kasih dan semoga sukses dalam perjalanan karier Anda bersama kami.`;

const PESAN_OFFERING_LETTER_MENOLAK = `Terima kasih atas konfirmasi Anda.

Kami menghargai keputusan Anda untuk menolak offering letter yang telah diberikan. Semoga keputusan ini menjadi pilihan terbaik dan semoga Anda mendapatkan kesempatan karier yang lebih sesuai di masa depan.

Tetap semangat dan sukses selalu.`;

const PESAN_OFFERING_LETTER_TIDAK_MELANJUTKAN = `Terima kasih sudah mengikuti proses seleksi sampai tahap offering letter.

Kami menghargai waktu, usaha, dan keputusan Anda untuk tidak melanjutkan proses ini. Semoga pengalaman ini tetap memberi manfaat dan semoga Anda mendapatkan kesempatan terbaik dalam perjalanan karier berikutnya.

Tetap semangat dan sukses selalu.`;

const PESAN_TIDAK_LOLOS_SELEKSI = `Terima kasih telah mengikuti proses seleksi di perusahaan kami.

Setelah melalui proses evaluasi, kami belum dapat melanjutkan Anda ke tahap berikutnya. Kami mengapresiasi waktu dan usaha yang telah diberikan.

Semoga sukses dan lancar dalam perjalanan karier Anda ke depan. Terima kasih.`;


function InfoItem({ label, value }) {
    const safeValue = value === null || value === undefined || value === "" ? "-" : value;

    return (
        <div className="min-w-0 rounded-2xl border border-slate-200 bg-white/80 p-3 shadow-sm sm:p-4">
            <p className="text-[11px] font-black uppercase tracking-wide text-slate-500">
                {label}
            </p>

            <p className="mt-1 break-words text-sm font-extrabold leading-6 text-slate-800">
                {safeValue}
            </p>
        </div>
    );
}

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

                if (cancelled) return;

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
                if (cancelled) return;

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
        <main className="min-h-screen overflow-x-hidden bg-gradient-to-br from-slate-100 via-slate-50 to-cyan-50 px-3 py-6 sm:px-4 sm:py-10">
            <div className="mx-auto w-full max-w-5xl">
                <div className="mb-6 text-center sm:mb-8">
                    <span className="inline-flex rounded-full bg-cyan-50 px-4 py-1 text-xs font-bold uppercase tracking-wide text-teal-700 ring-1 ring-cyan-100">
                        Cek Tahapan Seleksi
                    </span>

                    <h1 className="mt-4 text-2xl font-black leading-tight text-slate-950 sm:text-3xl">
                        Status Pendaftaran Kandidat
                    </h1>

                    <p className="mt-3 text-sm leading-6 text-slate-500">
                        Data tahapan seleksi muncul otomatis berdasarkan token pada link pendaftaran.
                    </p>
                </div>

                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 sm:rounded-[2rem]">
                    <div className="border-b border-white/10 bg-gradient-to-r from-slate-950 via-blue-950 to-teal-900 px-4 py-5 text-white sm:px-8 sm:py-6">
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
                                <div className="flex min-w-0 items-start gap-3 sm:gap-4">
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

                    <div className="border-t border-slate-100 bg-white px-4 py-5 sm:px-8">
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
    const hasilTest = getHasilTestFromHasil(hasil, jadwalTest);
    const jadwalMmpi = getJadwalMmpiFromHasil(hasil);
    const hasilTestMmpi = getHasilTestMmpiFromHasil(hasil, jadwalMmpi);
    const jadwalInterview = getJadwalInterviewFromHasil(hasil);
    const hasilInterview = getHasilInterviewFromHasil(hasil, jadwalInterview);
    const statusReviewManagement = getStatusReviewManagementFromHasil(
        hasil,
        jadwalInterview
    );
    const jadwalOfferingLetter = getJadwalOfferingLetterFromHasil(hasil, jadwalInterview);
    const statusJadwalOfferingLetter = getStatusJadwalOfferingLetterFromHasil(
        hasil,
        jadwalOfferingLetter
    );

    const completion = getKelengkapanForm(hasil);
    const bolehLanjutJadwalTestZoom = canAccessJadwalTestZoom(hasil);
    const pesanJadwalTestZoom = getPesanJadwalTestZoom(hasil);

    const isTerjadwal = Boolean(jadwalTest);
    const sudahAdaHasilTest = Boolean(hasilTest);
    const jadwalTerkunci = isTerjadwal && !bolehLanjutJadwalTestZoom;

    const interviewGagal =
        hasilInterview === "tidak_lolos_interview" ||
        hasilInterview === "gagal";

    const interviewLanjutReview = Boolean(hasilInterview) && !interviewGagal;
    const reviewDiterima = interviewLanjutReview && statusReviewManagement === "diterima";
    const reviewGagal = interviewLanjutReview && statusReviewManagement === "gagal";
    const reviewProses = interviewLanjutReview && !statusReviewManagement;

    const punyaJadwalOfferingLetter = Boolean(jadwalOfferingLetter);
    const statusOl = normalizeStatusOl(statusJadwalOfferingLetter);
    const olMenerima = statusOl === "menerima";
    const olMenolak = statusOl === "menolak";
    const olTidakMelanjutkan = statusOl === "tidak_melanjutkan";

    const tahapan = buildTahapanTampil(
        jadwalTest,
        hasilTest,
        jadwalMmpi,
        hasilTestMmpi,
        jadwalInterview,
        hasilInterview,
        bolehLanjutJadwalTestZoom,
        completion,
        pesanJadwalTestZoom,
        statusReviewManagement,
        jadwalOfferingLetter,
        statusJadwalOfferingLetter
    );

    const statusUtama = reviewDiterima && punyaJadwalOfferingLetter
        ? olMenerima
            ? "Offering Letter Diterima"
            : olMenolak
            ? "Offering Letter Ditolak"
            : olTidakMelanjutkan
            ? "Tidak Melanjutkan Offering Letter"
            : "Jadwal Offering Letter"
        : reviewDiterima
        ? "Jadwal Offering Letter Pending"
        : reviewProses
        ? "Interview"
        : reviewGagal
        ? "Gagal Interview"
        : interviewGagal
        ? "Gagal Interview"
        : jadwalInterview?.kehadiran === "reschedule"
        ? "Interview Reschedule"
        : jadwalInterview
        ? "Jadwal Interview Tersedia"
        : hasilTestMmpi
        ? hasilTestMmpi === "lolos"
            ? "Lolos Seleksi Test MMPI"
            : "Gagal Seleksi Test MMPI"
        : jadwalMmpi
        ? "Jadwal Test MMPI Tersedia"
        : sudahAdaHasilTest
        ? hasilTest === "lolos"
            ? "Lolos Seleksi Test Zoom"
            : "Gagal Seleksi Test Zoom"
        : jadwalTerkunci
        ? "Pendaftaran Belum Lengkap"
        : isTerjadwal
        ? "Jadwal Test Zoom Tersedia"
        : "Administrasi";

    const keteranganUtama = reviewDiterima && punyaJadwalOfferingLetter
        ? olMenerima
            ? "Kandidat sudah menerima Offering Letter dan siap untuk bekerja."
            : olMenolak
            ? "Kandidat menolak Offering Letter."
            : olTidakMelanjutkan
            ? "Kandidat tidak melanjutkan proses Offering Letter."
            : "Jadwal Offering Letter sudah tersedia."
        : reviewDiterima
        ? PESAN_LOLOS_OFFERING_LETTER
        : reviewProses
        ? "Kandidat sedang dalam proses Review Management."
        : reviewGagal
        ? "Kandidat dinyatakan gagal pada tahap interview berdasarkan Review Management."
        : interviewGagal
        ? "Kandidat dinyatakan tidak lolos pada tahap interview."
        : jadwalInterview?.kehadiran === "reschedule"
        ? "Jadwal interview kandidat sedang dalam proses penjadwalan ulang."
        : jadwalInterview
        ? "Kandidat sudah mendapatkan jadwal interview."
        : hasilTestMmpi
        ? hasilTestMmpi === "lolos"
            ? "Kandidat dinyatakan lolos pada seleksi test MMPI."
            : "Kandidat dinyatakan belum lolos pada seleksi test MMPI."
        : jadwalMmpi
        ? "Kandidat sudah mendapatkan jadwal test MMPI."
        : sudahAdaHasilTest
        ? hasilTest === "lolos"
            ? "Kandidat dinyatakan lolos pada seleksi test Zoom."
            : "Kandidat dinyatakan belum lolos pada seleksi test Zoom."
        : jadwalTerkunci
        ? "Jadwal Test Zoom belum dapat dilanjutkan karena data pendaftaran belum lengkap sampai tahap Kesiapan Bekerja."
        : isTerjadwal
        ? "Kandidat sudah mendapatkan jadwal test Zoom."
        : "Status seleksi kandidat saat ini berada pada tahap Administrasi.";

    const saranUtama = reviewDiterima
        ? getSaranOfferingLetterFrontend(statusJadwalOfferingLetter)
        : reviewProses
        ? "Silakan pantau halaman ini secara berkala untuk melihat hasil Review Management."
        : reviewGagal
        ? PESAN_TIDAK_LOLOS_SELEKSI
        : interviewGagal
        ? PESAN_TIDAK_LOLOS_SELEKSI
        : jadwalInterview?.kehadiran === "reschedule"
        ? "Silakan pantau informasi jadwal interview terbaru dari tim rekrutmen."
        : jadwalInterview
        ? PESAN_LOLOS_SELEKSI
        : hasilTestMmpi
        ? hasilTestMmpi === "lolos"
            ? PESAN_LOLOS_SELEKSI
            : PESAN_TIDAK_LOLOS_SELEKSI
        : jadwalMmpi
        ? PESAN_LOLOS_SELEKSI
        : sudahAdaHasilTest
        ? hasilTest === "lolos"
            ? PESAN_LOLOS_SELEKSI
            : PESAN_TIDAK_LOLOS_SELEKSI
        : jadwalTerkunci
        ? pesanJadwalTestZoom
        : isTerjadwal
        ? PESAN_LOLOS_SELEKSI
        : "Silakan pantau halaman ini secara berkala untuk melihat perkembangan proses seleksi.";

    const tahapTerakhir = reviewDiterima
        ? "Jadwal Offering Letter"
        : reviewGagal || interviewGagal
        ? "Interview"
        : reviewProses
        ? "Interview"
        : jadwalInterview?.kehadiran === "reschedule"
        ? "Reschedule Interview"
        : jadwalInterview
        ? "Jadwal Interview"
        : hasilTestMmpi
        ? "Hasil Seleksi Test MMPI"
        : jadwalMmpi
        ? "Jadwal Test MMPI"
        : sudahAdaHasilTest
        ? "Hasil Seleksi Test Zoom"
        : jadwalTerkunci
        ? completion.lastCompletedLabel || "Pendaftaran"
        : isTerjadwal
        ? "Jadwal Test Zoom"
        : "Administrasi";

    const warnaUtama = reviewDiterima
        ? olMenolak || olTidakMelanjutkan
            ? "amber"
            : "emerald"
        : reviewGagal || interviewGagal
        ? "red"
        : reviewProses
        ? "teal"
        : jadwalInterview?.kehadiran === "reschedule"
        ? "blue"
        : jadwalInterview
        ? "indigo"
        : hasilTestMmpi
        ? hasilTestMmpi === "lolos"
            ? "emerald"
            : "red"
        : jadwalMmpi
        ? "indigo"
        : sudahAdaHasilTest
        ? hasilTest === "lolos"
            ? "emerald"
            : "red"
        : jadwalTerkunci
        ? "amber"
        : isTerjadwal
        ? "blue"
        : "teal";

    return (
        <div className="border-t border-slate-100 bg-slate-50 p-3 sm:p-8">
            <div className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <div className="mb-6 flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-wide text-teal-700">
                            Hasil Pengecekan
                        </p>

                        <h3 className={`mt-2 break-words text-xl font-black leading-tight sm:text-2xl ${getTextColor(warnaUtama)}`}>
                            {statusUtama}
                        </h3>

                        <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            {keteranganUtama}
                        </p>
                    </div>

                    <span className={`w-fit max-w-full rounded-full px-3 py-2 text-xs font-bold leading-relaxed sm:px-4 ${getBadgeColor(warnaUtama)}`}>
                        Tahap Terakhir: {tahapTerakhir}
                    </span>
                </div>

                {jadwalTerkunci && (
                    <div className="mb-6 rounded-3xl border border-amber-200 bg-amber-50 p-5">
                        <div className="flex min-w-0 items-start gap-3 sm:gap-4">
                            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-500 text-lg font-black text-white">
                                !
                            </div>

                            <div>
                                <h4 className="font-black text-amber-900">
                                    Tahapan Test Zoom Belum Dapat Dilanjutkan
                                </h4>

                                <p className="mt-2 text-sm font-semibold leading-6 text-amber-800">
                                    {pesanJadwalTestZoom}
                                </p>

                                <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <InfoItem
                                        label="Kelengkapan Form"
                                        value={`${completion.completedSteps} dari ${completion.totalSteps} tahapan`}
                                    />

                                    <InfoItem
                                        label="Progress"
                                        value={`${completion.percentage}%`}
                                    />

                                    <InfoItem
                                        label="Tahap Terakhir Form"
                                        value={completion.lastCompletedLabel || "-"}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                <div className="grid grid-cols-1 gap-3 rounded-3xl border border-slate-100 bg-slate-50 p-3 sm:gap-4 sm:p-5 md:grid-cols-2 lg:grid-cols-4">
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

                <div className={`mt-6 rounded-3xl border p-4 sm:p-5 ${getBoxColor(warnaUtama)}`}>
                    <div className="flex min-w-0 items-start gap-3 sm:gap-4">
                        <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg font-black text-white ${getCircleColor(warnaUtama)}`}>
                            !
                        </div>

                        <div>
                            <h4 className={`font-black ${getTitleColor(warnaUtama)}`}>
                                Informasi Seleksi
                            </h4>

                            <p className={`mt-2 whitespace-pre-line text-sm leading-6 ${getDescriptionColor(warnaUtama)}`}>
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
                                    disabledAllActions={!bolehLanjutJadwalTestZoom}
                                    disabledReason={pesanJadwalTestZoom}
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

function TahapanItem({ item, index, token, onUpdated, disabledAllActions = false, disabledReason = "" }) {
    const status = String(item?.status || "").toLowerCase();

    const isLolos = status.includes("lolos");
    const isGagal = status.includes("gagal");
    const isProses = status.includes("proses");
    const isTerjadwal = status.includes("jadwal") || status.includes("terjadwal");
    const isTerkunci = status.includes("terkunci") || item?.disabled === true;
    const hasOfferingLetterDetail = Boolean(
        item?.jadwal_offering_letter || item?.jadwalOfferingLetter
    );
    const showSaran = Boolean(item?.saran) &&
        String(item?.saran || "").trim() !== String(item?.keterangan || "").trim() &&
        !hasOfferingLetterDetail;

    return (
        <div
            className={`relative flex min-w-0 flex-col gap-3 rounded-3xl border p-3 shadow-sm sm:flex-row sm:gap-4 sm:p-4 ${
                isGagal
                    ? "border-red-200 bg-red-50"
                    : isTerkunci
                    ? "border-amber-200 bg-amber-50"
                    : isTerjadwal
                    ? "border-blue-200 bg-blue-50"
                    : isLolos
                    ? "border-emerald-200 bg-emerald-50"
                    : isProses
                    ? "border-teal-200 bg-teal-50"
                    : "border-slate-200 bg-white"
            }`}
        >
            <div
                className={`z-10 flex h-10 w-10 shrink-0 sm:h-11 sm:w-11 items-center justify-center rounded-full text-sm font-black text-white shadow-lg ${
                    isGagal
                        ? "bg-red-600 shadow-red-100"
                        : isTerkunci
                        ? "bg-amber-500 shadow-amber-100"
                        : isTerjadwal
                        ? "bg-blue-600 shadow-blue-100"
                        : isLolos
                        ? "bg-emerald-500 shadow-emerald-100"
                        : isProses
                        ? "bg-teal-600 shadow-teal-100"
                        : "bg-slate-400 shadow-slate-100"
                }`}
            >
                {isGagal ? "×" : isTerkunci ? "!" : isTerjadwal ? "📅" : isLolos ? "✓" : index + 1}
            </div>

            <div className="min-w-0 flex-1 break-words">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h5
                        className={`font-black ${
                            isGagal
                                ? "text-red-950"
                                : isTerkunci
                                ? "text-amber-950"
                                : isTerjadwal
                                ? "text-blue-950"
                                : "text-slate-950"
                        }`}
                    >
                        {item?.nama || "-"}
                    </h5>

                    <span
                        className={`w-fit rounded-full px-3 py-1 text-xs font-bold ${
                            isGagal
                                ? "bg-red-100 text-red-700"
                                : isTerkunci
                                ? "bg-amber-100 text-amber-700"
                                : isTerjadwal
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
                    className={`mt-2 whitespace-pre-line text-sm leading-6 ${
                        isGagal
                            ? "text-red-800"
                            : isTerkunci
                            ? "text-amber-800"
                            : isTerjadwal
                            ? "text-blue-800"
                            : "text-slate-500"
                    }`}
                >
                    {item?.keterangan || "-"}
                </p>

                {showSaran && (
                    <p
                        className={`mt-2 whitespace-pre-line text-sm font-semibold leading-6 ${
                            isGagal
                                ? "text-red-800"
                                : isTerkunci
                                ? "text-amber-800"
                                : "text-slate-600"
                        }`}
                    >
                        {item.saran}
                    </p>
                )}

                {item?.jadwal_test && (
                    <JadwalTestDalamTahapan
                        jadwalTest={item.jadwal_test}
                        token={token}
                        onUpdated={onUpdated}
                        disabled={disabledAllActions || item?.disabled === true}
                        disabledReason={item?.disabled_reason || item?.disabledReason || disabledReason}
                    />
                )}

                {item?.jadwal_test_mmpi && (
                    <JadwalMmpiDalamTahapan
                        jadwalMmpi={item.jadwal_test_mmpi}
                        token={token}
                        onUpdated={onUpdated}
                    />
                )}

                {(item?.jadwal_interview || item?.jadwalInterview) && (
                    <JadwalInterviewDalamTahapan
                        jadwalInterview={item.jadwal_interview || item.jadwalInterview}
                        token={token}
                        onUpdated={onUpdated}
                    />
                )}

                {(item?.jadwal_offering_letter || item?.jadwalOfferingLetter) && (
                    <JadwalOfferingLetterDalamTahapan
                        jadwalOl={item.jadwal_offering_letter || item.jadwalOfferingLetter}
                    />
                )}
            </div>
        </div>
    );
}

function JadwalTestDalamTahapan({
    jadwalTest,
    token,
    onUpdated,
    disabled = false,
    disabledReason = "",
}) {
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
    const jadwalDisabled =
        disabled ||
        localJadwalTest?.disabled === true ||
        localJadwalTest?.boleh_akses_jadwal_test_zoom === false ||
        localJadwalTest?.bolehAksesJadwalTestZoom === false;
    const lockReason =
        disabledReason ||
        localJadwalTest?.disabled_reason ||
        localJadwalTest?.disabledReason ||
        "Tahapan Test Zoom belum dapat dilanjutkan. Lengkapi terlebih dahulu seluruh data pendaftaran sampai tahap Kesiapan Bekerja.";
    const bolehIsiKehadiran =
        !jadwalDisabled && isJadwalHariIni(localJadwalTest);
    const kehadiran = normalizeKehadiran(localJadwalTest?.kehadiran);
    const sudahMengisiKehadiran = Boolean(kehadiran);
    const linkZoom = jadwalDisabled ? null : getLinkZoom(localJadwalTest);
    const bolehBukaLinkZoom =
        !jadwalDisabled &&
        kehadiran === "hadir" &&
        Boolean(linkZoom) &&
        Boolean(localJadwalTest?.boleh_buka_link_zoom ?? true);

    async function submitKehadiran(value) {
        if (jadwalDisabled) {
            setMessage(lockReason);
            return;
        }

        const currentKehadiran = normalizeKehadiran(localJadwalTest?.kehadiran);

        if (currentKehadiran) {
            setMessage("Status kehadiran sudah tersimpan dan tidak dapat diubah.");
            return;
        }

        if (saving) return;

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
                    const fixedServerJadwalTest = normalizeJadwalTest({
                        ...localJadwalTest,
                        ...serverJadwalTest,
                    });

                    setLocalJadwalTest(fixedServerJadwalTest);

                    onUpdated?.((previousData) =>
                        injectUpdatedJadwalTest(previousData, fixedServerJadwalTest)
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
                    ? getLinkZoom(updatedJadwalTest)
                        ? "Kehadiran berhasil disimpan: Hadir. Link Zoom sudah bisa dibuka."
                        : "Kehadiran berhasil disimpan: Hadir. Link Zoom belum tersedia."
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
        <div
            className={`mt-4 rounded-2xl border p-3 sm:p-4 ${
                jadwalDisabled
                    ? "border-amber-200 bg-amber-50"
                    : "border-blue-200 bg-white"
            }`}
        >
            <div className="flex min-w-0 flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div className="min-w-0 flex-1 break-words">
                    <p
                        className={`text-sm font-black ${
                            jadwalDisabled ? "text-amber-900" : "text-blue-900"
                        }`}
                    >
                        Jadwal Test Zoom: {tanggal}
                    </p>

                    <p
                        className={`mt-1 text-sm font-semibold ${
                            jadwalDisabled ? "text-amber-800" : "text-blue-700"
                        }`}
                    >
                        Pukul {jam} WIB
                    </p>

                    {jadwalDisabled && (
                        <div className="mt-3 rounded-2xl border border-amber-200 bg-white p-4">
                            <p className="text-xs font-bold uppercase tracking-wide text-amber-700">
                                Akses Jadwal Test Zoom Terkunci
                            </p>

                            <p className="mt-2 text-sm font-semibold leading-6 text-amber-800">
                                {lockReason}
                            </p>
                        </div>
                    )}

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

                    {!jadwalDisabled && !bolehIsiKehadiran && !sudahMengisiKehadiran && (
                        <p className="mt-3 text-sm font-semibold text-blue-700">
                            Pilihan kehadiran hanya muncul pada tanggal jadwal test Zoom.
                        </p>
                    )}

                    {!jadwalDisabled && bolehIsiKehadiran && !sudahMengisiKehadiran && (
                        <p className="mt-3 text-sm font-semibold text-blue-700">
                            Silakan pilih status kehadiran Anda.
                        </p>
                    )}

                    {sudahMengisiKehadiran && (
                        <p className="mt-3 text-sm font-semibold text-blue-700">
                            Status kehadiran sudah tersimpan dan tidak dapat diubah.
                        </p>
                    )}

                    {!jadwalDisabled && kehadiran === "hadir" && !linkZoom && (
                        <div className="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                            <p className="text-sm font-bold text-amber-800">
                                Anda sudah memilih Hadir, tetapi Link Zoom belum tersedia.
                            </p>
                        </div>
                    )}

                    {bolehBukaLinkZoom && (
                        <div className="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <p className="text-xs font-bold uppercase tracking-wide text-emerald-700">
                                Link Zoom
                            </p>

                            <p className="mt-1 break-all text-sm font-semibold text-emerald-800">
                                {linkZoom}
                            </p>

                            <a
                                href={linkZoom}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-3 inline-flex w-full justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700 sm:w-auto"
                            >
                                Buka Link Zoom
                            </a>
                        </div>
                    )}

                    {message && (
                        <p
                            className={`mt-3 text-sm font-bold ${
                                jadwalDisabled ? "text-amber-900" : "text-blue-900"
                            }`}
                        >
                            {message}
                        </p>
                    )}
                </div>

                <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap">
                    <button
                        type="button"
                        disabled={saving || jadwalDisabled || !bolehIsiKehadiran || sudahMengisiKehadiran}
                        onClick={() => submitKehadiran("hadir")}
                        className="w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                    >
                        {saving ? "Menyimpan..." : "Hadir"}
                    </button>

                    <button
                        type="button"
                        disabled={saving || jadwalDisabled || !bolehIsiKehadiran || sudahMengisiKehadiran}
                        onClick={() => submitKehadiran("tidak_hadir")}
                        className="w-full rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                    >
                        {saving ? "Menyimpan..." : "Tidak Hadir"}
                    </button>
                </div>
            </div>
        </div>
    );
}


function JadwalMmpiDalamTahapan({ jadwalMmpi, token, onUpdated }) {
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState("");
    const [localJadwalMmpi, setLocalJadwalMmpi] = useState(
        normalizeJadwalMmpi(jadwalMmpi)
    );

    useEffect(() => {
        setLocalJadwalMmpi(normalizeJadwalMmpi(jadwalMmpi));
        setMessage("");
    }, [jadwalMmpi]);

    const normalized = localJadwalMmpi;
    const tanggal = getJadwalTanggal(normalized);
    const jam = getJadwalJam(normalized);
    const kehadiran = normalizeKehadiran(normalized?.kehadiran);
    const hasilTestMmpi = normalizeHasilTest(normalized?.hasil_test || normalized?.hasilTest);
    const sudahMengisiKehadiran = Boolean(kehadiran);
    const bolehIsiKehadiran = isJadwalHariIni(normalized);

    async function submitKehadiran(value) {
        if (!bolehIsiKehadiran) {
            setMessage("Kehadiran Test MMPI hanya dapat diisi pada tanggal jadwal test.");
            return;
        }

        if (sudahMengisiKehadiran) {
            setMessage("Status kehadiran Test MMPI sudah tersimpan dan tidak dapat diubah.");
            return;
        }

        if (saving) return;

        if (!token || !normalized?.id) {
            setMessage("Data jadwal Test MMPI tidak lengkap.");
            return;
        }

        const pilihan = value === "hadir" ? "hadir" : "tidak_hadir";

        setSaving(true);
        setMessage("");

        try {
            const response = await fetch(
                `/pendaftaran/api/token/${encodeURIComponent(token)}/jadwal-test-mmpi/${encodeURIComponent(normalized.id)}/kehadiran`,
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
                const serverJadwalMmpi = getJadwalMmpiFromHasil(json?.data);

                if (serverJadwalMmpi?.kehadiran) {
                    setLocalJadwalMmpi(serverJadwalMmpi);
                }

                setMessage(
                    json?.message ||
                        "Gagal menyimpan kehadiran Test MMPI. Silakan coba lagi."
                );

                return;
            }

            const serverJadwalMmpi = getJadwalMmpiFromHasil(json?.data);
            const updatedJadwalMmpi = normalizeJadwalMmpi({
                ...normalized,
                ...(serverJadwalMmpi || {}),
                kehadiran: serverJadwalMmpi?.kehadiran || pilihan,
                status_kehadiran: serverJadwalMmpi?.status_kehadiran || pilihan,
            });

            setLocalJadwalMmpi(updatedJadwalMmpi);
            setMessage(
                pilihan === "hadir"
                    ? "Kehadiran Test MMPI berhasil disimpan: Hadir."
                    : "Kehadiran Test MMPI berhasil disimpan: Tidak Hadir."
            );

            if (json?.data) {
                onUpdated?.(json.data);
            }
        } catch (error) {
            setMessage("Gagal menyimpan kehadiran Test MMPI.");
        } finally {
            setSaving(false);
        }
    }

    return (
        <div className="mt-4 rounded-2xl border border-indigo-200 bg-white p-4">
            <div className="flex min-w-0 flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div className="min-w-0 flex-1 break-words">
                    <div className="flex min-w-0 items-start gap-3 sm:gap-4">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-black text-white">
                            📝
                        </div>

                        <div className="min-w-0 flex-1 break-words">
                            <p className="break-words text-sm font-black text-indigo-900">
                                Jadwal Test MMPI: {tanggal}
                            </p>

                            <p className="mt-1 text-sm font-semibold text-indigo-700">
                                {jam && jam !== "-" && jam !== "00.00" && jam !== "00:00"
                                    ? `Pukul ${jam} WIB`
                                    : "Silakan hadir sesuai informasi dari tim rekrutmen."}
                            </p>

                            {normalized?.keterangan && (
                                <p className="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                    {normalized.keterangan}
                                </p>
                            )}
                        </div>
                    </div>

                    {sudahMengisiKehadiran && (
                        <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p className="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Status Kehadiran Test MMPI
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
                        <p className="mt-4 text-sm font-semibold text-indigo-700">
                            Tombol kehadiran Test MMPI hanya muncul pada tanggal jadwal test.
                        </p>
                    )}

                    {bolehIsiKehadiran && !sudahMengisiKehadiran && (
                        <p className="mt-4 text-sm font-semibold text-indigo-700">
                            Silakan pilih status kehadiran Test MMPI Anda.
                        </p>
                    )}

                    {sudahMengisiKehadiran && (
                        <p className="mt-3 text-sm font-semibold text-indigo-700">
                            Status kehadiran sudah tersimpan dan tidak dapat diubah.
                        </p>
                    )}

                    {message && (
                        <p className="mt-3 text-sm font-bold text-indigo-900">
                            {message}
                        </p>
                    )}
                </div>

                {(bolehIsiKehadiran || sudahMengisiKehadiran) && (
                    <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap">
                        <button
                            type="button"
                            disabled={saving || sudahMengisiKehadiran || !bolehIsiKehadiran}
                            onClick={() => submitKehadiran("hadir")}
                            className="w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        >
                            {saving ? "Menyimpan..." : "Hadir"}
                        </button>

                        <button
                            type="button"
                            disabled={saving || sudahMengisiKehadiran || !bolehIsiKehadiran}
                            onClick={() => submitKehadiran("tidak_hadir")}
                            className="w-full rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        >
                            {saving ? "Menyimpan..." : "Tidak Hadir"}
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}

function JadwalInterviewDalamTahapan({ jadwalInterview, token, onUpdated }) {
    const normalized = normalizeJadwalInterview(jadwalInterview);
    const tanggal = getJadwalTanggal(normalized);
    const jam = getJadwalJam(normalized);
    const kehadiran = normalizeKehadiranInterview(
        normalized?.kehadiran ||
            normalized?.status_kehadiran ||
            normalized?.statusKehadiran ||
            normalized?.status_kehadiran_interview ||
            normalized?.statusKehadiranInterview
    );
    const catatan =
        normalized?.catatan ||
        normalized?.catatan_interview ||
        normalized?.catatanInterview ||
        "";

    return (
        <div className="mt-4 rounded-2xl border border-purple-200 bg-white p-4">
            <div className="flex min-w-0 items-start gap-3 sm:gap-4">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-purple-600 text-lg font-black text-white">
                    🎙️
                </div>

                <div className="min-w-0 flex-1 break-words">
                    <p className="break-words text-sm font-black text-purple-900">
                        Jadwal Interview: {tanggal}
                    </p>

                    <p className="mt-1 text-sm font-semibold text-purple-700">
                        {jam && jam !== "-" && jam !== "00.00" && jam !== "00:00"
                            ? `Pukul ${jam} WIB`
                            : "Silakan hadir sesuai informasi dari tim rekrutmen."}
                    </p>

                    {normalized?.judul_interview && (
                        <p className="mt-2 text-sm font-semibold leading-6 text-slate-600">
                            {normalized.judul_interview}
                        </p>
                    )}

                    {kehadiran && (
                        <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p className="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Status Kehadiran Interview
                            </p>

                            <p
                                className={`mt-1 text-lg font-black ${getKehadiranInterviewTextColor(
                                    kehadiran
                                )}`}
                            >
                                {formatKehadiranInterview(kehadiran)}
                            </p>
                        </div>
                    )}

                    {catatan && (
                        <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p className="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Catatan Interview
                            </p>

                            <p className="mt-1 text-sm font-semibold leading-6 text-slate-700">
                                {catatan}
                            </p>
                        </div>
                    )}

                    <DokumenInterviewDalamTahapan
                        jadwalInterview={normalized}
                        token={token}
                        onUpdated={onUpdated}
                    />
                </div>
            </div>
        </div>
    );
}

function DokumenInterviewDalamTahapan({ jadwalInterview, token, onUpdated }) {
    const [cvFile, setCvFile] = useState(null);
    const [fotoFile, setFotoFile] = useState(null);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState("");
    const [localDokumen, setLocalDokumen] = useState({
        file_cv: jadwalInterview?.file_cv || null,
        file_foto: jadwalInterview?.file_foto || null,
    });

    useEffect(() => {
        setLocalDokumen({
            file_cv: jadwalInterview?.file_cv || null,
            file_foto: jadwalInterview?.file_foto || null,
        });
        setCvFile(null);
        setFotoFile(null);
        setMessage("");
    }, [jadwalInterview?.id, jadwalInterview?.file_cv, jadwalInterview?.file_foto]);

    const jadwalInterviewKandidatId =
        jadwalInterview?.id ||
        jadwalInterview?.jadwal_interview_kandidat_id ||
        jadwalInterview?.jadwalInterviewKandidatId ||
        null;

    const hasSelectedFile = Boolean(cvFile || fotoFile);

    async function uploadDokumenInterview() {
        if (saving) return;

        if (!token || !jadwalInterviewKandidatId) {
            setMessage("Data jadwal interview tidak lengkap.");
            return;
        }

        if (!hasSelectedFile) {
            setMessage("Silakan pilih file CV atau Foto terlebih dahulu.");
            return;
        }

        setSaving(true);
        setMessage("");

        try {
            const formData = new FormData();

            if (cvFile) {
                formData.append("file_cv", cvFile);
            }

            if (fotoFile) {
                formData.append("file_foto", fotoFile);
            }

            const response = await fetch(
                `/pendaftaran/api/token/${encodeURIComponent(token)}/jadwal-interview/${encodeURIComponent(jadwalInterviewKandidatId)}/dokumen`,
                {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                    body: formData,
                }
            );

            const json = await parseJsonResponse(response);

            if (!response.ok || json?.success === false) {
                setMessage(json?.message || "Gagal upload dokumen interview.");
                return;
            }

            const uploaded = json?.data || {};
            const updatedDokumen = {
                file_cv: normalizeFileUrl(uploaded.file_cv || uploaded.fileCv || localDokumen.file_cv),
                file_foto: normalizeFileUrl(uploaded.file_foto || uploaded.fileFoto || localDokumen.file_foto),
            };

            setLocalDokumen(updatedDokumen);
            setCvFile(null);
            setFotoFile(null);
            setMessage(json?.message || "Dokumen interview berhasil diupload.");

            if (typeof onUpdated === "function") {
                onUpdated((previousData) =>
                    injectUpdatedDokumenInterview(
                        previousData,
                        jadwalInterviewKandidatId,
                        updatedDokumen
                    )
                );
            }
        } catch (error) {
            setMessage("Terjadi kesalahan saat upload dokumen interview.");
        } finally {
            setSaving(false);
        }
    }

    return (
        <div className="mt-4 rounded-2xl border border-purple-100 bg-purple-50 p-4">
            <p className="text-xs font-black uppercase tracking-wide text-purple-700">
                Upload Dokumen Interview
            </p>

            <p className="mt-2 text-sm font-semibold leading-6 text-purple-800">
                Silakan upload CV dan Foto setelah mendapatkan jadwal interview.
            </p>

            <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div className="rounded-2xl border border-white/70 bg-white p-4">
                    <div className="mb-3">
                        <p className="text-xs font-black uppercase tracking-wide text-slate-500">
                            CV
                        </p>

                        {localDokumen.file_cv ? (
                            <a
                                href={localDokumen.file_cv}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-2 inline-flex rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 transition hover:bg-blue-100"
                            >
                                Lihat CV
                            </a>
                        ) : (
                            <p className="mt-2 text-xs font-bold text-slate-400">
                                CV belum diupload.
                            </p>
                        )}
                    </div>

                    <input
                        type="file"
                        accept=".pdf,.doc,.docx"
                        disabled={saving}
                        onChange={(event) => setCvFile(event.target.files?.[0] || null)}
                        className="block w-full text-xs font-bold text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-black file:text-slate-700 hover:file:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-60"
                    />

                    {cvFile && (
                        <p className="mt-2 break-all text-xs font-bold text-purple-700">
                            File dipilih: {cvFile.name}
                        </p>
                    )}
                </div>

                <div className="rounded-2xl border border-white/70 bg-white p-4">
                    <div className="mb-3">
                        <p className="text-xs font-black uppercase tracking-wide text-slate-500">
                            Foto
                        </p>

                        {localDokumen.file_foto ? (
                            <a
                                href={localDokumen.file_foto}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-2 inline-flex rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 transition hover:bg-blue-100"
                            >
                                Lihat Foto
                            </a>
                        ) : (
                            <p className="mt-2 text-xs font-bold text-slate-400">
                                Foto belum diupload.
                            </p>
                        )}
                    </div>

                    <input
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                        disabled={saving}
                        onChange={(event) => setFotoFile(event.target.files?.[0] || null)}
                        className="block w-full text-xs font-bold text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-black file:text-slate-700 hover:file:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-60"
                    />

                    {fotoFile && (
                        <p className="mt-2 break-all text-xs font-bold text-purple-700">
                            File dipilih: {fotoFile.name}
                        </p>
                    )}
                </div>
            </div>

            {hasSelectedFile && (
                <button
                    type="button"
                    disabled={saving}
                    onClick={uploadDokumenInterview}
                    className="mt-4 w-full rounded-2xl bg-purple-600 px-5 py-3 text-sm font-black text-white transition hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >
                    {saving ? "Mengupload..." : "Upload Dokumen"}
                </button>
            )}

            {message && (
                <p className="mt-3 text-sm font-bold text-purple-900">
                    {message}
                </p>
            )}
        </div>
    );
}

function JadwalOfferingLetterDalamTahapan({ jadwalOl }) {
    const normalized = normalizeJadwalOfferingLetter(jadwalOl);

    if (!normalized) return null;

    const tanggal = getJadwalTanggal(normalized);
    const jam = getJadwalJam(normalized);
    const status = normalized.status_jadwal || "Pending";
    const statusOl = normalizeStatusOl(status);
    const pesan = getSaranOfferingLetterFrontend(status);
    const hasLink = Boolean(normalized.link && normalized.link !== "-");

    const statusClass =
        statusOl === "menerima"
            ? "bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200"
            : statusOl === "menolak" || statusOl === "tidak_melanjutkan"
            ? "bg-amber-100 text-amber-700 ring-1 ring-amber-200"
            : "bg-blue-100 text-blue-700 ring-1 ring-blue-200";

    return (
        <div className="mt-4 overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
            <div className="p-4">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex min-w-0 items-start gap-3 sm:gap-4">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-sm font-black text-white shadow-sm">
                            OL
                        </div>

                        <div className="min-w-0 flex-1 break-words">
                            <p className="text-sm font-black leading-6 text-emerald-900">
                                Jadwal Offering Letter: {tanggal}
                            </p>

                            <p className="mt-1 text-sm font-semibold leading-6 text-emerald-700">
                                {jam && jam !== "-" && jam !== "00.00" && jam !== "00:00"
                                    ? `Pukul ${jam} WIB`
                                    : "Silakan mengikuti informasi dari tim rekrutmen."}
                            </p>
                        </div>
                    </div>

                    <span className={`w-fit shrink-0 rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ${statusClass}`}>
                        {status}
                    </span>
                </div>

                <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <InfoItem label="Metode" value={normalized.metode || "-"} />
                    <InfoItem label="PIC" value={normalized.pic || "-"} />
                    <InfoItem label="Status Jadwal" value={status} />
                    <InfoItem label="Link" value={normalized.link || "-"} />
                </div>

                {normalized.catatan && (
                    <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p className="text-xs font-bold uppercase tracking-wide text-slate-500">
                            Catatan Offering Letter
                        </p>

                        <p className="mt-1 break-words text-sm font-semibold leading-6 text-slate-700">
                            {normalized.catatan}
                        </p>
                    </div>
                )}

                <div className="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                    <p className="whitespace-pre-line break-words text-sm font-bold leading-6 text-emerald-800">
                        {pesan}
                    </p>
                </div>

                {hasLink && (
                    <a
                        href={normalized.link}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="mt-4 inline-flex w-full justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-center text-sm font-black text-white transition hover:bg-emerald-700 sm:w-auto"
                    >
                        Buka Link Offering Letter
                    </a>
                )}
            </div>
        </div>
    );
}

function buildTahapanTampil(
    jadwalTest,
    hasilTest,
    jadwalMmpi = null,
    hasilTestMmpi = null,
    jadwalInterview = null,
    hasilInterview = null,
    bolehLanjutJadwalTestZoom = true,
    completion = {},
    pesanJadwalTestZoom = "",
    statusReviewManagement = null,
    jadwalOfferingLetter = null,
    statusJadwalOfferingLetter = null
) {
    const lockMessage =
        pesanJadwalTestZoom ||
        "Tahapan Test Zoom belum dapat dilanjutkan karena data pendaftaran belum lengkap sampai tahap Kesiapan Bekerja. Silakan lengkapi seluruh formulir pendaftaran terlebih dahulu.";
    const jadwalTerkunci = Boolean(jadwalTest) && !bolehLanjutJadwalTestZoom;

    const interviewGagal =
        hasilInterview === "tidak_lolos_interview" ||
        hasilInterview === "gagal";

    const interviewLanjutReview = Boolean(hasilInterview) && !interviewGagal;
    const reviewDiterima = interviewLanjutReview && statusReviewManagement === "diterima";
    const reviewGagal = interviewLanjutReview && statusReviewManagement === "gagal";
    const reviewProses = interviewLanjutReview && !statusReviewManagement;
    const punyaJadwalOfferingLetter = Boolean(jadwalOfferingLetter);

    const tahapan = [
        {
            nama: "Administrasi",
            status: jadwalTest && bolehLanjutJadwalTestZoom ? "Lolos" : "Proses",
            keterangan:
                jadwalTest && bolehLanjutJadwalTestZoom
                    ? "Tahap Administrasi sudah selesai."
                    : "Kandidat sedang berada pada tahap Administrasi.",
            saran:
                jadwalTest && bolehLanjutJadwalTestZoom
                    ? null
                    : jadwalTerkunci
                    ? lockMessage
                    : "Silakan pantau halaman ini secara berkala untuk melihat perkembangan proses seleksi.",
        },
    ];

    if (jadwalTest) {
        tahapan.push({
            nama: "Jadwal Test Zoom",
            status: bolehLanjutJadwalTestZoom ? "Terjadwal" : "Terkunci",
            keterangan: bolehLanjutJadwalTestZoom
                ? "Jadwal test Zoom kandidat sudah tersedia."
                : "Jadwal test Zoom sudah tersedia, namun belum dapat dilanjutkan karena formulir pendaftaran belum lengkap sampai tahap Kesiapan Bekerja.",
            saran: bolehLanjutJadwalTestZoom
                ? "Silakan mengikuti test Zoom sesuai jadwal yang sudah ditentukan."
                : lockMessage,
            jadwal_test: {
                ...jadwalTest,
                disabled: !bolehLanjutJadwalTestZoom,
                disabled_reason: bolehLanjutJadwalTestZoom ? null : lockMessage,
                disabledReason: bolehLanjutJadwalTestZoom ? null : lockMessage,
                boleh_akses_jadwal_test_zoom: bolehLanjutJadwalTestZoom,
                bolehAksesJadwalTestZoom: bolehLanjutJadwalTestZoom,
            },
            disabled: !bolehLanjutJadwalTestZoom,
            disabled_reason: bolehLanjutJadwalTestZoom ? null : lockMessage,
            disabledReason: bolehLanjutJadwalTestZoom ? null : lockMessage,
        });
    }

    if (hasilTest && bolehLanjutJadwalTestZoom) {
        tahapan.push({
            nama: "Hasil Seleksi Test Zoom",
            status: hasilTest === "lolos" ? "Lolos" : "Gagal",
            keterangan:
                hasilTest === "lolos"
                    ? PESAN_LOLOS_SELEKSI
                    : PESAN_TIDAK_LOLOS_SELEKSI,
            saran: null,
            hasil_test: hasilTest,
        });

        if (hasilTest === "lolos") {
            tahapan.push({
                nama: "Jadwal Test MMPI",
                status: jadwalMmpi ? "Terjadwal" : "Menunggu",
                keterangan: jadwalMmpi
                    ? "Jadwal test MMPI kandidat sudah tersedia."
                    : "Kandidat sudah lolos test Zoom dan sedang menunggu jadwal test MMPI.",
                saran: jadwalMmpi
                    ? "Silakan mengikuti test MMPI sesuai jadwal yang sudah ditentukan."
                    : "Silakan pantau halaman ini secara berkala untuk informasi jadwal test MMPI.",
                jadwal_test_mmpi: jadwalMmpi || null,
            });

            if (hasilTestMmpi) {
                tahapan.push({
                    nama: "Hasil Seleksi Test MMPI",
                    status: hasilTestMmpi === "lolos" ? "Lolos" : "Gagal",
                    keterangan:
                        hasilTestMmpi === "lolos"
                            ? PESAN_LOLOS_SELEKSI
                            : PESAN_TIDAK_LOLOS_SELEKSI,
                    saran: null,
                    hasil_test_mmpi: hasilTestMmpi,
                    hasilTestMmpi: hasilTestMmpi,
                });

                if (hasilTestMmpi === "lolos") {
                    tahapan.push({
                        nama: "Jadwal Interview",
                        status: jadwalInterview
                            ? jadwalInterview?.kehadiran === "reschedule"
                                ? "Reschedule"
                                : "Terjadwal"
                            : "Menunggu",
                        keterangan: jadwalInterview
                            ? jadwalInterview?.kehadiran === "reschedule"
                                ? "Jadwal interview kandidat sedang dalam proses penjadwalan ulang."
                                : "Jadwal interview kandidat sudah tersedia."
                            : "Kandidat sudah lolos test MMPI dan sedang menunggu jadwal interview.",
                        saran: jadwalInterview
                            ? jadwalInterview?.kehadiran === "reschedule"
                                ? "Silakan pantau informasi jadwal interview terbaru dari tim rekrutmen."
                                : "Silakan mengikuti interview sesuai jadwal yang sudah ditentukan."
                            : "Silakan pantau halaman ini secara berkala untuk informasi jadwal interview.",
                        jadwal_interview: jadwalInterview || null,
                        jadwalInterview: jadwalInterview || null,
                    });

                    if (hasilInterview) {
                        const reviewManagementText =
                            jadwalInterview?.review_management ||
                            jadwalInterview?.reviewManagement ||
                            null;

                        if (interviewGagal) {
                            tahapan.push({
                                nama: "Interview",
                                status: "Gagal Interview",
                                keterangan: PESAN_TIDAK_LOLOS_SELEKSI,
                                saran: null,
                                hasil_interview: hasilInterview,
                                hasilInterview: hasilInterview,
                                review_management: reviewManagementText,
                                reviewManagement: reviewManagementText,
                                status_review_management: statusReviewManagement,
                                statusReviewManagement: statusReviewManagement,
                            });
                        }

                        if (interviewLanjutReview) {
                            tahapan.push({
                                nama: "Interview",
                                status: reviewProses
                                    ? "Review Management"
                                    : reviewDiterima
                                    ? "Lolos Interview"
                                    : "Gagal Interview",
                                keterangan: reviewProses
                                    ? "Hasil interview sudah tersedia dan sedang diproses ke tahap Review Management."
                                    : reviewDiterima
                                    ? PESAN_LOLOS_SELEKSI
                                    : PESAN_TIDAK_LOLOS_SELEKSI,
                                saran: reviewProses
                                    ? "Silakan pantau halaman ini secara berkala untuk melihat hasil Review Management."
                                    : null,
                                hasil_interview: hasilInterview,
                                hasilInterview: hasilInterview,
                                review_management: reviewManagementText,
                                reviewManagement: reviewManagementText,
                                status_review_management: statusReviewManagement,
                                statusReviewManagement: statusReviewManagement,
                            });

                            if (reviewDiterima) {
                                tahapan.push({
                                    nama: "Jadwal Offering Letter",
                                    status: punyaJadwalOfferingLetter
                                        ? statusJadwalOfferingLetter || "Pending"
                                        : "Pending",
                                    keterangan: punyaJadwalOfferingLetter
                                        ? "Jadwal Offering Letter sudah tersedia."
                                        : PESAN_LOLOS_OFFERING_LETTER,
                                    saran: null,
                                    jadwal_offering_letter: jadwalOfferingLetter,
                                    jadwalOfferingLetter: jadwalOfferingLetter,
                                });
                            }
                        }
                    }
                }
            }
        }
    }

    return tahapan;
}

function getKelengkapanForm(hasil) {
    const completion = hasil?.kelengkapan_form || hasil?.kelengkapanForm || {};

    const totalSteps = Number(
        completion?.total_steps ??
            completion?.totalSteps ??
            hasil?.total_step_form ??
            hasil?.totalStepForm ??
            5
    );

    const completedSteps = Number(
        completion?.completed_steps ??
            completion?.completedSteps ??
            hasil?.total_step_terisi ??
            hasil?.totalStepTerisi ??
            0
    );

    const percentage = Number(
        completion?.percentage ??
            hasil?.persentase_kelengkapan ??
            hasil?.persentaseKelengkapan ??
            (totalSteps > 0 ? Math.round((completedSteps / totalSteps) * 100) : 0)
    );

    return {
        percentage: Math.min(100, Math.max(0, percentage || 0)),
        completedSteps: Math.min(totalSteps || 5, Math.max(0, completedSteps || 0)),
        totalSteps: totalSteps || 5,
        lastCompletedLabel:
            completion?.last_completed_label ??
            completion?.lastCompletedLabel ??
            hasil?.tahap_terakhir_form ??
            hasil?.tahapTerakhirForm ??
            "-",
        steps: Array.isArray(completion?.steps) ? completion.steps : [],
    };
}

function canAccessJadwalTestZoom(hasil) {
    const explicit =
        hasil?.boleh_melanjutkan_jadwal_test_zoom ??
        hasil?.bolehMelanjutkanJadwalTestZoom ??
        hasil?.can_access_jadwal_test_zoom ??
        hasil?.canAccessJadwalTestZoom ??
        hasil?.tahapan_seleksi?.boleh_melanjutkan_jadwal_test_zoom ??
        hasil?.tahapanSeleksi?.bolehMelanjutkanJadwalTestZoom ??
        null;

    if (explicit !== null && explicit !== undefined) {
        return Boolean(explicit);
    }

    const completion = getKelengkapanForm(hasil);

    return completion.completedSteps >= completion.totalSteps;
}

function getPesanJadwalTestZoom(hasil) {
    return (
        hasil?.pesan_jadwal_test_zoom ||
        hasil?.pesanJadwalTestZoom ||
        hasil?.tahapan_seleksi?.pesan_jadwal_test_zoom ||
        hasil?.tahapanSeleksi?.pesanJadwalTestZoom ||
        "Tahapan Test Zoom belum dapat dilanjutkan karena data pendaftaran belum lengkap sampai tahap Kesiapan Bekerja. Silakan lengkapi seluruh formulir pendaftaran terlebih dahulu."
    );
}




function getJadwalOfferingLetterFromHasil(hasil, jadwalInterview = null) {
    const direct =
        hasil?.jadwal_offering_letter ||
        hasil?.jadwalOfferingLetter ||
        hasil?.offering_letter ||
        hasil?.offeringLetter ||
        jadwalInterview?.jadwal_offering_letter ||
        jadwalInterview?.jadwalOfferingLetter ||
        jadwalInterview?.offering_letter ||
        jadwalInterview?.offeringLetter ||
        hasil?.tahapan_seleksi?.jadwal_offering_letter ||
        hasil?.tahapanSeleksi?.jadwalOfferingLetter ||
        null;

    if (direct) return normalizeJadwalOfferingLetter(direct);

    const tahapan = Array.isArray(hasil?.tahapan)
        ? hasil.tahapan
        : Array.isArray(hasil?.tahapan_seleksi?.tahapan)
        ? hasil.tahapan_seleksi.tahapan
        : Array.isArray(hasil?.tahapanSeleksi?.tahapan)
        ? hasil.tahapanSeleksi.tahapan
        : [];

    const itemOfferingLetter = tahapan.find((item) => {
        const nama = String(item?.nama || "").toLowerCase();
        return (
            nama.includes("offering") ||
            item?.jadwal_offering_letter ||
            item?.jadwalOfferingLetter ||
            item?.offering_letter ||
            item?.offeringLetter
        );
    });

    const nested =
        itemOfferingLetter?.jadwal_offering_letter ||
        itemOfferingLetter?.jadwalOfferingLetter ||
        itemOfferingLetter?.offering_letter ||
        itemOfferingLetter?.offeringLetter ||
        null;

    return nested ? normalizeJadwalOfferingLetter(nested) : null;
}

function getStatusJadwalOfferingLetterFromHasil(hasil, jadwalOl = null) {
    const raw =
        hasil?.status_jadwal_offering_letter ||
        hasil?.statusJadwalOfferingLetter ||
        hasil?.status_jadwal_ol ||
        hasil?.statusJadwalOl ||
        jadwalOl?.status_jadwal ||
        jadwalOl?.statusJadwal ||
        jadwalOl?.status ||
        null;

    return raw || null;
}

function normalizeJadwalOfferingLetter(jadwalOl) {
    if (!jadwalOl) return null;

    const status =
        jadwalOl?.status_jadwal ||
        jadwalOl?.statusJadwal ||
        jadwalOl?.status ||
        "Pending";

    return {
        ...jadwalOl,
        id: jadwalOl?.id || jadwalOl?.jadwal_offering_letter_id || jadwalOl?.jadwalOfferingLetterId || null,
        tanggal_ol:
            jadwalOl?.tanggal_ol ||
            jadwalOl?.tanggalOl ||
            jadwalOl?.tanggal ||
            jadwalOl?.jadwal ||
            null,
        tanggalOl:
            jadwalOl?.tanggalOl ||
            jadwalOl?.tanggal_ol ||
            jadwalOl?.tanggal ||
            jadwalOl?.jadwal ||
            null,
        jadwal:
            jadwalOl?.jadwal ||
            jadwalOl?.tanggal_ol ||
            jadwalOl?.tanggalOl ||
            jadwalOl?.tanggal ||
            null,
        jam_ol: jadwalOl?.jam_ol || jadwalOl?.jamOl || jadwalOl?.jam || null,
        jamOl: jadwalOl?.jamOl || jadwalOl?.jam_ol || jadwalOl?.jam || null,
        jam: jadwalOl?.jam || jadwalOl?.jam_ol || jadwalOl?.jamOl || null,
        metode: jadwalOl?.metode || "-",
        link: jadwalOl?.link || null,
        pic: jadwalOl?.pic || null,
        catatan: jadwalOl?.catatan || jadwalOl?.catatan_ol || jadwalOl?.catatanOl || null,
        status_jadwal: status || "Pending",
        statusJadwal: status || "Pending",
    };
}

function normalizeStatusOl(value) {
    if (value === null || value === undefined || value === "") return null;

    const normalized = String(value).toLowerCase().trim().replace(/[\s-]+/g, "_");

    if (["menerima", "terima", "diterima", "accept", "accepted"].includes(normalized)) {
        return "menerima";
    }

    if (["menolak", "tolak", "ditolak", "reject", "rejected"].includes(normalized)) {
        return "menolak";
    }

    if (["tidak_melanjutkan", "tidakmelanjutkan", "tidak_lanjut", "tidak_lanjutkan"].includes(normalized)) {
        return "tidak_melanjutkan";
    }

    if (["pending", "menunggu", "proses", ""].includes(normalized)) {
        return null;
    }

    return normalized;
}

function getSaranOfferingLetterFrontend(statusJadwal) {
    const status = normalizeStatusOl(statusJadwal);

    if (status === "menerima") {
        return PESAN_OFFERING_LETTER_MENERIMA;
    }

    if (status === "menolak") {
        return PESAN_OFFERING_LETTER_MENOLAK;
    }

    if (status === "tidak_melanjutkan") {
        return PESAN_OFFERING_LETTER_TIDAK_MELANJUTKAN;
    }

    return PESAN_LOLOS_OFFERING_LETTER;
}

function getJadwalInterviewFromHasil(hasil) {
    if (!hasil) return null;

    const direct =
        hasil?.jadwal_interview ||
        hasil?.jadwalInterview ||
        hasil?.interview ||
        hasil?.tahapan_seleksi?.jadwal_interview ||
        hasil?.tahapanSeleksi?.jadwalInterview ||
        null;

    if (direct && hasValidJadwalInterview(direct)) {
        return normalizeJadwalInterview(direct);
    }

    const tahapan = Array.isArray(hasil?.tahapan)
        ? hasil.tahapan
        : Array.isArray(hasil?.tahapan_seleksi?.tahapan)
        ? hasil.tahapan_seleksi.tahapan
        : Array.isArray(hasil?.tahapanSeleksi?.tahapan)
        ? hasil.tahapanSeleksi.tahapan
        : [];

    const itemDenganJadwal = tahapan.find((item) => item?.jadwal_interview || item?.jadwalInterview);
    const nested = itemDenganJadwal?.jadwal_interview || itemDenganJadwal?.jadwalInterview || null;

    if (nested && hasValidJadwalInterview(nested)) {
        return normalizeJadwalInterview(nested);
    }

    return null;
}

function getHasilInterviewFromHasil(hasil, jadwalInterview = null) {
    const raw =
        hasil?.hasil_interview ??
        hasil?.hasilInterview ??
        hasil?.status_hasil_interview ??
        hasil?.statusHasilInterview ??
        jadwalInterview?.hasil_interview ??
        jadwalInterview?.hasilInterview ??
        jadwalInterview?.status_hasil_interview ??
        jadwalInterview?.statusHasilInterview ??
        hasil?.tahapan_seleksi?.hasil_interview ??
        hasil?.tahapanSeleksi?.hasilInterview ??
        null;

    const direct = normalizeHasilInterview(raw);
    if (direct) return direct;

    const tahapan = Array.isArray(hasil?.tahapan)
        ? hasil.tahapan
        : Array.isArray(hasil?.tahapan_seleksi?.tahapan)
        ? hasil.tahapan_seleksi.tahapan
        : Array.isArray(hasil?.tahapanSeleksi?.tahapan)
        ? hasil.tahapanSeleksi.tahapan
        : [];

    const itemHasilInterview = tahapan.find((item) => {
        const nama = String(item?.nama || "").toLowerCase();
        return nama.includes("hasil interview") || normalizeHasilInterview(item?.hasil_interview) || normalizeHasilInterview(item?.hasilInterview);
    });

    return normalizeHasilInterview(
        itemHasilInterview?.hasil_interview ||
            itemHasilInterview?.hasilInterview ||
            itemHasilInterview?.status_hasil_interview ||
            itemHasilInterview?.statusHasilInterview ||
            itemHasilInterview?.status
    );
}

function getStatusReviewManagementFromHasil(hasil, jadwalInterview = null) {
    const raw =
        hasil?.status_review_management ??
        hasil?.statusReviewManagement ??
        hasil?.status_review ??
        hasil?.statusReview ??
        hasil?.hasil_review_management ??
        hasil?.hasilReviewManagement ??
        jadwalInterview?.status_review_management ??
        jadwalInterview?.statusReviewManagement ??
        jadwalInterview?.status_review ??
        jadwalInterview?.statusReview ??
        hasil?.tahapan_seleksi?.status_review_management ??
        hasil?.tahapanSeleksi?.statusReviewManagement ??
        null;

    const direct = normalizeStatusReviewManagement(raw);
    if (direct) return direct;

    const tahapan = Array.isArray(hasil?.tahapan)
        ? hasil.tahapan
        : Array.isArray(hasil?.tahapan_seleksi?.tahapan)
        ? hasil.tahapan_seleksi.tahapan
        : Array.isArray(hasil?.tahapanSeleksi?.tahapan)
        ? hasil.tahapanSeleksi.tahapan
        : [];

    const itemReviewManagement = tahapan.find((item) => {
        const nama = String(item?.nama || "").toLowerCase();

        return (
            nama.includes("review management") ||
            normalizeStatusReviewManagement(item?.status_review_management) ||
            normalizeStatusReviewManagement(item?.statusReviewManagement) ||
            normalizeStatusReviewManagement(item?.status_review) ||
            normalizeStatusReviewManagement(item?.statusReview)
        );
    });

    return normalizeStatusReviewManagement(
        itemReviewManagement?.status_review_management ||
            itemReviewManagement?.statusReviewManagement ||
            itemReviewManagement?.status_review ||
            itemReviewManagement?.statusReview ||
            itemReviewManagement?.status
    );
}

function getHasilTestMmpiFromHasil(hasil, jadwalMmpi = null) {
    const raw =
        hasil?.hasil_test_mmpi ??
        hasil?.hasilTestMmpi ??
        hasil?.status_hasil_test_mmpi ??
        hasil?.statusHasilTestMmpi ??
        jadwalMmpi?.hasil_test ??
        jadwalMmpi?.hasilTest ??
        jadwalMmpi?.status_hasil_test ??
        jadwalMmpi?.statusHasilTest ??
        hasil?.tahapan_seleksi?.hasil_test_mmpi ??
        hasil?.tahapanSeleksi?.hasil_test_mmpi ??
        hasil?.tahapanSeleksi?.hasilTestMmpi ??
        null;

    const direct = normalizeHasilTest(raw);

    if (direct) return direct;

    const tahapan = Array.isArray(hasil?.tahapan)
        ? hasil.tahapan
        : Array.isArray(hasil?.tahapan_seleksi?.tahapan)
        ? hasil.tahapan_seleksi.tahapan
        : Array.isArray(hasil?.tahapanSeleksi?.tahapan)
        ? hasil.tahapanSeleksi.tahapan
        : [];

    const itemHasilMmpi = tahapan.find((item) => {
        const nama = String(item?.nama || "").toLowerCase();

        return (
            nama.includes("hasil seleksi test mmpi") ||
            nama.includes("hasil test mmpi") ||
            normalizeHasilTest(item?.hasil_test_mmpi) ||
            normalizeHasilTest(item?.hasilTestMmpi) ||
            normalizeHasilTest(item?.status_hasil_test_mmpi) ||
            normalizeHasilTest(item?.statusHasilTestMmpi)
        );
    });

    return normalizeHasilTest(
        itemHasilMmpi?.hasil_test_mmpi ||
            itemHasilMmpi?.hasilTestMmpi ||
            itemHasilMmpi?.status_hasil_test_mmpi ||
            itemHasilMmpi?.statusHasilTestMmpi ||
            itemHasilMmpi?.hasil_test ||
            itemHasilMmpi?.hasilTest ||
            itemHasilMmpi?.status
    );
}

function getJadwalMmpiFromHasil(hasil) {
    if (!hasil) return null;

    const direct =
        hasil?.jadwal_test_mmpi ||
        hasil?.jadwalTestMmpi ||
        hasil?.jadwal_mmpi ||
        hasil?.jadwalMmpi ||
        hasil?.tahapan_seleksi?.jadwal_test_mmpi ||
        hasil?.tahapan_seleksi?.jadwal_mmpi ||
        hasil?.tahapanSeleksi?.jadwal_test_mmpi ||
        hasil?.tahapanSeleksi?.jadwalMmpi ||
        null;

    if (direct && hasValidJadwalMmpi(direct)) {
        return normalizeJadwalMmpi(direct);
    }

    const tahapan = Array.isArray(hasil?.tahapan)
        ? hasil.tahapan
        : Array.isArray(hasil?.tahapan_seleksi?.tahapan)
        ? hasil.tahapan_seleksi.tahapan
        : Array.isArray(hasil?.tahapanSeleksi?.tahapan)
        ? hasil.tahapanSeleksi.tahapan
        : [];

    const itemDenganJadwal = tahapan.find((item) => {
        return (
            item?.jadwal_test_mmpi ||
            item?.jadwalTestMmpi ||
            item?.jadwal_mmpi ||
            item?.jadwalMmpi
        );
    });

    const nested =
        itemDenganJadwal?.jadwal_test_mmpi ||
        itemDenganJadwal?.jadwalTestMmpi ||
        itemDenganJadwal?.jadwal_mmpi ||
        itemDenganJadwal?.jadwalMmpi ||
        null;

    if (nested && hasValidJadwalMmpi(nested)) {
        return normalizeJadwalMmpi(nested);
    }

    return null;
}

function normalizeJadwalMmpi(jadwalMmpi) {
    if (!jadwalMmpi) return null;

    const rawKehadiran =
        jadwalMmpi?.kehadiran ??
        jadwalMmpi?.status_kehadiran ??
        jadwalMmpi?.statusKehadiran ??
        jadwalMmpi?.konfirmasi_kehadiran ??
        jadwalMmpi?.konfirmasiKehadiran ??
        null;

    const rawHasilTest =
        jadwalMmpi?.hasil_test ??
        jadwalMmpi?.hasilTest ??
        jadwalMmpi?.status_hasil_test ??
        jadwalMmpi?.statusHasilTest ??
        null;

    return {
        id: jadwalMmpi?.id || null,
        daftar_hadir_test_mmpi_id:
            jadwalMmpi?.daftar_hadir_test_mmpi_id ||
            jadwalMmpi?.daftarHadirTestMmpiId ||
            null,
        daftarHadirTestMmpiId:
            jadwalMmpi?.daftarHadirTestMmpiId ||
            jadwalMmpi?.daftar_hadir_test_mmpi_id ||
            null,
        daftar_hadir_test_zoom_id:
            jadwalMmpi?.daftar_hadir_test_zoom_id ||
            jadwalMmpi?.daftarHadirTestZoomId ||
            null,
        data_riwayat_diri_id:
            jadwalMmpi?.data_riwayat_diri_id ||
            jadwalMmpi?.dataRiwayatDiriId ||
            null,
        jadwal:
            jadwalMmpi?.jadwal ||
            jadwalMmpi?.tanggal ||
            jadwalMmpi?.tanggal_jadwal ||
            jadwalMmpi?.tanggalJadwal ||
            null,
        tanggal:
            jadwalMmpi?.tanggal_label ||
            jadwalMmpi?.tanggalLabel ||
            jadwalMmpi?.tanggal ||
            null,
        jam: jadwalMmpi?.jam || null,
        keterangan: jadwalMmpi?.keterangan || null,

        kehadiran: normalizeKehadiran(rawKehadiran),
        status_kehadiran: normalizeKehadiran(rawKehadiran),
        statusKehadiran: normalizeKehadiran(rawKehadiran),
        konfirmasi_kehadiran: normalizeKehadiran(rawKehadiran),
        konfirmasiKehadiran: normalizeKehadiran(rawKehadiran),
        sudah_mengisi_kehadiran: Boolean(normalizeKehadiran(rawKehadiran)),
        sudahMengisiKehadiran: Boolean(normalizeKehadiran(rawKehadiran)),

        hasil_test: normalizeHasilTest(rawHasilTest),
        hasilTest: normalizeHasilTest(rawHasilTest),
        status_hasil_test: normalizeHasilTest(rawHasilTest),
        statusHasilTest: normalizeHasilTest(rawHasilTest),
    };
}


function hasValidJadwalMmpi(jadwalMmpi) {
    return Boolean(
        jadwalMmpi &&
            (jadwalMmpi.jadwal ||
                jadwalMmpi.tanggal ||
                jadwalMmpi.tanggal_jadwal ||
                jadwalMmpi.tanggalJadwal)
    );
}

function getJadwalTestFromHasil(hasil) {
    if (!hasil) return null;

    const direct =
        hasil?.jadwal_test_zoom ||
        hasil?.jadwalTestZoom ||
        hasil?.jadwal_test ||
        hasil?.jadwalTest ||
        hasil?.tahapan_seleksi?.jadwal_test_zoom ||
        hasil?.tahapan_seleksi?.jadwal_test ||
        hasil?.tahapanSeleksi?.jadwal_test_zoom ||
        hasil?.tahapanSeleksi?.jadwal_test ||
        null;

    if (direct && hasValidJadwal(direct)) {
        return normalizeJadwalTest(direct);
    }

    const tahapan = Array.isArray(hasil?.tahapan)
        ? hasil.tahapan
        : Array.isArray(hasil?.tahapan_seleksi?.tahapan)
        ? hasil.tahapan_seleksi.tahapan
        : Array.isArray(hasil?.tahapanSeleksi?.tahapan)
        ? hasil.tahapanSeleksi.tahapan
        : [];

    const itemDenganJadwal = tahapan.find((item) => {
        return (
            item?.jadwal_test_zoom ||
            item?.jadwalTestZoom ||
            item?.jadwal_test ||
            item?.jadwalTest
        );
    });

    const nested =
        itemDenganJadwal?.jadwal_test_zoom ||
        itemDenganJadwal?.jadwalTestZoom ||
        itemDenganJadwal?.jadwal_test ||
        itemDenganJadwal?.jadwalTest ||
        null;

    if (nested && hasValidJadwal(nested)) {
        return normalizeJadwalTest(nested);
    }

    return null;
}

function getHasilTestFromHasil(hasil, jadwalTest = null) {
    const raw =
        hasil?.hasil_test ??
        hasil?.hasilTest ??
        hasil?.status_hasil_test ??
        hasil?.statusHasilTest ??
        jadwalTest?.hasil_test ??
        jadwalTest?.hasilTest ??
        jadwalTest?.status_hasil_test ??
        jadwalTest?.statusHasilTest ??
        hasil?.tahapan_seleksi?.hasil_test ??
        hasil?.tahapanSeleksi?.hasil_test ??
        null;

    const direct = normalizeHasilTest(raw);

    if (direct) return direct;

    const tahapan = Array.isArray(hasil?.tahapan)
        ? hasil.tahapan
        : Array.isArray(hasil?.tahapan_seleksi?.tahapan)
        ? hasil.tahapan_seleksi.tahapan
        : Array.isArray(hasil?.tahapanSeleksi?.tahapan)
        ? hasil.tahapanSeleksi.tahapan
        : [];

    const itemHasil = tahapan.find((item) => {
        return (
            normalizeHasilTest(item?.hasil_test) ||
            normalizeHasilTest(item?.hasilTest) ||
            String(item?.nama || "").toLowerCase().includes("hasil seleksi")
        );
    });

    return normalizeHasilTest(
        itemHasil?.hasil_test ||
            itemHasil?.hasilTest ||
            itemHasil?.status
    );
}

function normalizeJadwalTest(jadwalTest) {
    if (!jadwalTest) return null;

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

    const rawHasilTest =
        jadwalTest?.hasil_test ??
        jadwalTest?.hasilTest ??
        jadwalTest?.status_hasil_test ??
        jadwalTest?.statusHasilTest ??
        null;

    const linkZoom =
        jadwalTest?.link_zoom ||
        jadwalTest?.linkZoom ||
        jadwalTest?.zoom_link ||
        jadwalTest?.zoomLink ||
        jadwalTest?.url_link ||
        jadwalTest?.urlLink ||
        jadwalTest?.link ||
        null;

    const bolehBukaLinkZoom =
        jadwalTest?.boleh_buka_link_zoom ??
        jadwalTest?.bolehBukaLinkZoom ??
        jadwalTest?.can_open_zoom_link ??
        jadwalTest?.canOpenZoomLink ??
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

        hasil_test: normalizeHasilTest(rawHasilTest),
        hasilTest: normalizeHasilTest(rawHasilTest),

        link_zoom: linkZoom,
        zoom_link: linkZoom,
        url_link: linkZoom,
        boleh_buka_link_zoom: bolehBukaLinkZoom,

        boleh_akses_jadwal_test_zoom:
            jadwalTest?.boleh_akses_jadwal_test_zoom ??
            jadwalTest?.bolehAksesJadwalTestZoom ??
            jadwalTest?.can_access_jadwal_test_zoom ??
            jadwalTest?.canAccessJadwalTestZoom ??
            null,
        bolehAksesJadwalTestZoom:
            jadwalTest?.bolehAksesJadwalTestZoom ??
            jadwalTest?.boleh_akses_jadwal_test_zoom ??
            jadwalTest?.canAccessJadwalTestZoom ??
            jadwalTest?.can_access_jadwal_test_zoom ??
            null,
        disabled: Boolean(jadwalTest?.disabled),
        disabled_reason:
            jadwalTest?.disabled_reason ||
            jadwalTest?.disabledReason ||
            null,
        disabledReason:
            jadwalTest?.disabledReason ||
            jadwalTest?.disabled_reason ||
            null,
    };
}

function normalizeKehadiran(value) {
    if (value === undefined || value === null || value === "") return null;

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

function formatKehadiranInterview(value) {
    const kehadiran = normalizeKehadiranInterview(value);

    if (kehadiran === "hadir") return "Hadir";
    if (kehadiran === "tidak_hadir") return "Tidak Hadir";
    if (kehadiran === "tidak_respon") return "Tidak Respon";
    if (kehadiran === "reschedule") return "Reschedule";

    return "-";
}

function getKehadiranInterviewTextColor(value) {
    const kehadiran = normalizeKehadiranInterview(value);

    if (kehadiran === "hadir") return "text-emerald-700";
    if (kehadiran === "tidak_hadir") return "text-red-700";
    if (kehadiran === "tidak_respon") return "text-amber-700";
    if (kehadiran === "reschedule") return "text-blue-700";

    return "text-slate-700";
}

function normalizeJadwalInterview(jadwalInterview) {
    if (!jadwalInterview) return null;

    const rawKehadiran =
        jadwalInterview.kehadiran ||
        jadwalInterview.status_kehadiran ||
        jadwalInterview.statusKehadiran ||
        jadwalInterview.status_kehadiran_interview ||
        jadwalInterview.statusKehadiranInterview;

    const rawHasilInterview =
        jadwalInterview.hasil_interview ||
        jadwalInterview.hasilInterview ||
        jadwalInterview.status_hasil_interview ||
        jadwalInterview.statusHasilInterview;

    const rawStatusReviewManagement =
        jadwalInterview.status_review_management ||
        jadwalInterview.statusReviewManagement ||
        jadwalInterview.status_review ||
        jadwalInterview.statusReview;

    const normalizedHasilInterview = normalizeHasilInterview(rawHasilInterview);
    const normalizedStatusReviewManagement = normalizeStatusReviewManagement(
        rawStatusReviewManagement
    );
    const jadwalOfferingLetter = normalizeJadwalOfferingLetter(
        jadwalInterview.jadwal_offering_letter ||
            jadwalInterview.jadwalOfferingLetter ||
            jadwalInterview.offering_letter ||
            jadwalInterview.offeringLetter ||
            null
    );

    return {
        ...jadwalInterview,
        jadwal:
            jadwalInterview.jadwal ||
            jadwalInterview.jadwal_interview ||
            jadwalInterview.jadwalInterview ||
            jadwalInterview.tanggal ||
            null,
        tanggal:
            jadwalInterview.tanggal ||
            jadwalInterview.tanggal_jadwal ||
            jadwalInterview.tanggalJadwal ||
            jadwalInterview.jadwal_interview ||
            jadwalInterview.jadwalInterview ||
            jadwalInterview.jadwal ||
            null,
        jam: jadwalInterview.jam || jadwalInterview.waktu || null,

        kehadiran: normalizeKehadiranInterview(rawKehadiran),
        status_kehadiran: normalizeKehadiranInterview(rawKehadiran),
        statusKehadiran: normalizeKehadiranInterview(rawKehadiran),

        hasil_interview: normalizedHasilInterview,
        hasilInterview: normalizedHasilInterview,
        status_hasil_interview: normalizedHasilInterview,
        statusHasilInterview: normalizedHasilInterview,

        review_management:
            jadwalInterview.review_management ||
            jadwalInterview.reviewManagement ||
            null,
        reviewManagement:
            jadwalInterview.reviewManagement ||
            jadwalInterview.review_management ||
            null,

        status_review_management: normalizedStatusReviewManagement,
        statusReviewManagement: normalizedStatusReviewManagement,
        status_review: normalizedStatusReviewManagement,
        statusReview: normalizedStatusReviewManagement,

        id:
            jadwalInterview.id ||
            jadwalInterview.jadwal_interview_kandidat_id ||
            jadwalInterview.jadwalInterviewKandidatId ||
            null,
        jadwal_interview_kandidat_id:
            jadwalInterview.jadwal_interview_kandidat_id ||
            jadwalInterview.id ||
            jadwalInterview.jadwalInterviewKandidatId ||
            null,
        jadwalInterviewKandidatId:
            jadwalInterview.jadwalInterviewKandidatId ||
            jadwalInterview.id ||
            jadwalInterview.jadwal_interview_kandidat_id ||
            null,
        file_cv: normalizeFileUrl(
            jadwalInterview.file_cv ||
                jadwalInterview.fileCv ||
                jadwalInterview.cv ||
                null
        ),
        fileCv: normalizeFileUrl(
            jadwalInterview.fileCv ||
                jadwalInterview.file_cv ||
                jadwalInterview.cv ||
                null
        ),
        file_foto: normalizeFileUrl(
            jadwalInterview.file_foto ||
                jadwalInterview.fileFoto ||
                jadwalInterview.foto ||
                null
        ),
        fileFoto: normalizeFileUrl(
            jadwalInterview.fileFoto ||
                jadwalInterview.file_foto ||
                jadwalInterview.foto ||
                null
        ),

        jadwal_offering_letter: jadwalOfferingLetter,
        jadwalOfferingLetter: jadwalOfferingLetter,
        status_jadwal_offering_letter: jadwalOfferingLetter?.status_jadwal || null,
        statusJadwalOfferingLetter: jadwalOfferingLetter?.status_jadwal || null,
    };
}

function hasValidJadwalInterview(jadwalInterview) {
    return Boolean(
        jadwalInterview &&
            (jadwalInterview.jadwal ||
                jadwalInterview.jadwal_interview ||
                jadwalInterview.jadwalInterview ||
                jadwalInterview.tanggal ||
                jadwalInterview.tanggal_jadwal ||
                jadwalInterview.tanggalJadwal)
    );
}

function normalizeKehadiranInterview(value) {
    if (value === null || value === undefined || value === "") return null;

    const normalized = String(value).toLowerCase().trim().replace(/[\s-]+/g, "_");

    if (["hadir", "1", "true", "ya", "yes"].includes(normalized)) return "hadir";
    if (["tidak_hadir", "tidakhadir", "tidak", "0", "false", "no"].includes(normalized)) return "tidak_hadir";
    if (["tidak_respon", "tidakrespon", "no_response", "noresponse"].includes(normalized)) return "tidak_respon";
    if (["reschedule", "rescheduled", "jadwal_ulang", "jadwalulang", "ubah_jadwal", "ubahjadwal"].includes(normalized)) return "reschedule";

    return null;
}

function normalizeHasilInterview(value) {
    if (value === null || value === undefined || value === "") return null;

    const normalized = String(value).toLowerCase().trim().replace(/[\s-]+/g, "_");

    if (
        [
            "tidak_lolos_interview",
            "tidak_lolos",
            "gagal",
            "0",
            "false",
            "tidak",
            "no",
        ].includes(normalized)
    ) {
        return "tidak_lolos_interview";
    }

    // Contoh dari database: Lolos, Dipertimbangkan, Lanjut, Ya, Diterima Interview, dll.
    return "lanjut_review";
}

function normalizeStatusReviewManagement(value) {
    if (value === null || value === undefined || value === "") return null;

    const normalized = String(value).toLowerCase().trim().replace(/[\s-]+/g, "_");

    if (
        [
            "diterima",
            "terima",
            "lolos",
            "approved",
            "approve",
            "accepted",
            "accept",
            "1",
            "true",
            "ya",
            "yes",
        ].includes(normalized)
    ) {
        return "diterima";
    }

    if (
        [
            "gagal",
            "ditolak",
            "tidak_diterima",
            "reject",
            "rejected",
            "not_approved",
            "0",
            "false",
            "tidak",
            "no",
        ].includes(normalized)
    ) {
        return "gagal";
    }

    return null;
}

function normalizeHasilTest(value) {
    if (value === undefined || value === null || value === "") return null;

    const normalized = String(value)
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "_")
        .replace(/-/g, "_");

    if (
        normalized === "lolos" ||
        normalized === "1" ||
        normalized === "true" ||
        normalized === "ya" ||
        normalized === "yes"
    ) {
        return "lolos";
    }

    if (
        normalized === "gagal" ||
        normalized === "0" ||
        normalized === "false" ||
        normalized === "tidak" ||
        normalized === "no"
    ) {
        return "gagal";
    }

    return null;
}

function injectUpdatedJadwalTest(data, updatedJadwalTest) {
    if (!data) return data;

    const normalizedUpdatedJadwalTest = normalizeJadwalTest(updatedJadwalTest);

    if (!normalizedUpdatedJadwalTest) return data;

    const fixedData = {
        ...data,
        jadwal_test: {
            ...(data?.jadwal_test || {}),
            ...normalizedUpdatedJadwalTest,
        },
        jadwal_test_zoom: {
            ...(data?.jadwal_test_zoom || {}),
            ...normalizedUpdatedJadwalTest,
        },
        jadwalTestZoom: {
            ...(data?.jadwalTestZoom || {}),
            ...normalizedUpdatedJadwalTest,
        },
    };

    if (Array.isArray(fixedData.tahapan)) {
        fixedData.tahapan = fixedData.tahapan.map((item) => {
            if (!item?.jadwal_test && !item?.jadwal_test_zoom) {
                return item;
            }

            return {
                ...item,
                jadwal_test: {
                    ...(item.jadwal_test || item.jadwal_test_zoom || {}),
                    ...normalizedUpdatedJadwalTest,
                },
                jadwal_test_zoom: {
                    ...(item.jadwal_test_zoom || item.jadwal_test || {}),
                    ...normalizedUpdatedJadwalTest,
                },
            };
        });
    }

    if (fixedData?.tahapan_seleksi?.jadwal_test || fixedData?.tahapan_seleksi?.jadwal_test_zoom) {
        fixedData.tahapan_seleksi = {
            ...fixedData.tahapan_seleksi,
            jadwal_test: {
                ...(fixedData.tahapan_seleksi.jadwal_test ||
                    fixedData.tahapan_seleksi.jadwal_test_zoom ||
                    {}),
                ...normalizedUpdatedJadwalTest,
            },
            jadwal_test_zoom: {
                ...(fixedData.tahapan_seleksi.jadwal_test_zoom ||
                    fixedData.tahapan_seleksi.jadwal_test ||
                    {}),
                ...normalizedUpdatedJadwalTest,
            },
        };
    }

    if (fixedData?.tahapanSeleksi?.jadwal_test || fixedData?.tahapanSeleksi?.jadwal_test_zoom) {
        fixedData.tahapanSeleksi = {
            ...fixedData.tahapanSeleksi,
            jadwal_test: {
                ...(fixedData.tahapanSeleksi.jadwal_test ||
                    fixedData.tahapanSeleksi.jadwal_test_zoom ||
                    {}),
                ...normalizedUpdatedJadwalTest,
            },
            jadwal_test_zoom: {
                ...(fixedData.tahapanSeleksi.jadwal_test_zoom ||
                    fixedData.tahapanSeleksi.jadwal_test ||
                    {}),
                ...normalizedUpdatedJadwalTest,
            },
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
    if (!jadwalTest) return "-";

    if (jadwalTest.tanggal) return jadwalTest.tanggal;

    if (!jadwalTest.jadwal) return "-";

    const date = parseJadwalDate(jadwalTest.jadwal);

    if (!date) return "-";

    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
}

function getJadwalJam(jadwalTest) {
    if (!jadwalTest) return "-";

    if (jadwalTest.jam) return jadwalTest.jam;

    if (!jadwalTest.jadwal) return "-";

    const date = parseJadwalDate(jadwalTest.jadwal);

    if (!date) return "-";

    return date.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    });
}

function getLinkZoom(jadwalTest) {
    if (!jadwalTest) return null;

    return (
        jadwalTest?.link_zoom ||
        jadwalTest?.zoom_link ||
        jadwalTest?.url_link ||
        jadwalTest?.linkZoom ||
        jadwalTest?.zoomLink ||
        jadwalTest?.urlLink ||
        null
    );
}

function isJadwalHariIni(jadwalTest) {
    const jadwalDate = parseJadwalDate(jadwalTest?.jadwal);

    if (!jadwalDate) return false;

    const today = new Date();

    return (
        jadwalDate.getFullYear() === today.getFullYear() &&
        jadwalDate.getMonth() === today.getMonth() &&
        jadwalDate.getDate() === today.getDate()
    );
}

function parseJadwalDate(value) {
    if (!value) return null;

    const normalizedValue =
        typeof value === "string" && value.includes(" ") && !value.includes("T")
            ? value.replace(" ", "T")
            : value;

    const date = new Date(normalizedValue);

    if (Number.isNaN(date.getTime())) return null;

    return date;
}


function normalizeFileUrl(value) {
    if (!value) return null;

    const text = String(value).trim();

    if (!text) return null;

    if (
        text.startsWith("http://") ||
        text.startsWith("https://") ||
        text.startsWith("/storage/")
    ) {
        return text;
    }

    return `/storage/${text.replace(/^\/+/, "")}`;
}

function injectUpdatedDokumenInterview(previousData, jadwalInterviewKandidatId, dokumen) {
    if (!previousData) return previousData;

    const normalizedDokumen = {
        file_cv: normalizeFileUrl(dokumen?.file_cv || dokumen?.fileCv || null),
        fileCv: normalizeFileUrl(dokumen?.fileCv || dokumen?.file_cv || null),
        file_foto: normalizeFileUrl(dokumen?.file_foto || dokumen?.fileFoto || null),
        fileFoto: normalizeFileUrl(dokumen?.fileFoto || dokumen?.file_foto || null),
    };

    const sameId = (item) => {
        const id =
            item?.id ||
            item?.jadwal_interview_kandidat_id ||
            item?.jadwalInterviewKandidatId ||
            null;

        return String(id || "") === String(jadwalInterviewKandidatId || "");
    };

    const updateInterview = (item) => {
        if (!item) return item;

        if (sameId(item)) {
            return {
                ...item,
                ...normalizedDokumen,
            };
        }

        return item;
    };

    const nextData = {
        ...previousData,
    };

    if (nextData.jadwal_interview) {
        nextData.jadwal_interview = updateInterview(nextData.jadwal_interview);
    }

    if (nextData.jadwalInterview) {
        nextData.jadwalInterview = updateInterview(nextData.jadwalInterview);
    }

    if (nextData.interview) {
        nextData.interview = updateInterview(nextData.interview);
    }

    if (Array.isArray(nextData.tahapan)) {
        nextData.tahapan = nextData.tahapan.map((item) => {
            const jadwalInterview = item?.jadwal_interview || item?.jadwalInterview;

            if (!jadwalInterview || !sameId(jadwalInterview)) {
                return item;
            }

            return {
                ...item,
                jadwal_interview: item.jadwal_interview
                    ? updateInterview(item.jadwal_interview)
                    : item.jadwal_interview,
                jadwalInterview: item.jadwalInterview
                    ? updateInterview(item.jadwalInterview)
                    : item.jadwalInterview,
            };
        });
    }

    if (nextData.tahapan_seleksi?.tahapan && Array.isArray(nextData.tahapan_seleksi.tahapan)) {
        nextData.tahapan_seleksi = {
            ...nextData.tahapan_seleksi,
            tahapan: nextData.tahapan_seleksi.tahapan.map((item) => {
                const jadwalInterview = item?.jadwal_interview || item?.jadwalInterview;

                if (!jadwalInterview || !sameId(jadwalInterview)) {
                    return item;
                }

                return {
                    ...item,
                    jadwal_interview: item.jadwal_interview
                        ? updateInterview(item.jadwal_interview)
                        : item.jadwal_interview,
                    jadwalInterview: item.jadwalInterview
                        ? updateInterview(item.jadwalInterview)
                        : item.jadwalInterview,
                };
            }),
        };
    }

    if (nextData.tahapanSeleksi?.tahapan && Array.isArray(nextData.tahapanSeleksi.tahapan)) {
        nextData.tahapanSeleksi = {
            ...nextData.tahapanSeleksi,
            tahapan: nextData.tahapanSeleksi.tahapan.map((item) => {
                const jadwalInterview = item?.jadwal_interview || item?.jadwalInterview;

                if (!jadwalInterview || !sameId(jadwalInterview)) {
                    return item;
                }

                return {
                    ...item,
                    jadwal_interview: item.jadwal_interview
                        ? updateInterview(item.jadwal_interview)
                        : item.jadwal_interview,
                    jadwalInterview: item.jadwalInterview
                        ? updateInterview(item.jadwalInterview)
                        : item.jadwalInterview,
                };
            }),
        };
    }

    return nextData;
}

async function parseJsonResponse(response) {
    const text = await response.text();

    if (!text) return {};

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

    if (!match?.[1]) return null;

    return decodeURIComponent(match[1]);
}

function getCsrfToken() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || ""
    );
}

function getTextColor(type) {
    const colors = {
        emerald: "text-emerald-700",
        red: "text-red-700",
        blue: "text-blue-700",
        indigo: "text-indigo-700",
        amber: "text-amber-700",
        teal: "text-teal-700",
    };

    return colors[type] || colors.teal;
}

function getBadgeColor(type) {
    const colors = {
        emerald: "bg-emerald-100 text-emerald-700",
        red: "bg-red-100 text-red-700",
        blue: "bg-blue-100 text-blue-700",
        indigo: "bg-indigo-100 text-indigo-700",
        amber: "bg-amber-100 text-amber-700",
        teal: "bg-teal-100 text-teal-700",
    };

    return colors[type] || colors.teal;
}

function getBoxColor(type) {
    const colors = {
        emerald: "border-emerald-200 bg-emerald-50",
        red: "border-red-200 bg-red-50",
        blue: "border-blue-200 bg-blue-50",
        indigo: "border-indigo-200 bg-indigo-50",
        amber: "border-amber-200 bg-amber-50",
        teal: "border-teal-200 bg-teal-50",
    };

    return colors[type] || colors.teal;
}

function getCircleColor(type) {
    const colors = {
        emerald: "bg-emerald-600",
        red: "bg-red-600",
        blue: "bg-blue-600",
        indigo: "bg-indigo-600",
        amber: "bg-amber-500",
        teal: "bg-teal-600",
    };

    return colors[type] || colors.teal;
}

function getTitleColor(type) {
    const colors = {
        emerald: "text-emerald-900",
        red: "text-red-900",
        blue: "text-blue-900",
        indigo: "text-indigo-900",
        amber: "text-amber-900",
        teal: "text-teal-800",
    };

    return colors[type] || colors.teal;
}

function getDescriptionColor(type) {
    const colors = {
        emerald: "text-emerald-800",
        red: "text-red-800",
        blue: "text-blue-800",
        indigo: "text-indigo-800",
        amber: "text-amber-800",
        teal: "text-teal-700",
    };

    return colors[type] || colors.teal;
}