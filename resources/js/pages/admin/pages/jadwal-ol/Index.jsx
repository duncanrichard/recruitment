import React, { useEffect, useMemo, useRef, useState } from "react";

export default function JadwalOlPage({ actionSignals }) {
    const [dataJadwal, setDataJadwal] = useState([]);
    const [dataKandidat, setDataKandidat] = useState([]);

    const [modalOpen, setModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [editId, setEditId] = useState(null);
    const [statusLoadingId, setStatusLoadingId] = useState(null);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    const [sortConfig, setSortConfig] = useState({
        key: "tanggal_ol",
        direction: "desc",
    });

    const [form, setForm] = useState({
        hasil_review_management_id: "",
        tanggal_ol: "",
        jam_ol: "",
        metode: "",
        link: "",
        pic: "",
        catatan: "",
    });

    const statusOptions = [
        { id: "Menerima", label: "Menerima" },
        { id: "Menolak", label: "Menolak" },
        { id: "Tidak Melanjutkan", label: "Tidak Melanjutkan" },
    ];

    const lastSignalRef = useRef(actionSignals?.jadwalOl || 0);

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const resetForm = () => {
        setEditId(null);

        setForm({
            hasil_review_management_id: "",
            tanggal_ol: "",
            jam_ol: "",
            metode: "",
            link: "",
            pic: "",
            catatan: "",
        });
    };

    const fetchData = async () => {
        setTableLoading(true);

        try {
            const response = await fetch("/admin/jadwal-ol/list", {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (result.success) {
                setDataJadwal(result.data || []);
            } else {
                alert(result.message || "Gagal mengambil data Jadwal OL.");
            }
        } catch (error) {
            console.error("Gagal mengambil data Jadwal OL:", error);
            alert("Terjadi kesalahan saat mengambil data Jadwal OL.");
        } finally {
            setTableLoading(false);
        }
    };

    const fetchKandidat = async () => {
        try {
            const response = await fetch("/admin/jadwal-ol/candidates", {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (result.success) {
                setDataKandidat(result.data || []);
            } else {
                alert(result.message || "Gagal mengambil kandidat diterima.");
            }
        } catch (error) {
            console.error("Gagal mengambil kandidat diterima:", error);
            alert("Terjadi kesalahan saat mengambil kandidat diterima.");
        }
    };

    useEffect(() => {
        fetchData();
        fetchKandidat();
    }, []);

    useEffect(() => {
        const currentSignal = actionSignals?.jadwalOl || 0;

        if (currentSignal > lastSignalRef.current) {
            resetForm();
            fetchKandidat();
            setModalOpen(true);
        }

        lastSignalRef.current = currentSignal;
    }, [actionSignals?.jadwalOl]);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage]);

    const handleChange = (event) => {
        const { name, value } = event.target;

        setForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const closeModal = () => {
        setModalOpen(false);
        resetForm();
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setLoading(true);

        try {
            const url = editId
                ? `/admin/jadwal-ol/${editId}`
                : "/admin/jadwal-ol";

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
                alert(result.message || "Data Jadwal OL gagal disimpan.");
                return;
            }

            alert(result.message || "Data Jadwal OL berhasil disimpan.");
            closeModal();
            fetchData();
            fetchKandidat();
        } catch (error) {
            console.error("Gagal menyimpan Jadwal OL:", error);
            alert("Terjadi kesalahan saat menyimpan Jadwal OL.");
        } finally {
            setLoading(false);
        }
    };

    const handleEdit = (item) => {
        setEditId(item.id);

        setForm({
            hasil_review_management_id: item.hasil_review_management_id || "",
            tanggal_ol: item.tanggal_ol || "",
            jam_ol: item.jam_ol || "",
            metode: item.metode || "",
            link: item.link || "",
            pic: item.pic || "",
            catatan: item.catatan || "",
        });

        fetchKandidat();
        setModalOpen(true);
    };

    const handleStatusChange = async (item, statusValue) => {
        setStatusLoadingId(item.id);

        try {
            const response = await fetch(`/admin/jadwal-ol/${item.id}/status`, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify({
                    status_jadwal: statusValue,
                }),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Status OL gagal diperbarui.");
                return;
            }

            setDataJadwal((prev) =>
                prev.map((row) =>
                    row.id === item.id
                        ? {
                              ...row,
                              status_jadwal: statusValue,
                          }
                        : row
                )
            );
        } catch (error) {
            console.error("Gagal memperbarui Status OL:", error);
            alert("Terjadi kesalahan saat memperbarui Status OL.");
        } finally {
            setStatusLoadingId(null);
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin ingin menghapus Jadwal OL ini?");

        if (!confirmDelete) return;

        try {
            const response = await fetch(`/admin/jadwal-ol/${id}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message || "Jadwal OL berhasil dihapus.");
                fetchData();
                fetchKandidat();
            } else {
                alert(result.message || "Jadwal OL gagal dihapus.");
            }
        } catch (error) {
            console.error("Gagal menghapus Jadwal OL:", error);
            alert("Terjadi kesalahan saat menghapus Jadwal OL.");
        }
    };

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) {
            return dataJadwal;
        }

        return dataJadwal.filter((item) => {
            const kandidat = String(item.kandidat_label || "").toLowerCase();
            const tanggal = String(item.tanggal_ol || "").toLowerCase();
            const jam = String(item.jam_ol || "").toLowerCase();
            const metode = String(item.metode || "").toLowerCase();
            const pic = String(item.pic || "").toLowerCase();
            const status = String(item.status_jadwal || "").toLowerCase();

            return (
                kandidat.includes(keyword) ||
                tanggal.includes(keyword) ||
                jam.includes(keyword) ||
                metode.includes(keyword) ||
                pic.includes(keyword) ||
                status.includes(keyword)
            );
        });
    }, [dataJadwal, search]);

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

    const kandidatOptions = useMemo(() => {
        const selectedCurrent = dataJadwal
            .filter(
                (item) =>
                    item.hasil_review_management_id ===
                    form.hasil_review_management_id
            )
            .map((item) => ({
                id: item.hasil_review_management_id,
                label: item.kandidat_label,
            }));

        const merged = [...selectedCurrent, ...dataKandidat];

        return merged.filter(
            (item, index, self) =>
                index === self.findIndex((value) => value.id === item.id)
        );
    }, [dataJadwal, dataKandidat, form.hasil_review_management_id]);

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
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Cari Jadwal OL..."
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
                                    label="Kandidat"
                                    sortKey="kandidat_label"
                                    onSort={handleSort}
                                    icon={sortIcon("kandidat_label")}
                                />

                                <SortableTableHead
                                    label="Tanggal"
                                    sortKey="tanggal_ol"
                                    onSort={handleSort}
                                    icon={sortIcon("tanggal_ol")}
                                />

                                <SortableTableHead
                                    label="Jam"
                                    sortKey="jam_ol"
                                    onSort={handleSort}
                                    icon={sortIcon("jam_ol")}
                                />

                                <SortableTableHead
                                    label="Metode"
                                    sortKey="metode"
                                    onSort={handleSort}
                                    icon={sortIcon("metode")}
                                />

                                <SortableTableHead
                                    label="PIC"
                                    sortKey="pic"
                                    onSort={handleSort}
                                    icon={sortIcon("pic")}
                                />

                                <SortableTableHead
                                    label="Status OL"
                                    sortKey="status_jadwal"
                                    onSort={handleSort}
                                    icon={sortIcon("status_jadwal")}
                                />

                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <td colSpan="8" className="px-6 py-16">
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
                                            <div className="max-w-md text-sm font-black text-slate-900">
                                                {item.kandidat_label || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {item.tanggal_ol || "-"}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {item.jam_ol || "-"}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {item.metode || "-"}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {item.pic || "-"}
                                        </td>

                                        <td className="px-6 py-5">
                                            <select
                                                value={item.status_jadwal || ""}
                                                disabled={statusLoadingId === item.id}
                                                onChange={(event) =>
                                                    handleStatusChange(
                                                        item,
                                                        event.target.value
                                                    )
                                                }
                                                className="min-w-48 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 disabled:cursor-not-allowed disabled:opacity-60"
                                            >
                                                <option value="">
                                                    Pilih Status OL
                                                </option>

                                                {statusOptions.map((status) => (
                                                    <option
                                                        key={status.id}
                                                        value={status.id}
                                                    >
                                                        {status.label}
                                                    </option>
                                                ))}
                                            </select>
                                        </td>

                                        <td className="px-6 py-5 text-right">
                                            <div className="flex justify-end gap-2">
                                                {item.link && (
                                                    <a
                                                        href={item.link}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-xs font-black text-emerald-600 shadow-sm transition hover:bg-emerald-50"
                                                    >
                                                        Link
                                                    </a>
                                                )}

                                                <button
                                                    type="button"
                                                    onClick={() => handleEdit(item)}
                                                    className="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(item.id)}
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
                                    <td colSpan="8" className="px-6 py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ◷
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Jadwal OL tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Belum ada Jadwal Offering Letter atau kata kunci pencarian tidak cocok.
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
                    <div className="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div className="shrink-0 border-b border-slate-200 bg-white">
                            <div className="flex items-center justify-between gap-4 px-6 py-5">
                                <div>
                                    <div className="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                                        Form Jadwal OL
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        {editId
                                            ? "Edit Jadwal OL"
                                            : "Tambah Jadwal OL"}
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Jadwalkan Offering Letter untuk kandidat yang diterima.
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
                            <div className="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">
                                <SelectInput
                                    label="Kandidat Diterima"
                                    name="hasil_review_management_id"
                                    value={form.hasil_review_management_id}
                                    onChange={handleChange}
                                    options={kandidatOptions}
                                    placeholder="Pilih kandidat diterima"
                                    required
                                />

                                <div className="grid gap-5 md:grid-cols-2">
                                    <Input
                                        label="Tanggal OL"
                                        name="tanggal_ol"
                                        type="date"
                                        value={form.tanggal_ol}
                                        onChange={handleChange}
                                        required
                                    />

                                    <Input
                                        label="Jam OL"
                                        name="jam_ol"
                                        type="time"
                                        value={form.jam_ol}
                                        onChange={handleChange}
                                        required
                                    />
                                </div>

                                <div className="grid gap-5 md:grid-cols-2">
                                    <SelectInput
                                        label="Metode"
                                        name="metode"
                                        value={form.metode}
                                        onChange={handleChange}
                                        options={[
                                            { id: "Online", label: "Online" },
                                            { id: "Offline", label: "Offline" },
                                        ]}
                                        placeholder="Pilih metode"
                                        required
                                    />

                                    <Input
                                        label="PIC"
                                        name="pic"
                                        value={form.pic}
                                        onChange={handleChange}
                                        placeholder="Nama PIC"
                                    />
                                </div>

                                {form.metode === "Online" && (
                                    <Input
                                        label="Link"
                                        name="link"
                                        value={form.link}
                                        onChange={handleChange}
                                        placeholder="Link meeting jika online"
                                    />
                                )}

                                <Textarea
                                    label="Catatan"
                                    name="catatan"
                                    value={form.catatan}
                                    onChange={handleChange}
                                    placeholder="Catatan tambahan"
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
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />
        </div>
    );
}

function SelectInput({
    label,
    name,
    value,
    onChange,
    options,
    placeholder = "Pilih data",
    required = false,
}) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <select
                name={name}
                value={value}
                onChange={onChange}
                required={required}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">{placeholder}</option>

                {options.map((item) => (
                    <option key={item.id} value={item.id}>
                        {item.label || item.nama || item.id}
                    </option>
                ))}
            </select>
        </div>
    );
}