import React, { useEffect, useMemo, useState } from "react";

export default function ReportOfferingLetterPage() {
    const [tanggalAwal, setTanggalAwal] = useState("");
    const [tanggalAkhir, setTanggalAkhir] = useState("");
    const [statusJadwal, setStatusJadwal] = useState("semua");
    const [metode, setMetode] = useState("semua");

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

        params.set("status_jadwal", statusJadwal);
        params.set("metode", metode);
        params.set("page", page);

        return params.toString();
    }, [tanggalAwal, tanggalAkhir, statusJadwal, metode, page]);

    async function fetchData() {
        setLoading(true);
        setMessage("");

        try {
            const response = await fetch(`/report-offering-letter?${queryString}`, {
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
                setMessage(json?.message || "Gagal mengambil report Offering Letter.");
                return;
            }

            setRows(Array.isArray(json?.data?.data) ? json.data.data : []);
            setMeta(json?.data || null);
            setSummary(json?.summary || null);
        } catch (error) {
            setRows([]);
            setMeta(null);
            setSummary(null);
            setMessage("Terjadi kesalahan saat mengambil report Offering Letter.");
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
        setStatusJadwal("semua");
        setMetode("semua");
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

        params.set("status_jadwal", statusJadwal);
        params.set("metode", metode);

        window.location.href = `/report-offering-letter/export?${params.toString()}`;
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 className="text-2xl font-black text-slate-950">
                        Offering Letter
                    </h1>

                    <p className="mt-2 text-sm font-semibold text-slate-500">
                        Report jadwal Offering Letter, status kandidat, metode, PIC, dan hasil konfirmasi kandidat.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={handleExportExcel}
                    className="w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700 sm:w-auto"
                >
                    Export Excel
                </button>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
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
                            Status OL
                        </label>

                        <select
                            value={statusJadwal}
                            onChange={(event) => setStatusJadwal(event.target.value)}
                            className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        >
                            <option value="semua">Semua</option>
                            <option value="pending">Pending</option>
                            <option value="menerima">Menerima</option>
                            <option value="menolak">Menolak</option>
                            <option value="tidak_melanjutkan">Tidak Melanjutkan</option>
                        </select>
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-bold text-slate-700">
                            Metode
                        </label>

                        <select
                            value={metode}
                            onChange={(event) => setMetode(event.target.value)}
                            className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        >
                            <option value="semua">Semua</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                        </select>
                    </div>

                    <div className="flex flex-col gap-2 md:col-span-2 md:flex-row md:items-end">
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
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                    <SummaryCard label="Total" value={summary.total} />
                    <SummaryCard label="Pending" value={summary.pending} />
                    <SummaryCard label="Menerima" value={summary.menerima} />
                    <SummaryCard label="Menolak" value={summary.menolak} />
                    <SummaryCard label="Tidak Melanjutkan" value={summary.tidak_melanjutkan} />
                    <SummaryCard label="Online" value={summary.online} />
                    <SummaryCard label="Offline" value={summary.offline} />
                </div>
            )}

            {message && (
                <div className="rounded-3xl border border-red-200 bg-red-50 p-5">
                    <p className="text-sm font-bold text-red-700">{message}</p>
                </div>
            )}

            <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div className="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 className="font-black text-slate-950">
                            Data Offering Letter
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
                                <Th>Nama Kandidat</Th>
                                <Th>Email</Th>
                                <Th>No WA</Th>
                                <Th>Posisi</Th>
                                <Th>Tanggal OL</Th>
                                <Th>Jam</Th>
                                <Th>Metode</Th>
                                <Th>PIC</Th>
                                <Th>Status</Th>
                                <Th>Link</Th>
                                <Th>Catatan</Th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {loading && (
                                <tr>
                                    <td
                                        colSpan="12"
                                        className="px-6 py-8 text-center text-sm font-semibold text-slate-500"
                                    >
                                        Memuat data...
                                    </td>
                                </tr>
                            )}

                            {!loading && rows.length === 0 && (
                                <tr>
                                    <td
                                        colSpan="12"
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

                                        <Td>
                                            <span className="font-black text-slate-800">
                                                {item.nama_kandidat || "-"}
                                            </span>
                                        </Td>

                                        <Td>{item.email || "-"}</Td>
                                        <Td>{item.no_wa || "-"}</Td>
                                        <Td>{item.posisi_dilamar || "-"}</Td>
                                        <Td>{formatDate(item.tanggal_ol)}</Td>
                                        <Td>{item.jam_ol || "-"}</Td>
                                        <Td>{item.metode || "-"}</Td>
                                        <Td>{item.pic || "-"}</Td>

                                        <Td>
                                            <StatusBadge
                                                value={item.status_jadwal_label}
                                                type={item.status_jadwal}
                                            />
                                        </Td>

                                        <Td>
                                            {item.link && item.link !== "-" ? (
                                                <a
                                                    href={item.link}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="font-black text-emerald-700 hover:text-emerald-800"
                                                >
                                                    Buka Link
                                                </a>
                                            ) : (
                                                "-"
                                            )}
                                        </Td>

                                        <Td>
                                            <span className="whitespace-normal break-words">
                                                {item.catatan || "-"}
                                            </span>
                                        </Td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>

                {meta && meta.last_page > 1 && (
                    <div className="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
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

function StatusBadge({ value, type }) {
    const colorClass =
        type === "menerima"
            ? "bg-emerald-100 text-emerald-700"
            : type === "menolak"
            ? "bg-red-100 text-red-700"
            : type === "tidak_melanjutkan"
            ? "bg-amber-100 text-amber-700"
            : "bg-slate-100 text-slate-700";

    return (
        <span className={`rounded-full px-3 py-1 text-xs font-black ${colorClass}`}>
            {value || "Pending"}
        </span>
    );
}

function formatDate(value) {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
}