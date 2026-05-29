import React, { useEffect, useMemo, useState } from "react";

export default function ReviewManagementPage() {
    const [dataReview, setDataReview] = useState([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [loading, setLoading] = useState(false);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    const [selectedItem, setSelectedItem] = useState(null);

    const [form, setForm] = useState({
        hasil_interview_id: "",
        review_management: "",
        status: "Diterima",
    });

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const fetchData = async () => {
        setTableLoading(true);

        try {
            const response = await fetch("/admin/review-management/list", {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (result.success) {
                setDataReview(result.data || []);
            } else {
                alert(result.message || "Gagal mengambil data review management.");
            }
        } catch (error) {
            console.error("Gagal mengambil data review management:", error);
            alert("Terjadi kesalahan saat mengambil data review management.");
        } finally {
            setTableLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage]);

    const openModal = (item) => {
        setSelectedItem(item);

        setForm({
            hasil_interview_id: item.id,
            review_management: item.review_management || "",
            status: item.status_review || "Diterima",
        });

        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setSelectedItem(null);

        setForm({
            hasil_interview_id: "",
            review_management: "",
            status: "Diterima",
        });
    };

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) {
            return dataReview;
        }

        return dataReview.filter((item) => {
            const namaKandidat = String(item.nama_kandidat || "").toLowerCase();
            const emailKandidat = String(item.email_kandidat || "").toLowerCase();
            const noWaKandidat = String(item.no_wa_kandidat || "").toLowerCase();
            const posisiLabel = String(item.posisi_label || "").toLowerCase();
            const perusahaanLabel = String(item.perusahaan_label || "").toLowerCase();
            const hasilInterview = String(item.hasil_interview || "").toLowerCase();
            const catatan = String(item.catatan || "").toLowerCase();
            const reviewManagement = String(item.review_management || "").toLowerCase();
            const statusReview = String(item.status_review || "").toLowerCase();

            return (
                namaKandidat.includes(keyword) ||
                emailKandidat.includes(keyword) ||
                noWaKandidat.includes(keyword) ||
                posisiLabel.includes(keyword) ||
                perusahaanLabel.includes(keyword) ||
                hasilInterview.includes(keyword) ||
                catatan.includes(keyword) ||
                reviewManagement.includes(keyword) ||
                statusReview.includes(keyword)
            );
        });
    }, [dataReview, search]);

    const totalPages = Math.max(1, Math.ceil(filteredData.length / entriesPerPage));

    const paginatedData = useMemo(() => {
        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = startIndex + entriesPerPage;

        return filteredData.slice(startIndex, endIndex);
    }, [filteredData, currentPage, entriesPerPage]);

    const showingFrom =
        filteredData.length === 0 ? 0 : (currentPage - 1) * entriesPerPage + 1;

    const showingTo = Math.min(currentPage * entriesPerPage, filteredData.length);

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

    const handleChange = (e) => {
        const { name, value } = e.target;

        setForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await fetch("/admin/review-management/review", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify(form),
            });

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || "Review management gagal disimpan.");
                return;
            }

            alert(result.message || "Review management berhasil disimpan.");
            closeModal();
            fetchData();
        } catch (error) {
            console.error("Gagal menyimpan review management:", error);
            alert("Terjadi kesalahan saat menyimpan review management.");
        } finally {
            setLoading(false);
        }
    };

    const handleDeleteReview = async (item) => {
        if (!item.review_management_id) {
            alert("Data review belum tersedia.");
            return;
        }

        const confirmDelete = confirm("Yakin ingin menghapus review management ini?");

        if (!confirmDelete) return;

        try {
            const response = await fetch(
                `/admin/review-management/review/${item.review_management_id}`,
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
                alert(result.message || "Review management berhasil dihapus.");
                fetchData();
            } else {
                alert(result.message || "Review management gagal dihapus.");
            }
        } catch (error) {
            console.error("Gagal menghapus review management:", error);
            alert("Terjadi kesalahan saat menghapus review management.");
        }
    };

    const rowColorClass = (hasilInterview) => {
        if (hasilInterview === "Lolos Interview") {
            return "bg-emerald-50/70 hover:bg-emerald-100/70";
        }

        if (hasilInterview === "Dipertimbangkan") {
            return "bg-amber-50/80 hover:bg-amber-100/80";
        }

        return "hover:bg-slate-50";
    };

    const hasilBadgeClass = (hasilInterview) => {
        if (hasilInterview === "Lolos Interview") {
            return "bg-emerald-100 text-emerald-700";
        }

        if (hasilInterview === "Dipertimbangkan") {
            return "bg-amber-100 text-amber-700";
        }

        return "bg-slate-100 text-slate-700";
    };

    const statusBadgeClass = (status) => {
        if (status === "Diterima") {
            return "bg-teal-100 text-teal-700";
        }

        if (status === "Gagal") {
            return "bg-rose-100 text-rose-700";
        }

        return "bg-slate-100 text-slate-500";
    };

    const handlePreviousPage = () => {
        setCurrentPage((prev) => Math.max(prev - 1, 1));
    };

    const handleNextPage = () => {
        setCurrentPage((prev) => Math.min(prev + 1, totalPages));
    };

    const formatDate = (value) => {
        if (!value) return "-";

        try {
            return new Date(value).toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "long",
                year: "numeric",
            });
        } catch {
            return value;
        }
    };

    const pelamar = selectedItem?.detail_kandidat || null;

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
                                    placeholder="Cari nama kandidat, hasil interview..."
                                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100 md:w-96"
                                />
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-2">
                            <span className="rounded-full bg-emerald-100 px-4 py-2 text-xs font-black uppercase tracking-wide text-emerald-700">
                                Lolos Interview
                            </span>

                            <span className="rounded-full bg-amber-100 px-4 py-2 text-xs font-black uppercase tracking-wide text-amber-700">
                                Dipertimbangkan
                            </span>
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Nama Kandidat</TableHead>
                                <TableHead>Posisi</TableHead>
                                <TableHead>Hasil Interview</TableHead>
                                <TableHead>Status Kehadiran</TableHead>
                                <TableHead>Catatan Interview</TableHead>
                                <TableHead>Review Management</TableHead>
                                <TableHead>Status Review</TableHead>
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
                                        className={`group transition ${rowColorClass(
                                            item.hasil_interview
                                        )}`}
                                    >
                                        <td className="px-6 py-5 text-sm font-black text-slate-500">
                                            {showingFrom + index}
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-800">
                                                {item.nama_kandidat || "-"}
                                            </div>
                                            <div className="mt-1 text-xs font-semibold text-slate-500">
                                                {item.email_kandidat || item.no_wa_kandidat || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-700">
                                                {item.posisi_label || "-"}
                                            </div>
                                            <div className="mt-1 text-xs font-semibold text-slate-500">
                                                {item.perusahaan_label || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <span
                                                className={`inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ${hasilBadgeClass(
                                                    item.hasil_interview
                                                )}`}
                                            >
                                                {item.hasil_interview}
                                            </span>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-700">
                                                {item.status_kehadiran || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="max-w-md text-sm font-semibold text-slate-600">
                                                {item.catatan || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="max-w-md text-sm font-semibold text-slate-600">
                                                {item.review_management || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <span
                                                className={`inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ${statusBadgeClass(
                                                    item.status_review
                                                )}`}
                                            >
                                                {item.status_review || "Belum Review"}
                                            </span>
                                        </td>

                                        <td className="px-6 py-5 text-right">
                                            <div className="flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => openModal(item)}
                                                    className="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                                >
                                                    Review
                                                </button>

                                                {item.review_management_id && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            handleDeleteReview(item)
                                                        }
                                                        className="rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:bg-rose-50 hover:text-rose-700"
                                                    >
                                                        Hapus
                                                    </button>
                                                )}
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
                                                Data review tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Hanya kandidat dengan hasil interview Lolos Interview dan Dipertimbangkan yang tampil di halaman ini.
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
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
                    <div className="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div className="shrink-0 border-b border-slate-200 bg-white">
                            <div className="flex items-center justify-between gap-4 px-6 py-5">
                                <div>
                                    <div className="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                                        Review Management
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        {selectedItem?.review_management_id
                                            ? "Edit Review"
                                            : "Tambah Review"}
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Kandidat:{" "}
                                        <span className="font-black text-slate-800">
                                            {selectedItem?.nama_kandidat || "-"}
                                        </span>
                                        {" • "}
                                        Hasil interview:{" "}
                                        <span className="font-black text-slate-800">
                                            {selectedItem?.hasil_interview}
                                        </span>
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
                            <div className="min-h-0 flex-1 overflow-y-auto px-6 py-6">
                                <div className="grid gap-6 lg:grid-cols-2">
                                    <div className="space-y-5">
                                        <SectionTitle title="Detail Kandidat" />

                                        <div className="rounded-3xl border border-slate-200 bg-slate-50/60 p-5">
                                            <div className="grid gap-3 sm:grid-cols-2">
                                                <DetailItem
                                                    label="Nama Lengkap"
                                                    value={pelamar?.nama_lengkap}
                                                />
                                                <DetailItem
                                                    label="Nama Panggil"
                                                    value={pelamar?.nama_panggil}
                                                />
                                                <DetailItem
                                                    label="Email"
                                                    value={pelamar?.email}
                                                />
                                                <DetailItem
                                                    label="No. WA"
                                                    value={pelamar?.no_wa}
                                                />
                                                <DetailItem
                                                    label="Posisi Dilamar"
                                                    value={pelamar?.posisi_label}
                                                />
                                                <DetailItem
                                                    label="Perusahaan"
                                                    value={pelamar?.perusahaan_label}
                                                />
                                                <DetailItem
                                                    label="Pendidikan"
                                                    value={pelamar?.pendidikan_label}
                                                />
                                                <DetailItem
                                                    label="Jurusan"
                                                    value={pelamar?.jurusan}
                                                />
                                                <DetailItem
                                                    label="Institusi"
                                                    value={pelamar?.nama_institusi}
                                                />
                                                <DetailItem
                                                    label="Agama"
                                                    value={pelamar?.agama_label}
                                                />
                                                <DetailItem
                                                    label="Tanggal Lahir"
                                                    value={formatDate(pelamar?.tanggal_lahir)}
                                                />
                                                <DetailItem
                                                    label="Tanggal Skrining"
                                                    value={formatDate(pelamar?.tanggal_skrining)}
                                                />
                                                <DetailItem
                                                    label="Kewarganegaraan"
                                                    value={pelamar?.kewarganegaraan_label}
                                                />
                                                <DetailItem
                                                    label="Status Pernikahan"
                                                    value={pelamar?.status_pernikahan_label}
                                                />
                                                <DetailItem
                                                    label="Golongan Darah"
                                                    value={pelamar?.gol_darah || pelamar?.golongan_darah}
                                                />
                                                <DetailItem
                                                    label="Tinggi / Berat"
                                                    value={`${pelamar?.tinggi_badan || "-"} cm / ${pelamar?.berat_badan || "-"} kg`}
                                                />
                                            </div>

                                            <div className="mt-4 grid gap-3">
                                                <DetailItem
                                                    label="Alamat KTP"
                                                    value={pelamar?.alamat_ktp}
                                                />
                                                <DetailItem
                                                    label="Alamat Domisili"
                                                    value={pelamar?.alamat_domisili}
                                                />
                                                <DetailItem
                                                    label="Sumber Informasi"
                                                    value={pelamar?.sumber_informasi_label}
                                                />
                                            </div>
                                        </div>

                                        <div className="rounded-3xl border border-slate-200 bg-white p-5">
                                            <div className="flex items-center justify-between gap-4">
                                                <div>
                                                    <h3 className="text-sm font-black text-slate-800">
                                                        Kelengkapan Form
                                                    </h3>
                                                    <p className="mt-1 text-xs font-semibold text-slate-500">
                                                        Tahap terakhir: {pelamar?.tahap_terakhir_form || "-"}
                                                    </p>
                                                </div>

                                                <div className="text-right">
                                                    <div className="text-2xl font-black text-teal-600">
                                                        {pelamar?.persentase_kelengkapan ?? 0}%
                                                    </div>
                                                    <div className="text-xs font-bold text-slate-400">
                                                        {pelamar?.total_step_terisi || 0}/{pelamar?.total_step_form || 0} tahap
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                                                <div
                                                    className="h-full rounded-full bg-teal-600 transition-all"
                                                    style={{
                                                        width: `${pelamar?.persentase_kelengkapan ?? 0}%`,
                                                    }}
                                                />
                                            </div>

                                            {pelamar?.kelengkapan_form?.steps?.length > 0 && (
                                                <div className="mt-4 grid gap-2">
                                                    {pelamar.kelengkapan_form.steps.map((step) => (
                                                        <div
                                                            key={step.key}
                                                            className="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"
                                                        >
                                                            <div>
                                                                <div className="text-xs font-black text-slate-700">
                                                                    {step.label}
                                                                </div>
                                                                <div className="text-[11px] font-semibold text-slate-400">
                                                                    {step.description}
                                                                </div>
                                                            </div>

                                                            <span
                                                                className={`rounded-full px-3 py-1 text-[10px] font-black uppercase ${
                                                                    step.completed
                                                                        ? "bg-teal-100 text-teal-700"
                                                                        : "bg-slate-100 text-slate-400"
                                                                }`}
                                                            >
                                                                {step.completed ? "Lengkap" : "Belum"}
                                                            </span>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-5">
                                        <SectionTitle title="Data Interview" />

                                        <div className="rounded-3xl border border-slate-200 bg-white p-5">
                                            <div className="grid gap-3">
                                                <DetailItem
                                                    label="Hasil Interview"
                                                    value={selectedItem?.hasil_interview}
                                                />
                                                <DetailItem
                                                    label="Status Kehadiran"
                                                    value={selectedItem?.status_kehadiran}
                                                />
                                                <DetailItem
                                                    label="Catatan Interview"
                                                    value={selectedItem?.catatan}
                                                />
                                            </div>
                                        </div>

                                        <SectionTitle title="Form Review Management" />

                                        <Textarea
                                            label="Review Management"
                                            name="review_management"
                                            value={form.review_management}
                                            onChange={handleChange}
                                            placeholder="Tuliskan hasil review management..."
                                        />

                                        <div>
                                            <label className="mb-2 block text-sm font-black text-slate-700">
                                                Status <span className="text-rose-500">*</span>
                                            </label>

                                            <select
                                                name="status"
                                                value={form.status}
                                                onChange={handleChange}
                                                required
                                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                                            >
                                                <option value="Diterima">Diterima</option>
                                                <option value="Gagal">Gagal</option>
                                            </select>
                                        </div>
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
                                        {loading ? "Menyimpan..." : "Simpan Review"}
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

function SectionTitle({ title }) {
    return (
        <div>
            <h3 className="text-lg font-black text-slate-950">{title}</h3>
            <div className="mt-2 h-1 w-12 rounded-full bg-teal-500" />
        </div>
    );
}

function DetailItem({ label, value }) {
    return (
        <div>
            <div className="text-[11px] font-black uppercase tracking-wide text-slate-400">
                {label}
            </div>
            <div className="mt-1 text-sm font-bold text-slate-700">
                {value || "-"}
            </div>
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
                rows={8}
                className="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            />
        </div>
    );
}