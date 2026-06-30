import React, { useEffect, useMemo, useState } from "react";

const STATUS_OPTIONS = ["Diterima", "Gagal"];
const SOURCE_TABS = [
    { key: "semua", label: "Semua" },
    { key: "test_zoom", label: "Hasil Test Zoom" },
    { key: "test_mmpi", label: "Hasil Test MMPI" },
    { key: "interview", label: "Interview" },
];

export default function ReviewManagementPage() {
    const getTodayDate = () => {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    };

    const today = getTodayDate();

    const [dataReview, setDataReview] = useState([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [loading, setLoading] = useState(false);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);
    const [activeSource, setActiveSource] = useState("semua");

    const [tanggalMulai, setTanggalMulai] = useState(today);
    const [tanggalSelesai, setTanggalSelesai] = useState(today);
    const [appliedFilter, setAppliedFilter] = useState({
        tanggalMulai: today,
        tanggalSelesai: today,
    });

    const [selectedItem, setSelectedItem] = useState(null);

    const [form, setForm] = useState({
        review_source: "interview",
        hasil_interview_id: "",
        hasil_test_zoom_id: "",
        hasil_test_mmpi_id: "",
        review_management: "",
        status: "",
    });

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const fetchData = async (filters = appliedFilter) => {
        setTableLoading(true);

        try {
            const params = new URLSearchParams();

            if (filters.tanggalMulai) {
                params.append("tanggal_mulai", filters.tanggalMulai);
            }

            if (filters.tanggalSelesai) {
                params.append("tanggal_selesai", filters.tanggalSelesai);
            }

            const url = params.toString()
                ? `/admin/review-management/list?${params.toString()}`
                : "/admin/review-management/list";

            const response = await fetch(url, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal mengambil data review management.");
                return;
            }

            setDataReview(Array.isArray(result.data) ? result.data : []);
        } catch (error) {
            console.error("Gagal mengambil data review management:", error);
            alert("Terjadi kesalahan saat mengambil data review management.");
        } finally {
            setTableLoading(false);
        }
    };

    useEffect(() => {
        const defaultFilter = {
            tanggalMulai: today,
            tanggalSelesai: today,
        };

        setAppliedFilter(defaultFilter);
        fetchData(defaultFilter);

        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage, dataReview, activeSource]);

    const handleFilterTanggal = (event) => {
        event.preventDefault();

        if (tanggalMulai && tanggalSelesai && tanggalSelesai < tanggalMulai) {
            alert("Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.");
            return;
        }

        const nextFilter = {
            tanggalMulai,
            tanggalSelesai,
        };

        setAppliedFilter(nextFilter);
        setCurrentPage(1);
        fetchData(nextFilter);
    };

    const handleResetTanggal = () => {
        const defaultFilter = {
            tanggalMulai: today,
            tanggalSelesai: today,
        };

        setTanggalMulai(today);
        setTanggalSelesai(today);
        setSearch("");
        setActiveSource("semua");
        setCurrentPage(1);
        setAppliedFilter(defaultFilter);
        fetchData(defaultFilter);
    };

    const getReviewSource = (item) => {
        return item?.review_source || item?.sumber_review || "interview";
    };

    const getSourceLabel = (item) => {
        const source = getReviewSource(item);

        if (source === "test_zoom") return "Hasil Test Zoom";
        if (source === "test_mmpi") return "Hasil Test MMPI";
        return "Interview";
    };

    const getSourceId = (item) => {
        const source = getReviewSource(item);

        if (source === "test_zoom") {
            return item?.hasil_test_zoom_id || item?.source_id || item?.id || "";
        }

        if (source === "test_mmpi") {
            return item?.hasil_test_mmpi_id || item?.source_id || item?.id || "";
        }

        return item?.hasil_interview_id || item?.source_id || item?.id || "";
    };

    const openModal = (item) => {
        const source = getReviewSource(item);

        setSelectedItem(item);
        setForm({
            review_source: source,
            hasil_interview_id: source === "interview" ? getSourceId(item) : "",
            hasil_test_zoom_id: source === "test_zoom" ? getSourceId(item) : "",
            hasil_test_mmpi_id: source === "test_mmpi" ? getSourceId(item) : "",
            review_management: item.review_management || "",
            status: item.status_review || "",
        });

        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setSelectedItem(null);

        setForm({
            review_source: "interview",
            hasil_interview_id: "",
            hasil_test_zoom_id: "",
            hasil_test_mmpi_id: "",
            review_management: "",
            status: "",
        });
    };

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        return dataReview.filter((item) => {
            const source = getReviewSource(item);

            if (activeSource !== "semua" && source !== activeSource) {
                return false;
            }

            if (!keyword) {
                return true;
            }

            const searchable = [
                item.nama_kandidat,
                item.email_kandidat,
                item.no_wa_kandidat,
                item.posisi_label,
                item.perusahaan_label,
                item.hasil_interview,
                item.hasil_test,
                item.hasil_label,
                item.hasil_test_iq,
                item.hasil_test_disc,
                item.hasil_test_eysenck,
                item.status_kehadiran,
                item.catatan,
                item.review_management,
                item.status_review,
                item.judul_interview,
                item.judul_tahapan,
                item.tanggal_interview,
                item.tanggal_interview_format,
                item.tanggal_tahapan_format,
                getSourceLabel(item),
            ]
                .map((value) => String(value || "").toLowerCase())
                .join(" ");

            return searchable.includes(keyword);
        });
    }, [dataReview, search, activeSource]);

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

    const handleChange = (event) => {
        const { name, value } = event.target;

        setForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setLoading(true);

        try {
            const payload = {
                review_source: form.review_source,
                hasil_interview_id:
                    form.review_source === "interview" ? form.hasil_interview_id : null,
                hasil_test_zoom_id:
                    form.review_source === "test_zoom" ? form.hasil_test_zoom_id : null,
                hasil_test_mmpi_id:
                    form.review_source === "test_mmpi" ? form.hasil_test_mmpi_id : null,
                status: form.status || null,
                review_management: form.review_management || null,
            };

            const response = await fetch("/admin/review-management/review", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Review management gagal disimpan.");
                return;
            }

            alert(result.message || "Review management berhasil disimpan.");
            closeModal();
            fetchData(appliedFilter);
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

        const confirmDelete = confirm("Yakin ingin mengosongkan review management ini?");

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

            if (!response.ok || !result.success) {
                alert(result.message || "Review management gagal dikosongkan.");
                return;
            }

            alert(result.message || "Review management berhasil dikosongkan.");
            fetchData(appliedFilter);
        } catch (error) {
            console.error("Gagal mengosongkan review management:", error);
            alert("Terjadi kesalahan saat mengosongkan review management.");
        }
    };

    const rowColorClass = (item) => {
        const source = getReviewSource(item);
        const hasil = item?.hasil_interview || item?.hasil_label || item?.hasil_test;

        if (source === "test_zoom") {
            return "bg-sky-50/70 hover:bg-sky-100/70";
        }

        if (source === "test_mmpi") {
            return "bg-cyan-50/70 hover:bg-cyan-100/70";
        }

        if (hasil === "Lolos Interview") {
            return "bg-emerald-50/70 hover:bg-emerald-100/70";
        }

        if (hasil === "Dipertimbangkan") {
            return "bg-amber-50/80 hover:bg-amber-100/80";
        }

        return "hover:bg-slate-50";
    };

    const hasilBadgeClass = (item) => {
        const source = getReviewSource(item);
        const hasil = item?.hasil_interview || item?.hasil_label || item?.hasil_test;

        if (source === "test_zoom") {
            return "bg-sky-100 text-sky-700";
        }

        if (source === "test_mmpi") {
            return "bg-cyan-100 text-cyan-700";
        }

        if (hasil === "Lolos Interview") {
            return "bg-emerald-100 text-emerald-700";
        }

        if (hasil === "Dipertimbangkan") {
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

    const formatDateTime = (value) => {
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

    const getTanggalTahapan = (item) => {
        return (
            item?.tanggal_tahapan_format ||
            item?.tanggal_interview_format ||
            formatDateTime(item?.tanggal_tahapan || item?.tanggal_interview)
        );
    };

    const getHasilLabel = (item) => {
        return item?.hasil_label || item?.hasil_interview || item?.hasil_test || "-";
    };

    const isSelectedTestZoom = selectedItem && getReviewSource(selectedItem) === "test_zoom";
    const isSelectedTestMmpi = selectedItem && getReviewSource(selectedItem) === "test_mmpi";
    const isSelectedInterview = selectedItem && getReviewSource(selectedItem) === "interview";

    const getFileHasilTest = (item) => {
        return (
            item?.file_hasil_test_zoom_url ||
            item?.file_hasil_test_zoom ||
            item?.file_hasil_test_mmpi_url ||
            item?.file_hasil_test_mmpi ||
            item?.file_hasil_test ||
            null
        );
    };

    const getAlamatLengkap = (pelamarData) => {
        return (
            pelamarData?.alamat_ktp ||
            pelamarData?.alamat_domisili ||
            pelamarData?.alamat ||
            "-"
        );
    };

    const pelamar = selectedItem?.detail_kandidat || null;
    const selectedSource = selectedItem ? getSourceLabel(selectedItem) : "-";
    const selectedFileHasilTest = getFileHasilTest(selectedItem);

    const sameKandidat = (row, item) => {
        if (!row || !item) return false;

        const rowKandidatId = String(row.data_riwayat_diri_id || row.kandidat_id || "").trim();
        const itemKandidatId = String(item.data_riwayat_diri_id || item.kandidat_id || "").trim();

        if (rowKandidatId && itemKandidatId && rowKandidatId === itemKandidatId) {
            return true;
        }

        const rowEmail = String(row.email_kandidat || row.email || "").trim().toLowerCase();
        const itemEmail = String(item.email_kandidat || item.email || "").trim().toLowerCase();

        if (rowEmail && itemEmail && rowEmail === itemEmail) {
            return true;
        }

        const rowWa = String(row.no_wa_kandidat || row.no_wa || "").replace(/[^0-9]/g, "");
        const itemWa = String(item.no_wa_kandidat || item.no_wa || "").replace(/[^0-9]/g, "");

        if (rowWa && itemWa && rowWa === itemWa) {
            return true;
        }

        const rowNama = String(row.nama_kandidat || row.nama_lengkap || "").trim().toLowerCase();
        const itemNama = String(item.nama_kandidat || item.nama_lengkap || "").trim().toLowerCase();

        return Boolean(rowNama && itemNama && rowNama === itemNama);
    };

    const selectedZoomTest = selectedItem
        ? selectedItem.latest_test_zoom ||
          selectedItem.hasil_test_zoom_detail ||
          dataReview.find((row) => getReviewSource(row) === "test_zoom" && sameKandidat(row, selectedItem))
        : null;

    const selectedMmpiTest = selectedItem
        ? selectedItem.latest_test_mmpi ||
          selectedItem.hasil_test_mmpi_detail ||
          dataReview.find((row) => getReviewSource(row) === "test_mmpi" && sameKandidat(row, selectedItem))
        : null;

    const zoomTestData = isSelectedTestZoom ? selectedItem : selectedZoomTest;
    const mmpiTestData = isSelectedTestMmpi ? selectedItem : selectedMmpiTest;

    const zoomFileHasilTest = getFileHasilTest(zoomTestData);
    const mmpiFileHasilTest = getFileHasilTest(mmpiTestData);

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                        <form
                            onSubmit={handleFilterTanggal}
                            className="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto] md:items-end"
                        >
                            <DateInput
                                label="Tanggal Mulai Tahapan"
                                value={tanggalMulai}
                                onChange={setTanggalMulai}
                            />

                            <DateInput
                                label="Tanggal Selesai Tahapan"
                                value={tanggalSelesai}
                                onChange={setTanggalSelesai}
                            />

                            <button
                                type="submit"
                                disabled={tableLoading}
                                className="rounded-2xl bg-teal-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                Filter
                            </button>

                            <button
                                type="button"
                                disabled={tableLoading}
                                onClick={handleResetTanggal}
                                className="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                Reset Hari Ini
                            </button>
                        </form>

                        <div className="flex flex-wrap gap-2">
                            <span className="rounded-full bg-sky-100 px-4 py-2 text-xs font-black uppercase tracking-wide text-sky-700">
                                Hasil Test Zoom
                            </span>

                            <span className="rounded-full bg-cyan-100 px-4 py-2 text-xs font-black uppercase tracking-wide text-cyan-700">
                                Hasil Test MMPI
                            </span>

                            <span className="rounded-full bg-emerald-100 px-4 py-2 text-xs font-black uppercase tracking-wide text-emerald-700">
                                Lolos Interview
                            </span>

                            <span className="rounded-full bg-amber-100 px-4 py-2 text-xs font-black uppercase tracking-wide text-amber-700">
                                Dipertimbangkan
                            </span>
                        </div>
                    </div>
                </div>

                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div className="flex flex-col gap-4 md:flex-row md:items-center">
                            <div className="flex items-center gap-2">
                                <span className="text-sm font-bold text-slate-600">Show</span>

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

                                <span className="text-sm font-bold text-slate-600">entries</span>
                            </div>

                            <div className="flex items-center gap-2">
                                <span className="text-sm font-bold text-slate-600">Search:</span>

                                <input
                                    type="text"
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Cari nama, hasil test, interview, review..."
                                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100 md:w-96"
                                />
                            </div>
                        </div>

                        <p className="text-sm font-bold text-slate-500">
                            Filter aktif: {tanggalMulai || "-"} sampai {tanggalSelesai || "-"}
                        </p>
                    </div>

                    <div className="mt-4 flex flex-wrap gap-2">
                        {SOURCE_TABS.map((tab) => (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => setActiveSource(tab.key)}
                                className={`rounded-2xl px-4 py-2 text-xs font-black uppercase tracking-wide transition ${
                                    activeSource === tab.key
                                        ? "bg-teal-600 text-white shadow-lg shadow-teal-100"
                                        : "border border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Sumber</TableHead>
                                <TableHead>Tanggal Tahapan</TableHead>
                                <TableHead>Nama Kandidat</TableHead>
                                <TableHead>Posisi</TableHead>
                                <TableHead>Hasil</TableHead>
                                <TableHead>Status Kehadiran</TableHead>
                                <TableHead>Detail Hasil / Catatan</TableHead>
                                <TableHead>Review Management</TableHead>
                                <TableHead>Status Review</TableHead>
                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <td colSpan="11" className="px-6 py-16">
                                        <div className="text-center text-sm font-black text-slate-500">
                                            Memuat data...
                                        </div>
                                    </td>
                                </tr>
                            ) : paginatedData.length > 0 ? (
                                paginatedData.map((item, index) => (
                                    <tr
                                        key={`${getReviewSource(item)}-${getSourceId(item)}`}
                                        className={`group transition ${rowColorClass(item)}`}
                                    >
                                        <td className="px-6 py-5 text-sm font-black text-slate-500">
                                            {showingFrom + index}
                                        </td>

                                        <td className="px-6 py-5">
                                            <span className="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-wide text-slate-700">
                                                {getSourceLabel(item)}
                                            </span>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-800">
                                                {getTanggalTahapan(item)}
                                            </div>
                                            <div className="mt-1 text-xs font-semibold text-slate-500">
                                                {item.judul_tahapan || item.judul_interview || "-"}
                                            </div>
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
                                                    item
                                                )}`}
                                            >
                                                {getHasilLabel(item)}
                                            </span>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="text-sm font-black text-slate-700">
                                                {item.status_kehadiran || "-"}
                                            </div>
                                        </td>

                                        <td className="px-6 py-5">
                                            <div className="max-w-md text-sm font-semibold text-slate-600">
                                                {getReviewSource(item) === "test_zoom" ? (
                                                    <div className="space-y-1">
                                                        <div>Hasil Test: {getHasilLabel(item)}</div>
                                                        <div className="text-xs text-slate-500">IQ: {item.hasil_test_iq || "-"} • DISC: {item.hasil_test_disc || "-"} • Eysenck: {item.hasil_test_eysenck || "-"}</div>
                                                    </div>
                                                ) : getReviewSource(item) === "test_mmpi" ? (
                                                    <div>Hasil Test MMPI: {getHasilLabel(item)}</div>
                                                ) : (
                                                    item.catatan || "-"
                                                )}
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
                                                {item.status_review || "Belum Dipilih"}
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
                                                        onClick={() => handleDeleteReview(item)}
                                                        className="rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:bg-rose-50 hover:text-rose-700"
                                                    >
                                                        Kosongkan
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="11" className="px-6 py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ◉
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data review tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Belum ada data hasil test atau interview pada tanggal ini.
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
                        Showing {showingFrom} to {showingTo} of {filteredData.length} entries
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
                                        Review Management - {selectedSource}
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        Review Kandidat
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Kandidat: <span className="font-black text-slate-800">{selectedItem?.nama_kandidat || "-"}</span>
                                        {" • "}
                                        Tahapan: <span className="font-black text-slate-800">{selectedSource}</span>
                                        {" • "}
                                        Hasil: <span className="font-black text-slate-800">{getHasilLabel(selectedItem)}</span>
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
                                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                                    <div className="space-y-5">
                                        <SectionTitle title="Detail Kandidat" />

                                        <div className="rounded-3xl border border-slate-200 bg-slate-50/60 p-5">
                                            <div className="grid gap-3 sm:grid-cols-2">
                                                <DetailItem label="Nama Lengkap" value={pelamar?.nama_lengkap || selectedItem?.nama_kandidat} />
                                                <DetailItem label="Nama Panggil" value={pelamar?.nama_panggil} />
                                                <DetailItem label="Email" value={pelamar?.email || selectedItem?.email_kandidat} />
                                                <DetailItem label="No. WA" value={pelamar?.no_wa || selectedItem?.no_wa_kandidat} />
                                                <DetailItem label="Posisi Dilamar" value={pelamar?.posisi_label || selectedItem?.posisi_label} />
                                                <DetailItem label="Perusahaan" value={pelamar?.perusahaan_label || selectedItem?.perusahaan_label} />
                                                <DetailItem label="Pendidikan" value={pelamar?.pendidikan_label} />
                                                <DetailItem label="Jurusan" value={pelamar?.jurusan} />
                                                <DetailItem label="Institusi" value={pelamar?.nama_institusi} />
                                                <DetailItem label="Agama" value={pelamar?.agama_label} />
                                                <DetailItem label="Tanggal Lahir" value={formatDate(pelamar?.tanggal_lahir)} />
                                                <DetailItem label="Tanggal Skrining" value={formatDate(pelamar?.tanggal_skrining)} />
                                                <DetailItem label="Tempat Lahir" value={pelamar?.tempat_lahir} />
                                                <DetailItem label="Jenis Kelamin" value={pelamar?.jenis_kelamin} />
                                                <DetailItem label="Kewarganegaraan" value={pelamar?.kewarganegaraan_label} />
                                                <DetailItem label="Status Pernikahan" value={pelamar?.status_pernikahan_label} />
                                                <DetailItem label="Golongan Darah" value={pelamar?.gol_darah || pelamar?.golongan_darah} />
                                                <DetailItem
                                                    label="Tinggi / Berat"
                                                    value={`${pelamar?.tinggi_badan || "-"} cm / ${pelamar?.berat_badan || "-"} kg`}
                                                />
                                            </div>

                                            <div className="mt-4 grid gap-3">
                                                <DetailItem label="Alamat KTP" value={pelamar?.alamat_ktp} />
                                                <DetailItem label="Alamat Domisili" value={pelamar?.alamat_domisili} />
                                                <DetailItem label="Alamat Utama" value={getAlamatLengkap(pelamar)} />
                                                <DetailItem label="Sumber Informasi" value={pelamar?.sumber_informasi_label} />
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
                                                            className="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3"
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
                                        <SectionTitle title="Data Tahapan" />

                                        <div className="rounded-3xl border border-slate-200 bg-white p-5">
                                            <div className="grid gap-3">
                                                <DetailItem label="Sumber Review" value={selectedSource} />
                                                <DetailItem label="Judul Tahapan" value={selectedItem?.judul_tahapan || selectedItem?.judul_interview} />
                                                <DetailItem label="Tanggal Tahapan" value={getTanggalTahapan(selectedItem)} />
                                                <DetailItem label="Status Kehadiran" value={selectedItem?.status_kehadiran} />
                                                <DetailItem label="Hasil" value={getHasilLabel(selectedItem)} />
                                                <DetailItem label="Catatan" value={selectedItem?.catatan} />

                                                {isSelectedInterview && (
                                                    <>
                                                        <DetailItem label="Judul Interview" value={selectedItem?.judul_interview || selectedItem?.judul_tahapan} />
                                                        <DetailItem label="Tanggal Interview" value={selectedItem?.tanggal_interview_format || getTanggalTahapan(selectedItem)} />
                                                        <DetailItem label="Hasil Interview" value={selectedItem?.hasil_interview || selectedItem?.hasil_label} />
                                                    </>
                                                )}

                                                <div className="rounded-3xl border border-sky-100 bg-sky-50/70 p-4">
                                                    <div className="mb-3 text-xs font-black uppercase tracking-wide text-sky-700">
                                                        Hasil Test Zoom
                                                    </div>

                                                    <div className="grid gap-3 sm:grid-cols-2">
                                                        <DetailItem label="Tanggal Test Zoom" value={getTanggalTahapan(zoomTestData)} />
                                                        <DetailItem label="Status Kehadiran Zoom" value={zoomTestData?.status_kehadiran} />
                                                        <DetailItem label="Hasil Test Zoom" value={zoomTestData ? getHasilLabel(zoomTestData) : "-"} />
                                                        <DetailItem label="File Hasil Test Zoom" value={<FileLink url={zoomFileHasilTest} />} />
                                                        <DetailItem label="IQ" value={zoomTestData?.hasil_test_iq} />
                                                        <DetailItem label="DISC" value={zoomTestData?.hasil_test_disc} />
                                                        <DetailItem label="Eysenck" value={zoomTestData?.hasil_test_eysenck} />
                                                    </div>
                                                </div>

                                                <div className="rounded-3xl border border-cyan-100 bg-cyan-50/70 p-4">
                                                    <div className="mb-3 text-xs font-black uppercase tracking-wide text-cyan-700">
                                                        Hasil Test MMPI
                                                    </div>

                                                    <div className="grid gap-3 sm:grid-cols-2">
                                                        <DetailItem label="Tanggal Test MMPI" value={getTanggalTahapan(mmpiTestData)} />
                                                        <DetailItem label="Status Kehadiran MMPI" value={mmpiTestData?.status_kehadiran} />
                                                        <DetailItem label="Hasil Test MMPI" value={mmpiTestData ? getHasilLabel(mmpiTestData) : "-"} />
                                                        <DetailItem label="File Hasil Test MMPI" value={<FileLink url={mmpiFileHasilTest} />} />
                                                    </div>
                                                </div>
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
                                                Status
                                            </label>

                                            <select
                                                name="status"
                                                value={form.status}
                                                onChange={handleChange}
                                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                                            >
                                                <option value="">Pilih Status</option>
                                                {STATUS_OPTIONS.map((option) => (
                                                    <option key={option} value={option}>
                                                        {option}
                                                    </option>
                                                ))}
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

function DateInput({ label, value, onChange }) {
    return (
        <label className="block">
            <span className="mb-2 block text-sm font-black text-slate-700">{label}</span>

            <input
                type="date"
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            />
        </label>
    );
}

function TableHead({ children, align = "left" }) {
    const alignClass = align === "right" ? "text-right" : "text-left";

    return (
        <th
            className={`whitespace-nowrap px-6 py-4 ${alignClass} text-xs font-black uppercase tracking-[0.12em] text-slate-500`}
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
            <div className="mt-1 text-sm font-bold text-slate-700">{value || "-"}</div>
        </div>
    );
}

function FileLink({ url }) {
    if (!url) {
        return "-";
    }

    return (
        <a
            href={url}
            target="_blank"
            rel="noreferrer"
            className="inline-flex rounded-xl bg-white px-3 py-2 text-xs font-black text-teal-700 underline decoration-teal-300 underline-offset-4 transition hover:text-teal-900"
        >
            Lihat File
        </a>
    );
}

function Textarea({ label, name, value, onChange, required = false, placeholder = "" }) {
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
