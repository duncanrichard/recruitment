import React, { useEffect, useMemo, useRef, useState } from "react";

export default function JadwalTestMmpiPage({ actionSignals }) {
    const [jadwalMmpi, setJadwalMmpi] = useState([]);
    const [kandidat, setKandidat] = useState([]);
    const [selectedIds, setSelectedIds] = useState([]);
    const [tanggal, setTanggal] = useState("");
    const [search, setSearch] = useState("");
    const [loadingList, setLoadingList] = useState(false);
    const [loadingKandidat, setLoadingKandidat] = useState(false);
    const [saving, setSaving] = useState(false);
    const [modalOpen, setModalOpen] = useState(false);

    const [filterTanggalMmpiMulai, setFilterTanggalMmpiMulai] = useState("");
    const [filterTanggalMmpiSelesai, setFilterTanggalMmpiSelesai] = useState("");

    const isFirstActionSignalRender = useRef(true);

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const isFilterTanggalMmpiInvalid =
        filterTanggalMmpiMulai &&
        filterTanggalMmpiSelesai &&
        filterTanggalMmpiSelesai < filterTanggalMmpiMulai;

    const fetchJadwalMmpi = async (filters = null) => {
        const activeFilters = filters || {
            tanggal_mulai: filterTanggalMmpiMulai,
            tanggal_selesai: filterTanggalMmpiSelesai,
        };

        if (
            activeFilters.tanggal_mulai &&
            activeFilters.tanggal_selesai &&
            activeFilters.tanggal_selesai < activeFilters.tanggal_mulai
        ) {
            setJadwalMmpi([]);
            return [];
        }

        setLoadingList(true);

        try {
            const params = new URLSearchParams();

            if (activeFilters.tanggal_mulai) {
                params.append("tanggal_mulai", activeFilters.tanggal_mulai);
            }

            if (activeFilters.tanggal_selesai) {
                params.append("tanggal_selesai", activeFilters.tanggal_selesai);
            }

            const url = params.toString()
                ? `/admin/jadwal-test/mmpi/list?${params.toString()}`
                : "/admin/jadwal-test/mmpi/list";

            const response = await fetch(url, {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (result.success) {
                setJadwalMmpi(result.data || []);
                return result.data || [];
            }

            alert(result.message || "Gagal mengambil jadwal test MMPI.");
            return [];
        } catch (error) {
            console.error("Gagal mengambil jadwal test MMPI:", error);
            alert("Terjadi kesalahan saat mengambil jadwal test MMPI.");
            return [];
        } finally {
            setLoadingList(false);
        }
    };

    const resetFilterTanggalMmpi = () => {
        setFilterTanggalMmpiMulai("");
        setFilterTanggalMmpiSelesai("");
    };

    const fetchKandidat = async (currentJadwalMmpi = jadwalMmpi) => {
        setLoadingKandidat(true);

        try {
            const response = await fetch(
                "/admin/jadwal-test/mmpi/kandidat-lolos-zoom",
                {
                    headers: {
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (result.success) {
                const scheduledSet = new Set(
                    (currentJadwalMmpi || []).map((item) =>
                        String(item.daftar_hadir_test_zoom_id || "")
                    )
                );

                const cleanKandidat = (result.data || []).filter((item) => {
                    return !scheduledSet.has(
                        String(item.daftar_hadir_test_zoom_id || "")
                    );
                });

                setKandidat(cleanKandidat);
                return cleanKandidat;
            }

            alert(result.message || "Gagal mengambil kandidat lolos Zoom.");
            return [];
        } catch (error) {
            console.error("Gagal mengambil kandidat lolos Zoom:", error);
            alert("Terjadi kesalahan saat mengambil kandidat lolos Zoom.");
            return [];
        } finally {
            setLoadingKandidat(false);
        }
    };

    useEffect(() => {
        if (isFilterTanggalMmpiInvalid) return;

        fetchJadwalMmpi();
    }, [filterTanggalMmpiMulai, filterTanggalMmpiSelesai]);

    useEffect(() => {
        if (isFirstActionSignalRender.current) {
            isFirstActionSignalRender.current = false;
            return;
        }

        if (actionSignals?.jadwalTestMmpi > 0) {
            openModal();
        }
    }, [actionSignals?.jadwalTestMmpi]);

    const openModal = async () => {
        setTanggal("");
        setSelectedIds([]);
        setSearch("");
        setModalOpen(true);

        const currentJadwalMmpi = await fetchJadwalMmpi();
        await fetchKandidat(currentJadwalMmpi);
    };

    const closeModal = () => {
        if (saving) return;
        setModalOpen(false);
    };

    const scheduledDaftarHadirIds = useMemo(() => {
        return new Set(
            jadwalMmpi.map((item) => String(item.daftar_hadir_test_zoom_id || ""))
        );
    }, [jadwalMmpi]);

    const availableKandidat = useMemo(() => {
        return kandidat.filter((item) => {
            return !scheduledDaftarHadirIds.has(
                String(item.daftar_hadir_test_zoom_id || "")
            );
        });
    }, [kandidat, scheduledDaftarHadirIds]);

    const filteredKandidat = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) return availableKandidat;

        return availableKandidat.filter((item) => {
            return (
                String(item.nama || "").toLowerCase().includes(keyword) ||
                String(item.email || "").toLowerCase().includes(keyword) ||
                String(item.no_hp || "").toLowerCase().includes(keyword)
            );
        });
    }, [availableKandidat, search]);

    const selectedItems = useMemo(() => {
        const selectedSet = new Set(selectedIds);

        return availableKandidat.filter((item) =>
            selectedSet.has(String(item.daftar_hadir_test_zoom_id))
        );
    }, [availableKandidat, selectedIds]);

    const toggleSelect = (item) => {
        const id = String(item.daftar_hadir_test_zoom_id);

        setSelectedIds((prev) => {
            if (prev.includes(id)) {
                return prev.filter((selectedId) => selectedId !== id);
            }

            return [...prev, id];
        });
    };

    const toggleSelectAllFiltered = () => {
        const filteredIds = filteredKandidat.map((item) =>
            String(item.daftar_hadir_test_zoom_id)
        );

        const allFilteredSelected =
            filteredIds.length > 0 &&
            filteredIds.every((id) => selectedIds.includes(id));

        if (allFilteredSelected) {
            setSelectedIds((prev) =>
                prev.filter((id) => !filteredIds.includes(id))
            );
            return;
        }

        setSelectedIds((prev) => Array.from(new Set([...prev, ...filteredIds])));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        if (!tanggal) {
            alert("Tanggal test MMPI wajib diisi.");
            return;
        }

        if (selectedItems.length === 0) {
            alert("Pilih minimal satu kandidat.");
            return;
        }

        setSaving(true);

        try {
            const response = await fetch("/admin/jadwal-test/mmpi", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify({
                    tanggal,
                    items: selectedItems.map((item) => ({
                        daftar_hadir_test_zoom_id:
                            item.daftar_hadir_test_zoom_id,
                        data_riwayat_diri_id: item.data_riwayat_diri_id,
                    })),
                }),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal membuat jadwal test MMPI.");
                return;
            }

            alert(result.message || "Jadwal test MMPI berhasil dibuat.");
            closeModal();
            await fetchJadwalMmpi();
        } catch (error) {
            console.error("Gagal membuat jadwal test MMPI:", error);
            alert("Terjadi kesalahan saat membuat jadwal test MMPI.");
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id) => {
        const ok = confirm(
            "Yakin ingin menghapus jadwal test MMPI ini? Kandidat tetap tidak akan muncul lagi di modal karena sudah pernah mendapatkan jadwal MMPI."
        );

        if (!ok) return;

        try {
            const response = await fetch(`/admin/jadwal-test/mmpi/${id}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal menghapus jadwal test MMPI.");
                return;
            }

            alert(result.message || "Jadwal test MMPI berhasil dihapus.");
            await fetchJadwalMmpi();
        } catch (error) {
            console.error("Gagal menghapus jadwal test MMPI:", error);
            alert("Terjadi kesalahan saat menghapus jadwal test MMPI.");
        }
    };

    const formatTanggal = (value) => {
        if (!value) return "-";

        const date = new Date(String(value).replace(" ", "T"));

        if (Number.isNaN(date.getTime())) {
            return value;
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

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 className="text-lg font-black text-slate-950">
                            Jadwal Test MMPI
                        </h3>

                        <p className="mt-1 text-sm font-medium text-slate-500">
                            Kandidat yang sudah mendapatkan jadwal MMPI tidak akan tampil lagi di modal pemilihan kandidat.
                        </p>
                    </div>
                </div>

                <div className="border-b border-slate-100 px-6 py-5">
                    <p className="text-sm font-black text-slate-700">
                        Filter Range Tanggal Test MMPI
                    </p>

                    <p className="mt-1 text-xs font-semibold text-slate-500">
                        Tampilkan jadwal MMPI berdasarkan rentang tanggal test MMPI.
                    </p>

                    <div className="mt-4 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                        <div>
                            <label className="mb-2 block text-sm font-black text-slate-700">
                                Tanggal Test MMPI Mulai
                            </label>

                            <input
                                type="date"
                                value={filterTanggalMmpiMulai}
                                onChange={(event) =>
                                    setFilterTanggalMmpiMulai(event.target.value)
                                }
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            />
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-black text-slate-700">
                                Tanggal Test MMPI Selesai
                            </label>

                            <input
                                type="date"
                                value={filterTanggalMmpiSelesai}
                                min={filterTanggalMmpiMulai || undefined}
                                onChange={(event) =>
                                    setFilterTanggalMmpiSelesai(event.target.value)
                                }
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            />
                        </div>

                        {(filterTanggalMmpiMulai || filterTanggalMmpiSelesai) && (
                            <button
                                type="button"
                                onClick={resetFilterTanggalMmpi}
                                className="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                Reset Filter
                            </button>
                        )}
                    </div>

                    {isFilterTanggalMmpiInvalid && (
                        <div className="mt-4 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3">
                            <p className="text-sm font-black text-rose-700">
                                Tanggal Test MMPI Selesai tidak boleh lebih kecil dari Tanggal Test MMPI Mulai.
                            </p>
                        </div>
                    )}
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full whitespace-nowrap">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Peserta</TableHead>
                                <TableHead>Kontak</TableHead>
                                <TableHead>Jadwal MMPI</TableHead>
                                <TableHead>Jadwal Zoom</TableHead>
                                <TableHead>Hasil Test Zoom</TableHead>
                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {loadingList ? (
                                <tr>
                                    <td
                                        colSpan="7"
                                        className="px-6 py-16 text-center text-sm font-black text-slate-500"
                                    >
                                        Memuat data...
                                    </td>
                                </tr>
                            ) : jadwalMmpi.length > 0 ? (
                                jadwalMmpi.map((item, index) => (
                                    <tr
                                        key={item.id}
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
                                                className="mt-1 max-w-[180px] truncate text-xs font-bold text-slate-400"
                                                title={item.data_riwayat_diri_id || "-"}
                                            >
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

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-700">
                                                {formatTanggal(item.tanggal)}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-700">
                                                {formatTanggal(item.jadwal_zoom)}
                                            </div>
                                            <div className="mt-1 text-xs font-bold text-slate-400">
                                                {formatJam(item.jadwal_zoom)}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <Badge type="success">Lolos</Badge>
                                        </td>

                                        <td className="px-6 py-5 text-right">
                                            <button
                                                type="button"
                                                onClick={() => handleDelete(item.id)}
                                                className="rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:bg-rose-50 hover:text-rose-700"
                                            >
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="7" className="px-6 py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ◎
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Belum ada jadwal MMPI
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Buat jadwal MMPI untuk kandidat
                                                yang sudah lolos test Zoom.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
                    <div className="flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div className="shrink-0 border-b border-slate-200 px-6 py-5">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <div className="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                                        Form Jadwal MMPI
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        Pilih Kandidat Lolos Zoom
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Kandidat yang sudah mendapatkan jadwal MMPI tidak ditampilkan lagi di daftar pilihan.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    onClick={closeModal}
                                    className="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-500 transition hover:bg-slate-200 hover:text-slate-800"
                                >
                                    ×
                                </button>
                            </div>
                        </div>

                        <form
                            onSubmit={handleSubmit}
                            className="flex min-h-0 flex-1 flex-col"
                        >
                            <div className="min-h-0 flex-1 overflow-y-auto px-6 py-6">
                                <div className="mb-5 grid gap-4 md:grid-cols-3">
                                    <div>
                                        <label className="mb-2 block text-sm font-black text-slate-700">
                                            Tanggal Test MMPI{" "}
                                            <span className="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="date"
                                            value={tanggal}
                                            onChange={(event) => setTanggal(event.target.value)}
                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                            required
                                        />
                                    </div>

                                    <div className="md:col-span-2">
                                        <label className="mb-2 block text-sm font-black text-slate-700">
                                            Cari Kandidat
                                        </label>
                                        <input
                                            type="text"
                                            value={search}
                                            onChange={(event) => setSearch(event.target.value)}
                                            placeholder="Cari nama, email, atau nomor HP..."
                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                        />
                                    </div>
                                </div>

                                <div className="mb-4 flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-sm font-black text-slate-800">
                                            {selectedIds.length} kandidat dipilih
                                        </p>
                                        <p className="mt-1 text-xs font-semibold text-slate-500">
                                            Kandidat yang sudah pernah mendapat jadwal MMPI otomatis disembunyikan.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        onClick={toggleSelectAllFiltered}
                                        disabled={filteredKandidat.length === 0}
                                        className="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Pilih / Batal Semua Hasil Filter
                                    </button>
                                </div>

                                <div className="overflow-x-auto rounded-3xl border border-slate-200">
                                    <table className="min-w-full whitespace-nowrap">
                                        <thead>
                                            <tr className="bg-slate-50/80">
                                                <TableHead>Pilih</TableHead>
                                                <TableHead>Peserta</TableHead>
                                                <TableHead>Kontak</TableHead>
                                                <TableHead>Jadwal Zoom</TableHead>
                                                <TableHead>Hasil Test Zoom</TableHead>
                                            </tr>
                                        </thead>

                                        <tbody className="divide-y divide-slate-100 bg-white">
                                            {loadingKandidat ? (
                                                <tr>
                                                    <td
                                                        colSpan="5"
                                                        className="px-6 py-16 text-center text-sm font-black text-slate-500"
                                                    >
                                                        Memuat kandidat...
                                                    </td>
                                                </tr>
                                            ) : filteredKandidat.length > 0 ? (
                                                filteredKandidat.map((item) => {
                                                    const checked = selectedIds.includes(
                                                        String(item.daftar_hadir_test_zoom_id)
                                                    );

                                                    return (
                                                        <tr
                                                            key={item.daftar_hadir_test_zoom_id}
                                                            className="transition hover:bg-slate-50"
                                                        >
                                                            <td className="px-6 py-5">
                                                                <input
                                                                    type="checkbox"
                                                                    checked={checked}
                                                                    onChange={() => toggleSelect(item)}
                                                                    className="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                                />
                                                            </td>

                                                            <td className="px-6 py-5">
                                                                <div className="font-black text-slate-950">
                                                                    {item.nama || "-"}
                                                                </div>
                                                                <div
                                                                    className="mt-1 max-w-[180px] truncate text-xs font-bold text-slate-400"
                                                                    title={item.data_riwayat_diri_id || "-"}
                                                                >
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

                                                            <td className="px-6 py-5">
                                                                <div className="text-sm font-black text-slate-700">
                                                                    {formatTanggal(item.jadwal_zoom)}
                                                                </div>
                                                                <div className="mt-1 text-xs font-bold text-slate-400">
                                                                    {formatJam(item.jadwal_zoom)}
                                                                </div>
                                                            </td>

                                                            <td className="px-6 py-5">
                                                                <Badge type="success">Lolos</Badge>
                                                            </td>
                                                        </tr>
                                                    );
                                                })
                                            ) : (
                                                <tr>
                                                    <td colSpan="5" className="px-6 py-16">
                                                        <div className="mx-auto max-w-md text-center">
                                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                                ◎
                                                            </div>

                                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                                Kandidat tidak tersedia
                                                            </h3>

                                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                                Belum ada kandidat yang berstatus Hadir dan Lolos pada test Zoom, atau semua kandidat lolos sudah mendapatkan jadwal MMPI.
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div className="shrink-0 border-t border-slate-200 bg-white px-6 py-4">
                                <div className="flex justify-end gap-3">
                                    <button
                                        type="button"
                                        onClick={closeModal}
                                        className="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                                    >
                                        Batal
                                    </button>

                                    <button
                                        type="submit"
                                        disabled={
                                            saving ||
                                            !tanggal ||
                                            selectedItems.length === 0
                                        }
                                        className="rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-100 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {saving
                                            ? "Menyimpan..."
                                            : "Simpan Jadwal MMPI"}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
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
            className={`px-6 py-4 ${alignClass} text-xs font-black uppercase tracking-[0.12em] text-slate-500`}
        >
            {children}
        </th>
    );
}
