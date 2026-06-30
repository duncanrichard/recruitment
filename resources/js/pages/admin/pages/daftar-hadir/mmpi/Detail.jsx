import React, { useEffect, useMemo, useState } from "react";

export default function DetailDaftarHadirMmpiPage({ tanggal, onBack }) {
    const selectedTanggal = tanggal || getTanggalFromUrl();

    const [items, setItems] = useState([]);
    const [summary, setSummary] = useState({
        total: 0,
        hadir: 0,
        tidak_hadir: 0,
        belum_ada: 0,
        lolos: 0,
        gagal: 0,
    });

    const [activeTab, setActiveTab] = useState("semua");
    const [search, setSearch] = useState("");
    const [loading, setLoading] = useState(false);
    const [savingKey, setSavingKey] = useState("");
    const [uploadingKey, setUploadingKey] = useState("");
    const [selectedFiles, setSelectedFiles] = useState({});

    const fetchData = async () => {
        if (!selectedTanggal) return;

        setLoading(true);

        try {
            const params = new URLSearchParams();
            params.append("tanggal", selectedTanggal);
            params.append("status", activeTab);

            if (search.trim()) {
                params.append("search", search.trim());
            }

            const response = await fetch(
                `/admin/daftar-hadir/mmpi/detail?${params.toString()}`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal mengambil detail daftar hadir MMPI.");
                return;
            }

            setItems(Array.isArray(result.data) ? result.data : []);
            setSummary({
                total: Number(result.summary?.total || 0),
                hadir: Number(result.summary?.hadir || 0),
                tidak_hadir: Number(result.summary?.tidak_hadir || 0),
                belum_ada: Number(result.summary?.belum_ada || 0),
                lolos: Number(result.summary?.lolos || 0),
                gagal: Number(result.summary?.gagal || 0),
            });
        } catch (error) {
            console.error("Gagal mengambil detail daftar hadir MMPI:", error);
            alert("Terjadi kesalahan saat mengambil detail daftar hadir MMPI.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedTanggal, activeTab]);

    const filteredItems = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) return items;

        return items.filter((item) => {
            return (
                String(item.nama || "").toLowerCase().includes(keyword) ||
                String(item.email || "").toLowerCase().includes(keyword) ||
                String(item.no_hp || "").toLowerCase().includes(keyword) ||
                String(item.status_kehadiran_label || "").toLowerCase().includes(keyword) ||
                String(item.hasil_test_label || "").toLowerCase().includes(keyword)
            );
        });
    }, [items, search]);

    const handleFileChange = (item, file) => {
        const rowId = item?.jadwal_test_mmpi_id;

        if (!rowId) {
            alert("ID jadwal test MMPI tidak ditemukan.");
            return;
        }

        setSelectedFiles((prev) => ({
            ...prev,
            [rowId]: file || null,
        }));
    };

    const applyUpdatedRow = (jadwalTestMmpiId, resultData, fallback = {}) => {
        setItems((prev) =>
            prev.map((row) => {
                if (String(row.jadwal_test_mmpi_id) !== String(jadwalTestMmpiId)) {
                    return row;
                }

                const statusKehadiran =
                    resultData?.status_kehadiran ??
                    fallback.status_kehadiran ??
                    row.status_kehadiran ??
                    null;

                const hasilTest =
                    resultData?.hasil_test ??
                    fallback.hasil_test ??
                    row.hasil_test ??
                    null;

                const normalizedHasilTest =
                    statusKehadiran === "hadir" ? hasilTest : null;

                return {
                    ...row,
                    status_kehadiran: statusKehadiran,
                    status_kehadiran_label:
                        statusKehadiran === "hadir"
                            ? "Hadir"
                            : statusKehadiran === "tidak_hadir"
                            ? "Tidak Hadir"
                            : "Belum Ada",
                    hasil_test: normalizedHasilTest,
                    hasil_test_label:
                        normalizedHasilTest === "lolos"
                            ? "Lolos"
                            : normalizedHasilTest === "gagal"
                            ? "Tidak Lolos"
                            : "Belum Ada",
                    file_hasil_test_mmpi:
                        resultData?.file_hasil_test_mmpi ??
                        (statusKehadiran === "hadir" ? row.file_hasil_test_mmpi : null),
                    file_hasil_test_mmpi_url:
                        resultData?.file_hasil_test_mmpi_url ??
                        (statusKehadiran === "hadir" ? row.file_hasil_test_mmpi_url : null),
                };
            })
        );
    };

    const submitKehadiran = async (item, statusKehadiran) => {
        const jadwalTestMmpiId = item?.jadwal_test_mmpi_id;

        if (!jadwalTestMmpiId) {
            alert("ID jadwal test MMPI tidak ditemukan.");
            return;
        }

        if (!statusKehadiran) {
            alert("Silakan pilih status kehadiran terlebih dahulu.");
            return;
        }

        const key = `${jadwalTestMmpiId}:kehadiran`;
        setSavingKey(key);

        try {
            const response = await fetch(
                `/admin/daftar-hadir/mmpi/${jadwalTestMmpiId}/kehadiran`,
                {
                    method: "PATCH",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                    body: JSON.stringify({
                        status_kehadiran: statusKehadiran,
                    }),
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal memperbarui status kehadiran MMPI.");
                return;
            }

            applyUpdatedRow(jadwalTestMmpiId, result.data, {
                status_kehadiran: statusKehadiran,
            });

            fetchData();
        } catch (error) {
            console.error("Gagal memperbarui status kehadiran MMPI:", error);
            alert("Terjadi kesalahan saat memperbarui status kehadiran MMPI.");
        } finally {
            setSavingKey("");
        }
    };

    const submitHasilTest = async ({
        item,
        hasilTest,
        file = null,
        mode = "hasil",
    }) => {
        const jadwalTestMmpiId = item?.jadwal_test_mmpi_id;

        if (!jadwalTestMmpiId) {
            alert("ID jadwal test MMPI tidak ditemukan.");
            return;
        }

        if (!hasilTest) {
            alert("Silakan pilih hasil test terlebih dahulu.");
            return;
        }

        if (mode === "upload" && !file) {
            alert("Silakan pilih file hasil test MMPI terlebih dahulu.");
            return;
        }

        const key = `${jadwalTestMmpiId}:${mode}`;

        if (mode === "upload") {
            setUploadingKey(key);
        } else {
            setSavingKey(key);
        }

        try {
            const formData = new FormData();
            formData.append("_method", "PATCH");
            formData.append("hasil_test", hasilTest);

            if (file) {
                formData.append("file_hasil_test_mmpi", file);
            }

            const response = await fetch(
                `/admin/daftar-hadir/mmpi/${jadwalTestMmpiId}/hasil-test`,
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

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal memperbarui hasil test MMPI.");
                return;
            }

            applyUpdatedRow(jadwalTestMmpiId, result.data, {
                hasil_test: hasilTest,
            });

            if (mode === "upload") {
                setSelectedFiles((prev) => ({
                    ...prev,
                    [jadwalTestMmpiId]: null,
                }));
            }

            fetchData();
        } catch (error) {
            console.error("Gagal memperbarui hasil test MMPI:", error);
            alert("Terjadi kesalahan saat memperbarui hasil test MMPI.");
        } finally {
            if (mode === "upload") {
                setUploadingKey("");
            } else {
                setSavingKey("");
            }
        }
    };

    const handleHasilTestChange = (item, hasilTest) => {
        if (!hasilTest) return;

        submitHasilTest({
            item,
            hasilTest,
            mode: "hasil",
        });
    };

    const handleUploadFile = (item) => {
        const rowId = item?.jadwal_test_mmpi_id;
        const selectedFile = selectedFiles[rowId] || null;

        submitHasilTest({
            item,
            hasilTest: item.hasil_test,
            file: selectedFile,
            mode: "upload",
        });
    };

    const goBack = () => {
        if (typeof onBack === "function") {
            onBack();
            return;
        }

        window.dispatchEvent(
            new CustomEvent("admin:navigate", {
                detail: {
                    key: "daftar-hadir-mmpi",
                },
            })
        );
    };

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                    <div>
                        <button
                            type="button"
                            onClick={goBack}
                            className="mb-4 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 transition hover:bg-slate-50"
                        >
                            ← Kembali
                        </button>

                        <div className="inline-flex whitespace-nowrap rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                            Detail Daftar Hadir MMPI
                        </div>

                        <h2 className="mt-2 whitespace-nowrap text-2xl font-black text-slate-950">
                            Tanggal Test: {formatTanggal(selectedTanggal)}
                        </h2>

                        <p className="mt-1 text-sm font-semibold text-slate-500">
                            Kelola hasil test MMPI dan dokumen hasil test peserta.
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
                        <div>
                            <label className="mb-2 block whitespace-nowrap text-xs font-black uppercase tracking-wide text-slate-500">
                                Search
                            </label>

                            <input
                                type="text"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Cari peserta, email, nomor HP, status..."
                                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                            />
                        </div>

                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                onClick={fetchData}
                                className="whitespace-nowrap rounded-2xl bg-teal-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-teal-700"
                            >
                                Filter
                            </button>

                            <button
                                type="button"
                                onClick={() => {
                                    setSearch("");
                                    setActiveTab("semua");
                                    setTimeout(() => fetchData(), 0);
                                }}
                                className="whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                Reset
                            </button>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-wrap gap-2">
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

                        <TabButton
                            active={activeTab === "lolos"}
                            onClick={() => setActiveTab("lolos")}
                        >
                            Lolos
                        </TabButton>

                        <TabButton
                            active={activeTab === "gagal"}
                            onClick={() => setActiveTab("gagal")}
                        >
                            Tidak Lolos
                        </TabButton>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full whitespace-nowrap">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Peserta</TableHead>
                                <TableHead>Kontak</TableHead>
                                <TableHead>Jadwal MMPI</TableHead>
                                <TableHead>Kehadiran</TableHead>
                                <TableHead>Hasil Test</TableHead>
                                <TableHead>File Hasil</TableHead>
                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {loading ? (
                                <tr>
                                    <td
                                        colSpan="8"
                                        className="px-6 py-16 text-center text-sm font-black text-slate-500"
                                    >
                                        Memuat data...
                                    </td>
                                </tr>
                            ) : filteredItems.length > 0 ? (
                                filteredItems.map((item, index) => {
                                    const hasilSaving =
                                        savingKey === `${item.jadwal_test_mmpi_id}:hasil`;
                                    const kehadiranSaving =
                                        savingKey === `${item.jadwal_test_mmpi_id}:kehadiran`;
                                    const uploadSaving =
                                        uploadingKey === `${item.jadwal_test_mmpi_id}:upload`;
                                    const hadir = item.status_kehadiran === "hadir";
                                    const selectedFile =
                                        selectedFiles[item.jadwal_test_mmpi_id] || null;

                                    return (
                                        <tr
                                            key={item.jadwal_test_mmpi_id}
                                            className="group transition hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-5 text-sm font-black text-slate-500">
                                                {index + 1}
                                            </td>

                                            <td className="px-6 py-5">
                                                <div className="font-black text-slate-950">
                                                    {item.nama || "-"}
                                                </div>

                                                <div
                                                    className="mt-1 max-w-[240px] truncate text-xs font-bold text-slate-400"
                                                    title={item.data_riwayat_diri_id || "-"}
                                                >
                                                    ID: {item.data_riwayat_diri_id || "-"}
                                                </div>
                                            </td>

                                            <td className="px-6 py-5">
                                                <div
                                                    className="max-w-[260px] truncate text-sm font-bold text-slate-700"
                                                    title={item.email || "-"}
                                                >
                                                    {item.email || "-"}
                                                </div>

                                                <div className="mt-1 text-xs font-bold text-slate-400">
                                                    {item.no_hp || "-"}
                                                </div>
                                            </td>

                                            <td className="px-6 py-5">
                                                <div className="text-sm font-black text-slate-700">
                                                    {formatTanggal(item.tanggal_mmpi)}
                                                </div>

                                                <div className="mt-1 text-xs font-bold text-slate-400">
                                                    {formatJam(item.tanggal_mmpi)}
                                                </div>
                                            </td>

                                            <td className="px-6 py-5">
                                                <div className="min-w-[170px] space-y-2">
                                                    <select
                                                        value={item.status_kehadiran || ""}
                                                        disabled={kehadiranSaving || hasilSaving || uploadSaving}
                                                        onChange={(event) =>
                                                            submitKehadiran(item, event.target.value)
                                                        }
                                                        className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:opacity-70"
                                                    >
                                                        <option value="">Belum Ada</option>
                                                        <option value="hadir">Hadir</option>
                                                        <option value="tidak_hadir">Tidak Hadir</option>
                                                    </select>

                                                    <div>
                                                        {renderKehadiranBadge(item)}
                                                    </div>

                                                    {kehadiranSaving && (
                                                        <p className="text-xs font-black text-teal-700">
                                                            Menyimpan...
                                                        </p>
                                                    )}
                                                </div>
                                            </td>

                                            <td className="px-6 py-5">
                                                {hadir ? (
                                                    <div className="min-w-[170px]">
                                                        <select
                                                            value={item.hasil_test || ""}
                                                            disabled={hasilSaving || uploadSaving}
                                                            onChange={(event) =>
                                                                handleHasilTestChange(
                                                                    item,
                                                                    event.target.value
                                                                )
                                                            }
                                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:opacity-70"
                                                        >
                                                            <option value="">Pilih Hasil</option>
                                                            <option value="lolos">Lolos</option>
                                                            <option value="gagal">Tidak Lolos</option>
                                                        </select>

                                                        {hasilSaving && (
                                                            <p className="mt-1 text-xs font-black text-teal-700">
                                                                Menyimpan...
                                                            </p>
                                                        )}
                                                    </div>
                                                ) : (
                                                    <Badge type="neutral">Tidak Ada Action</Badge>
                                                )}
                                            </td>

                                            <td className="px-6 py-5">
                                                <div className="min-w-[240px] space-y-2">
                                                    {item.file_hasil_test_mmpi_url ? (
                                                        <a
                                                            href={item.file_hasil_test_mmpi_url}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="inline-flex rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 transition hover:bg-blue-100"
                                                        >
                                                            Lihat Dokumen
                                                        </a>
                                                    ) : (
                                                        <span className="text-xs font-black text-slate-400">
                                                            Belum ada file
                                                        </span>
                                                    )}

                                                    {hadir && (
                                                        <div>
                                                            <input
                                                                type="file"
                                                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                                                disabled={hasilSaving || uploadSaving}
                                                                onChange={(event) =>
                                                                    handleFileChange(
                                                                        item,
                                                                        event.target.files?.[0] || null
                                                                    )
                                                                }
                                                                className="block w-full text-xs font-bold text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-black file:text-slate-700 hover:file:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-60"
                                                            />

                                                            {selectedFile && (
                                                                <p className="mt-1 break-all text-xs font-bold text-teal-700">
                                                                    File dipilih: {selectedFile.name}
                                                                </p>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            </td>

                                            <td className="px-6 py-5 text-right">
                                                {hadir && selectedFile ? (
                                                    <button
                                                        type="button"
                                                        disabled={hasilSaving || uploadSaving}
                                                        onClick={() => handleUploadFile(item)}
                                                        className="rounded-2xl bg-teal-600 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
                                                    >
                                                        {uploadSaving ? "Upload..." : "Upload File"}
                                                    </button>
                                                ) : (
                                                    <span className="text-xs font-black text-slate-400">
                                                        {hadir
                                                            ? "Pilih file untuk upload"
                                                            : "Tidak ada action"}
                                                    </span>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan="8" className="px-6 py-16 text-center">
                                        <EmptyState text="Belum ada peserta pada filter ini." />
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

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
}

function getTanggalFromUrl() {
    const params = new URLSearchParams(window.location.search || "");
    return params.get("tanggal") || "";
}

function renderKehadiranBadge(item) {
    if (item.status_kehadiran === "hadir") return <Badge type="success">Hadir</Badge>;
    if (item.status_kehadiran === "tidak_hadir") return <Badge type="danger">Tidak Hadir</Badge>;

    return <Badge type="neutral">Belum Ada</Badge>;
}

function SummaryCard({ label, value }) {
    return (
        <div className="rounded-2xl bg-slate-50 px-4 py-3 text-center">
            <div className="whitespace-nowrap text-xl font-black text-slate-950">
                {value}
            </div>

            <div className="mt-1 whitespace-nowrap text-xs font-black uppercase tracking-wide text-slate-500">
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
            className={`whitespace-nowrap rounded-2xl px-5 py-2.5 text-sm font-black transition ${
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
            className={`inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ${classes[type]}`}
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

function EmptyState({ text }) {
    return (
        <div className="mx-auto max-w-sm text-center">
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                ◎
            </div>

            <h3 className="mt-4 text-lg font-black text-slate-900">
                Data tidak ditemukan
            </h3>

            <p className="mt-2 text-sm font-medium text-slate-500">
                {text}
            </p>
        </div>
    );
}

function formatTanggal(value) {
    if (!value) return "-";

    const date = new Date(String(value).replace(" ", "T"));

    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
}

function formatJam(value) {
    if (!value) return "-";

    const date = new Date(String(value).replace(" ", "T"));

    if (Number.isNaN(date.getTime())) return "-";

    return date.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
    });
}
