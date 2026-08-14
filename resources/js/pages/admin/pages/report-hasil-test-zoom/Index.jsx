import React, { useEffect, useMemo, useState } from "react";

export default function ReportHasilTestZoomPage() {
    const [tanggalAwal, setTanggalAwal] = useState("");
    const [tanggalAkhir, setTanggalAkhir] = useState("");
    const [statusKehadiran, setStatusKehadiran] = useState("semua");
    const [hasilTest, setHasilTest] = useState("semua");

    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState(null);
    const [dashboard, setDashboard] = useState(null);
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState("");
    const [showFilter, setShowFilter] = useState(true);

    const queryString = useMemo(() => {
        const params = new URLSearchParams();

        if (tanggalAwal) params.set("tanggal_awal", tanggalAwal);
        if (tanggalAkhir) params.set("tanggal_akhir", tanggalAkhir);

        params.set("status_kehadiran", statusKehadiran);
        params.set("hasil_test", hasilTest);
        params.set("page", page);

        return params.toString();
    }, [tanggalAwal, tanggalAkhir, statusKehadiran, hasilTest, page]);

    async function fetchData() {
        setLoading(true);
        setMessage("");

        try {
            const response = await fetch(`/report-hasil-test-zoom?${queryString}`, {
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
                setDashboard(null);
                setMessage(json?.message || "Gagal mengambil report hasil test Zoom.");
                return;
            }

            setRows(Array.isArray(json?.data?.rows?.data) ? json.data.rows.data : []);
            setMeta(json?.data?.rows || null);
            setDashboard(json?.data?.dashboard || null);
        } catch (error) {
            setRows([]);
            setMeta(null);
            setDashboard(null);
            setMessage("Terjadi kesalahan saat mengambil report hasil test Zoom.");
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
        setStatusKehadiran("semua");
        setHasilTest("semua");
        setPage(1);
    }

    function handleExportExcel() {
        const params = new URLSearchParams();

        if (tanggalAwal) params.set("tanggal_awal", tanggalAwal);
        if (tanggalAkhir) params.set("tanggal_akhir", tanggalAkhir);

        params.set("status_kehadiran", statusKehadiran);
        params.set("hasil_test", hasilTest);

        window.location.href = `/report-hasil-test-zoom/export?${params.toString()}`;
    }

    const summary = dashboard?.summary || {};
    const demografi = dashboard?.demografi || {};
    const top = dashboard?.top || {};
    const trend = dashboard?.trend || [];

    return (
        <div className="min-h-screen bg-slate-50 p-4 sm:p-6">
            <div className="mx-auto max-w-7xl space-y-6">
                <div className="overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-slate-900 to-violet-900 p-6 text-white shadow-xl">
                    <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div className="mb-3 inline-flex rounded-full bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-widest text-violet-100 ring-1 ring-white/10">
                                Zoom Test Analytics
                            </div>

                            <h1 className="text-3xl font-black tracking-tight sm:text-4xl">
                                Hasil Test Zoom
                            </h1>

                            <p className="mt-3 max-w-3xl text-sm font-semibold leading-6 text-slate-200">
                                Monitoring hasil test Zoom berdasarkan jadwal, tanggal kehadiran,
                                status kehadiran, hasil test, dan demografi peserta.
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
                            title="Total Peserta"
                            value={summary.total}
                            description="Semua data sesuai filter"
                        />

                        <SummaryCard
                            title="Hadir"
                            value={summary.hadir}
                            description={`${summary.persentase_hadir || 0}% dari total`}
                        />

                        <SummaryCard
                            title="Lolos Test"
                            value={summary.lolos}
                            description={`${summary.persentase_lolos || 0}% dari total`}
                        />

                        <SummaryCard
                            title="Belum Hasil"
                            value={summary.belum_ada_hasil}
                            description="Peserta belum memiliki hasil test"
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
                                    className="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
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
                                    className="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                                />
                            </div>

                            <div className="xl:col-span-2">
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Kehadiran
                                </label>

                                <select
                                    value={statusKehadiran}
                                    onChange={(event) => setStatusKehadiran(event.target.value)}
                                    className="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                                >
                                    <option value="semua">Semua</option>
                                    <option value="hadir">Hadir</option>
                                    <option value="tidak_hadir">Tidak Hadir</option>
                                    <option value="belum_ada">Belum Ada</option>
                                </select>
                            </div>

                            <div className="xl:col-span-2">
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Hasil Test
                                </label>

                                <select
                                    value={hasilTest}
                                    onChange={(event) => setHasilTest(event.target.value)}
                                    className="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                                >
                                    <option value="semua">Semua</option>
                                    <option value="lolos">Lolos</option>
                                    <option value="gagal">Gagal</option>
                                    <option value="belum_ada">Belum Ada</option>
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

                <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <div className="xl:col-span-2">
                        <ChartCard
                            title="Trend Kehadiran Test Zoom"
                            description="Jumlah peserta berdasarkan tanggal kehadiran"
                        >
                            <TrendChart items={trend} />
                        </ChartCard>
                    </div>

                    <ChartCard
                        title="Hasil Test"
                        description="Komposisi hasil test peserta"
                    >
                        <ProgressList items={demografi.hasil_test || []} />
                    </ChartCard>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <ChartCard title="Status Kehadiran" description="Kehadiran peserta Zoom">
                        <ProgressList items={demografi.status_kehadiran || []} />
                    </ChartCard>

                    <ChartCard
                        title="Kombinasi Kehadiran & Hasil"
                        description="Analisa hasil berdasarkan kehadiran"
                    >
                        <ProgressList items={demografi.kehadiran_hasil || []} />
                    </ChartCard>

                    <ChartCard title="Top Jadwal Test" description="Jadwal dengan peserta terbanyak">
                        <ProgressList items={top.jadwal || []} />
                    </ChartCard>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <ChartCard title="Top Peserta" description="Nama peserta paling sering muncul">
                        <ProgressList items={top.peserta || []} />
                    </ChartCard>

                    <ChartCard title="Domain Email" description="Demografi domain email peserta">
                        <ProgressList items={demografi.domain_email || []} />
                    </ChartCard>

                    <ChartCard title="Kontak Terisi" description="Kelengkapan data kontak">
                        <ProgressList items={demografi.kelengkapan_kontak || []} />
                    </ChartCard>
                </div>

                <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 className="text-lg font-black text-slate-950">
                                Detail Data Hasil Test Zoom
                            </h2>

                            <p className="mt-1 text-sm font-semibold text-slate-500">
                                Total data: {meta?.total ?? rows.length}
                            </p>
                        </div>

                        <div className="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-600">
                            Filter: {tanggalAwal || "Semua"} s/d {tanggalAkhir || "Semua"} |{" "}
                            Kehadiran: {labelFilterKehadiran(statusKehadiran)} | Hasil:{" "}
                            {labelFilterHasil(hasilTest)}
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100">
                            <thead className="bg-slate-50">
                                <tr>
                                    <Th>No</Th>
                                    <Th>Tanggal Kehadiran</Th>
                                    <Th>Jadwal</Th>
                                    <Th>Token</Th>
                                    <Th>Nama</Th>
                                    <Th>Email</Th>
                                    <Th>No HP / WA</Th>
                                    <Th>Kehadiran</Th>
                                    <Th>Hasil Test</Th>
                                    <Th>Created At</Th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100 bg-white">
                                {loading && (
                                    <tr>
                                        <td
                                            colSpan="10"
                                            className="px-6 py-10 text-center text-sm font-bold text-slate-500"
                                        >
                                            Memuat data...
                                        </td>
                                    </tr>
                                )}

                                {!loading && rows.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan="10"
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
                                            <Td>{formatDate(item.tanggal_kehadiran)}</Td>
                                            <Td>{formatDateTime(item.jadwal)}</Td>
                                            <Td>
                                                <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">
                                                    {item.token || "-"}
                                                </span>
                                            </Td>
                                            <Td>
                                                <div className="font-black text-slate-900">
                                                    {item.nama || "-"}
                                                </div>
                                                <div className="text-xs font-semibold text-slate-400">
                                                    {item.nama_panggil || "-"}
                                                </div>
                                            </Td>
                                            <Td>{item.email || "-"}</Td>
                                            <Td>{item.no_hp || "-"}</Td>
                                            <Td>
                                                <StatusBadge
                                                    value={item.status_kehadiran_label}
                                                    type={item.status_kehadiran}
                                                />
                                            </Td>
                                            <Td>
                                                <HasilBadge
                                                    value={item.hasil_test_label}
                                                    type={item.hasil_test}
                                                />
                                            </Td>
                                            <Td>{formatDateTime(item.created_at)}</Td>
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

function ProgressList({ items }) {
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
                                className="h-full rounded-full bg-violet-500"
                                style={{ width: `${Math.max(percent, value > 0 ? 4 : 0)}%` }}
                            />
                        </div>

                        <p className="mt-1 text-xs font-bold text-slate-400">{percent}%</p>
                    </div>
                );
            })}
        </div>
    );
}

function TrendChart({ items }) {
    const safeItems = Array.isArray(items) ? items : [];
    const maxValue = Math.max(...safeItems.map((item) => Number(item.total || 0)), 0);

    if (safeItems.length === 0) {
        return (
            <div className="rounded-3xl bg-slate-50 p-6 text-center text-sm font-bold text-slate-400">
                Belum ada data trend.
            </div>
        );
    }

    return (
        <div className="flex min-h-[260px] items-end gap-3 overflow-x-auto rounded-3xl bg-slate-50 p-5">
            {safeItems.map((item, index) => {
                const value = Number(item.total || 0);
                const height = maxValue > 0 ? Math.round((value / maxValue) * 180) : 0;

                return (
                    <div
                        key={`${item.label}-${index}`}
                        className="flex min-w-[58px] flex-col items-center justify-end gap-2"
                    >
                        <p className="text-xs font-black text-slate-700">{value}</p>

                        <div
                            className="w-10 rounded-t-2xl bg-violet-500 shadow-sm transition"
                            style={{ height: `${Math.max(height, value > 0 ? 16 : 0)}px` }}
                        />

                        <p className="w-16 truncate text-center text-[11px] font-bold text-slate-500">
                            {formatShortDate(item.label)}
                        </p>
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
        type === "hadir"
            ? "bg-emerald-100 text-emerald-700"
            : type === "tidak_hadir"
            ? "bg-red-100 text-red-700"
            : "bg-slate-100 text-slate-700";

    return (
        <span className={`rounded-full px-3 py-1 text-xs font-black ${colorClass}`}>
            {value || "Belum Ada"}
        </span>
    );
}

function HasilBadge({ value, type }) {
    const colorClass =
        type === "lolos"
            ? "bg-emerald-100 text-emerald-700"
            : type === "gagal"
            ? "bg-red-100 text-red-700"
            : "bg-slate-100 text-slate-700";

    return (
        <span className={`rounded-full px-3 py-1 text-xs font-black ${colorClass}`}>
            {value || "Belum Ada"}
        </span>
    );
}

function labelFilterKehadiran(value) {
    if (value === "hadir") return "Hadir";
    if (value === "tidak_hadir") return "Tidak Hadir";
    if (value === "belum_ada") return "Belum Ada";

    return "Semua";
}

function labelFilterHasil(value) {
    if (value === "lolos") return "Lolos";
    if (value === "gagal") return "Gagal";
    if (value === "belum_ada") return "Belum Ada";

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

function formatShortDate(value) {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
    });
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

function numberFormat(value) {
    return new Intl.NumberFormat("id-ID").format(Number(value || 0));
}