import React, { useEffect, useMemo, useState } from "react";

export default function DaftarHadirMmpiPage({ onNavigate }) {
    const [items, setItems] = useState([]);
    const [summary, setSummary] = useState({
        total: 0,
        hadir: 0,
        tidak_hadir: 0,
        belum_ada: 0,
        lolos: 0,
        gagal: 0,
    });
    const [tanggalMulai, setTanggalMulai] = useState("");
    const [tanggalSelesai, setTanggalSelesai] = useState("");
    const [search, setSearch] = useState("");
    const [loading, setLoading] = useState(false);

    const fetchData = async () => {
        setLoading(true);

        try {
            const params = new URLSearchParams();

            if (tanggalMulai) params.append("tanggal_mulai", tanggalMulai);
            if (tanggalSelesai) params.append("tanggal_selesai", tanggalSelesai);
            if (search.trim()) params.append("search", search.trim());

            const response = await fetch(`/admin/daftar-hadir/mmpi/groups?${params.toString()}`, {
                headers: { Accept: "application/json" },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal mengambil group daftar hadir MMPI.");
                return;
            }

            setItems(result.data || []);
            setSummary({
                total: Number(result.summary?.total || 0),
                hadir: Number(result.summary?.hadir || 0),
                tidak_hadir: Number(result.summary?.tidak_hadir || 0),
                belum_ada: Number(result.summary?.belum_ada || 0),
                lolos: Number(result.summary?.lolos || 0),
                gagal: Number(result.summary?.gagal || 0),
            });
        } catch (error) {
            console.error("Gagal mengambil group daftar hadir MMPI:", error);
            alert("Terjadi kesalahan saat mengambil data daftar hadir MMPI.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const filteredItems = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) return items;

        return items.filter((item) => {
            return (
                String(item.tanggal_test || "").toLowerCase().includes(keyword) ||
                String(item.tanggal_label || "").toLowerCase().includes(keyword)
            );
        });
    }, [items, search]);

    const applyFilter = () => fetchData();

    const resetFilter = () => {
        setTanggalMulai("");
        setTanggalSelesai("");
        setSearch("");

        setTimeout(() => fetchData(), 0);
    };

    const openDetail = (tanggal) => {
        if (typeof onNavigate === "function") {
            onNavigate("daftar-hadir-mmpi-detail", { tanggal });
            return;
        }

        window.dispatchEvent(
            new CustomEvent("admin:navigate", {
                detail: {
                    key: "daftar-hadir-mmpi-detail",
                    props: { tanggal },
                },
            })
        );
    };

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                    <div>
                        <div className="inline-flex whitespace-nowrap rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                            Daftar Hadir Test MMPI
                        </div>

                        <h2 className="mt-2 whitespace-nowrap text-2xl font-black text-slate-950">
                            Rekap Kehadiran Peserta
                        </h2>

                        <p className="mt-1 max-w-xl text-sm font-semibold leading-6 text-slate-500">
                            Data diambil dari tabel jadwal_test_mmpi dan status kehadiran dari tabel daftar_hadir_test_mmpi.
                        </p>
                    </div>

                    <div className="grid grid-cols-2 gap-3 md:grid-cols-6">
                        <SummaryCard label="Total" value={summary.total} />
                        <SummaryCard label="Hadir" value={summary.hadir} />
                        <SummaryCard label="Tidak Hadir" value={summary.tidak_hadir} />
                        <SummaryCard label="Belum Ada" value={summary.belum_ada} />
                        <SummaryCard label="Lolos" value={summary.lolos} />
                        <SummaryCard label="Tidak Lolos" value={summary.gagal} />
                    </div>
                </div>
            </div>

            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="grid gap-4 xl:grid-cols-[1fr_auto] xl:items-end">
                        <div className="grid gap-3 md:grid-cols-4">
                            <FieldDate label="Tanggal Mulai" value={tanggalMulai} onChange={setTanggalMulai} />
                            <FieldDate label="Tanggal Selesai" value={tanggalSelesai} onChange={setTanggalSelesai} />

                            <div className="md:col-span-2">
                                <label className="mb-2 block whitespace-nowrap text-xs font-black uppercase tracking-wide text-slate-500">
                                    Search
                                </label>
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Cari tanggal..."
                                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                                />
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                onClick={applyFilter}
                                className="whitespace-nowrap rounded-2xl bg-teal-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-teal-700"
                            >
                                Filter
                            </button>
                            <button
                                type="button"
                                onClick={resetFilter}
                                className="whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full whitespace-nowrap">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Tanggal Test</TableHead>
                                <TableHead>Total Peserta</TableHead>
                                <TableHead>Hadir</TableHead>
                                <TableHead>Tidak Hadir</TableHead>
                                <TableHead>Belum Ada</TableHead>
                                <TableHead>Lolos</TableHead>
                                <TableHead>Tidak Lolos</TableHead>
                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {loading ? (
                                <tr>
                                    <td colSpan="9" className="px-6 py-16 text-center text-sm font-black text-slate-500">
                                        Memuat data...
                                    </td>
                                </tr>
                            ) : filteredItems.length > 0 ? (
                                filteredItems.map((item, index) => (
                                    <tr key={item.tanggal_test} className="group transition hover:bg-slate-50">
                                        <td className="px-6 py-5 text-sm font-black text-slate-500">{index + 1}</td>
                                        <td className="px-6 py-5">
                                            <div className="font-black text-slate-950">{item.tanggal_label || formatTanggal(item.tanggal_test)}</div>
                                            <div className="mt-1 text-xs font-bold text-slate-400">{item.tanggal_test || "-"}</div>
                                        </td>
                                        <NumberCell value={item.total_peserta} />
                                        <NumberCell value={item.total_hadir} type="success" />
                                        <NumberCell value={item.total_tidak_hadir} type="danger" />
                                        <NumberCell value={item.total_belum_ada} />
                                        <NumberCell value={item.total_lolos} type="success" />
                                        <NumberCell value={item.total_gagal} type="danger" />
                                        <td className="px-6 py-5 text-right">
                                            <button
                                                type="button"
                                                onClick={() => openDetail(item.tanggal_test)}
                                                className="rounded-2xl bg-teal-600 px-5 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-teal-700"
                                            >
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="9" className="px-6 py-16 text-center">
                                        <EmptyState text="Belum ada jadwal MMPI pada filter ini." />
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

function FieldDate({ label, value, onChange }) {
    return (
        <div>
            <label className="mb-2 block whitespace-nowrap text-xs font-black uppercase tracking-wide text-slate-500">
                {label}
            </label>
            <input
                type="date"
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            />
        </div>
    );
}

function NumberCell({ value, type = "neutral" }) {
    const classes = {
        success: "text-emerald-700",
        danger: "text-rose-600",
        neutral: "text-slate-700",
    };

    return <td className={`px-6 py-5 text-sm font-black ${classes[type]}`}>{Number(value || 0)}</td>;
}

function SummaryCard({ label, value }) {
    return (
        <div className="rounded-2xl bg-slate-50 px-4 py-3 text-center">
            <div className="whitespace-nowrap text-xl font-black text-slate-950">{value}</div>
            <div className="mt-1 whitespace-nowrap text-xs font-black uppercase tracking-wide text-slate-500">{label}</div>
        </div>
    );
}

function TableHead({ children, align = "left" }) {
    const alignClass = align === "right" ? "text-right" : "text-left";

    return (
        <th className={`whitespace-nowrap px-6 py-4 ${alignClass} text-xs font-black uppercase tracking-[0.12em] text-slate-500`}>
            {children}
        </th>
    );
}

function EmptyState({ text }) {
    return (
        <div className="mx-auto max-w-sm text-center">
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">◎</div>
            <h3 className="mt-4 text-lg font-black text-slate-900">Data tidak ditemukan</h3>
            <p className="mt-2 text-sm font-medium text-slate-500">{text}</p>
        </div>
    );
}

function formatTanggal(value) {
    if (!value) return "-";
    const date = new Date(String(value).replace(" ", "T"));
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString("id-ID", { day: "2-digit", month: "long", year: "numeric" });
}
