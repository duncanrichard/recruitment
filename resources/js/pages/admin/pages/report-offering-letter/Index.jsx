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
    const [showFilter, setShowFilter] = useState(true);

    const queryString = useMemo(() => {
        const params = new URLSearchParams();

        if (tanggalAwal) params.set("tanggal_awal", tanggalAwal);
        if (tanggalAkhir) params.set("tanggal_akhir", tanggalAkhir);

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

        if (tanggalAwal) params.set("tanggal_awal", tanggalAwal);
        if (tanggalAkhir) params.set("tanggal_akhir", tanggalAkhir);

        params.set("status_jadwal", statusJadwal);
        params.set("metode", metode);

        window.location.href = `/report-offering-letter/export?${params.toString()}`;
    }

    const summaryData = summary || {};

    const statusItems = [
        { label: "Pending", total: summaryData.pending || 0 },
        { label: "Menerima", total: summaryData.menerima || 0 },
        { label: "Menolak", total: summaryData.menolak || 0 },
        { label: "Tidak Melanjutkan", total: summaryData.tidak_melanjutkan || 0 },
    ].filter((item) => item.total > 0);

    const metodeItems = [
        { label: "Online", total: summaryData.online || 0 },
        { label: "Offline", total: summaryData.offline || 0 },
    ].filter((item) => item.total > 0);

    return (
        <div className="min-h-screen bg-slate-50 p-4 sm:p-6">
            <div className="mx-auto max-w-7xl space-y-6">
                <div className="overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-900 p-6 text-white shadow-xl">
                    <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div className="mb-3 inline-flex rounded-full bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-widest text-emerald-100 ring-1 ring-white/10">
                                Offering Letter Analytics
                            </div>

                            <h1 className="text-3xl font-black tracking-tight sm:text-4xl">
                                Dashboard Offering Letter
                            </h1>

                            <p className="mt-3 max-w-3xl text-sm font-semibold leading-6 text-slate-200">
                                Monitoring jadwal Offering Letter, status kandidat, metode,
                                PIC, link, dan hasil konfirmasi kandidat.
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <button
                                type="button"
                                onClick={() => setShowFilter((prev) => !prev)}
                                className="rounded-2xl bg-white/10 px-5 py-3 text-sm font-black text-white ring-1 ring-white/20 transition hover:bg-white/20"
                            >
                                {showFilter ? "Tutup Filter" : "Buka Filter"}
                            </button>

                            <button
                                type="button"
                                onClick={handleExportExcel}
                                className="rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-600"
                            >
                                Export Excel
                            </button>
                        </div>
                    </div>

                    <div className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <SummaryCard
                            title="Total Offering"
                            value={summaryData.total}
                            description="Semua data sesuai filter"
                        />

                        <SummaryCard
                            title="Menerima"
                            value={summaryData.menerima}
                            description="Kandidat menerima offering"
                        />

                        <SummaryCard
                            title="Menolak"
                            value={summaryData.menolak}
                            description="Kandidat menolak offering"
                        />

                        <SummaryCard
                            title="Pending"
                            value={summaryData.pending}
                            description="Belum ada keputusan kandidat"
                        />
                    </div>
                </div>

                {showFilter && (
                    <div className="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                        <form
                            onSubmit={handleSubmit}
                            className="grid grid-cols-1 items-end gap-4 md:grid-cols-2 xl:grid-cols-12"
                        >
                            <div className="xl:col-span-3">
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Tanggal Awal
                                </label>

                                <input
                                    type="date"
                                    value={tanggalAwal}
                                    onChange={(event) => setTanggalAwal(event.target.value)}
                                    className="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                />
                            </div>

                            <div className="xl:col-span-3">
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Tanggal Akhir
                                </label>

                                <input
                                    type="date"
                                    value={tanggalAkhir}
                                    onChange={(event) => setTanggalAkhir(event.target.value)}
                                    className="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                />
                            </div>

                            <div className="xl:col-span-2">
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Status OL
                                </label>

                                <select
                                    value={statusJadwal}
                                    onChange={(event) => setStatusJadwal(event.target.value)}
                                    className="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                >
                                    <option value="semua">Semua</option>
                                    <option value="pending">Pending</option>
                                    <option value="menerima">Menerima</option>
                                    <option value="menolak">Menolak</option>
                                    <option value="tidak_melanjutkan">Tidak Melanjutkan</option>
                                </select>
                            </div>

                            <div className="xl:col-span-2">
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Metode
                                </label>

                                <select
                                    value={metode}
                                    onChange={(event) => setMetode(event.target.value)}
                                    className="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                >
                                    <option value="semua">Semua</option>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                            </div>

                            <div className="flex gap-3 md:col-span-2 xl:col-span-2">
                                <button
                                    type="submit"
                                    className="h-12 flex-1 rounded-2xl bg-slate-950 px-5 text-sm font-black text-white shadow-sm transition hover:bg-slate-800"
                                >
                                    Tampilkan
                                </button>

                                <button
                                    type="button"
                                    onClick={handleReset}
                                    className="h-12 rounded-2xl bg-slate-100 px-5 text-sm font-black text-slate-700 transition hover:bg-slate-200"
                                >
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                )}

                {message && (
                    <div className="rounded-3xl border border-red-200 bg-red-50 p-5">
                        <p className="text-sm font-bold text-red-700">{message}</p>
                    </div>
                )}

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <ChartCard
                        title="Demografi Status Offering Letter"
                        description="Distribusi keputusan kandidat terhadap offering"
                    >
                        <ProgressList items={statusItems} colorClass="bg-emerald-500" />
                    </ChartCard>

                    <ChartCard
                        title="Demografi Metode Offering"
                        description="Distribusi metode pelaksanaan Offering Letter"
                    >
                        <ProgressList items={metodeItems} colorClass="bg-violet-500" />
                    </ChartCard>
                </div>

                <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 className="text-lg font-black text-slate-950">
                                Detail Data Offering Letter
                            </h2>

                            <p className="mt-1 text-sm font-semibold text-slate-500">
                                Total data: {meta?.total ?? rows.length}
                            </p>
                        </div>

                        <div className="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-600">
                            Filter: {tanggalAwal || "Semua"} s/d {tanggalAkhir || "Semua"} |{" "}
                            Status: {labelFilterStatus(statusJadwal)} | Metode:{" "}
                            {labelFilterMetode(metode)}
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100">
                            <thead className="bg-slate-50">
                                <tr>
                                    <Th>No</Th>
                                    <Th>Kandidat</Th>
                                    <Th>Email</Th>
                                    <Th>No WA</Th>
                                    <Th>Posisi</Th>
                                    <Th>Tanggal OL</Th>
                                    <Th>Jam</Th>
                                    <Th>Metode</Th>
                                    <Th>PIC</Th>
                                    <Th>Status</Th>
                                    <Th>Review Management</Th>
                                    <Th>Link</Th>
                                    <Th>Catatan</Th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100 bg-white">
                                {loading && (
                                    <tr>
                                        <td
                                            colSpan="13"
                                            className="px-6 py-10 text-center text-sm font-bold text-slate-500"
                                        >
                                            Memuat data...
                                        </td>
                                    </tr>
                                )}

                                {!loading && rows.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan="13"
                                            className="px-6 py-10 text-center text-sm font-bold text-slate-500"
                                        >
                                            Data tidak ditemukan.
                                        </td>
                                    </tr>
                                )}

                                {!loading &&
                                    rows.map((item, index) => (
                                        <tr
                                            key={item.id || index}
                                            className="transition hover:bg-slate-50"
                                        >
                                            <Td>{meta?.from ? meta.from + index : index + 1}</Td>

                                            <Td>
                                                <div className="font-black text-slate-900">
                                                    {item.nama_kandidat || "-"}
                                                </div>
                                                <div className="text-xs font-semibold text-slate-400">
                                                    {item.nama_panggil || "-"}
                                                </div>
                                            </Td>

                                            <Td>{item.email || "-"}</Td>
                                            <Td>{item.no_wa || "-"}</Td>

                                            <Td>
                                                <div className="max-w-[220px] whitespace-normal font-semibold leading-6 text-slate-700">
                                                    {item.posisi_dilamar || "-"}
                                                </div>
                                            </Td>

                                            <Td>{formatDate(item.tanggal_ol)}</Td>
                                            <Td>{item.jam_ol || "-"}</Td>

                                            <Td>
                                                <MetodeBadge value={item.metode} />
                                            </Td>

                                            <Td>{item.pic || "-"}</Td>

                                            <Td>
                                                <StatusBadge
                                                    value={item.status_jadwal_label}
                                                    type={item.status_jadwal}
                                                />
                                            </Td>

                                            <Td>
                                                <div className="max-w-[220px] whitespace-normal font-semibold leading-6 text-slate-700">
                                                    {item.review_management || "-"}
                                                </div>
                                            </Td>

                                            <Td>
                                                {item.link && item.link !== "-" ? (
                                                    <a
                                                        href={item.link}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700 transition hover:bg-emerald-100"
                                                    >
                                                        Buka Link
                                                    </a>
                                                ) : (
                                                    "-"
                                                )}
                                            </Td>

                                            <Td>
                                                <div className="max-w-[260px] whitespace-normal break-words font-semibold leading-6 text-slate-600">
                                                    {item.catatan || "-"}
                                                </div>
                                            </Td>
                                        </tr>
                                    ))}
                            </tbody>
                        </table>
                    </div>

                    {meta && meta.last_page > 1 && (
                        <div className="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-sm font-bold text-slate-500">
                                Halaman {meta.current_page} dari {meta.last_page}
                            </p>

                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    disabled={page <= 1}
                                    onClick={() => setPage((prev) => Math.max(1, prev - 1))}
                                    className="rounded-xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Sebelumnya
                                </button>

                                <button
                                    type="button"
                                    disabled={page >= meta.last_page}
                                    onClick={() =>
                                        setPage((prev) => Math.min(meta.last_page, prev + 1))
                                    }
                                    className="rounded-xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Berikutnya
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

function SummaryCard({ title, value, description }) {
    return (
        <div className="rounded-3xl bg-white/10 p-5 ring-1 ring-white/10 backdrop-blur">
            <p className="text-sm font-black text-slate-200">{title}</p>
            <p className="mt-3 text-3xl font-black text-white">{numberFormat(value)}</p>
            <p className="mt-2 text-xs font-semibold text-slate-300">{description}</p>
        </div>
    );
}

function ChartCard({ title, description, children }) {
    return (
        <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div className="mb-5">
                <h3 className="text-base font-black text-slate-950">{title}</h3>
                <p className="mt-1 text-sm font-semibold text-slate-500">{description}</p>
            </div>

            {children}
        </div>
    );
}

function ProgressList({ items, colorClass = "bg-emerald-500" }) {
    const safeItems = Array.isArray(items) ? items : [];
    const total = safeItems.reduce((sum, item) => sum + Number(item.total || 0), 0);

    if (safeItems.length === 0) {
        return (
            <div className="rounded-3xl bg-slate-50 p-6 text-center text-sm font-bold text-slate-400">
                Belum ada data.
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {safeItems.map((item, index) => {
                const value = Number(item.total || 0);
                const percent = total > 0 ? Math.round((value / total) * 100) : 0;

                return (
                    <div key={`${item.label}-${index}`}>
                        <div className="mb-2 flex items-center justify-between gap-3">
                            <p className="truncate text-sm font-black text-slate-700">
                                {item.label || "Tidak Diisi"}
                            </p>

                            <p className="shrink-0 text-sm font-black text-slate-950">
                                {numberFormat(value)}
                            </p>
                        </div>

                        <div className="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div
                                className={`h-full rounded-full ${colorClass}`}
                                style={{
                                    width: `${Math.max(percent, value > 0 ? 4 : 0)}%`,
                                }}
                            />
                        </div>

                        <p className="mt-1 text-xs font-bold text-slate-400">{percent}%</p>
                    </div>
                );
            })}
        </div>
    );
}

function Th({ children }) {
    return (
        <th className="whitespace-nowrap px-6 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
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

function MetodeBadge({ value }) {
    const normalized = String(value || "").toLowerCase();

    const colorClass =
        normalized === "online"
            ? "bg-violet-100 text-violet-700"
            : normalized === "offline"
            ? "bg-violet-100 text-violet-700"
            : "bg-slate-100 text-slate-700";

    return (
        <span className={`rounded-full px-3 py-1 text-xs font-black ${colorClass}`}>
            {value || "-"}
        </span>
    );
}

function labelFilterStatus(value) {
    if (value === "pending") return "Pending";
    if (value === "menerima") return "Menerima";
    if (value === "menolak") return "Menolak";
    if (value === "tidak_melanjutkan") return "Tidak Melanjutkan";

    return "Semua";
}

function labelFilterMetode(value) {
    if (value === "online") return "Online";
    if (value === "offline") return "Offline";

    return "Semua";
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

function numberFormat(value) {
    return new Intl.NumberFormat("id-ID").format(Number(value || 0));
}