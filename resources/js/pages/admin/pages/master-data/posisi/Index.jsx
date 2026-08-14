import React, { useEffect, useMemo, useState } from "react";

const INITIAL_FORM = {
    nama_posisi: "",
    deskripsi: "",
    spesifikasi_items: [],
    str_aktif: "non_active",
};

export default function PosisiPage({ actionSignals }) {
    const [dataPosisi, setDataPosisi] = useState([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [editId, setEditId] = useState(null);
    const [newSpecificationName, setNewSpecificationName] = useState("");

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);
    const [sortConfig, setSortConfig] = useState({
        key: "nama_posisi",
        direction: "asc",
    });

    const [form, setForm] = useState({ ...INITIAL_FORM });

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const resetForm = () => {
        setEditId(null);
        setForm({ ...INITIAL_FORM });
    };

    const getFirstErrorMessage = (errors) => {
        if (!errors) return null;

        const firstError = Object.values(errors)?.[0];

        if (Array.isArray(firstError)) {
            return firstError[0];
        }

        return firstError;
    };

    const parseResponse = async (response) => {
        const text = await response.text();

        if (!text) {
            return {
                success: false,
                message: `Response server kosong. Status: ${response.status} ${response.statusText}`,
            };
        }

        try {
            return JSON.parse(text);
        } catch (error) {
            console.error("Response server bukan JSON valid:", {
                status: response.status,
                statusText: response.statusText,
                body: text,
                parseError: error.message,
            });

            return {
                success: false,
                message: `Response server bukan JSON valid. Status: ${response.status} ${response.statusText}.`,
                error: error.message,
                raw: text,
            };
        }
    };

    const fetchData = async () => {
        setTableLoading(true);

        try {
            const response = await fetch("/admin/master-data/posisi/list", {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await parseResponse(response);

            if (!response.ok) {
                alert(
                    getFirstErrorMessage(result.errors) ||
                        result.message ||
                        result.error ||
                        `Gagal mengambil data posisi. Status: ${response.status}`
                );
                return;
            }

            if (result.success) {
                setDataPosisi(result.data || []);
            } else {
                alert(result.message || "Gagal mengambil data posisi.");
            }
        } catch (error) {
            console.error("Gagal mengambil data posisi:", error);
            alert(
                error.message ||
                    "Terjadi kesalahan saat mengambil data posisi."
            );
        } finally {
            setTableLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    useEffect(() => {
        if (actionSignals?.masterPosisi > 0) {
            resetForm();
            setModalOpen(true);
        }
    }, [actionSignals?.masterPosisi]);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage]);

    const isActive = (status) => status === "active";

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) {
            return dataPosisi;
        }

        return dataPosisi.filter((item) => {
            const searchableText = [
                item.nama_posisi,
                item.deskripsi,
                ...(item.spesifikasi_items || []).map((entry) => entry.spesifikasi),
                isActive(item.str_aktif)
                    ? "aktif active"
                    : "tidak aktif non_active",
            ]
                .map((value) => String(value || "").toLowerCase())
                .join(" ");

            return searchableText.includes(keyword);
        });
    }, [dataPosisi, search]);

    const sortedData = useMemo(() => {
        const result = [...filteredData];

        result.sort((a, b) => {
            let valueA;
            let valueB;

            if (sortConfig.key === "status") {
                valueA = isActive(a.str_aktif) ? "aktif" : "tidak aktif";
                valueB = isActive(b.str_aktif) ? "aktif" : "tidak aktif";
            } else {
                valueA = String(a[sortConfig.key] || "").toLowerCase();
                valueB = String(b[sortConfig.key] || "").toLowerCase();
            }

            if (valueA < valueB) {
                return sortConfig.direction === "asc" ? -1 : 1;
            }

            if (valueA > valueB) {
                return sortConfig.direction === "asc" ? 1 : -1;
            }

            return 0;
        });

        return result;
    }, [filteredData, sortConfig]);

    const totalPages = Math.max(
        1,
        Math.ceil(sortedData.length / entriesPerPage)
    );

    useEffect(() => {
        if (currentPage > totalPages) {
            setCurrentPage(totalPages);
        }
    }, [currentPage, totalPages]);

    const paginatedData = useMemo(() => {
        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = startIndex + entriesPerPage;

        return sortedData.slice(startIndex, endIndex);
    }, [sortedData, currentPage, entriesPerPage]);

    const showingFrom =
        sortedData.length === 0
            ? 0
            : (currentPage - 1) * entriesPerPage + 1;

    const showingTo = Math.min(
        currentPage * entriesPerPage,
        sortedData.length
    );

    const pageNumbers = useMemo(() => {
        const pages = [];
        const maxVisiblePages = 5;

        let startPage = Math.max(
            1,
            currentPage - Math.floor(maxVisiblePages / 2)
        );

        let endPage = Math.min(
            totalPages,
            startPage + maxVisiblePages - 1
        );

        startPage = Math.max(1, endPage - maxVisiblePages + 1);

        for (let page = startPage; page <= endPage; page += 1) {
            pages.push(page);
        }

        return pages;
    }, [currentPage, totalPages]);

    const handleSort = (key) => {
        setSortConfig((previous) => {
            if (previous.key === key) {
                return {
                    key,
                    direction:
                        previous.direction === "asc" ? "desc" : "asc",
                };
            }

            return {
                key,
                direction: "asc",
            };
        });
    };

    const sortIcon = (key) => {
        if (sortConfig.key !== key) {
            return "⇅";
        }

        return sortConfig.direction === "asc" ? "↑" : "↓";
    };

    const handleChange = (event) => {
        const { name, value } = event.target;

        setForm((previous) => ({
            ...previous,
            [name]: value,
        }));
    };

    const closeModal = () => {
        setModalOpen(false);
        resetForm();
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        const payload = {
            nama_posisi: form.nama_posisi.trim(),
            deskripsi: form.deskripsi.trim() || null,
            spesifikasi: form.spesifikasi_items.map((item) => item.spesifikasi.trim()).filter(Boolean),
            str_aktif: form.str_aktif,
        };

        if (!payload.nama_posisi) {
            alert("Nama posisi wajib diisi.");
            return;
        }

        if (!payload.spesifikasi.length) {
            alert("Pilih minimal satu spesifikasi kualifikasi.");
            return;
        }

        setLoading(true);

        try {
            const url = editId
                ? `/admin/master-data/posisi/${editId}`
                : "/admin/master-data/posisi";

            const method = editId ? "PUT" : "POST";

            const response = await fetch(url, {
                method,
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            const result = await parseResponse(response);

            if (!response.ok) {
                alert(
                    getFirstErrorMessage(result.errors) ||
                        result.message ||
                        result.error ||
                        "Data posisi gagal disimpan."
                );
                return;
            }

            alert(result.message || "Data posisi berhasil disimpan.");
            closeModal();
            fetchData();
        } catch (error) {
            console.error("Gagal menyimpan data posisi:", error);
            alert(
                error.message ||
                    "Terjadi kesalahan saat menyimpan data posisi."
            );
        } finally {
            setLoading(false);
        }
    };

    const handleEdit = (item) => {
        setEditId(item.id);

        setForm({
            nama_posisi: item.nama_posisi || "",
            deskripsi: item.deskripsi || "",
            spesifikasi_items: item.spesifikasi_items || [],
            str_aktif: item.str_aktif || "non_active",
        });

        setModalOpen(true);
    };

    const handleDelete = async (id) => {
        const confirmed = confirm(
            "Yakin ingin menghapus data posisi ini?"
        );

        if (!confirmed) return;

        try {
            const response = await fetch(
                `/admin/master-data/posisi/${id}`,
                {
                    method: "DELETE",
                    credentials: "same-origin",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                }
            );

            const result = await parseResponse(response);

            if (!response.ok) {
                alert(
                    getFirstErrorMessage(result.errors) ||
                        result.message ||
                        result.error ||
                        "Data posisi gagal dihapus."
                );
                return;
            }

            alert(result.message || "Data posisi berhasil dihapus.");
            fetchData();
        } catch (error) {
            console.error("Gagal menghapus data posisi:", error);
            alert(
                error.message ||
                    "Terjadi kesalahan saat menghapus data posisi."
            );
        }
    };

    const qualificationPreview = (text) => {
        const value = String(text || "").trim();

        if (!value) {
            return [];
        }

        return value
            .split(/\r?\n/)
            .map((line) => line.trim())
            .filter(Boolean);
    };

    const createSpecification = () => {
        const name = newSpecificationName.trim();
        if (!name) return alert("Spesifikasi wajib diisi.");
        setForm((previous) => ({ ...previous, spesifikasi_items: [...previous.spesifikasi_items, { id: `local-${Date.now()}`, spesifikasi: name }] }));
        setNewSpecificationName("");
    };

    const deleteSpecification = (item) => {
        if (!confirm(`Hapus spesifikasi “${item.spesifikasi}” dari posisi ini?`)) return;
        setForm((previous) => ({
            ...previous,
            spesifikasi_items: previous.spesifikasi_items.filter((entry) => entry.id !== item.id),
        }));
    };

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-center gap-2">
                            <span className="text-sm font-bold text-slate-600">
                                Show
                            </span>

                            <select
                                value={entriesPerPage}
                                onChange={(event) =>
                                    setEntriesPerPage(
                                        Number(event.target.value)
                                    )
                                }
                                className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >
                                {[5, 10, 25, 50, 100].map((value) => (
                                    <option key={value} value={value}>
                                        {value}
                                    </option>
                                ))}
                            </select>

                            <span className="text-sm font-bold text-slate-600">
                                entries
                            </span>
                        </div>

                        <div className="flex items-center gap-2">
                            <span className="text-sm font-bold text-slate-600">
                                Search:
                            </span>

                            <input
                                type="text"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Cari posisi atau kualifikasi..."
                                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 md:w-80"
                            />
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <SortableTableHead
                                    label="Nama Posisi"
                                    sortKey="nama_posisi"
                                    onSort={handleSort}
                                    icon={sortIcon("nama_posisi")}
                                />

                                <SortableTableHead
                                    label="Deskripsi"
                                    sortKey="deskripsi"
                                    onSort={handleSort}
                                    icon={sortIcon("deskripsi")}
                                />

                                <SortableTableHead
                                    label="Kualifikasi"
                                    sortKey="kualifikasi"
                                    onSort={handleSort}
                                    icon={sortIcon("kualifikasi")}
                                />

                                <SortableTableHead
                                    label="Status STR"
                                    sortKey="status"
                                    onSort={handleSort}
                                    icon={sortIcon("status")}
                                />

                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <td colSpan="5" className="px-6 py-16">
                                        <div className="text-center text-sm font-black text-slate-500">
                                            Memuat data...
                                        </div>
                                    </td>
                                </tr>
                            ) : paginatedData.length > 0 ? (
                                paginatedData.map((item) => {
                                    const specifications = item.spesifikasi_items || [];

                                    return (
                                        <tr
                                            key={item.id}
                                            className="group transition hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-5 align-top">
                                                <div className="font-black text-slate-950">
                                                    {item.nama_posisi}
                                                </div>
                                            </td>

                                            <td className="max-w-xs px-6 py-5 align-top">
                                                <p className="line-clamp-3 text-sm font-medium leading-6 text-slate-500">
                                                    {item.deskripsi || "-"}
                                                </p>
                                            </td>

                                            <td className="min-w-[440px] max-w-[580px] px-6 py-5 align-top">
                                                <QualificationTableCell items={specifications} />
                                            </td>

                                            <td className="px-6 py-5 align-top">
                                                <span
                                                    className={`inline-flex rounded-full px-3 py-1.5 text-xs font-black ${
                                                        isActive(
                                                            item.str_aktif
                                                        )
                                                            ? "bg-emerald-50 text-emerald-700"
                                                            : "bg-rose-50 text-rose-700"
                                                    }`}
                                                >
                                                    {isActive(
                                                        item.str_aktif
                                                    )
                                                        ? "Aktif"
                                                        : "Tidak Aktif"}
                                                </span>
                                            </td>

                                            <td className="px-6 py-5 text-right align-top">
                                                <div className="flex justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            handleEdit(item)
                                                        }
                                                        className="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                                    >
                                                        Edit
                                                    </button>

                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            handleDelete(
                                                                item.id
                                                            )
                                                        }
                                                        className="rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:bg-rose-50"
                                                    >
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan="5" className="px-6 py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ▦
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Tidak ada data posisi yang
                                                sesuai dengan pencarian.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="flex flex-col gap-4 border-t border-slate-100 px-6 py-4 md:flex-row md:items-center md:justify-between">
                    <p className="text-sm font-bold text-slate-500">
                        Showing {showingFrom} to {showingTo} of{" "}
                        {sortedData.length} entries
                        {search && (
                            <span>
                                {" "}
                                filtered from {dataPosisi.length} total
                                entries
                            </span>
                        )}
                    </p>

                    <div className="flex flex-wrap items-center gap-2">
                        <PaginationButton
                            disabled={currentPage === 1}
                            onClick={() =>
                                setCurrentPage((previous) =>
                                    Math.max(previous - 1, 1)
                                )
                            }
                        >
                            Previous
                        </PaginationButton>

                        {pageNumbers.map((page) => (
                            <button
                                key={page}
                                type="button"
                                onClick={() => setCurrentPage(page)}
                                className={`rounded-xl px-4 py-2 text-sm font-black shadow-sm transition ${
                                    currentPage === page
                                        ? "bg-indigo-600 text-white"
                                        : "border border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
                                }`}
                            >
                                {page}
                            </button>
                        ))}

                        <PaginationButton
                            disabled={currentPage === totalPages}
                            onClick={() =>
                                setCurrentPage((previous) =>
                                    Math.min(
                                        previous + 1,
                                        totalPages
                                    )
                                )
                            }
                        >
                            Next
                        </PaginationButton>
                    </div>
                </div>
            </div>

            {modalOpen && (
                <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
                    <div className="flex max-h-[94vh] w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div className="shrink-0 border-b border-slate-200 bg-white">
                            <div className="flex items-start justify-between gap-4 px-6 py-5">
                                <div>
                                    <div className="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                                        Form Posisi
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        {editId
                                            ? "Edit Posisi"
                                            : "Tambah Posisi"}
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Lengkapi posisi, deskripsi,
                                        kualifikasi, dan status STR.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    onClick={closeModal}
                                    className="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-500 transition hover:bg-slate-200"
                                >
                                    ×
                                </button>
                            </div>
                        </div>

                        <form
                            onSubmit={handleSubmit}
                            className="flex min-h-0 flex-1 flex-col"
                        >
                            <div className="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">
                                <Input
                                    label="Nama Posisi"
                                    name="nama_posisi"
                                    value={form.nama_posisi}
                                    onChange={handleChange}
                                    placeholder="Contoh: Staff Administrasi"
                                    required
                                />

                                <Textarea
                                    label="Deskripsi"
                                    name="deskripsi"
                                    value={form.deskripsi}
                                    onChange={handleChange}
                                    placeholder="Masukkan deskripsi posisi"
                                    rows={4}
                                />

                                <section className="rounded-3xl border border-indigo-100 bg-indigo-50/40 p-5">
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p className="text-xs font-black uppercase tracking-[0.16em] text-indigo-600">Kualifikasi Posisi</p>
                                            <h3 className="mt-1 text-lg font-black text-slate-950">Pilih spesifikasi yang dibutuhkan</h3>
                                            <p className="mt-1 text-sm font-semibold text-slate-500">Dapat memilih lebih dari satu spesifikasi dari berbagai jenis.</p>
                                        </div>
                                        <span className="rounded-full bg-white px-3 py-1 text-xs font-black text-indigo-700 shadow-sm">{form.spesifikasi_items.length} spesifikasi</span>
                                    </div>

                                    <div className="mt-5 grid gap-3 lg:grid-cols-[1fr_auto]">
                                        <input value={newSpecificationName} onChange={(event) => setNewSpecificationName(event.target.value)} onKeyDown={(event) => { if (event.key === "Enter") { event.preventDefault(); createSpecification(); } }} placeholder="Tulis spesifikasi, contoh: Pendidikan minimal S1" className="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-violet-500" />
                                        <button type="button" onClick={createSpecification} className="rounded-xl bg-violet-600 px-4 py-3 text-sm font-black text-white hover:bg-violet-700">+ Tambah Spesifikasi</button>
                                    </div>

                                    <QualificationMultiSelect
                                        items={form.spesifikasi_items}
                                        onChange={(items) => setForm((previous) => ({ ...previous, spesifikasi_items: items }))}
                                        onDelete={deleteSpecification}
                                    />
                                </section>

                                <StatusSelect
                                    label="Status STR"
                                    name="str_aktif"
                                    value={form.str_aktif}
                                    onChange={handleChange}
                                />
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
                                        disabled={loading}
                                        className="rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-100 transition hover:from-indigo-700 hover:to-violet-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {loading
                                            ? "Menyimpan..."
                                            : editId
                                            ? "Update Data"
                                            : "Simpan Data"}
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

function CreatableTypeSelect({ types, selectedId, query, onQueryChange, onSelect, onCreate }) {
    const [open, setOpen] = useState(false);
    const normalizedQuery = query.trim().toLowerCase();
    const matches = types.filter((type) =>
        !normalizedQuery || type.nama.toLowerCase().includes(normalizedQuery)
    );
    const exactMatch = types.some((type) => type.nama.toLowerCase() === normalizedQuery);
    const selectedType = types.find((type) => type.id === selectedId);

    return (
        <div className="relative">
            <label className="mb-2 block text-sm font-black text-slate-700">Cari atau buat jenis kualifikasi</label>
            <div className={`flex items-center rounded-xl border bg-white px-4 ring-4 ring-transparent transition focus-within:border-indigo-500 focus-within:ring-indigo-100 ${selectedId ? "border-indigo-300" : "border-slate-200"}`}>
                <span className="mr-3 text-indigo-500">⌕</span>
                <input
                    value={query}
                    onFocus={() => setOpen(true)}
                    onChange={(event) => { onQueryChange(event.target.value); setOpen(true); }}
                    placeholder="Contoh: Pendidikan, Pengalaman Kerja, Soft Skill"
                    className="min-w-0 flex-1 bg-transparent py-3 text-sm font-bold text-slate-700 outline-none"
                />
                {selectedType && <span className="rounded-lg bg-emerald-50 px-2 py-1 text-[10px] font-black uppercase text-emerald-700">Terpilih</span>}
            </div>

            {open && (
                <div className="absolute z-20 mt-2 max-h-64 w-full overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl">
                    {matches.map((type) => (
                        <button key={type.id} type="button" onClick={() => { onSelect(type); setOpen(false); }} className={`flex w-full items-center justify-between rounded-xl px-4 py-3 text-left text-sm font-bold transition hover:bg-indigo-50 ${selectedId === type.id ? "bg-indigo-50 text-indigo-700" : "text-slate-700"}`}>
                            <span>{type.nama}</span>
                            <span className="text-xs text-slate-400">{(type.spesifikasi || []).length} spesifikasi</span>
                        </button>
                    ))}
                    {normalizedQuery && !exactMatch && (
                        <button type="button" onClick={() => { onCreate(); setOpen(false); }} className="mt-1 flex w-full items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-left text-sm font-black text-white">
                            <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15">+</span>
                            Buat jenis “{query.trim()}”
                        </button>
                    )}
                    {!matches.length && !normalizedQuery && <p className="px-4 py-5 text-center text-sm font-bold text-slate-400">Ketik nama jenis kualifikasi.</p>}
                </div>
            )}
            <p className="mt-2 text-xs font-semibold text-slate-500">Cari data yang tersedia. Jika tidak ditemukan, buat jenis baru dari hasil pencarian.</p>
        </div>
    );
}

function QualificationTableCell({ items }) {
    const [expanded, setExpanded] = useState(false);
    const visibleItems = expanded ? items : items.slice(0, 3);

    if (!items.length) {
        return (
            <div className="inline-flex items-center gap-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3">
                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-sm text-slate-400 shadow-sm">—</span>
                <div>
                    <p className="text-sm font-black text-slate-500">Belum ada spesifikasi</p>
                    <p className="text-xs font-semibold text-slate-400">Tambahkan melalui menu Edit.</p>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-2.5">
            <div className="mb-1 flex items-center gap-2">
                <span className="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-indigo-700 ring-1 ring-indigo-100">
                    {items.length} spesifikasi
                </span>
            </div>

            {visibleItems.map((item, index) => (
                <div key={item.id} className="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2.5 transition hover:border-indigo-100 hover:bg-indigo-50/50">
                    <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-[11px] font-black text-indigo-700">
                        {index + 1}
                    </span>
                    <p className="min-w-0 text-sm font-semibold leading-5 text-slate-700">
                        {item.spesifikasi}
                    </p>
                </div>
            ))}

            {items.length > 3 && (
                <button type="button" onClick={() => setExpanded((value) => !value)} className="inline-flex items-center gap-2 rounded-lg px-2 py-1 text-xs font-black text-indigo-600 transition hover:bg-indigo-50 hover:text-violet-700">
                    {expanded ? "Ringkas" : `Lihat ${items.length - 3} spesifikasi lainnya`}
                    <span>{expanded ? "↑" : "↓"}</span>
                </button>
            )}
        </div>
    );
}

function QualificationMultiSelect({ items, onChange, onDelete }) {
    const updateValue = (id, value) => onChange(items.map((item) =>
        item.id === id ? { ...item, spesifikasi: value } : item
    ));
    return (
        <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div className="max-h-72 overflow-y-auto p-4">
                <div className="grid gap-2 sm:grid-cols-2">
                            {items.map((item, index) => (
                                <div key={item.id} className="flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2.5">
                                    <span className="text-xs font-black text-indigo-400">{index + 1}.</span>
                                    <input value={item.spesifikasi} onChange={(event) => updateValue(item.id, event.target.value)} className="min-w-0 flex-1 bg-transparent text-sm font-bold text-slate-700 outline-none focus:text-indigo-800" aria-label="Edit spesifikasi" />
                                    <span title="Dapat diedit" className="text-xs text-indigo-400">✎</span>
                                    <button type="button" onClick={() => onDelete(item)} title="Hapus dari posisi ini" aria-label={`Hapus ${item.spesifikasi}`} className="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-sm font-black text-slate-400 transition hover:bg-rose-100 hover:text-rose-600">×</button>
                                </div>
                            ))}
                </div>
                {!items.length && <p className="py-5 text-center text-sm font-bold text-slate-400">Belum ada kualifikasi untuk posisi ini.</p>}
            </div>
        </div>
    );
}

function TableHead({ children, align = "left" }) {
    const alignClass =
        align === "right" ? "text-right" : "text-left";

    return (
        <th
            className={`px-6 py-4 ${alignClass} text-xs font-black uppercase tracking-[0.12em] text-slate-500`}
        >
            {children}
        </th>
    );
}

function SortableTableHead({
    label,
    sortKey,
    onSort,
    icon,
}) {
    return (
        <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-500">
            <button
                type="button"
                onClick={() => onSort(sortKey)}
                className="inline-flex items-center gap-2 font-black uppercase tracking-[0.12em] text-slate-500 transition hover:text-slate-800"
            >
                <span>{label}</span>
                <span className="text-xs">{icon}</span>
            </button>
        </th>
    );
}

function Input({
    label,
    name,
    value,
    onChange,
    type = "text",
    required = false,
    placeholder = "",
}) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && (
                    <span className="text-rose-500"> *</span>
                )}
            </label>

            <input
                type={type}
                name={name}
                value={value}
                onChange={onChange}
                required={required}
                placeholder={placeholder}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />
        </div>
    );
}

function Textarea({
    label,
    name,
    value,
    onChange,
    placeholder = "",
    rows = 4,
    required = false,
    helperText = "",
}) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && (
                    <span className="text-rose-500"> *</span>
                )}
            </label>

            <textarea
                name={name}
                value={value}
                onChange={onChange}
                rows={rows}
                required={required}
                placeholder={placeholder}
                className="w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-7 text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />

            {helperText && (
                <p className="mt-2 rounded-xl bg-slate-50 px-3 py-2 text-xs font-bold leading-5 text-slate-500">
                    {helperText}
                </p>
            )}
        </div>
    );
}

function StatusSelect({ label, name, value, onChange }) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
            </label>

            <select
                name={name}
                value={value}
                onChange={onChange}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="non_active">Tidak Aktif</option>
                <option value="active">Aktif</option>
            </select>
        </div>
    );
}

function PaginationButton({
    children,
    disabled,
    onClick,
}) {
    return (
        <button
            type="button"
            disabled={disabled}
            onClick={onClick}
            className="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
        >
            {children}
        </button>
    );
}
