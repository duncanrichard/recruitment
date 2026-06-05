import React, { useEffect, useMemo, useRef, useState } from "react";

export default function JadwalInterviewPage({ actionSignals }) {
    const [dataJadwalInterview, setDataJadwalInterview] = useState([]);
    const [dataInterviewers, setDataInterviewers] = useState([]);

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
            } else {
                alert(result.message || "Gagal mengambil data interviewer.");
            }
        } catch (error) {
            console.error("Gagal mengambil data interviewer:", error);
            alert("Terjadi kesalahan saat mengambil data interviewer.");
        }
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
                                placeholder="Cari jadwal interview..."
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
                    <div className="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-visible rounded-[2rem] bg-white shadow-2xl">
                        <div className="shrink-0 border-b border-slate-200 bg-white">
                            <div className="flex items-center justify-between gap-4 px-6 py-5">
                                <div>
                                    <div className="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
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
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
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
}) {
    const wrapperRef = useRef(null);
    const inputRef = useRef(null);

    const [open, setOpen] = useState(false);
    const [keyword, setKeyword] = useState("");

    const selectedOptions = useMemo(() => {
        return options.filter((item) => value.includes(item.id));
    }, [options, value]);

    const availableOptions = useMemo(() => {
        const lowerKeyword = keyword.toLowerCase().trim();

        return options.filter((item) => {
            const isSelected = value.includes(item.id);
            const matchKeyword = String(item.nama || "")
                .toLowerCase()
                .includes(lowerKeyword);

            return !isSelected && matchKeyword;
        });
    }, [options, value, keyword]);

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
                        ? "border-teal-500 ring-4 ring-teal-100"
                        : "border-slate-200"
                }`}
            >
                <div className="flex flex-wrap items-center gap-2">
                    {selectedOptions.map((item) => (
                        <span
                            key={item.id}
                            className="inline-flex items-center gap-2 rounded-xl bg-teal-50 px-3 py-1.5 text-xs font-black text-teal-700"
                        >
                            {item.nama}

                            <button
                                type="button"
                                onClick={(event) => {
                                    event.stopPropagation();
                                    handleRemove(item.id);
                                }}
                                className="rounded-full text-sm leading-none text-teal-700 hover:text-rose-600"
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
                    {availableOptions.length > 0 ? (
                        availableOptions.map((item) => (
                            <button
                                key={item.id}
                                type="button"
                                onClick={() => handleSelect(item.id)}
                                className="flex w-full items-center justify-between rounded-xl px-4 py-3 text-left text-sm font-bold text-slate-700 transition hover:bg-teal-50 hover:text-teal-700"
                            >
                                <span>{item.nama}</span>
                                <span className="text-xs text-slate-400">＋</span>
                            </button>
                        ))
                    ) : (
                        <div className="px-4 py-6 text-center text-sm font-bold text-slate-400">
                            {options.length === 0
                                ? "Belum ada data interviewer"
                                : "Data tidak ditemukan"}
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