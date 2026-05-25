import React, { useEffect, useMemo, useState } from "react";

export default function DetailDaftarHadirZoomPage({ tanggal, onBack }) {
    const [dataPeserta, setDataPeserta] = useState([]);
    const [summary, setSummary] = useState({
        total: 0,
        hadir: 0,
        tidak_hadir: 0,
        belum_ada: 0,
        lolos: 0,
        gagal: 0,
    });

    const [activeTab, setActiveTab] = useState("semua");
    const [tableLoading, setTableLoading] = useState(false);
    const [savingId, setSavingId] = useState(null);
    const [search, setSearch] = useState("");

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const fetchDetail = async () => {
        if (!tanggal) return;

        setTableLoading(true);

        try {
            const params = new URLSearchParams({
                tanggal,
                status: activeTab,
            });

            const response = await fetch(
                `/admin/daftar-hadir/zoom/detail?${params.toString()}`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal mengambil detail daftar hadir.");
                return;
            }

            setDataPeserta(Array.isArray(result.data) ? result.data : []);
            setSummary({
                total: Number(result.summary?.total || 0),
                hadir: Number(result.summary?.hadir || 0),
                tidak_hadir: Number(result.summary?.tidak_hadir || 0),
                belum_ada: Number(result.summary?.belum_ada || 0),
                lolos: Number(result.summary?.lolos || 0),
                gagal: Number(result.summary?.gagal || 0),
            });
        } catch (error) {
            console.error("Gagal mengambil detail daftar hadir:", error);
            alert("Terjadi kesalahan saat mengambil detail daftar hadir.");
        } finally {
            setTableLoading(false);
        }
    };

    useEffect(() => {
        fetchDetail();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [tanggal, activeTab]);

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) return dataPeserta;

        return dataPeserta.filter((item) => {
            return (
                String(item.nama || "").toLowerCase().includes(keyword) ||
                String(item.email || "").toLowerCase().includes(keyword) ||
                String(item.no_hp || "").toLowerCase().includes(keyword) ||
                String(item.kehadiran_label || "").toLowerCase().includes(keyword) ||
                String(item.hasil_test_label || "").toLowerCase().includes(keyword)
            );
        });
    }, [dataPeserta, search]);

    const updateHasilTest = async (item, hasilTest) => {
        const jadwalTestZoomId = item?.jadwal_test_zoom_id || item?.id;

        if (!jadwalTestZoomId) {
            alert("ID jadwal test Zoom tidak ditemukan.");
            return;
        }

        setSavingId(jadwalTestZoomId);

        try {
            const response = await fetch(
                `/admin/daftar-hadir/zoom/${jadwalTestZoomId}/hasil-test`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                    body: JSON.stringify({
                        hasil_test: hasilTest,
                    }),
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal memperbarui hasil test.");
                return;
            }

            setDataPeserta((prev) =>
                prev.map((row) =>
                    String(row.jadwal_test_zoom_id || row.id) === String(jadwalTestZoomId)
                        ? {
                              ...row,
                              hasil_test: hasilTest,
                              hasil_test_label:
                                  hasilTest === "lolos" ? "Lolos" : "Gagal",
                          }
                        : row
                )
            );

            fetchDetail();
        } catch (error) {
            console.error("Gagal memperbarui hasil test:", error);
            alert("Terjadi kesalahan saat memperbarui hasil test.");
        } finally {
            setSavingId(null);
        }
    };

    const formatTanggal = (value) => {
        if (!value) return "-";

        const date = new Date(`${value}T00:00:00`);

        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return date.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    };

    const formatJam = (value) => {
        if (!value) return "-";

        const date = new Date(String(value).replace(" ", "T"));

        if (Number.isNaN(date.getTime())) {
            return "-";
        }

        return date.toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
        });
    };

    const normalizeKehadiran = (item) => {
        const raw = item?.status_kehadiran ?? item?.kehadiran ?? null;

        if (raw === null || raw === undefined || String(raw).trim() === "") {
            return "belum_ada";
        }

        const normalized = String(raw)
            .trim()
            .toLowerCase()
            .replace(/\s+/g, "_")
            .replace(/-/g, "_");

        if (normalized === "hadir") {
            return "hadir";
        }

        if (
            normalized === "tidak_hadir" ||
            normalized === "tidakhadir" ||
            normalized === "tidak"
        ) {
            return "tidak_hadir";
        }

        return "belum_ada";
    };

    const isHadir = (item) => normalizeKehadiran(item) === "hadir";

    const renderKehadiranBadge = (item) => {
        const status = normalizeKehadiran(item);

        if (status === "hadir") {
            return <Badge type="success">Hadir</Badge>;
        }

        if (status === "tidak_hadir") {
            return <Badge type="danger">Tidak Hadir</Badge>;
        }

        return <Badge type="neutral">Belum Ada</Badge>;
    };

    const renderHasilTestBadge = (item) => {
        if (item.hasil_test === "lolos") {
            return <Badge type="success">Lolos</Badge>;
        }

        if (item.hasil_test === "gagal") {
            return <Badge type="danger">Gagal</Badge>;
        }

        return <Badge type="neutral">Belum Ada</Badge>;
    };

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <button
                    type="button"
                    onClick={onBack}
                    className="mb-4 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                >
                    ← Kembali
                </button>

                <div className="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                    <div>
                        <div className="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                            Detail Daftar Hadir Zoom
                        </div>

                        <h2 className="mt-2 text-2xl font-black text-slate-950">
                            {formatTanggal(tanggal)}
                        </h2>

                        <p className="mt-1 max-w-2xl text-sm font-medium leading-6 text-slate-500">
                           Kehadiran Test Zoom dan hasil Test .
                        </p>
                    </div>

                    <div className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                        <SummaryCard label="Total" value={summary.total} />
                        <SummaryCard label="Hadir" value={summary.hadir} />
                        <SummaryCard label="Tidak Hadir" value={summary.tidak_hadir} />
                        <SummaryCard label="Belum Ada" value={summary.belum_ada} />
                        <SummaryCard label="Lolos" value={summary.lolos} />
                        <SummaryCard label="Gagal" value={summary.gagal} />
                    </div>
                </div>
            </div>

            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div className="flex flex-wrap gap-2">
                            <TabButton
                                active={activeTab === "semua"}
                                onClick={() => setActiveTab("semua")}
                            >
                                Semua
                            </TabButton>

                            <TabButton
                                active={activeTab === "hadir"}
                                onClick={() => setActiveTab("hadir")}
                            >
                                Hadir
                            </TabButton>

                            <TabButton
                                active={activeTab === "tidak_hadir"}
                                onClick={() => setActiveTab("tidak_hadir")}
                            >
                                Tidak Hadir
                            </TabButton>
                        </div>

                        <div className="flex items-center gap-2">
                            <span className="text-sm font-bold text-slate-600">
                                Search:
                            </span>

                            <input
                                type="text"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Cari peserta..."
                                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100 md:w-80"
                            />
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Peserta</TableHead>
                                <TableHead>Kontak</TableHead>
                                <TableHead>Jam Test</TableHead>
                                <TableHead>Kehadiran</TableHead>
                                <TableHead>Hasil Test</TableHead>
                                <TableHead align="right">Action</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <td
                                        colSpan="7"
                                        className="px-6 py-16 text-center text-sm font-black text-slate-500"
                                    >
                                        Memuat data...
                                    </td>
                                </tr>
                            ) : filteredData.length > 0 ? (
                                filteredData.map((item, index) => {
                                    const rowId = item.jadwal_test_zoom_id || item.id || index;
                                    const isRowSaving = String(savingId) === String(item.jadwal_test_zoom_id || item.id);

                                    return (
                                        <tr
                                            key={`${rowId}-${index}`}
                                            className="group transition hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-5 text-sm font-black text-slate-500">
                                                {index + 1}
                                            </td>

                                            <td className="px-6 py-5">
                                                <div className="font-black text-slate-950">
                                                    {item.nama || "-"}
                                                </div>

                                                <div className="mt-1 text-xs font-bold text-slate-400">
                                                    ID: {item.data_riwayat_diri_id || "-"}
                                                </div>
                                            </td>

                                            <td className="px-6 py-5">
                                                <div className="text-sm font-bold text-slate-700">
                                                    {item.email || "-"}
                                                </div>

                                                <div className="mt-1 text-xs font-bold text-slate-400">
                                                    {item.no_hp || "-"}
                                                </div>
                                            </td>

                                            <td className="px-6 py-5 text-sm font-black text-slate-700">
                                                {formatJam(item.jadwal)}
                                            </td>

                                            <td className="px-6 py-5">
                                                {renderKehadiranBadge(item)}
                                            </td>

                                            <td className="px-6 py-5">
                                                {renderHasilTestBadge(item)}
                                            </td>

                                            <td className="px-6 py-5 text-right">
                                                {isHadir(item) ? (
                                                    <div className="flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            disabled={isRowSaving}
                                                            onClick={() => updateHasilTest(item, "lolos")}
                                                            className={`rounded-2xl px-4 py-2 text-xs font-black shadow-sm transition disabled:cursor-not-allowed disabled:opacity-60 ${
                                                                item.hasil_test === "lolos"
                                                                    ? "bg-emerald-600 text-white"
                                                                    : "border border-emerald-100 bg-white text-emerald-700 hover:bg-emerald-50"
                                                            }`}
                                                        >
                                                            {isRowSaving ? "Menyimpan..." : "Lolos"}
                                                        </button>

                                                        <button
                                                            type="button"
                                                            disabled={isRowSaving}
                                                            onClick={() => updateHasilTest(item, "gagal")}
                                                            className={`rounded-2xl px-4 py-2 text-xs font-black shadow-sm transition disabled:cursor-not-allowed disabled:opacity-60 ${
                                                                item.hasil_test === "gagal"
                                                                    ? "bg-rose-600 text-white"
                                                                    : "border border-rose-100 bg-white text-rose-700 hover:bg-rose-50"
                                                            }`}
                                                        >
                                                            {isRowSaving ? "Menyimpan..." : "Gagal"}
                                                        </button>
                                                    </div>
                                                ) : (
                                                    <span className="text-xs font-black text-slate-400">
                                                        Tidak ada action
                                                    </span>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan="7" className="px-6 py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ◎
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Tidak ada peserta pada filter ini.
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

function TabButton({ active, children, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`rounded-2xl px-5 py-2.5 text-sm font-black transition ${
                active
                    ? "bg-teal-600 text-white shadow-lg shadow-teal-100"
                    : "border border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
            }`}
        >
            {children}
        </button>
    );
}

function Badge({ children, type = "neutral" }) {
    const classes = {
        success: "bg-emerald-50 text-emerald-700",
        danger: "bg-rose-50 text-rose-700",
        neutral: "bg-slate-100 text-slate-600",
    };

    return (
        <span
            className={`inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ${classes[type]}`}
        >
            {children}
        </span>
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
