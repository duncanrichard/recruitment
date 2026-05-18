import React, { useEffect, useMemo, useRef, useState } from "react";

export default function SumberInformasiPage({ actionSignals }) {
    const [dataSumberInformasi, setDataSumberInformasi] = useState([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [editId, setEditId] = useState(null);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    const [sortConfig, setSortConfig] = useState({
        key: "informasi",
        direction: "asc",
    });

    const [form, setForm] = useState({
        informasi: "",
    });

    const currentSignal = actionSignals?.masterSumberInformasi || 0;
    const lastSignalRef = useRef(currentSignal);

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const resetForm = () => {
        setEditId(null);

        setForm({
            informasi: "",
        });
    };

    const openCreateModal = () => {
        resetForm();
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        resetForm();
    };

    const fetchData = async () => {
        setTableLoading(true);

        try {
            const response = await fetch(
                "/admin/master-data/sumber-informasi/list",
                {
                    headers: {
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (result.success) {
                setDataSumberInformasi(result.data || []);
            } else {
                alert(result.message || "Gagal mengambil data sumber informasi.");
            }
        } catch (error) {
            console.error("Gagal mengambil data sumber informasi:", error);
            alert("Terjadi kesalahan saat mengambil data sumber informasi.");
        } finally {
            setTableLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    useEffect(() => {
        if (currentSignal > lastSignalRef.current) {
            openCreateModal();
        }

        lastSignalRef.current = currentSignal;
    }, [currentSignal]);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage]);

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) {
            return dataSumberInformasi;
        }

        return dataSumberInformasi.filter((item) => {
            const value = String(item.informasi || "").toLowerCase();

            return value.includes(keyword);
        });
    }, [dataSumberInformasi, search]);

    const sortedData = useMemo(() => {
        const data = [...filteredData];

        data.sort((a, b) => {
            const valueA = String(a[sortConfig.key] || "").toLowerCase();
            const valueB = String(b[sortConfig.key] || "").toLowerCase();

            if (valueA < valueB) {
                return sortConfig.direction === "asc" ? -1 : 1;
            }

            if (valueA > valueB) {
                return sortConfig.direction === "asc" ? 1 : -1;
            }

            return 0;
        });

        return data;
    }, [filteredData, sortConfig]);

    const totalPages = Math.max(1, Math.ceil(sortedData.length / entriesPerPage));

    const paginatedData = useMemo(() => {
        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = startIndex + entriesPerPage;

        return sortedData.slice(startIndex, endIndex);
    }, [sortedData, currentPage, entriesPerPage]);

    const showingFrom =
        sortedData.length === 0 ? 0 : (currentPage - 1) * entriesPerPage + 1;

    const showingTo = Math.min(currentPage * entriesPerPage, sortedData.length);

    const pageNumbers = useMemo(() => {
        const pages = [];
        const maxVisiblePages = 5;

        let startPage = Math.max(
            1,
            currentPage - Math.floor(maxVisiblePages / 2)
        );

        let endPage = startPage + maxVisiblePages - 1;

        if (endPage > totalPages) {
            endPage = totalPages;
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        for (let page = startPage; page <= endPage; page++) {
            pages.push(page);
        }

        return pages;
    }, [currentPage, totalPages]);

    const handleSort = (key) => {
        setSortConfig((prev) => {
            if (prev.key === key) {
                return {
                    key,
                    direction: prev.direction === "asc" ? "desc" : "asc",
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

    const handleChange = (e) => {
        const { name, value } = e.target;

        setForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleEdit = (item) => {
        setEditId(item.id);

        setForm({
            informasi: item.informasi || "",
        });

        setModalOpen(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const url = editId
                ? `/admin/master-data/sumber-informasi/${editId}`
                : "/admin/master-data/sumber-informasi";

            const method = editId ? "PUT" : "POST";

            const response = await fetch(url, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify(form),
            });

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || "Data gagal disimpan.");
                return;
            }

            alert(result.message || "Data sumber informasi berhasil disimpan.");
            closeModal();
            fetchData();
        } catch (error) {
            console.error("Gagal menyimpan data sumber informasi:", error);
            alert("Terjadi kesalahan saat menyimpan data sumber informasi.");
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm(
            "Yakin ingin menghapus data sumber informasi ini?"
        );

        if (!confirmDelete) return;

        try {
            const response = await fetch(
                `/admin/master-data/sumber-informasi/${id}`,
                {
                    method: "DELETE",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                }
            );

            const result = await response.json();

            if (result.success) {
                alert(result.message || "Data sumber informasi berhasil dihapus.");
                fetchData();
            } else {
                alert(result.message || "Data gagal dihapus.");
            }
        } catch (error) {
            console.error("Gagal menghapus data sumber informasi:", error);
            alert("Terjadi kesalahan saat menghapus data sumber informasi.");
        }
    };

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                            Master Data
                        </div>

                        <h1 className="mt-3 text-2xl font-black text-slate-950">
                            Sumber Informasi
                        </h1>

                        <p className="mt-1 text-sm font-medium text-slate-500">
                            Kelola daftar sumber informasi untuk data pelamar.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-3 sm:flex">
                        <div className="rounded-2xl bg-slate-50 px-4 py-3">
                            <p className="text-xs font-bold uppercase text-slate-400">
                                Total
                            </p>

                            <p className="mt-1 text-xl font-black text-slate-950">
                                {dataSumberInformasi.length}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-center gap-2">
                            <span className="text-sm font-bold text-slate-600">
                                Show
                            </span>

                            <select
                                value={entriesPerPage}
                                onChange={(event) =>
                                    setEntriesPerPage(Number(event.target.value))
                                }
                                className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                            >
                                <option value={5}>5</option>
                                <option value={10}>10</option>
                                <option value={25}>25</option>
                                <option value={50}>50</option>
                                <option value={100}>100</option>
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
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Cari sumber informasi..."
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

                                <SortableTableHead
                                    label="Informasi"
                                    sortKey="informasi"
                                    onSort={handleSort}
                                    icon={sortIcon("informasi")}
                                />

                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <td colSpan="3" className="px-6 py-16">
                                        <div className="text-center text-sm font-black text-slate-500">
                                            Memuat data...
                                        </div>
                                    </td>
                                </tr>
                            ) : paginatedData.length > 0 ? (
                                paginatedData.map((item, index) => (
                                    <tr
                                        key={item.id}
                                        className="group transition hover:bg-slate-50"
                                    >
                                        <td className="px-6 py-5 text-sm font-black text-slate-500">
                                            {showingFrom + index}
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="font-black text-slate-950">
                                                {item.informasi}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5 text-right">
                                            <div className="flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => handleEdit(item)}
                                                    className="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        handleDelete(item.id)
                                                    }
                                                    className="rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:bg-rose-50 hover:text-rose-700"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="3" className="px-6 py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ◉
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data sumber informasi tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Belum ada data sumber informasi atau kata kunci pencarian tidak cocok.
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
                                filtered from {dataSumberInformasi.length} total
                                entries
                            </span>
                        )}
                    </p>

                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            disabled={currentPage === 1}
                            onClick={() =>
                                setCurrentPage((prev) => Math.max(prev - 1, 1))
                            }
                            className="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>

                        {pageNumbers.map((page) => (
                            <button
                                key={page}
                                type="button"
                                onClick={() => setCurrentPage(page)}
                                className={`rounded-xl px-4 py-2 text-sm font-black shadow-sm transition ${
                                    currentPage === page
                                        ? "bg-teal-600 text-white"
                                        : "border border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
                                }`}
                            >
                                {page}
                            </button>
                        ))}

                        <button
                            type="button"
                            disabled={currentPage === totalPages}
                            onClick={() =>
                                setCurrentPage((prev) =>
                                    Math.min(prev + 1, totalPages)
                                )
                            }
                            className="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>

            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
                    <div className="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div className="shrink-0 border-b border-slate-200 bg-white">
                            <div className="flex items-center justify-between gap-4 px-6 py-5">
                                <div>
                                    <div className="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                                        Form Sumber Informasi
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        {editId
                                            ? "Edit Sumber Informasi"
                                            : "Tambah Sumber Informasi"}
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Lengkapi sumber informasi yang akan digunakan
                                        sebagai referensi.
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

                        <form onSubmit={handleSubmit}>
                            <div className="space-y-5 px-6 py-6">
                                <Input
                                    label="Informasi"
                                    name="informasi"
                                    value={form.informasi}
                                    onChange={handleChange}
                                    placeholder="Contoh: Instagram, LinkedIn, Website, Teman"
                                    required
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
                                        className="rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-teal-100 transition hover:from-teal-700 hover:to-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
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

function SortableTableHead({ label, sortKey, onSort, icon }) {
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
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <input
                type={type}
                name={name}
                value={value}
                onChange={onChange}
                required={required}
                placeholder={placeholder}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            />
        </div>
    );
}