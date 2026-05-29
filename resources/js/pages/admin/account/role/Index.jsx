import React, { useEffect, useMemo, useRef, useState } from "react";

export default function RolePage({ actionSignals }) {
    const firstActionSignalRender = useRef(true);

    const [dataRole, setDataRole] = useState([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [editId, setEditId] = useState(null);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    const [sortConfig, setSortConfig] = useState({
        key: "nama_role",
        direction: "asc",
    });

    const [form, setForm] = useState({
        nama_role: "",
        kode_role: "",
        keterangan: "",
        is_active: true,
    });

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const resetForm = () => {
        setEditId(null);

        setForm({
            nama_role: "",
            kode_role: "",
            keterangan: "",
            is_active: true,
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
            const response = await fetch("/admin/account/role/list", {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (result.success) {
                setDataRole(result.data || []);
            } else {
                alert(result.message || "Gagal mengambil data role.");
            }
        } catch (error) {
            console.error("Gagal mengambil data role:", error);
            alert("Terjadi kesalahan saat mengambil data role.");
        } finally {
            setTableLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    useEffect(() => {
        if (firstActionSignalRender.current) {
            firstActionSignalRender.current = false;
            return;
        }

        if (actionSignals?.accountRole > 0) {
            openCreateModal();
        }
    }, [actionSignals?.accountRole]);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage]);

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) {
            return dataRole;
        }

        return dataRole.filter((item) => {
            const namaRole = String(item.nama_role || "").toLowerCase();
            const kodeRole = String(item.kode_role || "").toLowerCase();
            const keterangan = String(item.keterangan || "").toLowerCase();
            const status = item.is_active ? "aktif" : "nonaktif";

            return (
                namaRole.includes(keyword) ||
                kodeRole.includes(keyword) ||
                keterangan.includes(keyword) ||
                status.includes(keyword)
            );
        });
    }, [dataRole, search]);

    const sortedData = useMemo(() => {
        const data = [...filteredData];

        data.sort((a, b) => {
            let valueA = a[sortConfig.key];
            let valueB = b[sortConfig.key];

            if (typeof valueA === "boolean") {
                valueA = valueA ? "aktif" : "nonaktif";
            }

            if (typeof valueB === "boolean") {
                valueB = valueB ? "aktif" : "nonaktif";
            }

            valueA = String(valueA || "").toLowerCase();
            valueB = String(valueB || "").toLowerCase();

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
        const { name, value, type, checked } = e.target;

        setForm((prev) => ({
            ...prev,
            [name]: type === "checkbox" ? checked : value,
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const url = editId
                ? `/admin/account/role/${editId}`
                : "/admin/account/role";

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
                alert(result.message || "Data role gagal disimpan.");
                return;
            }

            alert(result.message || "Data role berhasil disimpan.");
            closeModal();
            fetchData();
        } catch (error) {
            console.error("Gagal menyimpan data role:", error);
            alert("Terjadi kesalahan saat menyimpan data role.");
        } finally {
            setLoading(false);
        }
    };

    const handleEdit = (item) => {
        setEditId(item.id);

        setForm({
            nama_role: item.nama_role || "",
            kode_role: item.kode_role || "",
            keterangan: item.keterangan || "",
            is_active: Boolean(item.is_active),
        });

        setModalOpen(true);
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin ingin menghapus data role ini?");

        if (!confirmDelete) return;

        try {
            const response = await fetch(`/admin/account/role/${id}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message || "Data role berhasil dihapus.");
                fetchData();
            } else {
                alert(result.message || "Data role gagal dihapus.");
            }
        } catch (error) {
            console.error("Gagal menghapus data role:", error);
            alert("Terjadi kesalahan saat menghapus data role.");
        }
    };

    const handlePreviousPage = () => {
        setCurrentPage((prev) => Math.max(prev - 1, 1));
    };

    const handleNextPage = () => {
        setCurrentPage((prev) => Math.min(prev + 1, totalPages));
    };

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div className="flex flex-col gap-4 md:flex-row md:items-center">
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
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder="Cari role..."
                                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100 md:w-80"
                                />
                            </div>
                        </div>

                      
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>

                                <SortableTableHead
                                    label="Nama Role"
                                    sortKey="nama_role"
                                    onSort={handleSort}
                                    icon={sortIcon("nama_role")}
                                />

                                <SortableTableHead
                                    label="Kode Role"
                                    sortKey="kode_role"
                                    onSort={handleSort}
                                    icon={sortIcon("kode_role")}
                                />

                                <TableHead>Keterangan</TableHead>

                                <SortableTableHead
                                    label="Status"
                                    sortKey="is_active"
                                    onSort={handleSort}
                                    icon={sortIcon("is_active")}
                                />

                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <td colSpan="6" className="px-6 py-16">
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
                                                {item.nama_role}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <span className="rounded-xl bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-wide text-slate-600">
                                                {item.kode_role || "-"}
                                            </span>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="max-w-md text-sm font-semibold text-slate-500">
                                                {item.keterangan || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <span
                                                className={`inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ${
                                                    item.is_active
                                                        ? "bg-teal-50 text-teal-700"
                                                        : "bg-rose-50 text-rose-700"
                                                }`}
                                            >
                                                {item.is_active
                                                    ? "Aktif"
                                                    : "Nonaktif"}
                                            </span>
                                        </td>

                                        <td className="px-6 py-5 text-right">
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
                                    <td colSpan="6" className="px-6 py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ◎
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data role tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Belum ada data role atau kata kunci pencarian tidak cocok.
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
                    </p>

                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            disabled={currentPage === 1}
                            onClick={handlePreviousPage}
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
                            onClick={handleNextPage}
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
                                        Form Role
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        {editId ? "Edit Role" : "Tambah Role"}
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Lengkapi data role untuk hak akses akun.
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
                                    label="Nama Role"
                                    name="nama_role"
                                    value={form.nama_role}
                                    onChange={handleChange}
                                    placeholder="Contoh: Super Admin"
                                    required
                                />

                                <Input
                                    label="Kode Role"
                                    name="kode_role"
                                    value={form.kode_role}
                                    onChange={handleChange}
                                    placeholder="Contoh: super_admin"
                                />

                                <Textarea
                                    label="Keterangan"
                                    name="keterangan"
                                    value={form.keterangan}
                                    onChange={handleChange}
                                    placeholder="Tuliskan keterangan role..."
                                />

                                <label className="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        checked={form.is_active}
                                        onChange={handleChange}
                                        className="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                    />

                                    <div>
                                        <div className="text-sm font-black text-slate-700">
                                            Role Aktif
                                        </div>
                                        <div className="text-xs font-semibold text-slate-500">
                                            Role aktif dapat digunakan pada akun pengguna.
                                        </div>
                                    </div>
                                </label>
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

function Textarea({
    label,
    name,
    value,
    onChange,
    required = false,
    placeholder = "",
}) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <textarea
                name={name}
                value={value}
                onChange={onChange}
                required={required}
                placeholder={placeholder}
                rows={4}
                className="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            />
        </div>
    );
}