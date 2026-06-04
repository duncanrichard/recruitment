import React, { useEffect, useMemo, useState } from "react";

export default function ReportInterviewKandidatPage() {
    const [tanggalAwal, setTanggalAwal] = useState("");
    const [tanggalAkhir, setTanggalAkhir] = useState("");
    const [statusKehadiran, setStatusKehadiran] = useState("semua");
    const [hasilInterview, setHasilInterview] = useState("semua");

    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState(null);
    const [summary, setSummary] = useState(null);
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState("");

    const queryString = useMemo(() => {
        const params = new URLSearchParams();

        if (tanggalAwal) {
            params.set("tanggal_awal", tanggalAwal);
        }

        if (tanggalAkhir) {
            params.set("tanggal_akhir", tanggalAkhir);
        }

        params.set("status_kehadiran", statusKehadiran);
        params.set("hasil_interview", hasilInterview);
        params.set("page", page);

        return params.toString();
    }, [tanggalAwal, tanggalAkhir, statusKehadiran, hasilInterview, page]);

    async function fetchData() {
        setLoading(true);
        setMessage("");

        try {
            const response = await fetch(`/report-interview-kandidat?${queryString}`, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const json = await response.json();

            if (!response.ok || json?.success === false) {
                setRows([]);
                setMeta(null);
                setSummary(null);
                setMessage(json?.message || "Gagal mengambil report interview kandidat.");
                return;
            }

            setRows(Array.isArray(json?.data?.data) ? json.data.data : []);
            setMeta(json?.data || null);
            setSummary(json?.summary || null);
        } catch (error) {
            setRows([]);
            setMeta(null);
            setSummary(null);
            setMessage("Terjadi kesalahan saat mengambil report interview kandidat.");
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        fetchData();
    }, [queryString]);

    function handleSubmit(event) {
        event.preventDefault();
        setPage(1);
        fetchData();
    }

    function handleReset() {
        setTanggalAwal("");
        setTanggalAkhir("");
        setStatusKehadiran("semua");
        setHasilInterview("semua");
        setPage(1);
    }

    function handleExportExcel() {
        const params = new URLSearchParams();

        if (tanggalAwal) {
            params.set("tanggal_awal", tanggalAwal);
        }

        if (tanggalAkhir) {
            params.set("tanggal_akhir", tanggalAkhir);
        }

        params.set("status_kehadiran", statusKehadiran);
        params.set("hasil_interview", hasilInterview);

        window.location.href = `/report-interview-kandidat/export?${params.toString()}`;
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 className="text-2xl font-black text-slate-950">
                        Interview Kandidat
                    </h1>

                    <p className="mt-2 text-sm font-semibold text-slate-500">
                        Report kandidat interview, interviewer, status kehadiran, dan hasil interview.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={handleExportExcel}
                    className="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700"
                >
                    Export Excel
                </button>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <form
                    onSubmit={handleSubmit}
                    className="grid grid-cols-1 gap-4 md:grid-cols-6"
                >
                    <div>
                        <label className="mb-2 block text-sm font-bold text-slate-700">
                            Tanggal Awal
                        </label>

                        <input
                            type="date"
                            value={tanggalAwal}
                            onChange={(event) => setTanggalAwal(event.target.value)}
                            className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        />
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-bold text-slate-700">
                            Tanggal Akhir
                        </label>

                        <input
                            type="date"
                            value={tanggalAkhir}
                            onChange={(event) => setTanggalAkhir(event.target.value)}
                            className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        />
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-bold text-slate-700">
                            Kehadiran
                        </label>

                        <select
                            value={statusKehadiran}
                            onChange={(event) => setStatusKehadiran(event.target.value)}
                            className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        >
                            <option value="semua">Semua</option>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                            <option value="tidak_respon">Tidak Respon</option>
                            <option value="reschedule">Reschedule</option>
                            <option value="belum_ada">Belum Ada</option>
                        </select>
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-bold text-slate-700">
                            Hasil Interview
                        </label>

                        <select
                            value={hasilInterview}
                            onChange={(event) => setHasilInterview(event.target.value)}
                            className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        >
                            <option value="semua">Semua</option>
                            <option value="lolos_interview">Lolos Interview</option>
                            <option value="tidak_lolos_interview">
                                Tidak Lolos Interview
                            </option>
                            <option value="dipertimbangkan">Dipertimbangkan</option>
                            <option value="belum_ada">Belum Ada</option>
                        </select>
                    </div>

                    <div className="flex items-end gap-2 md:col-span-2">
                        <button
                            type="submit"
                            className="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-800"
                        >
                            Tampilkan
                        </button>

                        <button
                            type="button"
                            onClick={handleReset}
                            className="rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200"
                        >
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            {summary && (
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4 xl:grid-cols-5">
                    <SummaryCard label="Total" value={summary.total} />
                    <SummaryCard label="Hadir" value={summary.hadir} />
                    <SummaryCard label="Tidak Hadir" value={summary.tidak_hadir} />
                    <SummaryCard label="Lolos Interview" value={summary.lolos_interview} />
                    <SummaryCard label="Tidak Lolos" value={summary.tidak_lolos_interview} />
                </div>
            )}

            {message && (
                <div className="rounded-3xl border border-red-200 bg-red-50 p-5">
                    <p className="text-sm font-bold text-red-700">{message}</p>
                </div>
            )}

            <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div className="flex flex-col gap-2 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="font-black text-slate-950">
                            Data Interview Kandidat
                        </h2>

                        <p className="mt-1 text-sm text-slate-500">
                            Total data: {meta?.total ?? rows.length}
                        </p>
                    </div>

                    <p className="text-sm font-semibold text-slate-500">
                        Filter: {tanggalAwal || "Semua"} s/d {tanggalAkhir || "Semua"}
                    </p>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-100">
                        <thead className="bg-slate-50">
                            <tr>
                                <Th>No</Th>
                                <Th>Judul Interview</Th>
                                <Th>Tanggal Interview</Th>
                                <Th>Interviewer</Th>
                                <Th>Nama Kandidat</Th>
                                <Th>Email</Th>
                                <Th>No WA</Th>
                                <Th>Posisi</Th>
                                <Th>Kehadiran</Th>
                                <Th>Hasil Interview</Th>
                                <Th>Catatan</Th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {loading && (
                                <tr>
                                    <td
                                        colSpan="11"
                                        className="px-6 py-8 text-center text-sm font-semibold text-slate-500"
                                    >
                                        Memuat data...
                                    </td>
                                </tr>
                            )}

                            {!loading && rows.length === 0 && (
                                <tr>
                                    <td
                                        colSpan="11"
                                        className="px-6 py-8 text-center text-sm font-semibold text-slate-500"
                                    >
                                        Data tidak ditemukan.
                                    </td>
                                </tr>
                            )}

                            {!loading &&
                                rows.map((item, index) => (
                                    <tr key={item.id || index} className="hover:bg-slate-50">
                                        <Td>
                                            {meta?.from
                                                ? meta.from + index
                                                : index + 1}
                                        </Td>
                                        <Td>{item.judul_interview || "-"}</Td>
                                        <Td>{formatDateTime(item.jadwal_interview)}</Td>
                                        <Td>
                                            <InterviewerName item={item} />
                                        </Td>
                                        <Td>{item.nama_lengkap || "-"}</Td>
                                        <Td>{item.email || "-"}</Td>
                                        <Td>{item.no_wa || "-"}</Td>
                                        <Td>{item.posisi_dilamar || "-"}</Td>
                                        <Td>
                                            <KehadiranBadge
                                                value={item.status_kehadiran_label}
                                                type={item.status_kehadiran}
                                            />
                                        </Td>
                                        <Td>
                                            <HasilBadge
                                                value={item.hasil_interview_label}
                                                type={item.hasil_interview}
                                            />
                                        </Td>
                                        <Td>{item.catatan || "-"}</Td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>

                {meta && meta.last_page > 1 && (
                    <div className="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-sm font-semibold text-slate-500">
                            Halaman {meta.current_page} dari {meta.last_page}
                        </p>

                        <div className="flex gap-2">
                            <button
                                type="button"
                                disabled={page <= 1}
                                onClick={() => setPage((prev) => Math.max(1, prev - 1))}
                                className="rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Sebelumnya
                            </button>

                            <button
                                type="button"
                                disabled={page >= meta.last_page}
                                onClick={() =>
                                    setPage((prev) => Math.min(meta.last_page, prev + 1))
                                }
                                className="rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Berikutnya
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}

function SummaryCard({ label, value }) {
    return (
        <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-xs font-bold uppercase tracking-wide text-slate-500">
                {label}
            </p>
            <p className="mt-2 text-2xl font-black text-slate-950">
                {value ?? 0}
            </p>
        </div>
    );
}

function Th({ children }) {
    return (
        <th className="whitespace-nowrap px-6 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">
            {children}
        </th>
    );
}

function Td({ children }) {
    return (
        <td className="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">
            {children}
        </td>
    );
}

function InterviewerName({ item }) {
    const interviewer =
        item?.interviewer ||
        item?.interview_oleh ||
        item?.diinterview_oleh ||
        item?.nama_interviewer ||
        item?.panelis ||
        item?.nama_panelis ||
        "-";

    return (
        <span className="font-black text-slate-800">
            {interviewer || "-"}
        </span>
    );
}

function KehadiranBadge({ value, type }) {
    const colorClass =
        type === "hadir"
            ? "bg-emerald-100 text-emerald-700"
            : type === "tidak_hadir" || type === "tidak_respon"
            ? "bg-red-100 text-red-700"
            : type === "reschedule"
            ? "bg-amber-100 text-amber-700"
            : "bg-slate-100 text-slate-700";

    return (
        <span className={`rounded-full px-3 py-1 text-xs font-black ${colorClass}`}>
            {value || "Belum Ada"}
        </span>
    );
}

function HasilBadge({ value, type }) {
    const colorClass =
        type === "lolos_interview"
            ? "bg-emerald-100 text-emerald-700"
            : type === "tidak_lolos_interview"
            ? "bg-red-100 text-red-700"
            : type === "dipertimbangkan"
            ? "bg-amber-100 text-amber-700"
            : "bg-slate-100 text-slate-700";

    return (
        <span className={`rounded-full px-3 py-1 text-xs font-black ${colorClass}`}>
            {value || "Belum Ada"}
        </span>
    );
}

function formatDateTime(value) {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}