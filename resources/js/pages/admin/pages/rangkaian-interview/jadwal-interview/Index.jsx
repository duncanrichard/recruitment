import React, { useEffect, useMemo, useRef, useState } from "react";

export default function JadwalInterviewPage({ actionSignals }) {
    const [dataJadwalInterview, setDataJadwalInterview] = useState([]);
    const [dataInterviewers, setDataInterviewers] = useState([]);
    const [dataJabatan, setDataJabatan] = useState([]);

    const [modalOpen, setModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [editId, setEditId] = useState(null);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    const [sortConfig, setSortConfig] = useState({
        key: "jadwal_interview",
        direction: "desc",
    });

    const [form, setForm] = useState({
        judul_interview: "",
        jadwal_interview: "",
        interviewer_ids: [],
    });

    const lastJadwalInterviewSignalRef = useRef(
        actionSignals?.jadwalInterview || 0
    );

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const getMinDateTimeLocal = () => {
        const now = new Date();
        now.setSeconds(0, 0);

        const timezoneOffset = now.getTimezoneOffset() * 60000;
        const localDate = new Date(now.getTime() - timezoneOffset);

        return localDate.toISOString().slice(0, 16);
    };

    const formatDateTimeLocal = (value) => {
        if (!value) return "";

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return String(value).replace(" ", "T").slice(0, 16);
        }

        const timezoneOffset = date.getTimezoneOffset() * 60000;
        const localDate = new Date(date.getTime() - timezoneOffset);

        return localDate.toISOString().slice(0, 16);
    };

    const formatDisplayDateTime = (value) => {
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
    };

    const formatPanelis = (panelis = []) => {
        if (!Array.isArray(panelis) || panelis.length === 0) {
            return "-";
        }

        return panelis.map((item) => item.nama).join(", ");
    };

    const resetForm = () => {
        setEditId(null);

        setForm({
            judul_interview: "",
            jadwal_interview: "",
            interviewer_ids: [],
        });
    };

    const fetchData = async () => {
        setTableLoading(true);

        try {
            const response = await fetch(
                "/admin/rangkaian-interview/jadwal-interview/list",
                {
                    headers: {
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (result.success) {
                setDataJadwalInterview(result.data || []);
            } else {
                alert(result.message || "Gagal mengambil data jadwal interview.");
            }
        } catch (error) {
            console.error("Gagal mengambil data jadwal interview:", error);
            alert("Terjadi kesalahan saat mengambil data jadwal interview.");
        } finally {
            setTableLoading(false);
        }
    };

    const fetchInterviewers = async () => {
        try {
            const response = await fetch(
                "/admin/rangkaian-interview/jadwal-interview/interviewers",
                {
                    headers: {
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (result.success) {
                setDataInterviewers(result.data || []);
                setDataJabatan(result.meta?.jabatan || []);
            } else {
                alert(result.message || "Gagal mengambil data interviewer.");
            }
        } catch (error) {
            console.error("Gagal mengambil data interviewer:", error);
            alert("Terjadi kesalahan saat mengambil data interviewer.");
        }
    };

    const postJson = async (url, payload) => {
        const response = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-TOKEN": getCsrfToken() },
            body: JSON.stringify(payload),
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(Object.values(result.errors || {})?.flat()?.[0] || result.message || "Data gagal ditambahkan.");
        }
        return result.data;
    };

    const createJabatan = async (nama) => {
        const item = await postJson("/admin/master-data/jabatan", { nama });
        setDataJabatan((items) => [...items, item].sort((a, b) => a.nama.localeCompare(b.nama, "id")));
        return item;
    };

    const createInterviewer = async ({ nama, no_wa, jabatan_id }) => {
        const item = await postJson("/admin/rangkaian-interview/interviewer", { nama, no_wa, jabatan_id, divisi_id: null });
        setDataInterviewers((items) => [...items, item].sort((a, b) => a.nama.localeCompare(b.nama, "id")));
        return item;
    };

    useEffect(() => {
        fetchData();
        fetchInterviewers();
    }, []);

    useEffect(() => {
        const currentSignal = actionSignals?.jadwalInterview || 0;

        if (currentSignal > lastJadwalInterviewSignalRef.current) {
            resetForm();
            setModalOpen(true);
        }

        lastJadwalInterviewSignalRef.current = currentSignal;
    }, [actionSignals?.jadwalInterview]);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage]);

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) {
            return dataJadwalInterview;
        }

        return dataJadwalInterview.filter((item) => {
            const judulInterview = String(
                item.judul_interview || ""
            ).toLowerCase();

            const jadwalInterview = String(
                item.jadwal_interview || ""
            ).toLowerCase();

            const jadwalDisplay = formatDisplayDateTime(
                item.jadwal_interview
            ).toLowerCase();

            const panelis = formatPanelis(item.panelis).toLowerCase();

            return (
                judulInterview.includes(keyword) ||
                jadwalInterview.includes(keyword) ||
                jadwalDisplay.includes(keyword) ||
                panelis.includes(keyword)
            );
        });
    }, [dataJadwalInterview, search]);

    const sortedData = useMemo(() => {
        const data = [...filteredData];

        data.sort((a, b) => {
            let valueA = String(a[sortConfig.key] || "").toLowerCase();
            let valueB = String(b[sortConfig.key] || "").toLowerCase();

            if (sortConfig.key === "jadwal_interview") {
                valueA = new Date(a.jadwal_interview || "").getTime() || 0;
                valueB = new Date(b.jadwal_interview || "").getTime() || 0;
            }

            if (sortConfig.key === "panelis") {
                valueA = formatPanelis(a.panelis).toLowerCase();
                valueB = formatPanelis(b.panelis).toLowerCase();
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

    const handleInterviewerChange = (selectedIds) => {
        setForm((prev) => ({
            ...prev,
            interviewer_ids: selectedIds,
        }));
    };

    const closeModal = () => {
        setModalOpen(false);
        resetForm();
    };

    const validateForm = () => {
        if (!form.judul_interview.trim()) {
            alert("Judul interview wajib diisi.");
            return false;
        }

        if (!form.jadwal_interview) {
            alert("Jadwal interview wajib diisi.");
            return false;
        }

        if (!form.interviewer_ids.length) {
            alert("Minimal pilih satu interviewer.");
            return false;
        }

        const selectedDate = new Date(form.jadwal_interview);
        const now = new Date();

        if (selectedDate < now) {
            alert("Jadwal interview tidak boleh tanggal atau jam yang sudah lewat.");
            return false;
        }

        return true;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) return;

        setLoading(true);

        try {
            const url = editId
                ? `/admin/rangkaian-interview/jadwal-interview/${editId}`
                : "/admin/rangkaian-interview/jadwal-interview";

            const method = editId ? "PUT" : "POST";

            const response = await fetch(url, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify({
                    judul_interview: form.judul_interview.trim(),
                    jadwal_interview: form.jadwal_interview,
                    interviewer_ids: form.interviewer_ids,
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || "Data gagal disimpan.");
                return;
            }

            alert(result.message || "Data jadwal interview berhasil disimpan.");
            closeModal();
            fetchData();
        } catch (error) {
            console.error("Gagal menyimpan data jadwal interview:", error);
            alert("Terjadi kesalahan saat menyimpan data jadwal interview.");
        } finally {
            setLoading(false);
        }
    };

    const handleEdit = (item) => {
        setEditId(item.id);

        setForm({
            judul_interview: item.judul_interview || "",
            jadwal_interview: formatDateTimeLocal(item.jadwal_interview),
            interviewer_ids: Array.isArray(item.panelis)
                ? item.panelis.map((panelis) => panelis.id)
                : [],
        });

        setModalOpen(true);
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm(
            "Yakin ingin menghapus data jadwal interview ini?"
        );

        if (!confirmDelete) return;

        try {
            const response = await fetch(
                `/admin/rangkaian-interview/jadwal-interview/${id}`,
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
                alert(result.message || "Data jadwal interview berhasil dihapus.");
                fetchData();
            } else {
                alert(result.message || "Data gagal dihapus.");
            }
        } catch (error) {
            console.error("Gagal menghapus data jadwal interview:", error);
            alert("Terjadi kesalahan saat menghapus data jadwal interview.");
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
                                className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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
                                placeholder="Cari jadwal interview..."
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

                                <SortableTableHead
                                    label="Judul Interview"
                                    sortKey="judul_interview"
                                    onSort={handleSort}
                                    icon={sortIcon("judul_interview")}
                                />

                                <SortableTableHead
                                    label="Jadwal Interview"
                                    sortKey="jadwal_interview"
                                    onSort={handleSort}
                                    icon={sortIcon("jadwal_interview")}
                                />

                                <SortableTableHead
                                    label="Interviewer"
                                    sortKey="panelis"
                                    onSort={handleSort}
                                    icon={sortIcon("panelis")}
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
                                                {item.judul_interview || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {formatDisplayDateTime(
                                                item.jadwal_interview
                                            )}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {formatPanelis(item.panelis)}
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
                                    <td colSpan="5" className="px-6 py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ◷
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data jadwal interview tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Belum ada data jadwal interview atau kata kunci pencarian tidak cocok.
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
                                filtered from {dataJadwalInterview.length} total entries
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
                                        ? "bg-indigo-600 text-white"
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
                    <div className="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-visible rounded-[2rem] bg-white shadow-2xl">
                        <div className="shrink-0 border-b border-slate-200 bg-white">
                            <div className="flex items-center justify-between gap-4 px-6 py-5">
                                <div>
                                    <div className="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                                        Form Jadwal Interview
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        {editId
                                            ? "Edit Jadwal Interview"
                                            : "Tambah Jadwal Interview"}
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Lengkapi judul, jadwal, dan interviewer.
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
                                    label="Judul Interview"
                                    name="judul_interview"
                                    value={form.judul_interview}
                                    onChange={handleChange}
                                    placeholder="Contoh: Interview Tahap 1"
                                    required
                                />

                                <Input
                                    label="Jadwal Interview"
                                    name="jadwal_interview"
                                    type="datetime-local"
                                    value={form.jadwal_interview}
                                    onChange={handleChange}
                                    min={getMinDateTimeLocal()}
                                    required
                                />

                                <Select2Multi
                                    label="Interviewer"
                                    value={form.interviewer_ids}
                                    options={dataInterviewers}
                                    onChange={handleInterviewerChange}
                                    placeholder="Pilih interviewer..."
                                    required
                                    jabatanOptions={dataJabatan}
                                    onCreateJabatan={createJabatan}
                                    onCreateInterviewer={createInterviewer}
                                />

                                <p className="rounded-xl bg-slate-50 px-4 py-3 text-xs font-bold leading-5 text-slate-500">
                                    Bisa pilih lebih dari satu interviewer.
                                    Tanggal dan jam yang sudah lewat tidak bisa dipilih.
                                </p>
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
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />
        </div>
    );
}

function Select2Multi({
    label,
    value,
    options,
    onChange,
    placeholder = "Pilih data...",
    required = false,
    jabatanOptions = [],
    onCreateJabatan,
    onCreateInterviewer,
}) {
    const wrapperRef = useRef(null);
    const inputRef = useRef(null);

    const [open, setOpen] = useState(false);
    const [keyword, setKeyword] = useState("");
    const [newNoWa, setNewNoWa] = useState("");
    const [newJabatanId, setNewJabatanId] = useState("");
    const [creating, setCreating] = useState(false);
    const [createError, setCreateError] = useState("");

    const selectedOptions = useMemo(() => {
        return options.filter((item) => value.includes(item.id));
    }, [options, value]);

    const availableOptions = useMemo(() => {
        const lowerKeyword = keyword.toLowerCase().trim();

        return options.filter((item) => {
            const isSelected = value.includes(item.id);
            const searchableText = [
                item.nama,
                item.no_wa,
                item.jabatan?.nama,
                item.divisi?.nama,
            ]
                .filter(Boolean)
                .join(" ")
                .toLowerCase();
            const matchKeyword = searchableText.includes(lowerKeyword);

            return !isSelected && matchKeyword;
        });
    }, [options, value, keyword]);

    const normalizedKeyword = keyword.trim().toLowerCase();
    const exactInterviewer = options.some((item) => String(item.nama || "").trim().toLowerCase() === normalizedKeyword);

    const handleCreateInterviewer = async () => {
        if (!keyword.trim() || !newNoWa.trim() || !newJabatanId || creating) {
            setCreateError("Nama, nomor WhatsApp, dan jabatan wajib diisi.");
            return;
        }
        setCreating(true); setCreateError("");
        try {
            const item = await onCreateInterviewer({ nama: keyword.trim(), no_wa: newNoWa.trim(), jabatan_id: newJabatanId });
            onChange([...value, item.id]);
            setKeyword(""); setNewNoWa(""); setNewJabatanId("");
        } catch (error) { setCreateError(error.message || "Interviewer gagal ditambahkan."); }
        finally { setCreating(false); }
    };

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                wrapperRef.current &&
                !wrapperRef.current.contains(event.target)
            ) {
                setOpen(false);
                setKeyword("");
            }
        };

        document.addEventListener("mousedown", handleClickOutside);

        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, []);

    const handleSelect = (id) => {
        if (!value.includes(id)) {
            onChange([...value, id]);
        }

        setKeyword("");
        setOpen(true);

        setTimeout(() => {
            inputRef.current?.focus();
        }, 0);
    };

    const handleRemove = (id) => {
        onChange(value.filter((itemId) => itemId !== id));
    };

    const handleClear = () => {
        onChange([]);
        setKeyword("");
        setOpen(false);
    };

    return (
        <div ref={wrapperRef} className="relative">
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <div
                role="button"
                tabIndex={0}
                onClick={() => {
                    setOpen(true);
                    inputRef.current?.focus();
                }}
                onKeyDown={(event) => {
                    if (event.key === "Enter" || event.key === " ") {
                        event.preventDefault();
                        setOpen(true);
                        inputRef.current?.focus();
                    }
                }}
                className={`min-h-[54px] w-full rounded-2xl border bg-white px-3 py-2 shadow-sm transition ${
                    open
                        ? "border-indigo-500 ring-4 ring-indigo-100"
                        : "border-slate-200"
                }`}
            >
                <div className="flex flex-wrap items-center gap-2">
                    {selectedOptions.map((item) => (
                        <span
                            key={item.id}
                            className="inline-flex items-center gap-2 rounded-xl bg-indigo-50 px-3 py-1.5 text-xs font-black text-indigo-700"
                        >
                            <span>
                                <span className="block">{item.nama}</span>
                                {(item.jabatan?.nama || item.divisi?.nama) && (
                                    <span className="mt-0.5 block text-[10px] font-bold text-indigo-600/80">
                                        {[item.jabatan?.nama, item.divisi?.nama].filter(Boolean).join(" • ")}
                                    </span>
                                )}
                            </span>

                            <button
                                type="button"
                                onClick={(event) => {
                                    event.stopPropagation();
                                    handleRemove(item.id);
                                }}
                                className="rounded-full text-sm leading-none text-indigo-700 hover:text-rose-600"
                            >
                                ×
                            </button>
                        </span>
                    ))}

                    <input
                        ref={inputRef}
                        type="text"
                        value={keyword}
                        onChange={(event) => {
                            setKeyword(event.target.value);
                            setOpen(true);
                        }}
                        onFocus={() => setOpen(true)}
                        placeholder={
                            selectedOptions.length === 0 ? placeholder : "Cari..."
                        }
                        className="min-w-[140px] flex-1 border-none bg-transparent px-1 py-1 text-sm font-bold text-slate-700 outline-none placeholder:text-slate-300"
                    />

                    {value.length > 0 && (
                        <button
                            type="button"
                            onClick={(event) => {
                                event.stopPropagation();
                                handleClear();
                            }}
                            className="ml-auto rounded-xl px-2 py-1 text-xs font-black text-slate-400 transition hover:bg-slate-100 hover:text-rose-600"
                        >
                            Clear
                        </button>
                    )}

                    <span className="text-xs font-black text-slate-400">
                        {open ? "▲" : "▼"}
                    </span>
                </div>
            </div>

            {open && (
                <div className="absolute z-[70] mt-2 max-h-64 w-full overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl">
                    {availableOptions.length > 0 && (
                        availableOptions.map((item) => (
                            <button
                                key={item.id}
                                type="button"
                                onClick={() => handleSelect(item.id)}
                                className="flex w-full items-center justify-between rounded-xl px-4 py-3 text-left text-sm font-bold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
                            >
                                <span className="min-w-0">
                                    <span className="block text-slate-800">{item.nama}</span>
                                    <span className="mt-1 block text-xs font-semibold text-slate-400">
                                        {[item.jabatan?.nama || "Jabatan belum diisi", item.divisi?.nama || "Divisi belum diisi"].join(" • ")}
                                    </span>
                                    {item.no_wa && <span className="mt-0.5 block text-xs font-semibold text-indigo-600">WA: {item.no_wa}</span>}
                                </span>
                                <span className="text-xs text-slate-400">＋</span>
                            </button>
                        ))
                    )}
                    {!availableOptions.length && (!normalizedKeyword || exactInterviewer) && (
                        <div className="px-4 py-6 text-center text-sm font-bold text-slate-400">
                            {options.length === 0
                                ? "Belum ada data interviewer"
                                : "Data tidak ditemukan"}
                        </div>
                    )}
                    {normalizedKeyword && !exactInterviewer && onCreateInterviewer && (
                        <div className="mt-2 space-y-3 rounded-2xl border border-violet-200 bg-violet-50 p-3">
                            <div>
                                <p className="text-sm font-black text-violet-900">Tambah “{keyword.trim()}” sebagai interviewer</p>
                                <p className="mt-1 text-xs font-semibold text-violet-600">Lengkapi data berikut, lalu interviewer langsung dipilih.</p>
                            </div>
                            <input type="text" inputMode="tel" value={newNoWa} onChange={(event) => { setNewNoWa(event.target.value); setCreateError(""); }} placeholder="No. WhatsApp, contoh 081234567890" className="w-full rounded-xl border border-violet-200 bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-violet-500" />
                            <InlineJabatanSelect value={newJabatanId} onChange={setNewJabatanId} options={jabatanOptions} onCreate={onCreateJabatan} />
                            <button type="button" disabled={creating} onClick={handleCreateInterviewer} className="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-sm font-black text-white disabled:opacity-60">{creating ? "Menyimpan interviewer..." : "+ Simpan dan pilih interviewer"}</button>
                            {createError && <p className="rounded-lg bg-rose-50 px-3 py-2 text-xs font-bold text-rose-600">{createError}</p>}
                        </div>
                    )}
                </div>
            )}

            {required && value.length === 0 && (
                <input
                    tabIndex={-1}
                    autoComplete="off"
                    value=""
                    onChange={() => {}}
                    required
                    className="pointer-events-none absolute bottom-3 left-4 h-px w-px opacity-0"
                />
            )}
        </div>
    );
}

function InlineJabatanSelect({ value, onChange, options, onCreate }) {
    const [search, setSearch] = useState("");
    const [creating, setCreating] = useState(false);
    const [error, setError] = useState("");
    const normalized = search.trim().toLowerCase();
    const exact = options.some((item) => String(item.nama || "").trim().toLowerCase() === normalized);
    const filtered = options.filter((item) => String(item.nama || "").toLowerCase().includes(normalized));
    const create = async () => {
        if (!normalized || creating) return;
        setCreating(true); setError("");
        try { const item = await onCreate(search.trim()); onChange(item.id); setSearch(""); }
        catch (exception) { setError(exception.message || "Jabatan gagal ditambahkan."); }
        finally { setCreating(false); }
    };
    return <div className="rounded-xl border border-violet-200 bg-white p-2">
        <input value={search} onChange={(event) => { setSearch(event.target.value); setError(""); }} onKeyDown={(event) => { if (event.key === "Enter" && normalized && !exact) { event.preventDefault(); create(); } }} placeholder="Cari atau tambah jabatan..." className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold outline-none focus:border-violet-500" />
        <div className="mt-1 max-h-32 overflow-y-auto">
            {filtered.map((item) => <button key={item.id} type="button" onClick={() => { onChange(item.id); setSearch(""); }} className={`block w-full rounded-lg px-3 py-2 text-left text-sm font-bold ${String(value) === String(item.id) ? "bg-violet-600 text-white" : "text-slate-700 hover:bg-violet-50"}`}>{item.nama}</button>)}
            {normalized && !exact && <button type="button" disabled={creating} onClick={create} className="mt-1 block w-full rounded-lg bg-violet-100 px-3 py-2 text-left text-sm font-black text-violet-700">{creating ? "Menambahkan..." : `+ Tambah jabatan “${search.trim()}”`}</button>}
        </div>
        {value && <p className="mt-1 px-2 text-xs font-bold text-emerald-600">Jabatan terpilih: {options.find((item) => String(item.id) === String(value))?.nama}</p>}
        {error && <p className="mt-1 px-2 text-xs font-bold text-rose-600">{error}</p>}
    </div>;
}
