import React, { useEffect, useMemo, useRef, useState } from "react";

const emptyForm = {
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role_id: "",
    divisi_id: "",
    email_verified_at: "",
};

export default function UserPage({ actionSignals }) {
    const firstActionSignalRender = useRef(true);

    const [dataUser, setDataUser] = useState([]);
    const [dataRole, setDataRole] = useState([]);
    const [dataDivisi, setDataDivisi] = useState([]);

    const [modalOpen, setModalOpen] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [loading, setLoading] = useState(false);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    const [selectedItem, setSelectedItem] = useState(null);
    const [form, setForm] = useState(emptyForm);

    const isEdit = Boolean(selectedItem?.id);

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const resetForm = () => {
        setForm({
            name: "",
            email: "",
            password: "",
            password_confirmation: "",
            role_id: "",
            divisi_id: "",
            email_verified_at: "",
        });
    };

    const formatDateTimeLocal = (value) => {
        if (!value) return "";

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return "";
        }

        const pad = (number) => String(number).padStart(2, "0");

        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(
            date.getDate()
        )}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    };

    const openCreateModal = () => {
        setSelectedItem(null);
        resetForm();
        setModalOpen(true);
    };

    const openEditModal = (item) => {
        setSelectedItem(item);

        setForm({
            name: item.name || "",
            email: item.email || "",
            password: "",
            password_confirmation: "",
            role_id: item.role_id ? String(item.role_id) : "",
            divisi_id: item.divisi_id ? String(item.divisi_id) : "",
            email_verified_at: formatDateTimeLocal(item.email_verified_at),
        });

        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setSelectedItem(null);
        resetForm();
    };

    const fetchData = async () => {
        setTableLoading(true);

        try {
            const response = await fetch("/admin/account/user/list", {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                setDataUser(result.data || []);
            } else {
                alert(result.message || "Gagal mengambil data user.");
            }
        } catch (error) {
            console.error("Gagal mengambil data user:", error);
            alert("Terjadi kesalahan saat mengambil data user.");
        } finally {
            setTableLoading(false);
        }
    };

    const fetchOptions = async () => {
        try {
            const response = await fetch("/admin/account/user/options", {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                setDataRole(result.data?.roles || []);
                setDataDivisi(result.data?.divisi || []);
            } else {
                alert(result.message || "Gagal mengambil data role/divisi.");
            }
        } catch (error) {
            console.error("Gagal mengambil data role/divisi:", error);
            alert("Terjadi kesalahan saat mengambil data role/divisi.");
        }
    };

    useEffect(() => {
        fetchData();
        fetchOptions();
    }, []);

    useEffect(() => {
        if (firstActionSignalRender.current) {
            firstActionSignalRender.current = false;
            return;
        }

        if (actionSignals?.accountUser > 0) {
            openCreateModal();
        }
    }, [actionSignals?.accountUser]);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage]);

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) {
            return dataUser;
        }

        return dataUser.filter((item) => {
            const name = String(item.name || "").toLowerCase();
            const email = String(item.email || "").toLowerCase();
            const role = String(item.role_label || "").toLowerCase();
            const roleKode = String(item.role_kode || "").toLowerCase();
            const divisi = String(item.divisi_label || "").toLowerCase();
            const verified = item.email_verified_at
                ? "verified terverifikasi"
                : "belum verified belum terverifikasi";

            return (
                name.includes(keyword) ||
                email.includes(keyword) ||
                role.includes(keyword) ||
                roleKode.includes(keyword) ||
                divisi.includes(keyword) ||
                verified.includes(keyword)
            );
        });
    }, [dataUser, search]);

    const totalPages = Math.max(
        1,
        Math.ceil(filteredData.length / entriesPerPage)
    );

    useEffect(() => {
        setCurrentPage((prev) => Math.min(prev, totalPages));
    }, [totalPages]);

    const paginatedData = useMemo(() => {
        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = startIndex + entriesPerPage;

        return filteredData.slice(startIndex, endIndex);
    }, [filteredData, currentPage, entriesPerPage]);

    const showingFrom =
        filteredData.length === 0 ? 0 : (currentPage - 1) * entriesPerPage + 1;

    const showingTo = Math.min(
        currentPage * entriesPerPage,
        filteredData.length
    );

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

    const handlePreviousPage = () => {
        setCurrentPage((prev) => Math.max(prev - 1, 1));
    };

    const handleNextPage = () => {
        setCurrentPage((prev) => Math.min(prev + 1, totalPages));
    };

    const handleChange = (event) => {
        const { name, value } = event.target;

        setForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const buildPayload = () => {
        const payload = {
            name: form.name,
            email: form.email,
            role_id: form.role_id,
            divisi_id: form.divisi_id || null,
            email_verified_at: form.email_verified_at || null,
        };

        if (form.password) {
            payload.password = form.password;
            payload.password_confirmation = form.password_confirmation;
        }

        if (!isEdit) {
            payload.password = form.password;
            payload.password_confirmation = form.password_confirmation;
        }

        return payload;
    };

    const getFirstErrorMessage = (errors) => {
        if (!errors) return null;

        const firstError = Object.values(errors)?.[0];

        if (Array.isArray(firstError)) {
            return firstError[0];
        }

        return firstError;
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        if (form.password || form.password_confirmation) {
            if (form.password !== form.password_confirmation) {
                alert("Konfirmasi password tidak sama.");
                return;
            }
        }

        setLoading(true);

        const url = isEdit
            ? `/admin/account/user/${selectedItem.id}`
            : "/admin/account/user";

        const method = isEdit ? "PUT" : "POST";

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify(buildPayload()),
            });

            const result = await response.json();

            if (!response.ok) {
                alert(
                    getFirstErrorMessage(result.errors) ||
                        result.message ||
                        "Data user gagal disimpan."
                );
                return;
            }

            alert(result.message || "Data user berhasil disimpan.");
            closeModal();
            fetchData();
        } catch (error) {
            console.error("Gagal menyimpan data user:", error);
            alert("Terjadi kesalahan saat menyimpan data user.");
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (item) => {
        const confirmDelete = confirm(`Yakin ingin menghapus user "${item.name}"?`);

        if (!confirmDelete) return;

        try {
            const response = await fetch(`/admin/account/user/${item.id}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert(result.message || "User berhasil dihapus.");
                fetchData();
            } else {
                alert(result.message || "User gagal dihapus.");
            }
        } catch (error) {
            console.error("Gagal menghapus user:", error);
            alert("Terjadi kesalahan saat menghapus user.");
        }
    };

    const formatDate = (value) => {
        if (!value) return "-";

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    };

    const verifiedBadgeClass = (value) => {
        if (value) {
            return "bg-teal-100 text-teal-700";
        }

        return "bg-rose-100 text-rose-700";
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
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Cari nama, email, role, divisi..."
                                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100 md:w-96"
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
                                <TableHead>Nama</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Divisi</TableHead>
                                <TableHead>Email Verified</TableHead>
                                <TableHead>Dibuat</TableHead>
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
                                            <div className="text-sm font-black text-slate-800">
                                                {item.name || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-bold text-slate-700">
                                                {item.email || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-700">
                                                {item.role_label || "-"}
                                            </div>

                                            <div className="mt-1 text-xs font-semibold text-slate-500">
                                                {item.role_kode || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-bold text-slate-700">
                                                {item.divisi_label || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <span
                                                className={`inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ${verifiedBadgeClass(
                                                    item.email_verified_at
                                                )}`}
                                            >
                                                {item.email_verified_at
                                                    ? "Verified"
                                                    : "Belum Verified"}
                                            </span>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-semibold text-slate-600">
                                                {formatDate(item.created_at)}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5 text-right">
                                            <div className="flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => openEditModal(item)}
                                                    className="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(item)}
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
                                                ◉
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data user tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Klik tombol Tambah User di bagian atas untuk menambahkan data.
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
                        {filteredData.length} entries
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
                <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
                    <div className="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div className="shrink-0 border-b border-slate-200 bg-white">
                            <div className="flex items-center justify-between gap-4 px-6 py-5">
                                <div>
                                    <div className="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                                        User
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        {isEdit ? "Edit User" : "Tambah User"}
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Lengkapi data user, role, dan divisi.
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
                                <div className="grid gap-5 md:grid-cols-2">
                                    <Input
                                        label="Nama"
                                        name="name"
                                        value={form.name}
                                        onChange={handleChange}
                                        required
                                        placeholder="Masukkan nama user"
                                    />

                                    <Input
                                        label="Email"
                                        type="email"
                                        name="email"
                                        value={form.email}
                                        onChange={handleChange}
                                        required
                                        placeholder="Masukkan email user"
                                    />

                                    <Input
                                        label={isEdit ? "Password Baru" : "Password"}
                                        type="password"
                                        name="password"
                                        value={form.password}
                                        onChange={handleChange}
                                        required={!isEdit}
                                        placeholder={
                                            isEdit
                                                ? "Kosongkan jika tidak diganti"
                                                : "Masukkan password"
                                        }
                                    />

                                    <Input
                                        label="Konfirmasi Password"
                                        type="password"
                                        name="password_confirmation"
                                        value={form.password_confirmation}
                                        onChange={handleChange}
                                        required={!isEdit}
                                        placeholder={
                                            isEdit
                                                ? "Kosongkan jika password tidak diganti"
                                                : "Ulangi password"
                                        }
                                    />

                                    <div>
                                        <label className="mb-2 block text-sm font-black text-slate-700">
                                            Role <span className="text-rose-500">*</span>
                                        </label>

                                        <select
                                            name="role_id"
                                            value={form.role_id}
                                            onChange={handleChange}
                                            required
                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                                        >
                                            <option value="">Pilih Role</option>

                                            {dataRole.map((role) => (
                                                <option key={role.id} value={role.id}>
                                                    {role.nama_role}
                                                    {role.kode_role
                                                        ? ` - ${role.kode_role}`
                                                        : ""}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-black text-slate-700">
                                            Divisi
                                        </label>

                                        <select
                                            name="divisi_id"
                                            value={form.divisi_id}
                                            onChange={handleChange}
                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                                        >
                                            <option value="">Pilih Divisi</option>

                                            {dataDivisi.map((item) => (
                                                <option key={item.id} value={item.id}>
                                                    {item.nama}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="md:col-span-2">
                                        <label className="mb-2 block text-sm font-black text-slate-700">
                                            Email Verified At
                                        </label>

                                        <input
                                            type="datetime-local"
                                            name="email_verified_at"
                                            value={form.email_verified_at}
                                            onChange={handleChange}
                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                                        />

                                        <p className="mt-2 text-xs font-semibold text-slate-400">
                                            Kosongkan jika email belum diverifikasi.
                                        </p>
                                    </div>
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
                                        disabled={loading}
                                        className="rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-teal-100 transition hover:from-teal-700 hover:to-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {loading
                                            ? "Menyimpan..."
                                            : isEdit
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