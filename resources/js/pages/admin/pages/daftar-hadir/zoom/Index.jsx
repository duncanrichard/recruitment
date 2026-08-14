import React, { useEffect, useMemo, useState } from "react";

export default function DaftarHadirZoomPage({ onOpenDetailDaftarHadirZoom }) {
    const [groups, setGroups] = useState([]);
    const [tableLoading, setTableLoading] = useState(false);
    const [search, setSearch] = useState("");
    const [tanggalMulai, setTanggalMulai] = useState("");
    const [tanggalSelesai, setTanggalSelesai] = useState("");
    const [appliedFilter, setAppliedFilter] = useState({
        tanggalMulai: "",
        tanggalSelesai: "",
    });

    const fetchGroups = async (filters = appliedFilter) => {
        setTableLoading(true);

        try {
            const params = new URLSearchParams();

            if (filters.tanggalMulai) {
                params.append("tanggal_mulai", filters.tanggalMulai);
            }

            if (filters.tanggalSelesai) {
                params.append("tanggal_selesai", filters.tanggalSelesai);
            }

            const url = params.toString()
                ? `/admin/daftar-hadir/zoom/groups?${params.toString()}`
                : "/admin/daftar-hadir/zoom/groups";

            const response = await fetch(url, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal mengambil data daftar hadir.");
                return;
            }

            setGroups(Array.isArray(result.data) ? result.data : []);
        } catch (error) {
            console.error("Gagal mengambil data daftar hadir:", error);
            alert("Terjadi kesalahan saat mengambil data daftar hadir.");
        } finally {
            setTableLoading(false);
        }
    };

    useEffect(() => {
        fetchGroups({ tanggalMulai: "", tanggalSelesai: "" });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const filteredGroups = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) return groups;

        return groups.filter((item) => {
            return (
                String(item.tanggal_test || "").toLowerCase().includes(keyword) ||
                formatTanggal(item.tanggal_test).toLowerCase().includes(keyword)
            );
        });
    }, [groups, search]);

    const totalSummary = useMemo(() => {
        return filteredGroups.reduce(
            (summary, item) => ({
                total_peserta:
                    summary.total_peserta + Number(item.total_peserta || 0),
                total_hadir: summary.total_hadir + Number(item.total_hadir || 0),
                total_tidak_hadir:
                    summary.total_tidak_hadir +
                    Number(item.total_tidak_hadir || 0),
                total_belum_ada:
                    summary.total_belum_ada + Number(item.total_belum_ada || 0),
                total_lolos: summary.total_lolos + Number(item.total_lolos || 0),
                total_gagal: summary.total_gagal + Number(item.total_gagal || 0),
            }),
            {
                total_peserta: 0,
                total_hadir: 0,
                total_tidak_hadir: 0,
                total_belum_ada: 0,
                total_lolos: 0,
                total_gagal: 0,
            }
        );
    }, [filteredGroups]);

    const handleFilter = (event) => {
        event.preventDefault();

        if (tanggalMulai && tanggalSelesai && tanggalSelesai < tanggalMulai) {
            alert("Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.");
            return;
        }

        const nextFilter = {
            tanggalMulai,
            tanggalSelesai,
        };

        setAppliedFilter(nextFilter);
        fetchGroups(nextFilter);
    };

    const handleReset = () => {
        const nextFilter = {
            tanggalMulai: "",
            tanggalSelesai: "",
        };

        setTanggalMulai("");
        setTanggalSelesai("");
        setSearch("");
        setAppliedFilter(nextFilter);
        fetchGroups(nextFilter);
    };

    function formatTanggal(tanggal) {
        if (!tanggal) return "-";

        const date = new Date(`${tanggal}T00:00:00`);

        if (Number.isNaN(date.getTime())) {
            return String(tanggal);
        }

        return date.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div className="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                            Daftar Hadir Test Zoom
                        </div>

                        <h3 className="mt-2 text-xl font-black text-slate-950">
                            Rekap Kehadiran Peserta
                        </h3>

                        <p className="mt-1 max-w-2xl text-sm font-medium leading-6 text-slate-500">
                            Data diambil dari tabel jadwal_test_zoom dan status kehadiran dari tabel daftar_hadir_test_zoom.
                        </p>
                    </div>

                    <div className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                        <SummaryCard label="Total" value={totalSummary.total_peserta} />
                        <SummaryCard label="Hadir" value={totalSummary.total_hadir} />
                        <SummaryCard label="Tidak Hadir" value={totalSummary.total_tidak_hadir} />
                        <SummaryCard label="Belum Ada" value={totalSummary.total_belum_ada} />
                        <SummaryCard label="Lolos" value={totalSummary.total_lolos} />
                        <SummaryCard label="Gagal" value={totalSummary.total_gagal} />
                    </div>
                </div>
            </div>

            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                        <form
                            onSubmit={handleFilter}
                            className="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto] md:items-end"
                        >
                            <DateInput
                                label="Tanggal Mulai"
                                value={tanggalMulai}
                                onChange={setTanggalMulai}
                            />

                            <DateInput
                                label="Tanggal Selesai"
                                value={tanggalSelesai}
                                onChange={setTanggalSelesai}
                            />

                            <button
                                type="submit"
                                disabled={tableLoading}
                                className="rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                Filter
                            </button>

                            <button
                                type="button"
                                disabled={tableLoading}
                                onClick={handleReset}
                                className="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                Reset
                            </button>
                        </form>

                        <div className="flex items-center gap-2">
                            <span className="text-sm font-bold text-slate-600">
                                Search:
                            </span>

                            <input
                                type="text"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Cari tanggal..."
                                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 md:w-80"
                            />
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Tanggal Test</TableHead>
                                <TableHead>Total Peserta</TableHead>
                                <TableHead>Hadir</TableHead>
                                <TableHead>Tidak Hadir</TableHead>
                                <TableHead>Belum Ada</TableHead>
                                <TableHead>Lolos</TableHead>
                                <TableHead>Gagal</TableHead>
                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <td
                                        colSpan="9"
                                        className="px-6 py-16 text-center text-sm font-black text-slate-500"
                                    >
                                        Memuat data...
                                    </td>
                                </tr>
                            ) : filteredGroups.length > 0 ? (
                                filteredGroups.map((item, index) => (
                                    <tr
                                        key={item.tanggal_test || index}
                                        className="group transition hover:bg-slate-50"
                                    >
                                        <td className="px-6 py-5 text-sm font-black text-slate-500">
                                            {index + 1}
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="font-black text-slate-950">
                                                {formatTanggal(item.tanggal_test)}
                                            </div>

                                            <div className="mt-1 text-xs font-bold text-slate-400">
                                                {item.tanggal_test || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5 text-sm font-black text-slate-700">
                                            {Number(item.total_peserta || 0)}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-black text-emerald-700">
                                            {Number(item.total_hadir || 0)}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-black text-rose-600">
                                            {Number(item.total_tidak_hadir || 0)}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-black text-slate-500">
                                            {Number(item.total_belum_ada || 0)}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-black text-indigo-700">
                                            {Number(item.total_lolos || 0)}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-black text-red-600">
                                            {Number(item.total_gagal || 0)}
                                        </td>

                                        <td className="px-6 py-5 text-right">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    onOpenDetailDaftarHadirZoom?.(
                                                        item.tanggal_test
                                                    )
                                                }
                                                className="rounded-2xl bg-indigo-600 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-indigo-700"
                                            >
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="9" className="px-6 py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ◎
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Belum ada data daftar hadir Zoom pada filter ini.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}

function DateInput({ label, value, onChange }) {
    return (
        <label className="block">
            <span className="mb-2 block text-sm font-black text-slate-700">
                {label}
            </span>

            <input
                type="date"
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />
        </label>
    );
}

function SummaryCard({ label, value }) {
    return (
        <div className="rounded-2xl bg-slate-50 px-4 py-3 text-center">
            <div className="text-xl font-black text-slate-950">{value}</div>
            <div className="mt-1 text-xs font-black uppercase tracking-wide text-slate-500">
                {label}
            </div>
        </div>
    );
}

function TableHead({ children, align = "left" }) {
    const alignClass = align === "right" ? "text-right" : "text-left";

    return (
        <th
            className={`whitespace-nowrap px-6 py-4 ${alignClass} text-xs font-black uppercase tracking-[0.12em] text-slate-500`}
        >
            {children}
        </th>
    );
}
