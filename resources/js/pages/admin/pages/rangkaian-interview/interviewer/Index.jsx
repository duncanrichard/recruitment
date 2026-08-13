import React, { useEffect, useMemo, useRef, useState } from "react";

export default function InterviewerPage({ actionSignals }) {
    const [dataInterviewer, setDataInterviewer] = useState([]);
    const [dataJabatan, setDataJabatan] = useState([]);
    const [dataDivisi, setDataDivisi] = useState([]);

    const [modalOpen, setModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [editId, setEditId] = useState(null);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    const [sortConfig, setSortConfig] = useState({
        key: "nama",
        direction: "asc",
    });

    const [form, setForm] = useState({
        nama: "",
        no_wa: "",
        jabatan_id: "",
        divisi_id: "",
    });

    const lastInterviewerSignalRef = useRef(actionSignals?.interviewer || 0);

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const resetForm = () => {
        setEditId(null);

        setForm({
            nama: "",
            no_wa: "",
            jabatan_id: "",
            divisi_id: "",
        });
    };

    const fetchData = async () => {
        setTableLoading(true);

        try {
            const response = await fetch(
                "/admin/rangkaian-interview/interviewer/list",
                {
                    headers: {
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (result.success) {
                setDataInterviewer(result.data || []);
            } else {
                alert(result.message || "Gagal mengambil data interviewer.");
            }
        } catch (error) {
            console.error("Gagal mengambil data interviewer:", error);
            alert("Terjadi kesalahan saat mengambil data interviewer.");
        } finally {
            setTableLoading(false);
        }
    };

    const fetchOptions = async () => {
        try {
            const response = await fetch(
                "/admin/rangkaian-interview/interviewer/options",
                {
                    headers: {
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (result.success) {
                setDataJabatan(result.data?.jabatan || []);
                setDataDivisi(result.data?.divisi || []);
            } else {
                alert(result.message || "Gagal mengambil data option.");
            }
        } catch (error) {
            console.error("Gagal mengambil data option:", error);
            alert("Terjadi kesalahan saat mengambil data option.");
        }
    };

    useEffect(() => {
        fetchData();
        fetchOptions();
    }, []);

    useEffect(() => {
        const currentSignal = actionSignals?.interviewer || 0;

        if (currentSignal > lastInterviewerSignalRef.current) {
            resetForm();
            setModalOpen(true);
        }

        lastInterviewerSignalRef.current = currentSignal;
    }, [actionSignals?.interviewer]);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage]);

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) {
            return dataInterviewer;
        }

        return dataInterviewer.filter((item) => {
            const nama = String(item.nama || "").toLowerCase();
            const noWa = String(item.no_wa || "").toLowerCase();
            const jabatan = String(item.jabatan?.nama || "").toLowerCase();
            const divisi = String(item.divisi?.nama || "").toLowerCase();

            return (
                nama.includes(keyword) ||
                noWa.includes(keyword) ||
                jabatan.includes(keyword) ||
                divisi.includes(keyword)
            );
        });
    }, [dataInterviewer, search]);

    const sortedData = useMemo(() => {
        const data = [...filteredData];

        data.sort((a, b) => {
            let valueA = String(a[sortConfig.key] || "").toLowerCase();
            let valueB = String(b[sortConfig.key] || "").toLowerCase();

            if (sortConfig.key === "no_wa") {
                valueA = String(a.no_wa || "").toLowerCase();
                valueB = String(b.no_wa || "").toLowerCase();
            }

            if (sortConfig.key === "jabatan") {
                valueA = String(a.jabatan?.nama || "").toLowerCase();
                valueB = String(b.jabatan?.nama || "").toLowerCase();
            }

            if (sortConfig.key === "divisi") {
                valueA = String(a.divisi?.nama || "").toLowerCase();
                valueB = String(b.divisi?.nama || "").toLowerCase();
            }

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

    const createJabatan = async (nama) => {
        const response = await fetch("/admin/master-data/jabatan", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
            },
            body: JSON.stringify({ nama }),
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(Object.values(result.errors || {})?.flat()?.[0] || result.message || "Jabatan gagal ditambahkan.");
        }
        setDataJabatan((items) => [...items, result.data].sort((a, b) => a.nama.localeCompare(b.nama, "id")));
        return result.data;
    };

    const createDivisi = async (nama) => {
        const response = await fetch("/admin/master-data/divisi", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
            },
            body: JSON.stringify({ nama }),
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(Object.values(result.errors || {})?.flat()?.[0] || result.message || "Divisi gagal ditambahkan.");
        }
        setDataDivisi((items) => [...items, result.data].sort((a, b) => a.nama.localeCompare(b.nama, "id")));
        return result.data;
    };

    const closeModal = () => {
        setModalOpen(false);
        resetForm();
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const url = editId
                ? `/admin/rangkaian-interview/interviewer/${editId}`
                : "/admin/rangkaian-interview/interviewer";

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

            alert(result.message || "Data interviewer berhasil disimpan.");
            closeModal();
            fetchData();
        } catch (error) {
            console.error("Gagal menyimpan data interviewer:", error);
            alert("Terjadi kesalahan saat menyimpan data interviewer.");
        } finally {
            setLoading(false);
        }
    };

    const handleEdit = (item) => {
        setEditId(item.id);

        setForm({
            nama: item.nama || "",
            no_wa: item.no_wa || "",
            jabatan_id: item.jabatan_id || "",
            divisi_id: item.divisi_id || "",
        });

        setModalOpen(true);
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin ingin menghapus data interviewer ini?");

        if (!confirmDelete) return;

        try {
            const response = await fetch(
                `/admin/rangkaian-interview/interviewer/${id}`,
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
                alert(result.message || "Data interviewer berhasil dihapus.");
                fetchData();
            } else {
                alert(result.message || "Data gagal dihapus.");
            }
        } catch (error) {
            console.error("Gagal menghapus data interviewer:", error);
            alert("Terjadi kesalahan saat menghapus data interviewer.");
        }
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
                                placeholder="Cari interviewer..."
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
                                    label="Nama Interviewer"
                                    sortKey="nama"
                                    onSort={handleSort}
                                    icon={sortIcon("nama")}
                                />

                                <SortableTableHead
                                    label="No WA"
                                    sortKey="no_wa"
                                    onSort={handleSort}
                                    icon={sortIcon("no_wa")}
                                />

                                <SortableTableHead
                                    label="Jabatan"
                                    sortKey="jabatan"
                                    onSort={handleSort}
                                    icon={sortIcon("jabatan")}
                                />

                                <SortableTableHead
                                    label="Divisi"
                                    sortKey="divisi"
                                    onSort={handleSort}
                                    icon={sortIcon("divisi")}
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
                                                {item.nama || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            {item.no_wa ? (
                                                <a
                                                    href={`https://wa.me/${normalizePhoneForWhatsapp(item.no_wa)}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700 transition hover:bg-emerald-100"
                                                >
                                                    {item.no_wa}
                                                </a>
                                            ) : (
                                                <span className="text-sm font-bold text-slate-400">
                                                    -
                                                </span>
                                            )}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {item.jabatan?.nama || "-"}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {item.divisi?.nama || "-"}
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
                                                ◉
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data interviewer tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Belum ada data interviewer atau kata kunci pencarian tidak cocok.
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
                                filtered from {dataInterviewer.length} total entries
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
                                        Form Interviewer
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        {editId
                                            ? "Edit Interviewer"
                                            : "Tambah Interviewer"}
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Lengkapi data interviewer.
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

                        <form onSubmit={handleSubmit} className="flex min-h-0 flex-1 flex-col">
                            <div className="min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain px-6 py-6 pb-10 [scrollbar-gutter:stable]">
                                <Input
                                    label="Nama Interviewer"
                                    name="nama"
                                    value={form.nama}
                                    onChange={handleChange}
                                    placeholder="Contoh: Dr Teddy"
                                    required
                                />

                                <Input
                                    label="No WA"
                                    name="no_wa"
                                    value={form.no_wa}
                                    onChange={handleChange}
                                    placeholder="Contoh: 081234567890"
                                    type="number"
                                    min="0"
                                    inputMode="numeric"
                                />

                                <Select2Creatable
                                    label="Jabatan"
                                    value={form.jabatan_id}
                                    onChange={(value) => setForm((current) => ({ ...current, jabatan_id: value }))}
                                    options={dataJabatan}
                                    placeholder="Pilih jabatan"
                                    onCreate={createJabatan}
                                />

                                <Select2Creatable
                                    label="Divisi"
                                    value={form.divisi_id}
                                    onChange={(value) => setForm((current) => ({ ...current, divisi_id: value }))}
                                    options={dataDivisi}
                                    placeholder="Pilih divisi"
                                    onCreate={createDivisi}
                                    searchPlaceholder="Cari atau tambah divisi..."
                                    createLabel="Tambah divisi"
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
    min,
    inputMode,
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
                min={min}
                inputMode={inputMode}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            />
        </div>
    );
}

function normalizePhoneForWhatsapp(value) {
    const number = String(value || "").replace(/[^\d]/g, "");

    if (!number) {
        return "";
    }

    if (number.startsWith("0")) {
        return `62${number.slice(1)}`;
    }

    if (number.startsWith("62")) {
        return number;
    }

    return number;
}

function SelectInput({
    label,
    name,
    value,
    onChange,
    options,
    placeholder = "Pilih data",
}) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
            </label>

            <select
                name={name}
                value={value}
                onChange={onChange}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            >
                <option value="">{placeholder}</option>

                {options.map((item) => (
                    <option key={item.id} value={item.id}>
                        {item.nama}
                    </option>
                ))}
            </select>
        </div>
    );
}

function Select2Creatable({ label, value, onChange, options, placeholder, onCreate, searchPlaceholder = "Cari atau tambah jabatan...", createLabel = "Tambah jabatan" }) {
    const wrapperRef = useRef(null);
    const inputRef = useRef(null);
    const [open, setOpen] = useState(false);
    const [keyword, setKeyword] = useState("");
    const [creating, setCreating] = useState(false);
    const [error, setError] = useState("");
    const selected = options.find((item) => String(item.id) === String(value));
    const normalized = keyword.trim().toLowerCase();
    const filtered = options.filter((item) => String(item.nama || "").toLowerCase().includes(normalized));
    const exact = options.some((item) => String(item.nama || "").trim().toLowerCase() === normalized);

    useEffect(() => {
        const close = (event) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
                setOpen(false);
                setKeyword("");
            }
        };
        document.addEventListener("mousedown", close);
        return () => document.removeEventListener("mousedown", close);
    }, []);

    const create = async () => {
        if (!normalized || creating) return;
        setCreating(true); setError("");
        try {
            const item = await onCreate(keyword.trim());
            onChange(item.id); setOpen(false); setKeyword("");
        } catch (exception) { setError(exception.message || "Data gagal ditambahkan."); }
        finally { setCreating(false); }
    };

    return (
        <div ref={wrapperRef} className="relative">
            <label className="mb-2 block text-sm font-black text-slate-700">{label}</label>
            <button type="button" onClick={() => { setOpen((state) => !state); setTimeout(() => inputRef.current?.focus(), 0); }} className={`flex w-full items-center justify-between rounded-2xl border bg-white px-4 py-3 text-sm font-bold shadow-sm ${open ? "border-violet-500 ring-4 ring-violet-100" : "border-slate-200"}`}>
                <span className={selected ? "text-slate-700" : "text-slate-400"}>{selected?.nama || placeholder}</span>
                <span className="text-slate-400">⌄</span>
            </button>
            {open && <div className="relative z-[80] mt-2 w-full rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                <input ref={inputRef} value={keyword} onChange={(event) => { setKeyword(event.target.value); setError(""); }} onKeyDown={(event) => { if (event.key === "Enter" && normalized && !exact) { event.preventDefault(); create(); } }} placeholder={searchPlaceholder} className="w-full rounded-xl border border-violet-300 px-3 py-2.5 text-sm font-bold outline-none focus:ring-4 focus:ring-violet-100" />
                <div className="mt-2 max-h-48 overflow-y-auto">
                    {filtered.map((item) => <button key={item.id} type="button" onClick={() => { onChange(item.id); setOpen(false); setKeyword(""); }} className="block w-full rounded-xl px-3 py-2.5 text-left text-sm font-bold text-slate-700 hover:bg-violet-50">{item.nama}</button>)}
                    {normalized && !exact && <button type="button" disabled={creating} onClick={create} className="mt-1 block w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-3 py-2.5 text-left text-sm font-black text-white disabled:opacity-60">{creating ? "Menambahkan..." : `+ ${createLabel} “${keyword.trim()}”`}</button>}
                    {!filtered.length && !normalized && <p className="px-3 py-4 text-center text-sm font-bold text-slate-400">Ketik untuk mencari data</p>}
                </div>
                {error && <p className="mt-2 rounded-lg bg-rose-50 px-3 py-2 text-xs font-bold text-rose-600">{error}</p>}
            </div>}
        </div>
    );
}
