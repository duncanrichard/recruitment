import React, { useEffect, useMemo, useRef, useState } from "react";

const STATUS_KEHADIRAN_OPTIONS = ["Hadir", "Tidak Hadir", "Tidak Respon", "Reschedule"];
const HASIL_INTERVIEW_OPTIONS = ["Lolos Interview", "Tidak Lolos Interview", "Dipertimbangkan"];

export default function KandidatInterviewPage({ actionSignals }) {
    const getTodayDate = () => {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    };

    const today = getTodayDate();

    const [dataList, setDataList] = useState([]);
    const [dataJadwal, setDataJadwal] = useState([]);
    const [dataKandidat, setDataKandidat] = useState([]);
    const [detailData, setDetailData] = useState(null);

    const [modalOpen, setModalOpen] = useState(false);
    const [tanggalModalOpen, setTanggalModalOpen] = useState(false);
    const [statusModalOpen, setStatusModalOpen] = useState(false);
    const [hasilModalOpen, setHasilModalOpen] = useState(false);
    const [catatanModalOpen, setCatatanModalOpen] = useState(false);
    const [selectedKandidat, setSelectedKandidat] = useState(null);

    const [loading, setLoading] = useState(false);
    const [tableLoading, setTableLoading] = useState(false);
    const [editId, setEditId] = useState(null);

    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);
    const [sortConfig, setSortConfig] = useState({ key: "jadwal", direction: "desc" });

    const [tanggalMulai, setTanggalMulai] = useState(today);
    const [tanggalSelesai, setTanggalSelesai] = useState(today);
    const [appliedFilter, setAppliedFilter] = useState({
        tanggalMulai: today,
        tanggalSelesai: today,
    });

    const [form, setForm] = useState({ jadwal_interview_id: "", kandidat_ids: [] });
    const [tanggalForm, setTanggalForm] = useState({ jadwal_interview: "" });
    const [statusForm, setStatusForm] = useState({ status_kehadiran: "" });
    const [hasilForm, setHasilForm] = useState({ hasil_interview: "" });
    const [catatanForm, setCatatanForm] = useState({ catatan: "" });

    const lastSignalRef = useRef(actionSignals?.kandidatInterview || 0);

    const getCsrfToken = () => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    };

    const resetForm = () => {
        setEditId(null);
        setForm({ jadwal_interview_id: "", kandidat_ids: [] });
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
                ? `/admin/rangkaian-interview/kandidat/list?${params.toString()}`
                : "/admin/rangkaian-interview/kandidat/list";

            const response = await fetch(url, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal mengambil data kandidat interview.");
                return;
            }

            setDataList(Array.isArray(result.data) ? result.data : []);
        } catch (error) {
            console.error("Gagal mengambil data kandidat interview:", error);
            alert("Terjadi kesalahan saat mengambil data kandidat interview.");
        } finally {
            setTableLoading(false);
        }
    };

    const fetchDetail = async (jadwalInterviewId) => {
        setTableLoading(true);

        try {
            const response = await fetch(`/admin/rangkaian-interview/kandidat/${jadwalInterviewId}/detail`, {
                headers: { Accept: "application/json" },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Gagal mengambil detail kandidat interview.");
                return;
            }

            setDetailData(result.data);
        } catch (error) {
            console.error("Gagal mengambil detail kandidat interview:", error);
            alert("Terjadi kesalahan saat mengambil detail kandidat interview.");
        } finally {
            setTableLoading(false);
        }
    };

    const fetchOptions = async (includeJadwalInterviewId = null) => {
        try {
            const includeParam = includeJadwalInterviewId
                ? `?include_jadwal_interview_id=${encodeURIComponent(includeJadwalInterviewId)}`
                : "";

            const jadwalUrl = `/admin/rangkaian-interview/kandidat/jadwal-options${includeParam}`;
            const kandidatUrl = `/admin/rangkaian-interview/kandidat/kandidat-options${includeParam}`;

            const [jadwalResponse, kandidatResponse] = await Promise.all([
                fetch(jadwalUrl, {
                    headers: { Accept: "application/json" },
                }),
                fetch(kandidatUrl, {
                    headers: { Accept: "application/json" },
                }),
            ]);

            const jadwalResult = await jadwalResponse.json();
            const kandidatResult = await kandidatResponse.json();

            if (jadwalResult.success) setDataJadwal(jadwalResult.data || []);
            if (kandidatResult.success) setDataKandidat(kandidatResult.data || []);
        } catch (error) {
            console.error("Gagal mengambil option:", error);
            alert("Terjadi kesalahan saat mengambil option.");
        }
    };

    useEffect(() => {
        const defaultFilter = {
            tanggalMulai: today,
            tanggalSelesai: today,
        };

        setAppliedFilter(defaultFilter);
        fetchData(defaultFilter);
        fetchOptions();

        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        const currentSignal = actionSignals?.kandidatInterview || 0;

        if (currentSignal > lastSignalRef.current) {
            resetForm();
            fetchOptions();
            setModalOpen(true);
        }

        lastSignalRef.current = currentSignal;
    }, [actionSignals?.kandidatInterview]);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage, dataList]);

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
        setCurrentPage(1);
        setAppliedFilter(defaultFilter);
        fetchData(defaultFilter);
    };

    const parseDateTimeValue = (value) => {
        if (!value) return 0;

        const cleanValue = String(value).replace("T", " " ).slice(0, 16);
        const [datePart, timePart = "00:00"] = cleanValue.split(" " );
        const [year, month, day] = (datePart || "").split("-").map(Number);
        const [hour, minute] = (timePart || "00:00").split(":").map(Number);

        if (!year || !month || !day) return 0;

        return Date.UTC(year, month - 1, day, hour || 0, minute || 0);
    };

    const formatDisplayDateTime = (value) => {
        if (!value) return "-";

        const cleanValue = String(value).replace("T", " " ).slice(0, 16);
        const [datePart, timePart] = cleanValue.split(" " );

        if (!datePart || !timePart) return value;

        const [year, month, day] = datePart.split("-");
        const [hour, minute] = timePart.split(":");

        const monthNames = [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
        ];

        return `${day} ${monthNames[Number(month) - 1] || month} ${year} pukul ${hour}.${minute}`;
    };

    const toDateTimeLocalValue = (value) => {
        if (!value) return "";

        return String(value).replace(" ", "T").slice(0, 16);
    };

    const formatKandidat = (kandidats = []) => {
        if (!Array.isArray(kandidats) || kandidats.length === 0) return "-";

        return kandidats
            .map((item) => item.nama_lengkap || item.nama_panggil || "-")
            .join(", ");
    };

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();
        if (!keyword) return dataList;

        return dataList.filter((item) => {
            const judul = String(item.jadwal_interview?.judul_interview || "").toLowerCase();
            const tanggal = String(item.jadwal_interview?.jadwal_interview || "").toLowerCase();
            const tanggalFormat = formatDisplayDateTime(item.jadwal_interview?.jadwal_interview).toLowerCase();
            const kandidat = formatKandidat(item.kandidats).toLowerCase();

            return (
                judul.includes(keyword) ||
                tanggal.includes(keyword) ||
                tanggalFormat.includes(keyword) ||
                kandidat.includes(keyword)
            );
        });
    }, [dataList, search]);

    const sortedData = useMemo(() => {
        const data = [...filteredData];

        data.sort((a, b) => {
            let valueA = "";
            let valueB = "";

            if (sortConfig.key === "jadwal") {
                valueA = parseDateTimeValue(a.jadwal_interview?.jadwal_interview);
                valueB = parseDateTimeValue(b.jadwal_interview?.jadwal_interview);
            }

            if (sortConfig.key === "judul") {
                valueA = String(a.jadwal_interview?.judul_interview || "").toLowerCase();
                valueB = String(b.jadwal_interview?.judul_interview || "").toLowerCase();
            }

            if (sortConfig.key === "kandidat") {
                valueA = formatKandidat(a.kandidats).toLowerCase();
                valueB = formatKandidat(b.kandidats).toLowerCase();
            }

            if (valueA < valueB) return sortConfig.direction === "asc" ? -1 : 1;
            if (valueA > valueB) return sortConfig.direction === "asc" ? 1 : -1;
            return 0;
        });

        return data;
    }, [filteredData, sortConfig]);

    const totalPages = Math.max(1, Math.ceil(sortedData.length / entriesPerPage));

    const paginatedData = useMemo(() => {
        const startIndex = (currentPage - 1) * entriesPerPage;
        return sortedData.slice(startIndex, startIndex + entriesPerPage);
    }, [sortedData, currentPage, entriesPerPage]);

    const showingFrom = sortedData.length === 0 ? 0 : (currentPage - 1) * entriesPerPage + 1;
    const showingTo = Math.min(currentPage * entriesPerPage, sortedData.length);

    const pageNumbers = useMemo(() => {
        const pages = [];
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        startPage = Math.max(1, endPage - 4);

        for (let page = startPage; page <= endPage; page++) pages.push(page);
        return pages;
    }, [currentPage, totalPages]);

    const handleSort = (key) => {
        setSortConfig((prev) => {
            if (prev.key === key) {
                return { key, direction: prev.direction === "asc" ? "desc" : "asc" };
            }

            return { key, direction: "asc" };
        });
    };

    const sortIcon = (key) => {
        if (sortConfig.key !== key) return "⇅";
        return sortConfig.direction === "asc" ? "↑" : "↓";
    };

    const closeModal = () => {
        setModalOpen(false);
        resetForm();
    };

    const refreshAfterUpdate = async () => {
        await fetchData(appliedFilter);

        if (detailData?.jadwal_interview_id) {
            await fetchDetail(detailData.jadwal_interview_id);
            await fetchOptions(detailData.jadwal_interview_id);
        } else {
            await fetchOptions();
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!form.jadwal_interview_id) return alert("Jadwal interview wajib dipilih.");
        if (!form.kandidat_ids.length) return alert("Minimal pilih satu kandidat.");

        setLoading(true);

        try {
            const url = editId
                ? `/admin/rangkaian-interview/kandidat/${editId}`
                : "/admin/rangkaian-interview/kandidat";

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

            if (!response.ok || !result.success) {
                alert(result.message || "Data gagal disimpan.");
                return;
            }

            alert(result.message || "Data berhasil disimpan.");
            closeModal();
            await fetchOptions();
            await refreshAfterUpdate();
        } catch (error) {
            console.error("Gagal menyimpan data:", error);
            alert("Terjadi kesalahan saat menyimpan data.");
        } finally {
            setLoading(false);
        }
    };

    const handleEditGroup = async (item) => {
        setEditId(item.jadwal_interview_id);
        setForm({
            jadwal_interview_id: item.jadwal_interview_id || "",
            kandidat_ids: Array.isArray(item.kandidat_ids) ? item.kandidat_ids : [],
        });

        await fetchOptions(item.jadwal_interview_id);
        setModalOpen(true);
    };

    const openTanggalModal = (item) => {
        setEditId(item.jadwal_interview_id);
        setTanggalForm({
            jadwal_interview: toDateTimeLocalValue(item.jadwal_interview?.jadwal_interview),
        });
        setTanggalModalOpen(true);
    };

    const submitTanggal = async (e) => {
        e.preventDefault();

        if (!tanggalForm.jadwal_interview) return alert("Tanggal interview wajib diisi.");

        setLoading(true);

        try {
            const response = await fetch(`/admin/rangkaian-interview/kandidat/${editId}/tanggal`, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify({ jadwal_interview: tanggalForm.jadwal_interview }),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Tanggal gagal diperbarui.");
                return;
            }

            alert(result.message || "Tanggal berhasil diperbarui.");
            setTanggalModalOpen(false);
            await refreshAfterUpdate();
        } catch (error) {
            console.error("Gagal memperbarui tanggal:", error);
            alert("Terjadi kesalahan saat memperbarui tanggal.");
        } finally {
            setLoading(false);
        }
    };

    const openStatusModal = (kandidat) => {
        setSelectedKandidat(kandidat);
        setStatusForm({ status_kehadiran: kandidat.status_kehadiran || "" });
        setStatusModalOpen(true);
    };

    const openHasilModal = (kandidat) => {
        if (!canEditHasilInterview(kandidat)) {
            alert(getHasilInterviewDisabledMessage(kandidat));
            return;
        }

        setSelectedKandidat(kandidat);
        setHasilForm({ hasil_interview: kandidat.hasil_interview || "" });
        setHasilModalOpen(true);
    };

    const openCatatanModal = (kandidat) => {
        setSelectedKandidat(kandidat);
        setCatatanForm({ catatan: kandidat.catatan || "" });
        setCatatanModalOpen(true);
    };

    const hasStatusKehadiran = (kandidat) => {
        return Boolean(String(kandidat?.status_kehadiran || "").trim());
    };

    const canEditHasilInterview = (kandidat) => {
        const status = String(kandidat?.status_kehadiran || "").trim();
        return Boolean(status) && status !== "Reschedule";
    };

    const getHasilInterviewDisabledMessage = (kandidat) => {
        if (!hasStatusKehadiran(kandidat)) {
            return "Pilih status kehadiran terlebih dahulu sebelum mengisi hasil interview.";
        }

        if (String(kandidat?.status_kehadiran || "").trim() === "Reschedule") {
            return "Hasil interview tidak bisa diisi karena kandidat berstatus Reschedule.";
        }

        return "Klik untuk edit hasil interview";
    };

    const updateKandidatField = async (payload, successMessage = "Data kandidat berhasil diperbarui.") => {
        if (!detailData || !selectedKandidat) return;

        setLoading(true);

        try {
            const response = await fetch(
                `/admin/rangkaian-interview/kandidat/${detailData.jadwal_interview_id}/kandidat/${selectedKandidat.pivot_id}/hasil`,
                {
                    method: "PATCH",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                    body: JSON.stringify(payload),
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Data kandidat gagal diperbarui.");
                return;
            }

            alert(result.message || successMessage);
            setStatusModalOpen(false);
            setHasilModalOpen(false);
            setCatatanModalOpen(false);
            await refreshAfterUpdate();
        } catch (error) {
            console.error("Gagal memperbarui kandidat:", error);
            alert("Terjadi kesalahan saat memperbarui kandidat.");
        } finally {
            setLoading(false);
        }
    };

    const submitStatus = async (e) => {
        e.preventDefault();

        const statusKehadiran = statusForm.status_kehadiran || null;
        const payload = {
            status_kehadiran: statusKehadiran,
        };

        if (!statusKehadiran || statusKehadiran === "Reschedule") {
            payload.hasil_interview = null;
        }

        await updateKandidatField(payload, "Status kehadiran berhasil diperbarui.");
    };

    const submitHasil = async (e) => {
        e.preventDefault();
        await updateKandidatField(
            { hasil_interview: hasilForm.hasil_interview || null },
            "Hasil interview berhasil diperbarui."
        );
    };

    const submitCatatan = async (e) => {
        e.preventDefault();
        await updateKandidatField(
            { catatan: catatanForm.catatan || null },
            "Catatan berhasil disimpan."
        );
    };

    const handleDeleteGroup = async (id) => {
        if (!confirm("Yakin ingin menghapus semua kandidat dari jadwal ini?")) return;

        try {
            const response = await fetch(`/admin/rangkaian-interview/kandidat/${id}`, {
                method: "DELETE",
                headers: { Accept: "application/json", "X-CSRF-TOKEN": getCsrfToken() },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Data gagal dihapus.");
                return;
            }

            alert(result.message || "Data berhasil dihapus.");
            if (detailData?.jadwal_interview_id === id) setDetailData(null);
            await fetchData(appliedFilter);
        } catch (error) {
            console.error("Gagal menghapus data:", error);
            alert("Terjadi kesalahan saat menghapus data.");
        }
    };

    const handleDeleteKandidat = async (kandidat) => {
        if (!detailData) return;

        const nama = kandidat.nama_lengkap || kandidat.nama_panggil || "kandidat";
        if (!confirm(`Hapus ${nama} dari jadwal ini?`)) return;

        try {
            const response = await fetch(
                `/admin/rangkaian-interview/kandidat/${detailData.jadwal_interview_id}/kandidat/${kandidat.pivot_id}`,
                {
                    method: "DELETE",
                    headers: { Accept: "application/json", "X-CSRF-TOKEN": getCsrfToken() },
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                alert(result.message || "Kandidat gagal dihapus.");
                return;
            }

            alert(result.message || "Kandidat berhasil dihapus.");
            await refreshAfterUpdate();
        } catch (error) {
            console.error("Gagal menghapus kandidat:", error);
            alert("Terjadi kesalahan saat menghapus kandidat.");
        }
    };

    const renderModals = () => (
        <>
            {modalOpen && (
                <FormModal
                    title={editId ? "Edit Kandidat" : "Tambah Kandidat"}
                    subtitle="Pilih jadwal interview dan kandidat."
                    onClose={closeModal}
                    onSubmit={handleSubmit}
                    loading={loading}
                    submitLabel={editId ? "Update Data" : "Simpan Data"}
                >
                    {!editId && (
                        <div className="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs font-bold leading-5 text-amber-700">
                            <div className="mb-1 font-black text-amber-800">
                                Catatan Jadwal Interview
                            </div>

                            <p>
                                Jadwal interview yang sudah melewati tanggal atau jam pelaksanaan tidak akan tampil pada pilihan.
                                Jika jadwal yang dicari tidak tersedia, silakan buat jadwal interview baru terlebih dahulu.
                            </p>
                        </div>
                    )}

                    <SelectJadwal
                        label="Jadwal Interview"
                        name="jadwal_interview_id"
                        value={form.jadwal_interview_id}
                        onChange={(e) =>
                            setForm((prev) => ({
                                ...prev,
                                jadwal_interview_id: e.target.value,
                            }))
                        }
                        options={dataJadwal}
                        disabled={Boolean(editId)}
                        required
                    />

                    <Select2Multi
                        label="Kandidat"
                        value={form.kandidat_ids}
                        options={dataKandidat}
                        onChange={(selectedIds) =>
                            setForm((prev) => ({
                                ...prev,
                                kandidat_ids: selectedIds,
                            }))
                        }
                        placeholder="Pilih kandidat..."
                        required
                    />

                    <p className="rounded-xl bg-slate-50 px-4 py-3 text-xs font-bold leading-5 text-slate-500">
                        Kandidat yang tampil adalah pelamar yang sudah lolos MMPI dan belum memiliki jadwal interview aktif.
                        Dalam satu jadwal interview bisa memilih lebih dari satu kandidat.
                    </p>
                </FormModal>
            )}

            {tanggalModalOpen && (
                <FormModal
                    title="Edit Tanggal Interview"
                    subtitle="Tanggal akan berubah untuk semua kandidat dalam grup jadwal ini."
                    onClose={() => setTanggalModalOpen(false)}
                    onSubmit={submitTanggal}
                    loading={loading}
                    submitLabel="Update Tanggal"
                >
                    <InputField
                        label="Tanggal Interview"
                        type="datetime-local"
                        value={tanggalForm.jadwal_interview}
                        onChange={(e) => setTanggalForm({ jadwal_interview: e.target.value })}
                        required
                    />
                </FormModal>
            )}

            {statusModalOpen && (
                <FormModal
                    title="Edit Status Kehadiran"
                    subtitle={selectedKandidat ? selectedKandidat.nama_lengkap || selectedKandidat.nama_panggil || "" : ""}
                    onClose={() => setStatusModalOpen(false)}
                    onSubmit={submitStatus}
                    loading={loading}
                    submitLabel="Simpan Status"
                    maxWidth="max-w-xl"
                >
                    <SelectField
                        label="Status Kehadiran"
                        value={statusForm.status_kehadiran}
                        onChange={(e) => setStatusForm({ status_kehadiran: e.target.value })}
                        options={STATUS_KEHADIRAN_OPTIONS}
                        placeholder="Pilih status kehadiran"
                    />
                </FormModal>
            )}

            {hasilModalOpen && (
                <FormModal
                    title="Edit Hasil Interview"
                    subtitle={selectedKandidat ? selectedKandidat.nama_lengkap || selectedKandidat.nama_panggil || "" : ""}
                    onClose={() => setHasilModalOpen(false)}
                    onSubmit={submitHasil}
                    loading={loading}
                    submitLabel="Simpan Hasil"
                    maxWidth="max-w-xl"
                >
                    <p className="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs font-bold leading-5 text-amber-700">
                        Hasil interview hanya bisa diisi setelah status kehadiran kandidat dipilih.
                    </p>

                    <SelectField
                        label="Hasil Interview"
                        value={hasilForm.hasil_interview}
                        onChange={(e) => setHasilForm({ hasil_interview: e.target.value })}
                        options={HASIL_INTERVIEW_OPTIONS}
                        placeholder="Pilih hasil interview"
                    />
                </FormModal>
            )}

            {catatanModalOpen && (
                <FormModal
                    title="Tambahkan Catatan"
                    subtitle={selectedKandidat ? selectedKandidat.nama_lengkap || selectedKandidat.nama_panggil || "" : ""}
                    onClose={() => setCatatanModalOpen(false)}
                    onSubmit={submitCatatan}
                    loading={loading}
                    submitLabel="Simpan Catatan"
                >
                    <div>
                        <label className="mb-2 block text-sm font-black text-slate-700">Catatan</label>
                        <textarea
                            value={catatanForm.catatan}
                            onChange={(e) => setCatatanForm({ catatan: e.target.value })}
                            rows={5}
                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            placeholder="Tambahkan catatan interview..."
                        />
                    </div>
                </FormModal>
            )}
        </>
    );

    if (detailData) {
        return (
            <div className="space-y-6">
                <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <button
                                type="button"
                                onClick={() => setDetailData(null)}
                                className="mb-4 whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                            >
                                ← Kembali
                            </button>

                            <div className="inline-flex whitespace-nowrap rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                                Detail Jadwal Interview
                            </div>

                            <h2 className="mt-3 text-2xl font-black text-slate-950">
                                {detailData.jadwal_interview?.judul_interview || "-"}
                            </h2>

                            <p className="mt-2 text-sm font-bold text-slate-500">
                                Tanggal Interview: {formatDisplayDateTime(detailData.jadwal_interview?.jadwal_interview)}
                            </p>

                            <p className="mt-1 text-sm font-bold text-slate-500">
                                Total Kandidat: {detailData.jumlah_kandidat || 0}
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                onClick={() => openTanggalModal(detailData)}
                                className="whitespace-nowrap rounded-2xl bg-slate-900 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-slate-700"
                            >
                                Edit Tanggal
                            </button>

                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-max w-full">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <TableHead>Kandidat</TableHead>
                                <TableHead>No WA</TableHead>
                                <TableHead>Status Kehadiran</TableHead>
                                <TableHead>Hasil Interview</TableHead>
                                <TableHead>Catatan</TableHead>
                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {(detailData.kandidats || []).length > 0 ? (
                                (detailData.kandidats || []).map((kandidat, index) => (
                                    <tr key={kandidat.pivot_id} className="transition hover:bg-slate-50">
                                        <TableCell className="text-sm font-black text-slate-500">{index + 1}</TableCell>

                                        <TableCell>
                                            <div className="font-black text-slate-950">
                                                {kandidat.nama_lengkap || kandidat.nama_panggil || "-"}
                                            </div>
                                            <div className="text-xs font-bold text-slate-400">
                                                {kandidat.nama_panggil || "-"}
                                            </div>
                                        </TableCell>

                                        <TableCell className="text-sm font-bold text-slate-600">
                                            {kandidat.no_wa || "-"}
                                        </TableCell>

                                        <TableCell>
                                            <button
                                                type="button"
                                                onClick={() => openStatusModal(kandidat)}
                                                className="whitespace-nowrap rounded-full transition hover:scale-[1.02] focus:outline-none focus:ring-4 focus:ring-indigo-100"
                                                title="Klik untuk edit status kehadiran"
                                            >
                                                <Badge value={kandidat.status_kehadiran} />
                                            </button>
                                        </TableCell>

                                        <TableCell>
                                            <button
                                                type="button"
                                                disabled={!canEditHasilInterview(kandidat)}
                                                onClick={() => openHasilModal(kandidat)}
                                                className={`whitespace-nowrap rounded-full transition focus:outline-none focus:ring-4 focus:ring-indigo-100 ${
                                                    canEditHasilInterview(kandidat)
                                                        ? "hover:scale-[1.02]"
                                                        : "cursor-not-allowed opacity-60"
                                                }`}
                                                title={getHasilInterviewDisabledMessage(kandidat)}
                                            >
                                                <Badge value={kandidat.hasil_interview} />
                                            </button>
                                        </TableCell>

                                        <TableCell className="max-w-[360px] text-sm font-bold text-slate-500">
                                            <div className="truncate" title={kandidat.catatan || "-"}>
                                                {kandidat.catatan || "-"}
                                            </div>
                                        </TableCell>

                                        <TableCell align="right">
                                            <div className="flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => openCatatanModal(kandidat)}
                                                    className="whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                                >
                                                    Tambahkan Catatan
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => handleDeleteKandidat(kandidat)}
                                                    className="whitespace-nowrap rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:bg-rose-50"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </TableCell>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <TableCell colSpan={7} className="py-16 text-center text-sm font-black text-slate-500">
                                        Belum ada kandidat pada jadwal ini.
                                    </TableCell>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {renderModals()}
            </div>
        );
    }

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
                                label="Tanggal Mulai"
                                value={tanggalMulai}
                                onChange={setTanggalMulai}
                            />

                            <DateInput
                                label="Tanggal Selesai"
                                value={tanggalSelesai}
                                onChange={setTanggalSelesai}
                            />

                            <button
                                type="submit"
                                disabled={tableLoading}
                                className="rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
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

                        <div className="flex items-center gap-2">
                            <span className="whitespace-nowrap text-sm font-bold text-slate-600">Search:</span>

                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Cari kandidat interview..."
                                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 md:w-80"
                            />
                        </div>
                    </div>
                </div>

                <div className="border-b border-slate-100 px-6 py-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-center gap-2">
                            <span className="whitespace-nowrap text-sm font-bold text-slate-600">Show</span>

                            <select
                                value={entriesPerPage}
                                onChange={(e) => setEntriesPerPage(Number(e.target.value))}
                                className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >
                                <option value={5}>5</option>
                                <option value={10}>10</option>
                                <option value={25}>25</option>
                                <option value={50}>50</option>
                                <option value={100}>100</option>
                            </select>

                            <span className="whitespace-nowrap text-sm font-bold text-slate-600">entries</span>
                        </div>

                        <p className="text-sm font-bold text-slate-500">
                            Filter aktif: {tanggalMulai || "-"} sampai {tanggalSelesai || "-"}
                        </p>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-max w-full">
                        <thead>
                            <tr className="bg-slate-50/80">
                                <TableHead>No</TableHead>
                                <SortableTableHead label="Jadwal" sortKey="judul" onSort={handleSort} icon={sortIcon("judul")} />
                                <SortableTableHead label="Tanggal Interview" sortKey="jadwal" onSort={handleSort} icon={sortIcon("jadwal")} />
                                <TableHead>Jumlah</TableHead>
                                <TableHead align="right">Aksi</TableHead>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tableLoading ? (
                                <tr>
                                    <TableCell colSpan={5} className="py-16 text-center text-sm font-black text-slate-500">
                                        Memuat data...
                                    </TableCell>
                                </tr>
                            ) : paginatedData.length > 0 ? (
                                paginatedData.map((item, index) => (
                                    <tr key={item.jadwal_interview_id} className="group transition hover:bg-slate-50">
                                        <TableCell className="text-sm font-black text-slate-500">
                                            {showingFrom + index}
                                        </TableCell>

                                        <TableCell>
                                            <div className="font-black text-slate-950">
                                                {item.jadwal_interview?.judul_interview || "-"}
                                            </div>
                                        </TableCell>

                                        <TableCell className="text-sm font-bold text-slate-600">
                                            {formatDisplayDateTime(item.jadwal_interview?.jadwal_interview)}
                                        </TableCell>

                                        <TableCell className="text-sm font-black text-slate-600">
                                            {item.jumlah_kandidat || 0}
                                        </TableCell>

                                        <TableCell align="right">
                                            <div className="flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => fetchDetail(item.jadwal_interview_id)}
                                                    className="whitespace-nowrap rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-2 text-xs font-black text-indigo-700 shadow-sm transition hover:bg-indigo-100"
                                                >
                                                    Detail
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => openTanggalModal(item)}
                                                    className="whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                                >
                                                    Edit Tanggal
                                                </button>


                                                <button
                                                    type="button"
                                                    onClick={() => handleEditGroup(item)}
                                                    className="whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => handleDeleteGroup(item.jadwal_interview_id)}
                                                    className="whitespace-nowrap rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:bg-rose-50"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </TableCell>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <TableCell colSpan={5} className="py-16">
                                        <div className="mx-auto max-w-sm text-center">
                                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                                ▤
                                            </div>

                                            <h3 className="mt-4 text-lg font-black text-slate-900">
                                                Data kandidat interview tidak ditemukan
                                            </h3>

                                            <p className="mt-2 text-sm font-medium text-slate-500">
                                                Belum ada kandidat interview pada tanggal atau periode ini.
                                            </p>
                                        </div>
                                    </TableCell>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="flex flex-col gap-4 border-t border-slate-100 px-6 py-4 md:flex-row md:items-center md:justify-between">
                    <p className="whitespace-nowrap text-sm font-bold text-slate-500">
                        Showing {showingFrom} to {showingTo} of {sortedData.length} entries
                        {search && <span> filtered from {dataList.length} total entries</span>}
                    </p>

                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            disabled={currentPage === 1}
                            onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                            className="whitespace-nowrap rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>

                        {pageNumbers.map((page) => (
                            <button
                                key={page}
                                type="button"
                                onClick={() => setCurrentPage(page)}
                                className={`whitespace-nowrap rounded-xl px-4 py-2 text-sm font-black shadow-sm transition ${
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
                            onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages))}
                            className="whitespace-nowrap rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>

            {renderModals()}
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

function SortableTableHead({ label, sortKey, onSort, icon }) {
    return (
        <th className="whitespace-nowrap px-6 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-500">
            <button
                type="button"
                onClick={() => onSort(sortKey)}
                className="inline-flex whitespace-nowrap items-center gap-2 font-black uppercase tracking-[0.12em] text-slate-500 transition hover:text-slate-800"
            >
                <span>{label}</span>
                <span className="text-xs">{icon}</span>
            </button>
        </th>
    );
}

function TableCell({ children, align = "left", className = "", colSpan }) {
    return (
        <td
            colSpan={colSpan}
            className={`whitespace-nowrap px-6 py-5 ${align === "right" ? "text-right" : "text-left"} ${className}`}
        >
            {children}
        </td>
    );
}

function FormModal({ title, subtitle, children, onClose, onSubmit, loading, submitLabel, maxWidth = "max-w-3xl" }) {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div className={`flex max-h-[92vh] w-full ${maxWidth} flex-col overflow-visible rounded-[2rem] bg-white shadow-2xl`}>
                <div className="shrink-0 border-b border-slate-200 bg-white">
                    <div className="flex items-center justify-between gap-4 px-6 py-5">
                        <div>
                            <div className="inline-flex whitespace-nowrap rounded-full bg-indigo-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-indigo-700">
                                Form Kandidat Interview
                            </div>

                            <h2 className="mt-2 text-2xl font-black text-slate-950">{title}</h2>

                            {subtitle && <p className="mt-1 text-sm font-medium text-slate-500">{subtitle}</p>}
                        </div>

                        <button
                            type="button"
                            onClick={onClose}
                            className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-500 transition hover:bg-slate-200 hover:text-slate-800"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <form onSubmit={onSubmit}>
                    <div className="space-y-5 px-6 py-6">{children}</div>

                    <div className="shrink-0 border-t border-slate-200 bg-white px-6 py-4">
                        <div className="flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={onClose}
                                className="whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                disabled={loading}
                                className="whitespace-nowrap rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-100 transition hover:from-indigo-700 hover:to-violet-700 disabled:cursor-not-allowed disabled:opacity-60"
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

function InputField({ label, required, ...props }) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <input
                {...props}
                required={required}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            />
        </div>
    );
}

function SelectField({ label, value, onChange, options, placeholder }) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">{label}</label>

            <select
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

function Badge({ value }) {
    const cls =
        value === "Hadir" || value === "Lolos Interview"
            ? "bg-emerald-50 text-emerald-700"
            : value === "Tidak Hadir" || value === "Tidak Lolos Interview"
              ? "bg-rose-50 text-rose-700"
              : value === "Tidak Respon" || value === "Dipertimbangkan"
                ? "bg-amber-50 text-amber-700"
                : value === "Reschedule"
                  ? "bg-blue-50 text-blue-700"
                  : "bg-slate-100 text-slate-500";

    return (
        <span className={`inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-black ${cls}`}>
            {value || "Belum Diisi"}
        </span>
    );
}

function SelectJadwal({ label, name, value, onChange, options, required = false, disabled = false }) {
    const formatJadwalOptionDateTime = (dateTimeValue) => {
        if (!dateTimeValue) return "-";

        const cleanValue = String(dateTimeValue).replace("T", " ").slice(0, 16);
        const [datePart, timePart] = cleanValue.split(" ");

        if (!datePart || !timePart) return dateTimeValue;

        const [year, month, day] = datePart.split("-");
        const [hour, minute] = timePart.split(":");

        const monthNames = [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
        ];

        return `${day} ${monthNames[Number(month) - 1] || month} ${year} pukul ${hour}.${minute}`;
    };

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
                disabled={disabled}
                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 disabled:bg-slate-100"
            >
                <option value="">Pilih jadwal interview</option>

                {options.map((item) => (
                    <option key={item.id} value={item.id}>
                        {item.judul_interview || "-"} - {formatJadwalOptionDateTime(item.jadwal_interview)}
                    </option>
                ))}
            </select>

        </div>
    );
}

function Select2Multi({ label, value, options, onChange, placeholder = "Pilih data...", required = false }) {
    const wrapperRef = useRef(null);
    const inputRef = useRef(null);

    const [open, setOpen] = useState(false);
    const [keyword, setKeyword] = useState("");

    const selectedOptions = useMemo(() => {
        return options.filter((item) => value.includes(item.id));
    }, [options, value]);

    const getLabel = (item) => item.nama_lengkap || item.nama_panggil || "-";

    const getPosisiLabel = (item) => {
        return (
            item?.posisi_dilamar ||
            item?.posisi_yang_dilamar ||
            item?.nama_posisi ||
            item?.posisi ||
            item?.jabatan ||
            "-"
        );
    };

    const availableOptions = useMemo(() => {
        const lowerKeyword = keyword.toLowerCase().trim();

        return options.filter((item) => {
            const isSelected = value.includes(item.id);
            const labelText = `${getLabel(item)} ${getPosisiLabel(item)}`;

            return !isSelected && labelText.toLowerCase().includes(lowerKeyword);
        });
    }, [options, value, keyword]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
                setOpen(false);
                setKeyword("");
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    const handleSelect = (id) => {
        if (!value.includes(id)) onChange([...value, id]);
        setKeyword("");
        setOpen(true);
        setTimeout(() => inputRef.current?.focus(), 0);
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
                    open ? "border-indigo-500 ring-4 ring-indigo-100" : "border-slate-200"
                }`}
            >
                <div className="flex flex-wrap items-center gap-2">
                    {selectedOptions.map((item) => {
                        const posisiLabel = getPosisiLabel(item);

                        return (
                            <span
                                key={item.id}
                                className="inline-flex max-w-full items-center gap-2 rounded-xl bg-indigo-50 px-3 py-1.5 text-xs font-black text-indigo-700"
                                title={`${getLabel(item)}${posisiLabel !== "-" ? ` - ${posisiLabel}` : ""}`}
                            >
                                <span className="max-w-[240px] truncate">
                                    {getLabel(item)}
                                    {posisiLabel !== "-" && (
                                        <span className="font-bold text-indigo-600"> · {posisiLabel}</span>
                                    )}
                                </span>

                                <button
                                    type="button"
                                    onClick={(event) => {
                                        event.stopPropagation();
                                        onChange(value.filter((id) => id !== item.id));
                                    }}
                                    className="rounded-full text-sm leading-none text-indigo-700 hover:text-rose-600"
                                >
                                    ×
                                </button>
                            </span>
                        );
                    })}

                    <input
                        ref={inputRef}
                        type="text"
                        value={keyword}
                        onChange={(event) => {
                            setKeyword(event.target.value);
                            setOpen(true);
                        }}
                        onFocus={() => setOpen(true)}
                        placeholder={selectedOptions.length === 0 ? placeholder : "Cari..."}
                        className="min-w-[140px] flex-1 border-none bg-transparent px-1 py-1 text-sm font-bold text-slate-700 outline-none placeholder:text-slate-300"
                    />

                    {value.length > 0 && (
                        <button
                            type="button"
                            onClick={(event) => {
                                event.stopPropagation();
                                onChange([]);
                                setKeyword("");
                                setOpen(false);
                            }}
                            className="ml-auto whitespace-nowrap rounded-xl px-2 py-1 text-xs font-black text-slate-400 transition hover:bg-slate-100 hover:text-rose-600"
                        >
                            Clear
                        </button>
                    )}

                    <span className="whitespace-nowrap text-xs font-black text-slate-400">{open ? "▲" : "▼"}</span>
                </div>
            </div>

            {open && (
                <div className="absolute z-[70] mt-2 max-h-72 w-full overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl">
                    {availableOptions.length > 0 ? (
                        availableOptions.map((item) => {
                            const posisiLabel = getPosisiLabel(item);

                            return (
                                <button
                                    key={item.id}
                                    type="button"
                                    onClick={() => handleSelect(item.id)}
                                    className="flex w-full items-start justify-between gap-4 rounded-xl px-4 py-3 text-left text-sm font-bold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
                                >
                                    <span className="min-w-0">
                                        <span className="block truncate font-black text-slate-800">
                                            {getLabel(item)}
                                        </span>

                                        <span className="mt-1 inline-flex max-w-full rounded-full bg-slate-100 px-2 py-1 text-[11px] font-black text-slate-600">
                                            <span className="truncate">
                                                Posisi: {posisiLabel}
                                            </span>
                                        </span>
                                    </span>

                                    <span className="mt-1 shrink-0 text-xs text-slate-400">＋</span>
                                </button>
                            );
                        })
                    ) : (
                        <div className="px-4 py-6 text-center text-sm font-bold text-slate-400">
                            {options.length === 0 ? "Belum ada kandidat" : "Data tidak ditemukan"}
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
