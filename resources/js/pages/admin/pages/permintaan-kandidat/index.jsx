import React, { useEffect, useMemo, useRef, useState } from "react";

const TIPE_PEKERJAAN_OPTIONS = [
    "Kontrak",
    "Tetap",
    "Paruh Waktu",
    "Magang",
    "Freelance",
    "Lainnya",
];

const JENIS_KELAMIN_OPTIONS = [
    "Laki-laki",
    "Perempuan",
    "Laki-laki / Perempuan",
];

const URGENT_OPTIONS = ["Rendah", "Sedang", "Tinggi", "Sangat Urgent"];

const ALASAN_OPTIONS = [
    "Penggantian",
    "Baru Divisi",
    "Penambahan Karyawan",
    "Lainnya",
];

const STATUS_OPTIONS = [
    "Draft",
    "Diajukan",
    "Diproses",
    "Selesai",
    "Dibatalkan",
];

const defaultForm = {
    pt_membutuhkan: "PT. Derma Sembilan Indonesia",
    divisi_departemen: "",
    permintaan_oleh: "",
    tanggal_permintaan: "",
    deskripsi_permintaan: "",

    nama_posisi_jabatan: "",
    jumlah_karyawan: 1,
    lokasi_kerja: "Holding",

    tipe_pekerjaan: "",
    jadwal_kerja: "",
    deskripsi_pekerjaan: "",
    gaji_benefit: "",

    pendidikan_minimum: "",
    usia: "",
    jenis_kelamin: "",
    pengalaman_kerja: "",
    keterampilan_teknis: "",
    keterampilan_interpersonal: "",
    syarat_khusus: "",
    keahlian_khusus: "",
    sertifikat: "",

    tanggal_mulai_diperlukan: "",
    urgent_permintaan: "",
    alasan_permintaan: "",

    karakter_pribadi: "",
    hasil_test_tertulis: "",
    permintaan_khusus: "",
    karakter_profesional: "",

    proses_seleksi: "",
    materi_ppt: "",
    informasi_tambahan: "",
    penyebaran_iklan: "",

    status_permintaan: "Diajukan",
};

export default function PermintaanKandidatPage({ actionSignals }) {
    const [dataList, setDataList] = useState([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [detailOpen, setDetailOpen] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);

    const [tableLoading, setTableLoading] = useState(false);
    const [loading, setLoading] = useState(false);

    const [editId, setEditId] = useState(null);
    const [form, setForm] = useState(defaultForm);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    const [tanggalMulai, setTanggalMulai] = useState("");
    const [tanggalSelesai, setTanggalSelesai] = useState("");
    const [statusFilter, setStatusFilter] = useState("");
    const [urgentFilter, setUrgentFilter] = useState("");

    const lastSignalRef = useRef(actionSignals?.permintaanKandidat || 0);

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const getTodayDate = () => {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    };

    const resetForm = () => {
        setEditId(null);
        setForm({
            ...defaultForm,
            tanggal_permintaan: getTodayDate(),
        });
    };

    const fetchData = async (customFilter = null) => {
        setTableLoading(true);

        try {
            const filters = customFilter || {
                search,
                tanggalMulai,
                tanggalSelesai,
                statusFilter,
                urgentFilter,
            };

            const params = new URLSearchParams();

            if (filters.search?.trim()) {
                params.append("search", filters.search.trim());
            }

            if (filters.tanggalMulai) {
                params.append("tanggal_mulai", filters.tanggalMulai);
            }

            if (filters.tanggalSelesai) {
                params.append("tanggal_selesai", filters.tanggalSelesai);
            }

            if (filters.statusFilter) {
                params.append("status_permintaan", filters.statusFilter);
            }

            if (filters.urgentFilter) {
                params.append("urgent_permintaan", filters.urgentFilter);
            }

            const url = params.toString()
                ? `/admin/permintaan-kandidat-recruitment/list?${params.toString()}`
                : "/admin/permintaan-kandidat-recruitment/list";

            const response = await fetch(url, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal mengambil data permintaan kandidat.");
                return;
            }

            setDataList(Array.isArray(result.data) ? result.data : []);
        } catch (error) {
            console.error("Gagal mengambil data permintaan kandidat:", error);
            alert("Terjadi kesalahan saat mengambil data permintaan kandidat.");
        } finally {
            setTableLoading(false);
        }
    };

    useEffect(() => {
        fetchData({
            search: "",
            tanggalMulai: "",
            tanggalSelesai: "",
            statusFilter: "",
            urgentFilter: "",
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        const currentSignal = actionSignals?.permintaanKandidat || 0;

        if (currentSignal > lastSignalRef.current) {
            resetForm();
            setModalOpen(true);
        }

        lastSignalRef.current = currentSignal;
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [actionSignals?.permintaanKandidat]);

    useEffect(() => {
        setCurrentPage(1);
    }, [dataList, entriesPerPage]);

    const handleFilter = (event) => {
        event.preventDefault();

        if (tanggalMulai && tanggalSelesai && tanggalSelesai < tanggalMulai) {
            alert("Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.");
            return;
        }

        setCurrentPage(1);
        fetchData();
    };

    const handleResetFilter = () => {
        const resetFilter = {
            search: "",
            tanggalMulai: "",
            tanggalSelesai: "",
            statusFilter: "",
            urgentFilter: "",
        };

        setSearch("");
        setTanggalMulai("");
        setTanggalSelesai("");
        setStatusFilter("");
        setUrgentFilter("");
        setCurrentPage(1);

        fetchData(resetFilter);
    };

    const openEditModal = (item) => {
        setEditId(item.id);

        setForm({
            pt_membutuhkan: item.pt_membutuhkan || "",
            divisi_departemen: item.divisi_departemen || "",
            permintaan_oleh: item.permintaan_oleh || "",
            tanggal_permintaan: item.tanggal_permintaan || "",
            deskripsi_permintaan: item.deskripsi_permintaan || "",

            nama_posisi_jabatan: item.nama_posisi_jabatan || "",
            jumlah_karyawan: item.jumlah_karyawan || 1,
            lokasi_kerja: item.lokasi_kerja || "",

            tipe_pekerjaan: item.tipe_pekerjaan || "",
            jadwal_kerja: item.jadwal_kerja || "",
            deskripsi_pekerjaan: item.deskripsi_pekerjaan || "",
            gaji_benefit: item.gaji_benefit || "",

            pendidikan_minimum: item.pendidikan_minimum || "",
            usia: item.usia || "",
            jenis_kelamin: item.jenis_kelamin || "",
            pengalaman_kerja: item.pengalaman_kerja || "",
            keterampilan_teknis: item.keterampilan_teknis || "",
            keterampilan_interpersonal: item.keterampilan_interpersonal || "",
            syarat_khusus: item.syarat_khusus || "",
            keahlian_khusus: item.keahlian_khusus || "",
            sertifikat: item.sertifikat || "",

            tanggal_mulai_diperlukan: item.tanggal_mulai_diperlukan || "",
            urgent_permintaan: item.urgent_permintaan || "",
            alasan_permintaan: item.alasan_permintaan || "",

            karakter_pribadi: item.karakter_pribadi || "",
            hasil_test_tertulis: item.hasil_test_tertulis || "",
            permintaan_khusus: item.permintaan_khusus || "",
            karakter_profesional: item.karakter_profesional || "",

            proses_seleksi: item.proses_seleksi || "",
            materi_ppt: item.materi_ppt || "",
            informasi_tambahan: item.informasi_tambahan || "",
            penyebaran_iklan: item.penyebaran_iklan || "",

            status_permintaan: item.status_permintaan || "Diajukan",
        });

        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        resetForm();
    };

    const openDetail = (item) => {
        setSelectedItem(item);
        setDetailOpen(true);
    };

    const closeDetail = () => {
        setSelectedItem(null);
        setDetailOpen(false);
    };

    const handleChange = (event) => {
        const { name, value } = event.target;

        setForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        if (!form.nama_posisi_jabatan.trim()) {
            alert("Nama posisi / jabatan wajib diisi.");
            return;
        }

        if (!form.jumlah_karyawan || Number(form.jumlah_karyawan) < 1) {
            alert("Jumlah karyawan minimal 1.");
            return;
        }

        setLoading(true);

        try {
            const url = editId
                ? `/admin/permintaan-kandidat-recruitment/${editId}`
                : "/admin/permintaan-kandidat-recruitment";

            const method = editId ? "PUT" : "POST";

            const response = await fetch(url, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify({
                    ...form,
                    jumlah_karyawan: Number(form.jumlah_karyawan || 1),
                }),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Data gagal disimpan.");
                return;
            }

            alert(result.message || "Data berhasil disimpan.");
            closeModal();
            fetchData();
        } catch (error) {
            console.error("Gagal menyimpan data:", error);
            alert("Terjadi kesalahan saat menyimpan data.");
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (item) => {
        const posisi = item.nama_posisi_jabatan || "data ini";

        if (!confirm(`Yakin ingin menghapus permintaan untuk posisi ${posisi}?`)) {
            return;
        }

        try {
            const response = await fetch(
                `/admin/permintaan-kandidat-recruitment/${item.id}`,
                {
                    method: "DELETE",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Data gagal dihapus.");
                return;
            }

            alert(result.message || "Data berhasil dihapus.");
            fetchData();
        } catch (error) {
            console.error("Gagal menghapus data:", error);
            alert("Terjadi kesalahan saat menghapus data.");
        }
    };

    const filteredData = useMemo(() => dataList, [dataList]);

    const totalPages = Math.max(1, Math.ceil(filteredData.length / entriesPerPage));

    const paginatedData = useMemo(() => {
        const startIndex = (currentPage - 1) * entriesPerPage;
        return filteredData.slice(startIndex, startIndex + entriesPerPage);
    }, [filteredData, currentPage, entriesPerPage]);

    const showingFrom =
        filteredData.length === 0 ? 0 : (currentPage - 1) * entriesPerPage + 1;

    const showingTo = Math.min(currentPage * entriesPerPage, filteredData.length);

    const pageNumbers = useMemo(() => {
        const pages = [];
        const maxVisiblePages = 5;

        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
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

    const statusBadgeClass = (status) => {
        if (status === "Diajukan") return "bg-blue-50 text-blue-700";
        if (status === "Diproses") return "bg-amber-50 text-amber-700";
        if (status === "Selesai") return "bg-emerald-50 text-emerald-700";
        if (status === "Dibatalkan") return "bg-rose-50 text-rose-700";
        return "bg-slate-100 text-slate-500";
    };

    const urgentBadgeClass = (urgent) => {
        if (urgent === "Sangat Urgent") return "bg-red-100 text-red-700";
        if (urgent === "Tinggi") return "bg-rose-50 text-rose-700";
        if (urgent === "Sedang") return "bg-amber-50 text-amber-700";
        if (urgent === "Rendah") return "bg-emerald-50 text-emerald-700";
        return "bg-slate-100 text-slate-500";
    };

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-100 px-6 py-4">
                    <form
                        onSubmit={handleFilter}
                        className="grid gap-3 lg:grid-cols-[1.5fr_1fr_1fr_1fr_1fr_auto_auto] lg:items-end"
                    >
                        <InputField
                            label="Search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Cari posisi, divisi, PT, lokasi..."
                        />

                        <DateInput
                            label="Tanggal Mulai"
                            value={tanggalMulai}
                            onChange={setTanggalMulai}
                        />

                        <DateInput
                            label="Tanggal Selesai"
                            value={tanggalSelesai}
                            onChange={setTanggalSelesai}
                        />

                        <SelectField
                            label="Status"
                            value={statusFilter}
                            onChange={(event) => setStatusFilter(event.target.value)}
                            options={STATUS_OPTIONS}
                            placeholder="Semua"
                        />

                        <SelectField
                            label="Urgent"
                            value={urgentFilter}
                            onChange={(event) => setUrgentFilter(event.target.value)}
                            options={URGENT_OPTIONS}
                            placeholder="Semua"
                        />

                        <button
                            type="submit"
                            disabled={tableLoading}
                            className="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Filter
                        </button>

                        <button
                            type="button"
                            disabled={tableLoading}
                            onClick={handleResetFilter}
                            className="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Reset
                        </button>
                    </form>
                </div>

                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="flex items-center gap-2">
                        <span className="text-sm font-bold text-slate-600">Show</span>

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

                        <span className="text-sm font-bold text-slate-600">entries</span>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Tanggal Permintaan</TableHead>
                                <TableHead>Posisi</TableHead>
                                <TableHead>Divisi</TableHead>
                                <TableHead>Jumlah</TableHead>
                                <TableHead>Lokasi</TableHead>
                                <TableHead>Urgent</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <td
                                        colSpan="9"
                                        className="px-6 py-16 text-center text-sm font-black text-slate-500"
                                    >
                                        Memuat data...
                                    </td>
                                </tr>
                            ) : paginatedData.length > 0 ? (
                                paginatedData.map((item, index) => (
                                    <tr
                                        key={item.id}
                                        className="transition hover:bg-slate-50"
                                    >
                                        <td className="px-6 py-5 text-sm font-black text-slate-500">
                                            {showingFrom + index}
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-800">
                                                {item.tanggal_permintaan_format ||
                                                    item.tanggal_permintaan ||
                                                    "-"}
                                            </div>
                                            <div className="mt-1 text-xs font-semibold text-slate-500">
                                                Oleh: {item.permintaan_oleh || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-800">
                                                {item.nama_posisi_jabatan || "-"}
                                            </div>
                                            <div className="mt-1 text-xs font-semibold text-slate-500">
                                                {item.tipe_pekerjaan || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {item.divisi_departemen || "-"}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-black text-slate-700">
                                            {item.jumlah_karyawan || 0}
                                        </td>

                                        <td className="px-6 py-5 text-sm font-bold text-slate-600">
                                            {item.lokasi_kerja || "-"}
                                        </td>

                                        <td className="px-6 py-5">
                                            <span
                                                className={`rounded-full px-3 py-1 text-xs font-black uppercase ${urgentBadgeClass(
                                                    item.urgent_permintaan
                                                )}`}
                                            >
                                                {item.urgent_permintaan || "-"}
                                            </span>
                                        </td>

                                        <td className="px-6 py-5">
                                            <span
                                                className={`rounded-full px-3 py-1 text-xs font-black uppercase ${statusBadgeClass(
                                                    item.status_permintaan
                                                )}`}
                                            >
                                                {item.status_permintaan || "-"}
                                            </span>
                                        </td>

                                        <td className="px-6 py-5 text-right">
                                            <div className="flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => openDetail(item)}
                                                    className="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-2 text-xs font-black text-indigo-700 shadow-sm transition hover:bg-indigo-100"
                                                >
                                                    Detail
                                                </button>

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
                                                    className="rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:bg-rose-50"
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
                                                ▤
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Belum ada permintaan kandidat recruitment pada filter ini.
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
                <FormModal
                    title={
                        editId
                            ? "Edit Permintaan Kandidat"
                            : "Tambah Permintaan Kandidat"
                    }
                    onClose={closeModal}
                    onSubmit={handleSubmit}
                    loading={loading}
                    submitLabel={editId ? "Update Data" : "Simpan Data"}
                >
                    <SectionTitle title="Informasi Permintaan" />

                    <div className="grid gap-4 md:grid-cols-2">
                        <InputField
                            label="PT yang Membutuhkan"
                            name="pt_membutuhkan"
                            value={form.pt_membutuhkan}
                            onChange={handleChange}
                        />

                        <InputField
                            label="Divisi / Departemen"
                            name="divisi_departemen"
                            value={form.divisi_departemen}
                            onChange={handleChange}
                        />

                        <InputField
                            label="Permintaan Oleh"
                            name="permintaan_oleh"
                            value={form.permintaan_oleh}
                            onChange={handleChange}
                        />

                        <InputField
                            label="Tanggal Permintaan"
                            type="date"
                            name="tanggal_permintaan"
                            value={form.tanggal_permintaan}
                            onChange={handleChange}
                        />
                    </div>

                    <Textarea
                        label="Deskripsi Permintaan"
                        name="deskripsi_permintaan"
                        value={form.deskripsi_permintaan}
                        onChange={handleChange}
                    />

                    <SectionTitle title="Detail Posisi" />

                    <div className="grid gap-4 md:grid-cols-2">
                        <InputField
                            required
                            label="Nama Posisi / Jabatan"
                            name="nama_posisi_jabatan"
                            value={form.nama_posisi_jabatan}
                            onChange={handleChange}
                        />

                        <InputField
                            required
                            label="Jumlah Karyawan"
                            type="number"
                            min="1"
                            name="jumlah_karyawan"
                            value={form.jumlah_karyawan}
                            onChange={handleChange}
                        />

                        <InputField
                            label="Lokasi Kerja"
                            name="lokasi_kerja"
                            value={form.lokasi_kerja}
                            onChange={handleChange}
                        />

                        <SelectField
                            label="Tipe Pekerjaan"
                            name="tipe_pekerjaan"
                            value={form.tipe_pekerjaan}
                            onChange={handleChange}
                            options={TIPE_PEKERJAAN_OPTIONS}
                            placeholder="Pilih tipe pekerjaan"
                        />

                        <InputField
                            label="Jadwal / Jam Kerja"
                            name="jadwal_kerja"
                            value={form.jadwal_kerja}
                            onChange={handleChange}
                        />

                        <InputField
                            label="Tanggal Mulai Diperlukan"
                            type="date"
                            name="tanggal_mulai_diperlukan"
                            value={form.tanggal_mulai_diperlukan}
                            onChange={handleChange}
                        />
                    </div>

                    <Textarea
                        label="Deskripsi Pekerjaan / Tugas"
                        name="deskripsi_pekerjaan"
                        value={form.deskripsi_pekerjaan}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Range Gaji & Benefit"
                        name="gaji_benefit"
                        value={form.gaji_benefit}
                        onChange={handleChange}
                    />

                    <SectionTitle title="Kualifikasi Kandidat" />

                    <div className="grid gap-4 md:grid-cols-2">
                        <InputField
                            label="Pendidikan Minimum"
                            name="pendidikan_minimum"
                            value={form.pendidikan_minimum}
                            onChange={handleChange}
                        />

                        <InputField
                            label="Usia"
                            name="usia"
                            value={form.usia}
                            onChange={handleChange}
                        />

                        <SelectField
                            label="Jenis Kelamin"
                            name="jenis_kelamin"
                            value={form.jenis_kelamin}
                            onChange={handleChange}
                            options={JENIS_KELAMIN_OPTIONS}
                            placeholder="Pilih jenis kelamin"
                        />

                        <InputField
                            label="Pengalaman Kerja"
                            name="pengalaman_kerja"
                            value={form.pengalaman_kerja}
                            onChange={handleChange}
                        />
                    </div>

                    <Textarea
                        label="Keterampilan Teknis"
                        name="keterampilan_teknis"
                        value={form.keterampilan_teknis}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Keterampilan Interpersonal"
                        name="keterampilan_interpersonal"
                        value={form.keterampilan_interpersonal}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Syarat Khusus"
                        name="syarat_khusus"
                        value={form.syarat_khusus}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Keahlian Khusus"
                        name="keahlian_khusus"
                        value={form.keahlian_khusus}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Sertifikat"
                        name="sertifikat"
                        value={form.sertifikat}
                        onChange={handleChange}
                    />

                    <SectionTitle title="Urgensi & Alasan" />

                    <div className="grid gap-4 md:grid-cols-3">
                        <SelectField
                            label="Urgent Permintaan"
                            name="urgent_permintaan"
                            value={form.urgent_permintaan}
                            onChange={handleChange}
                            options={URGENT_OPTIONS}
                            placeholder="Pilih urgent"
                        />

                        <SelectField
                            label="Alasan Permintaan"
                            name="alasan_permintaan"
                            value={form.alasan_permintaan}
                            onChange={handleChange}
                            options={ALASAN_OPTIONS}
                            placeholder="Pilih alasan"
                        />

                        <SelectField
                            label="Status Permintaan"
                            name="status_permintaan"
                            value={form.status_permintaan}
                            onChange={handleChange}
                            options={STATUS_OPTIONS}
                            placeholder="Pilih status"
                        />
                    </div>

                    <SectionTitle title="Karakter Kandidat" />

                    <Textarea
                        label="Karakter Pribadi"
                        name="karakter_pribadi"
                        value={form.karakter_pribadi}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Hasil Test Tertulis yang Diinginkan"
                        name="hasil_test_tertulis"
                        value={form.hasil_test_tertulis}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Permintaan Khusus"
                        name="permintaan_khusus"
                        value={form.permintaan_khusus}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Karakter Profesional"
                        name="karakter_profesional"
                        value={form.karakter_profesional}
                        onChange={handleChange}
                    />

                    <SectionTitle title="Proses Seleksi & Informasi Tambahan" />

                    <Textarea
                        label="Proses Seleksi yang Diusulkan"
                        name="proses_seleksi"
                        value={form.proses_seleksi}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Materi PPT / Presentasi"
                        name="materi_ppt"
                        value={form.materi_ppt}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Informasi Tambahan"
                        name="informasi_tambahan"
                        value={form.informasi_tambahan}
                        onChange={handleChange}
                    />

                    <Textarea
                        label="Penyebaran Iklan Dilakukan Dimana"
                        name="penyebaran_iklan"
                        value={form.penyebaran_iklan}
                        onChange={handleChange}
                    />
                </FormModal>
            )}

            {detailOpen && selectedItem && (
                <DetailModal item={selectedItem} onClose={closeDetail} />
            )}
        </div>
    );
}

function DetailModal({ item, onClose }) {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div className="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div className="border-b border-slate-200 px-6 py-5">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <div className="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                                Detail Permintaan Kandidat
                            </div>

                            <h2 className="mt-2 text-2xl font-black text-slate-950">
                                {item.nama_posisi_jabatan || "-"}
                            </h2>

                            <p className="mt-1 text-sm font-bold text-slate-500">
                                {item.divisi_departemen || "-"} •{" "}
                                {item.pt_membutuhkan || "-"}
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={onClose}
                            className="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-500 transition hover:bg-slate-200"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <div className="overflow-y-auto px-6 py-6">
                    <div className="grid gap-6 lg:grid-cols-2">
                        <DetailSection title="Informasi Permintaan">
                            <DetailItem label="PT Membutuhkan" value={item.pt_membutuhkan} />
                            <DetailItem label="Divisi / Departemen" value={item.divisi_departemen} />
                            <DetailItem label="Permintaan Oleh" value={item.permintaan_oleh} />
                            <DetailItem label="Tanggal Permintaan" value={item.tanggal_permintaan_format || item.tanggal_permintaan} />
                            <DetailItem label="Deskripsi Permintaan" value={item.deskripsi_permintaan} />
                        </DetailSection>

                        <DetailSection title="Detail Posisi">
                            <DetailItem label="Nama Posisi / Jabatan" value={item.nama_posisi_jabatan} />
                            <DetailItem label="Jumlah Karyawan" value={item.jumlah_karyawan} />
                            <DetailItem label="Lokasi Kerja" value={item.lokasi_kerja} />
                            <DetailItem label="Tipe Pekerjaan" value={item.tipe_pekerjaan} />
                            <DetailItem label="Jadwal Kerja" value={item.jadwal_kerja} />
                            <DetailItem label="Tanggal Mulai Diperlukan" value={item.tanggal_mulai_diperlukan_format || item.tanggal_mulai_diperlukan} />
                            <DetailItem label="Deskripsi Pekerjaan" value={item.deskripsi_pekerjaan} />
                            <DetailItem label="Gaji & Benefit" value={item.gaji_benefit} />
                        </DetailSection>

                        <DetailSection title="Kualifikasi">
                            <DetailItem label="Pendidikan Minimum" value={item.pendidikan_minimum} />
                            <DetailItem label="Usia" value={item.usia} />
                            <DetailItem label="Jenis Kelamin" value={item.jenis_kelamin} />
                            <DetailItem label="Pengalaman Kerja" value={item.pengalaman_kerja} />
                            <DetailItem label="Keterampilan Teknis" value={item.keterampilan_teknis} />
                            <DetailItem label="Keterampilan Interpersonal" value={item.keterampilan_interpersonal} />
                            <DetailItem label="Syarat Khusus" value={item.syarat_khusus} />
                            <DetailItem label="Keahlian Khusus" value={item.keahlian_khusus} />
                            <DetailItem label="Sertifikat" value={item.sertifikat} />
                        </DetailSection>

                        <DetailSection title="Kandidat & Seleksi">
                            <DetailItem label="Urgent Permintaan" value={item.urgent_permintaan} />
                            <DetailItem label="Alasan Permintaan" value={item.alasan_permintaan} />
                            <DetailItem label="Karakter Pribadi" value={item.karakter_pribadi} />
                            <DetailItem label="Hasil Test Tertulis" value={item.hasil_test_tertulis} />
                            <DetailItem label="Permintaan Khusus" value={item.permintaan_khusus} />
                            <DetailItem label="Karakter Profesional" value={item.karakter_profesional} />
                            <DetailItem label="Proses Seleksi" value={item.proses_seleksi} />
                            <DetailItem label="Materi PPT" value={item.materi_ppt} />
                            <DetailItem label="Informasi Tambahan" value={item.informasi_tambahan} />
                            <DetailItem label="Penyebaran Iklan" value={item.penyebaran_iklan} />
                            <DetailItem label="Status Permintaan" value={item.status_permintaan} />
                        </DetailSection>
                    </div>
                </div>
            </div>
        </div>
    );
}

function FormModal({ title, children, onClose, onSubmit, loading, submitLabel }) {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div className="flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div className="shrink-0 border-b border-slate-200 px-6 py-5">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <div className="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                                Form Permintaan Kandidat
                            </div>

                            <h2 className="mt-2 text-2xl font-black text-slate-950">
                                {title}
                            </h2>
                        </div>

                        <button
                            type="button"
                            onClick={onClose}
                            className="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-500 transition hover:bg-slate-200"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <form onSubmit={onSubmit} className="flex min-h-0 flex-1 flex-col">
                    <div className="min-h-0 flex-1 space-y-6 overflow-y-auto px-6 py-6">
                        {children}
                    </div>

                    <div className="shrink-0 border-t border-slate-200 px-6 py-4">
                        <div className="flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={onClose}
                                className="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                disabled={loading}
                                className="rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {loading ? "Menyimpan..." : submitLabel}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}

function SectionTitle({ title }) {
    return (
        <div>
            <h3 className="text-lg font-black text-slate-950">{title}</h3>
            <div className="mt-2 h-1 w-12 rounded-full bg-indigo-500" />
        </div>
    );
}

function DetailSection({ title, children }) {
    return (
        <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <SectionTitle title={title} />
            <div className="mt-5 grid gap-4">{children}</div>
        </div>
    );
}

function DetailItem({ label, value }) {
    return (
        <div>
            <div className="text-[11px] font-black uppercase tracking-wide text-slate-400">
                {label}
            </div>
            <div className="mt-1 whitespace-pre-line text-sm font-bold text-slate-700">
                {value || "-"}
            </div>
        </div>
    );
}

function InputField({ label, required = false, className = "", ...props }) {
    return (
        <div className={className}>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <input
                {...props}
                required={required}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />
        </div>
    );
}

function DateInput({ label, value, onChange }) {
    return (
        <label className="block">
            <span className="mb-2 block text-sm font-black text-slate-700">
                {label}
            </span>

            <input
                type="date"
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />
        </label>
    );
}

function SelectField({
    label,
    value,
    onChange,
    options,
    placeholder = "Pilih",
    name,
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
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">{placeholder}</option>
                {options.map((item) => (
                    <option key={item} value={item}>
                        {item}
                    </option>
                ))}
            </select>
        </div>
    );
}

function Textarea({ label, required = false, ...props }) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <textarea
                {...props}
                required={required}
                rows={4}
                className="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />
        </div>
    );
}

function TableHead({ children, align = "left" }) {
    return (
        <th
            className={`whitespace-nowrap px-6 py-4 ${
                align === "right" ? "text-right" : "text-left"
            } text-xs font-black uppercase tracking-[0.12em] text-slate-500`}
        >
            {children}
        </th>
    );
}