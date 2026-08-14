import React, { useEffect, useMemo, useRef, useState } from "react";

const emptyForm = {
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role_id: "",
    divisi_id: "",
    perusahaan_ids: [],
    email_verified_at: "",
};

export default function UserPage({ actionSignals }) {
    const firstActionSignalRender = useRef(true);

    const [dataUser, setDataUser] = useState([]);
    const [dataRole, setDataRole] = useState([]);
    const [dataDivisi, setDataDivisi] = useState([]);
    const [dataPerusahaan, setDataPerusahaan] = useState([]);

    const [modalOpen, setModalOpen] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [loading, setLoading] = useState(false);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    const [selectedItem, setSelectedItem] = useState(null);
    const [form, setForm] = useState(emptyForm);

    const isEdit = Boolean(selectedItem?.id);

    const scrollToTop = () => {
        window.scrollTo({
            top: 0,
            left: 0,
            behavior: "auto",
        });

        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;

        const adminContent = document.getElementById("admin-content");

        if (adminContent) {
            adminContent.scrollTo({
                top: 0,
                left: 0,
                behavior: "auto",
            });
        }

        const mainContent = document.querySelector("main");

        if (mainContent) {
            mainContent.scrollTo({
                top: 0,
                left: 0,
                behavior: "auto",
            });
        }
    };

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const getTodayDateTimeLocal = () => {
        const date = new Date();
        const pad = (number) => String(number).padStart(2, "0");

        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(
            date.getDate()
        )}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    };

    const resetForm = () => {
        setForm({
            ...emptyForm,
            email_verified_at: getTodayDateTimeLocal(),
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

    const getRoleIdFromItem = (item) => {
        return String(
            item?.role_id ||
                item?.role?.id ||
                item?.roles?.[0]?.id ||
                ""
        );
    };

    const getRoleName = (role) => {
        return role?.name || role?.nama_role || role?.role_label || "-";
    };

    const getRoleGuard = (role) => {
        return role?.guard_name || role?.kode_role || role?.role_guard || "web";
    };

    const getPerusahaanIdsFromItem = (item) => {
        if (Array.isArray(item?.perusahaan_ids)) {
            return item.perusahaan_ids.map((id) => String(id));
        }

        if (Array.isArray(item?.perusahaans)) {
            return item.perusahaans
                .map((perusahaan) => perusahaan?.id)
                .filter(Boolean)
                .map((id) => String(id));
        }

        if (item?.perusahaan_id) {
            return [String(item.perusahaan_id)];
        }

        return [];
    };

    const roleOptions = useMemo(() => {
        const options = [...dataRole];

        if (selectedItem?.role_id) {
            const selectedRoleExists = options.some(
                (role) => String(role.id) === String(selectedItem.role_id)
            );

            if (!selectedRoleExists) {
                options.unshift({
                    id: selectedItem.role_id,
                    name: selectedItem.role_label || "Role saat ini",
                    guard_name:
                        selectedItem.role_guard ||
                        selectedItem.role_kode ||
                        "web",
                });
            }
        }

        return options;
    }, [dataRole, selectedItem]);

    const openCreateModal = () => {
        scrollToTop();
        setSelectedItem(null);
        resetForm();
        setModalOpen(true);
    };

    const openEditModal = (item) => {
        scrollToTop();

        const roleId = getRoleIdFromItem(item);

        setSelectedItem({
            ...item,
            role_id: roleId,
        });

        setForm({
            name: item.name || "",
            email: item.email || "",
            password: "",
            password_confirmation: "",
            role_id: roleId,
            divisi_id: item.divisi_id ? String(item.divisi_id) : "",
            perusahaan_ids: getPerusahaanIdsFromItem(item),
            email_verified_at: item.email_verified_at
                ? formatDateTimeLocal(item.email_verified_at)
                : getTodayDateTimeLocal(),
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
                    "X-Requested-With": "XMLHttpRequest",
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
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                setDataRole(result.data?.roles || []);
                setDataDivisi(result.data?.divisi || []);
                setDataPerusahaan(result.data?.perusahaan || []);
            } else {
                alert(result.message || "Gagal mengambil data role/divisi/perusahaan.");
            }
        } catch (error) {
            console.error("Gagal mengambil data role/divisi/perusahaan:", error);
            alert("Terjadi kesalahan saat mengambil data role/divisi/perusahaan.");
        }
    };

    useEffect(() => {
        scrollToTop();
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
            const roleGuard = String(
                item.role_guard || item.role_kode || ""
            ).toLowerCase();
            const divisi = String(item.divisi_label || "").toLowerCase();
            const perusahaan = String(item.perusahaan_label || "").toLowerCase();
            const perusahaanKode = String(item.perusahaan_kode || "").toLowerCase();
            const verified = item.email_verified_at
                ? "verified terverifikasi"
                : "belum verified belum terverifikasi";

            return (
                name.includes(keyword) ||
                email.includes(keyword) ||
                role.includes(keyword) ||
                roleGuard.includes(keyword) ||
                divisi.includes(keyword) ||
                perusahaan.includes(keyword) ||
                perusahaanKode.includes(keyword) ||
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

    const handlePerusahaanChange = (values) => {
        setForm((prev) => ({
            ...prev,
            perusahaan_ids: values,
        }));
    };

    const buildPayload = () => {
        const payload = {
            name: form.name,
            email: form.email,
            role_id: form.role_id,
            divisi_id: form.divisi_id || null,
            perusahaan_ids: Array.isArray(form.perusahaan_ids)
                ? form.perusahaan_ids
                : [],
            email_verified_at: form.email_verified_at || null,
        };

        if (!isEdit || form.password) {
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

        if (!form.role_id) {
            alert("Role wajib dipilih.");
            return;
        }

        if (!isEdit && !form.password) {
            alert("Password wajib diisi.");
            return;
        }

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
                    "X-Requested-With": "XMLHttpRequest",
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
            scrollToTop();
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
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert(result.message || "User berhasil dihapus.");
                fetchData();
                scrollToTop();
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
            return "bg-indigo-100 text-indigo-700";
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
                                    placeholder="Cari nama, email, role, divisi, perusahaan..."
                                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 md:w-[28rem]"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-max w-full table-auto whitespace-nowrap">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Nama</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Divisi</TableHead>
                                <TableHead>Perusahaan</TableHead>
                                <TableHead>Email Verified</TableHead>
                                <TableHead>Dibuat</TableHead>
                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <td colSpan="9" className="px-6 py-16">
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
                                                {item.role_guard ||
                                                    item.role_kode ||
                                                    "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-bold text-slate-700">
                                                {item.divisi_label || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="max-w-[360px] whitespace-normal text-sm font-bold text-slate-700">
                                                {item.perusahaan_label || "-"}
                                            </div>

                                            {item.perusahaan_kode && (
                                                <div className="mt-1 max-w-[360px] whitespace-normal text-xs font-semibold text-slate-500">
                                                    {item.perusahaan_kode}
                                                </div>
                                            )}
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
                                                    onClick={() =>
                                                        openEditModal(item)
                                                    }
                                                    className="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        handleDelete(item)
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
                                    <td colSpan="9" className="px-6 py-16">
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
                                    <div className="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                                        User
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        {isEdit ? "Edit User" : "Tambah User"}
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Lengkapi data user, role, divisi, dan perusahaan.
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
                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                        >
                                            <option value="">Pilih Role</option>

                                            {roleOptions.map((role) => (
                                                <option
                                                    key={role.id}
                                                    value={String(role.id)}
                                                >
                                                    {getRoleName(role)}
                                                    {getRoleGuard(role)
                                                        ? ` - ${getRoleGuard(role)}`
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
                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                        >
                                            <option value="">Pilih Divisi</option>

                                            {dataDivisi.map((item) => (
                                                <option
                                                    key={item.id}
                                                    value={String(item.id)}
                                                >
                                                    {item.nama}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="md:col-span-2">
                                        <CustomMultiSelect
                                            label="Perusahaan"
                                            value={form.perusahaan_ids}
                                            options={dataPerusahaan.map((item) => ({
                                                id: String(item.id),
                                                nama:
                                                    item.label ||
                                                    `${item.kode ? `${item.kode} - ` : ""}${item.nama_perusahaan}`,
                                            }))}
                                            onChange={handlePerusahaanChange}
                                            placeholder="Pilih perusahaan..."
                                        />

                                        <p className="mt-2 text-xs font-semibold text-slate-400">
                                            Bisa pilih lebih dari satu perusahaan.
                                        </p>
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
                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                        />

                                        <p className="mt-2 text-xs font-semibold text-slate-400">
                                            Default terisi tanggal hari ini. Kosongkan jika email belum diverifikasi.
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
                                        className="rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-100 transition hover:from-indigo-700 hover:to-violet-700 disabled:cursor-not-allowed disabled:opacity-60"
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

function CustomMultiSelect({
    label,
    value = [],
    options = [],
    onChange,
    placeholder = "Pilih data...",
    required = false,
}) {
    const wrapperRef = useRef(null);
    const inputRef = useRef(null);

    const [open, setOpen] = useState(false);
    const [keyword, setKeyword] = useState("");

    const normalizedValue = useMemo(() => {
        return Array.isArray(value) ? value.map((item) => String(item)) : [];
    }, [value]);

    const selectedOptions = useMemo(() => {
        return options.filter((item) =>
            normalizedValue.includes(String(item.id))
        );
    }, [options, normalizedValue]);

    const availableOptions = useMemo(() => {
        const lowerKeyword = keyword.toLowerCase().trim();

        return options.filter((item) => {
            const id = String(item.id);
            const name = String(item.nama || "");

            const isSelected = normalizedValue.includes(id);
            const matchKeyword = name.toLowerCase().includes(lowerKeyword);

            return !isSelected && matchKeyword;
        });
    }, [options, normalizedValue, keyword]);

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
        const selectedId = String(id);

        if (!normalizedValue.includes(selectedId)) {
            onChange([...normalizedValue, selectedId]);
        }

        setKeyword("");
        setOpen(true);

        setTimeout(() => {
            inputRef.current?.focus();
        }, 0);
    };

    const handleRemove = (id) => {
        const selectedId = String(id);

        onChange(
            normalizedValue.filter((itemId) => String(itemId) !== selectedId)
        );
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
                            {item.nama}

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

                    {normalizedValue.length > 0 && (
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
                <div className="absolute z-[99999] mt-2 max-h-64 w-full overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl">
                    {availableOptions.length > 0 ? (
                        availableOptions.map((item) => (
                            <button
                                key={item.id}
                                type="button"
                                onClick={() => handleSelect(item.id)}
                                className="flex w-full items-center justify-between rounded-xl px-4 py-3 text-left text-sm font-bold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
                            >
                                <span>{item.nama}</span>
                                <span className="text-xs text-slate-400">＋</span>
                            </button>
                        ))
                    ) : (
                        <div className="px-4 py-6 text-center text-sm font-bold text-slate-400">
                            {options.length === 0
                                ? "Belum ada data perusahaan"
                                : "Data tidak ditemukan"}
                        </div>
                    )}
                </div>
            )}

            {required && normalizedValue.length === 0 && (
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
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />
        </div>
    );
}