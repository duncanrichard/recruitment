import React, { useEffect, useMemo, useRef, useState } from "react";

export default function JadwalTestZoomPage({ actionSignals }) {
    const [dataJadwal, setDataJadwal] = useState([]);
    const [dataPelamar, setDataPelamar] = useState([]);

    const [modalOpen, setModalOpen] = useState(false);
    const [detailModalOpen, setDetailModalOpen] = useState(false);

    const [loading, setLoading] = useState(false);
    const [detailLoading, setDetailLoading] = useState(false);

    const [editingData, setEditingData] = useState(null);
    const [detailData, setDetailData] = useState(null);

    const [linkModalOpen, setLinkModalOpen] = useState(false);
    const [linkLoading, setLinkLoading] = useState(false);
    const [editingGroup, setEditingGroup] = useState(null);

    const isFirstActionSignalRender = useRef(true);

    const [form, setForm] = useState({
        tanggal_skrining: "",
        data_riwayat_diri_ids: [],
        data_riwayat_diri_id: "",
        jadwal: "",
        link_zoom: "",
    });

    const [groupLinkForm, setGroupLinkForm] = useState({
        jadwal: "",
        link_zoom: "",
    });

    /*
    |--------------------------------------------------------------------------
    | Data pelamar yang sudah dijadwalkan
    |--------------------------------------------------------------------------
    | Setelah controller diganti menjadi grouped response, dataJadwal tidak lagi
    | selalu berisi data_riwayat_diri_id. Karena itu filter utama tetap memakai
    | field sudah_dijadwalkan dari endpoint pelamar/list.
    */
    const scheduledPelamarIds = useMemo(() => {
        const ids = [];

        dataJadwal.forEach((group) => {
            if (group?.data_riwayat_diri_id) {
                ids.push(group.data_riwayat_diri_id);
            }

            if (Array.isArray(group?.pelamar_ids)) {
                ids.push(...group.pelamar_ids);
            }
        });

        return new Set(ids.filter(Boolean));
    }, [dataJadwal]);

    const pelamarBelumDijadwalkan = useMemo(() => {
        return dataPelamar.filter((item) => {
            return !scheduledPelamarIds.has(item.id) && !item.sudah_dijadwalkan;
        });
    }, [dataPelamar, scheduledPelamarIds]);

    const tanggalSkriningOptions = useMemo(() => {
        const map = new Map();

        pelamarBelumDijadwalkan.forEach((item) => {
            const tanggal = normalizeDate(item.tanggal_skrining);

            if (!tanggal) return;

            if (!map.has(tanggal)) {
                map.set(tanggal, {
                    tanggal,
                    total: 0,
                });
            }

            map.get(tanggal).total += 1;
        });

        return Array.from(map.values()).sort((a, b) =>
            String(b.tanggal).localeCompare(String(a.tanggal))
        );
    }, [pelamarBelumDijadwalkan]);

    const filteredPelamarByTanggal = useMemo(() => {
        if (!form.tanggal_skrining) return [];

        return pelamarBelumDijadwalkan.filter((item) => {
            return normalizeDate(item.tanggal_skrining) === form.tanggal_skrining;
        });
    }, [pelamarBelumDijadwalkan, form.tanggal_skrining]);

    const selectedPelamar = useMemo(() => {
        return dataPelamar.filter((item) =>
            form.data_riwayat_diri_ids.includes(item.id)
        );
    }, [dataPelamar, form.data_riwayat_diri_ids]);

    const summary = useMemo(() => {
        return dataJadwal.reduce(
            (acc, item) => {
                acc.totalGroup += 1;
                acc.totalPelamar += Number(item?.total_pelamar || 0);
                acc.totalHadir += Number(item?.total_hadir || 0);
                acc.totalTidakHadir += Number(item?.total_tidak_hadir || 0);
                acc.totalBelumKonfirmasi += Number(
                    item?.total_belum_konfirmasi || 0
                );

                return acc;
            },
            {
                totalGroup: 0,
                totalPelamar: 0,
                totalHadir: 0,
                totalTidakHadir: 0,
                totalBelumKonfirmasi: 0,
            }
        );
    }, [dataJadwal]);

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const fetchJson = async (url, options = {}) => {
        const response = await fetch(url, {
            headers: {
                Accept: "application/json",
                ...(options.headers || {}),
            },
            ...options,
        });

        const text = await response.text();

        let result = {};

        try {
            result = text ? JSON.parse(text) : {};
        } catch (error) {
            result = {
                success: false,
                message: "Response server bukan JSON.",
            };
        }

        if (!response.ok) {
            throw new Error(result.message || "Request gagal.");
        }

        return result;
    };

    const fetchData = async () => {
        try {
            const result = await fetchJson("/admin/jadwal-test/zoom/list");

            if (result.success) {
                setDataJadwal(Array.isArray(result.data) ? result.data : []);
            }
        } catch (error) {
            console.error("Gagal mengambil data jadwal Zoom:", error);
            alert(error.message || "Gagal mengambil data jadwal Zoom.");
        }
    };

    const fetchPelamar = async () => {
        try {
            const result = await fetchJson("/admin/jadwal-test/zoom/pelamar/list");

            if (result.success) {
                setDataPelamar(Array.isArray(result.data) ? result.data : []);
            }
        } catch (error) {
            console.error("Gagal mengambil data pelamar:", error);
            alert(error.message || "Gagal mengambil data pelamar.");
        }
    };

    const refreshAllData = async () => {
        await Promise.all([fetchData(), fetchPelamar()]);
    };

    useEffect(() => {
        refreshAllData();
    }, []);

    useEffect(() => {
        if (isFirstActionSignalRender.current) {
            isFirstActionSignalRender.current = false;
            return;
        }

        if (actionSignals?.jadwalTestZoom > 0) {
            openCreateModal();
        }
    }, [actionSignals?.jadwalTestZoom]);

    const resetForm = () => {
        setEditingData(null);
        setForm({
            tanggal_skrining: "",
            data_riwayat_diri_ids: [],
            data_riwayat_diri_id: "",
            jadwal: "",
            link_zoom: "",
        });
    };

    const openCreateModal = async () => {
        await refreshAllData();
        resetForm();
        setModalOpen(true);
    };

    /*
    |--------------------------------------------------------------------------
    | Edit satu jadwal
    |--------------------------------------------------------------------------
    | Karena tabel utama sekarang grouped, edit per pelamar dilakukan dari modal
    | detail. Item yang dikirim ke fungsi ini adalah row jadwal individual dari
    | endpoint detail.
    */
    const openEditModal = (item) => {
        const pelamar = item?.data_riwayat_diri;

        setDetailModalOpen(false);
        setEditingData(item);

        setForm({
            tanggal_skrining: normalizeDate(pelamar?.tanggal_skrining),
            data_riwayat_diri_ids: item?.data_riwayat_diri_id
                ? [item.data_riwayat_diri_id]
                : [],
            data_riwayat_diri_id: item?.data_riwayat_diri_id || "",
            jadwal: toDateTimeLocalValue(item?.jadwal),
            link_zoom: item?.link_zoom || detailData?.link_zoom || "",
        });

        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        resetForm();
    };

    const closeDetailModal = () => {
        setDetailModalOpen(false);
        setDetailData(null);
    };

    const openLinkModal = (item) => {
        const groupKey = item?.group_key || item?.id;

        if (!groupKey) {
            alert("Group jadwal tidak valid.");
            return;
        }

        setEditingGroup(item);
        setGroupLinkForm({
            jadwal: toDateTimeLocalValue(item?.jadwal),
            link_zoom: item?.link_zoom || "",
        });
        setLinkModalOpen(true);
    };

    const closeLinkModal = () => {
        setLinkModalOpen(false);
        setEditingGroup(null);
        setGroupLinkForm({
            jadwal: "",
            link_zoom: "",
        });
    };

    const handleGroupLinkChange = (e) => {
        const { name, value } = e.target;

        setGroupLinkForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const validateGroupLinkForm = () => {
        if (!groupLinkForm.jadwal) {
            alert("Jadwal Zoom wajib diisi.");
            return false;
        }

        if (!groupLinkForm.link_zoom) {
            alert("Link Zoom wajib diisi.");
            return false;
        }

        try {
            new URL(groupLinkForm.link_zoom);
        } catch (error) {
            alert("Format Link Zoom tidak valid. Gunakan URL lengkap, contoh: https://zoom.us/j/123456789");
            return false;
        }

        return true;
    };

    const handleSubmitGroupLink = async (e) => {
        e.preventDefault();

        if (!validateGroupLinkForm()) return;

        const groupKey = editingGroup?.group_key || editingGroup?.id;

        if (!groupKey) {
            alert("Group jadwal tidak valid.");
            return;
        }

        setLinkLoading(true);

        try {
            const result = await fetchJson(
                `/admin/jadwal-test/zoom/group/${encodeURIComponent(groupKey)}`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                    body: JSON.stringify({
                        jadwal: groupLinkForm.jadwal,
                        link_zoom: groupLinkForm.link_zoom,
                    }),
                }
            );

            alert(result.message || "Link Zoom berhasil disimpan untuk semua pelamar pada jadwal ini.");

            closeLinkModal();
            await refreshAllData();

            if (detailModalOpen && detailData?.group_key) {
                closeDetailModal();
            }
        } catch (error) {
            console.error("Gagal menyimpan Link Zoom:", error);
            alert(error.message || "Terjadi kesalahan saat menyimpan Link Zoom.");
        } finally {
            setLinkLoading(false);
        }
    };

    const openDetailModal = async (item) => {
        const groupKey = item?.group_key || item?.id;

        if (!groupKey) {
            alert("Group jadwal tidak valid.");
            return;
        }

        setDetailModalOpen(true);
        setDetailLoading(true);
        setDetailData(null);

        try {
            const result = await fetchJson(
                `/admin/jadwal-test/zoom/detail/${encodeURIComponent(groupKey)}`
            );

            if (result.success) {
                setDetailData(result.data || null);
            } else {
                alert(result.message || "Gagal mengambil detail jadwal.");
            }
        } catch (error) {
            console.error("Gagal mengambil detail jadwal Zoom:", error);
            alert(error.message || "Gagal mengambil detail jadwal Zoom.");
            closeDetailModal();
        } finally {
            setDetailLoading(false);
        }
    };

    const handleTanggalChange = (tanggal) => {
        const ids = pelamarBelumDijadwalkan
            .filter((item) => normalizeDate(item.tanggal_skrining) === tanggal)
            .map((item) => item.id);

        setForm((prev) => ({
            ...prev,
            tanggal_skrining: tanggal,
            data_riwayat_diri_ids: editingData ? prev.data_riwayat_diri_ids : ids,
            data_riwayat_diri_id: editingData ? prev.data_riwayat_diri_id : "",
        }));
    };

    const handlePelamarMultiChange = (ids) => {
        setForm((prev) => ({
            ...prev,
            data_riwayat_diri_ids: ids,
            data_riwayat_diri_id: ids[0] || "",
        }));
    };

    const handleChange = (e) => {
        const { name, value } = e.target;

        setForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const validateForm = () => {
        if (!form.jadwal) {
            alert("Jadwal Zoom wajib diisi.");
            return false;
        }

        if (!form.link_zoom) {
            alert("Link Zoom wajib diisi.");
            return false;
        }

        try {
            new URL(form.link_zoom);
        } catch (error) {
            alert("Format Link Zoom tidak valid. Gunakan URL lengkap, contoh: https://zoom.us/j/123456789");
            return false;
        }

        if (editingData) {
            if (!form.data_riwayat_diri_id) {
                alert("Pelamar wajib dipilih.");
                return false;
            }

            return true;
        }

        if (!form.tanggal_skrining) {
            alert("Tanggal skrining wajib dipilih.");
            return false;
        }

        if (!form.data_riwayat_diri_ids.length) {
            alert("Tidak ada pelamar yang bisa dijadwalkan pada tanggal skrining ini.");
            return false;
        }

        return true;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) return;

        setLoading(true);

        try {
            const url = editingData
                ? `/admin/jadwal-test/zoom/${editingData.id}`
                : "/admin/jadwal-test/zoom";

            const method = editingData ? "PUT" : "POST";

            const payload = editingData
                ? {
                      data_riwayat_diri_id: form.data_riwayat_diri_id,
                      jadwal: form.jadwal,
                      link_zoom: form.link_zoom,
                  }
                : {
                      tanggal_skrining: form.tanggal_skrining,
                      data_riwayat_diri_ids: form.data_riwayat_diri_ids,
                      jadwal: form.jadwal,
                      link_zoom: form.link_zoom,
                  };

            const result = await fetchJson(url, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            alert(result.message || "Jadwal Zoom berhasil disimpan.");

            closeModal();
            await refreshAllData();
        } catch (error) {
            console.error("Gagal menyimpan jadwal Zoom:", error);
            alert(error.message || "Terjadi kesalahan saat menyimpan jadwal Zoom.");
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin ingin menghapus jadwal Zoom ini?");

        if (!confirmDelete) return;

        try {
            const result = await fetchJson(`/admin/jadwal-test/zoom/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });

            alert(result.message || "Jadwal Zoom berhasil dihapus.");

            await refreshAllData();

            if (detailModalOpen && detailData?.group_key) {
                await openDetailModal({ group_key: detailData.group_key });
            }
        } catch (error) {
            console.error("Gagal menghapus jadwal Zoom:", error);
            alert(error.message || "Terjadi kesalahan saat menghapus jadwal Zoom.");
        }
    };

    const getPelamarOptionLabel = (item) => {
        const posisi = item?.posisi?.nama_posisi;
        const perusahaan = item?.perusahaan?.nama_perusahaan;

        return [
            item.nama_lengkap || "Tanpa Nama",
            item.no_wa ? `WA: ${item.no_wa}` : null,
            posisi,
            perusahaan,
        ]
            .filter(Boolean)
            .join(" • ");
    };

    const columns = useMemo(() => {
        return [
            {
                key: "no",
                label: "No",
                render: (_item, index) => index + 1,
            },
            {
                key: "tanggal_test",
                label: "Tanggal Test",
                render: (item) => (
                    <div className="min-w-[190px]">
                        <div className="font-black text-slate-950">
                            {item?.tanggal_test_label ||
                                formatDate(item?.tanggal_test)}
                        </div>
                        <div className="mt-1 text-xs font-bold text-slate-500">
                            Group: {item?.group_key || item?.id || "-"}
                        </div>
                    </div>
                ),
            },
            {
                key: "jam_test",
                label: "Jam",
                render: (item) => (
                    <span className="inline-flex rounded-full bg-blue-50 px-3 py-1.5 text-xs font-black text-blue-700">
                        {item?.jam_test || formatTime(item?.jadwal)}
                    </span>
                ),
            },
            {
                key: "jadwal",
                label: "Jadwal Zoom",
                render: (item) => (
                    <span className="text-sm font-black text-slate-800">
                        {item?.jadwal_label || formatDateTime(item?.jadwal)}
                    </span>
                ),
            },
            {
                key: "link_zoom",
                label: "Link Zoom",
                render: (item) =>
                    item?.link_zoom ? (
                        <a
                            href={item.link_zoom}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-black text-cyan-700 transition hover:bg-cyan-100"
                        >
                            Buka Link
                        </a>
                    ) : (
                        <span className="text-sm font-bold text-slate-400">Belum Ada</span>
                    ),
            },
            {
                key: "total_pelamar",
                label: "Total Pelamar",
                render: (item) => (
                    <div className="min-w-[130px]">
                        <div className="text-2xl font-black text-slate-950">
                            {item?.total_pelamar || 0}
                        </div>
                        <div className="text-xs font-bold text-slate-500">
                            orang
                        </div>
                    </div>
                ),
            },
            {
                key: "hadir",
                label: "Hadir",
                render: (item) => (
                    <span className="inline-flex rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">
                        {item?.total_hadir || 0} Hadir
                    </span>
                ),
            },
            {
                key: "tidak_hadir",
                label: "Tidak Hadir",
                render: (item) => (
                    <span className="inline-flex rounded-full bg-rose-50 px-3 py-1.5 text-xs font-black text-rose-700">
                        {item?.total_tidak_hadir || 0} Tidak Hadir
                    </span>
                ),
            },
            {
                key: "belum",
                label: "Belum Konfirmasi",
                render: (item) => (
                    <span className="inline-flex rounded-full bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-700">
                        {item?.total_belum_konfirmasi || 0} Belum
                    </span>
                ),
            },
            {
                key: "aksi",
                label: "Aksi",
                align: "right",
                render: (item) => (
                    <div className="flex justify-end gap-2">
                        <button
                            type="button"
                            onClick={() => openLinkModal(item)}
                            className="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-2 text-xs font-black text-cyan-700 transition hover:bg-cyan-100"
                        >
                            Input Link
                        </button>

                        <button
                            type="button"
                            onClick={() => openDetailModal(item)}
                            className="rounded-2xl border border-teal-100 bg-teal-50 px-4 py-2 text-xs font-black text-teal-700 transition hover:bg-teal-100"
                        >
                            Detail
                        </button>
                    </div>
                ),
            },
        ];
    }, []);

    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-5">
                <StatCard
                    label="Group Jadwal"
                    value={summary.totalGroup}
                    description="tanggal & jam test"
                />

                <StatCard
                    label="Total Pelamar"
                    value={summary.totalPelamar}
                    description="sudah dijadwalkan"
                />

                <StatCard
                    label="Hadir"
                    value={summary.totalHadir}
                    description="konfirmasi hadir"
                />

                <StatCard
                    label="Tidak Hadir"
                    value={summary.totalTidakHadir}
                    description="konfirmasi tidak hadir"
                />

                <StatCard
                    label="Belum Konfirmasi"
                    value={summary.totalBelumKonfirmasi}
                    description="belum memilih"
                />
            </div>

            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                            Data Jadwal Test
                        </div>

                        <h3 className="mt-2 text-lg font-black text-slate-950">
                            Jadwal Test Zoom
                        </h3>

                        <p className="mt-1 text-sm font-medium text-slate-500">
                            Data sudah digroup berdasarkan tanggal dan jam test.
                            Klik Detail untuk melihat semua pelamar pada jadwal tersebut.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={openCreateModal}
                        className="w-fit rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-teal-100 transition hover:from-teal-700 hover:to-cyan-700"
                    >
                        + Tambah Jadwal
                    </button>
                </div>

                <DataTable
                    data={dataJadwal}
                    columns={columns}
                    searchPlaceholder="Cari tanggal test, jam, group, jumlah, status kehadiran..."
                    emptyTitle="Jadwal Zoom tidak ditemukan"
                    emptyDescription="Belum ada jadwal Zoom atau kata kunci pencarian tidak cocok."
                    getSearchText={(item) =>
                        [
                            item?.group_key,
                            item?.tanggal_test,
                            item?.tanggal_test_label,
                            item?.jam_test,
                            item?.jadwal,
                            item?.jadwal_label,
                            item?.link_zoom,
                            item?.total_pelamar,
                            item?.total_hadir,
                            item?.total_tidak_hadir,
                            item?.total_belum_konfirmasi,
                        ]
                            .filter(Boolean)
                            .join(" ")
                    }
                />
            </div>

            {modalOpen && (
                <JadwalFormModal
                    editingData={editingData}
                    form={form}
                    loading={loading}
                    tanggalSkriningOptions={tanggalSkriningOptions}
                    filteredPelamarByTanggal={filteredPelamarByTanggal}
                    dataPelamar={dataPelamar}
                    selectedPelamar={selectedPelamar}
                    handleTanggalChange={handleTanggalChange}
                    handlePelamarMultiChange={handlePelamarMultiChange}
                    handleChange={handleChange}
                    handleSubmit={handleSubmit}
                    closeModal={closeModal}
                    getPelamarOptionLabel={getPelamarOptionLabel}
                />
            )}

            {linkModalOpen && (
                <LinkZoomGroupModal
                    group={editingGroup}
                    form={groupLinkForm}
                    loading={linkLoading}
                    onChange={handleGroupLinkChange}
                    onSubmit={handleSubmitGroupLink}
                    onClose={closeLinkModal}
                />
            )}

            {detailModalOpen && (
                <DetailJadwalModal
                    loading={detailLoading}
                    detailData={detailData}
                    onClose={closeDetailModal}
                    onEdit={openEditModal}
                    onDelete={handleDelete}
                />
            )}
        </div>
    );
}

function StatCard({ label, value, description }) {
    return (
        <div className="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-xs font-black uppercase tracking-wide text-slate-500">
                {label}
            </p>

            <p className="mt-2 text-3xl font-black text-slate-950">{value}</p>

            <p className="mt-1 text-xs font-bold text-slate-500">
                {description}
            </p>
        </div>
    );
}

function JadwalFormModal({
    editingData,
    form,
    loading,
    tanggalSkriningOptions,
    filteredPelamarByTanggal,
    dataPelamar,
    selectedPelamar,
    handleTanggalChange,
    handlePelamarMultiChange,
    handleChange,
    handleSubmit,
    closeModal,
    getPelamarOptionLabel,
}) {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div className="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div className="shrink-0 border-b border-slate-200 px-6 py-5">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <div className="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                                {editingData ? "Edit Jadwal" : "Tambah Jadwal"}
                            </div>

                            <h2 className="mt-2 text-2xl font-black text-slate-950">
                                Jadwal Test Zoom
                            </h2>

                            <p className="mt-1 text-sm font-medium text-slate-500">
                                {editingData
                                    ? "Edit jadwal Zoom untuk satu pelamar."
                                    : "Pilih tanggal skrining. Sistem hanya menampilkan pelamar yang belum dijadwalkan."}
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={closeModal}
                            className="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-500 transition hover:bg-slate-200"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="flex min-h-0 flex-1 flex-col">
                    <div className="min-h-0 flex-1 overflow-y-auto p-6">
                        <div className="grid gap-5">
                            <Select2Single
                                label="Tanggal Skrining"
                                value={form.tanggal_skrining}
                                options={tanggalSkriningOptions}
                                optionValue="tanggal"
                                optionLabel={(item) =>
                                    `${formatDate(item.tanggal)} • ${item.total} Pelamar Belum Dijadwalkan`
                                }
                                placeholder="Pilih tanggal skrining"
                                searchPlaceholder="Cari tanggal skrining..."
                                onChange={handleTanggalChange}
                                disabled={Boolean(editingData)}
                                required
                            />

                            {!editingData && form.tanggal_skrining && (
                                <div className="rounded-2xl border border-teal-100 bg-teal-50 px-4 py-3">
                                    <p className="text-sm font-black text-teal-800">
                                        {filteredPelamarByTanggal.length} pelamar belum dijadwalkan pada tanggal {formatDate(form.tanggal_skrining)}.
                                    </p>

                                    <p className="mt-1 text-xs font-bold text-teal-700">
                                        Pelamar yang sudah memiliki jadwal Zoom tidak ditampilkan lagi.
                                    </p>
                                </div>
                            )}

                            <Select2Multi
                                label="Pelamar"
                                value={form.data_riwayat_diri_ids}
                                options={editingData ? dataPelamar : filteredPelamarByTanggal}
                                optionValue="id"
                                optionLabel={getPelamarOptionLabel}
                                placeholder={
                                    form.tanggal_skrining
                                        ? "Pilih pelamar"
                                        : "Pilih tanggal skrining terlebih dahulu"
                                }
                                searchPlaceholder="Cari nama, WA, posisi, perusahaan..."
                                onChange={handlePelamarMultiChange}
                                disabled={!editingData && !form.tanggal_skrining}
                                required
                            />

                            {selectedPelamar.length > 0 && (
                                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div className="flex items-center justify-between gap-3">
                                        <p className="text-sm font-black text-slate-800">
                                            Pelamar Terpilih
                                        </p>

                                        <span className="rounded-full bg-white px-3 py-1 text-xs font-black text-slate-500">
                                            {selectedPelamar.length} orang
                                        </span>
                                    </div>

                                    <div className="mt-3 flex flex-wrap gap-2">
                                        {selectedPelamar.map((item) => (
                                            <span
                                                key={item.id}
                                                className="inline-flex rounded-full bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm"
                                            >
                                                {item.nama_lengkap || "-"}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            )}

                            <div>
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Jadwal Zoom <span className="text-rose-500">*</span>
                                </label>

                                <input
                                    type="datetime-local"
                                    name="jadwal"
                                    value={form.jadwal}
                                    onChange={handleChange}
                                    required
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                                />
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Link Zoom <span className="text-rose-500">*</span>
                                </label>

                                <input
                                    type="url"
                                    name="link_zoom"
                                    value={form.link_zoom}
                                    onChange={handleChange}
                                    required
                                    placeholder="https://zoom.us/j/123456789"
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                                />

                                <p className="mt-2 text-xs font-semibold text-slate-500">
                                    Link ini akan tersimpan bersama jadwal Zoom.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="shrink-0 border-t border-slate-100 bg-white px-6 py-4">
                        <div className="flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={closeModal}
                                className="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                disabled={loading}
                                className="rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-teal-100 transition hover:from-teal-700 hover:to-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {loading ? "Menyimpan..." : "Simpan Jadwal"}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}


function LinkZoomGroupModal({
    group,
    form,
    loading,
    onChange,
    onSubmit,
    onClose,
}) {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div className="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div className="shrink-0 border-b border-slate-200 px-6 py-5">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <div className="inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-cyan-700">
                                Input Link Zoom
                            </div>

                            <h2 className="mt-2 text-2xl font-black text-slate-950">
                                Simpan Link Zoom Jadwal Test
                            </h2>

                            <p className="mt-1 text-sm font-medium text-slate-500">
                                Link dan jadwal ini akan diperbarui untuk semua pelamar pada jadwal test yang sama.
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

                <form onSubmit={onSubmit} className="flex min-h-0 flex-1 flex-col">
                    <div className="min-h-0 flex-1 overflow-y-auto p-6">
                        <div className="rounded-2xl border border-cyan-100 bg-cyan-50 p-4">
                            <p className="text-xs font-black uppercase tracking-wide text-cyan-700">
                                Jadwal Dipilih
                            </p>

                            <p className="mt-2 text-sm font-black text-slate-900">
                                {group?.jadwal_label || formatDateTime(group?.jadwal)}
                            </p>

                            <p className="mt-1 text-xs font-bold text-slate-500">
                                Total pelamar: {group?.total_pelamar || 0} orang
                            </p>
                        </div>

                        <div className="mt-5 grid gap-5">
                            <div>
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Jadwal Zoom <span className="text-rose-500">*</span>
                                </label>

                                <input
                                    type="datetime-local"
                                    name="jadwal"
                                    value={form.jadwal}
                                    onChange={onChange}
                                    required
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                                />

                                <p className="mt-2 text-xs font-semibold text-slate-500">
                                    Jika jadwal diubah, semua pelamar pada group jadwal ini ikut berubah.
                                </p>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-black text-slate-700">
                                    Link Zoom <span className="text-rose-500">*</span>
                                </label>

                                <input
                                    type="url"
                                    name="link_zoom"
                                    value={form.link_zoom}
                                    onChange={onChange}
                                    required
                                    placeholder="https://zoom.us/j/123456789"
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                                />

                                <p className="mt-2 text-xs font-semibold text-slate-500">
                                    Masukkan URL lengkap, contoh: https://zoom.us/j/123456789
                                </p>
                            </div>

                            {form.link_zoom && (
                                <a
                                    href={form.link_zoom}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex w-fit rounded-2xl bg-cyan-50 px-4 py-2 text-sm font-black text-cyan-700 transition hover:bg-cyan-100"
                                >
                                    Coba Buka Link
                                </a>
                            )}
                        </div>
                    </div>

                    <div className="shrink-0 border-t border-slate-100 bg-white px-6 py-4">
                        <div className="flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={onClose}
                                className="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                disabled={loading}
                                className="rounded-2xl bg-gradient-to-r from-cyan-600 to-teal-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-cyan-100 transition hover:from-cyan-700 hover:to-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {loading ? "Menyimpan..." : "Simpan Link Zoom"}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}

function DetailJadwalModal({ loading, detailData, onClose, onEdit, onDelete }) {
    const pelamarRows = Array.isArray(detailData?.pelamar)
        ? detailData.pelamar
        : [];

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div className="flex max-h-[92vh] w-full max-w-7xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div className="shrink-0 border-b border-slate-200 px-6 py-5">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <div className="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-blue-700">
                                Detail Jadwal Test
                            </div>

                            <h2 className="mt-2 text-2xl font-black text-slate-950">
                                {detailData?.jadwal_label ||
                                    `${detailData?.tanggal_test_label || "-"} ${detailData?.jam_test || ""}`}
                            </h2>

                            <p className="mt-1 text-sm font-medium text-slate-500">
                                Semua pelamar pada jadwal test ini.
                            </p>

                            {detailData?.link_zoom && (
                                <a
                                    href={detailData.link_zoom}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="mt-3 inline-flex rounded-2xl bg-cyan-50 px-4 py-2 text-sm font-black text-cyan-700 transition hover:bg-cyan-100"
                                >
                                    Buka Link Zoom
                                </a>
                            )}
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

                <div className="min-h-0 flex-1 overflow-y-auto p-6">
                    {loading && (
                        <div className="rounded-3xl border border-blue-100 bg-blue-50 p-5">
                            <p className="text-sm font-black text-blue-800">
                                Memuat detail jadwal...
                            </p>
                        </div>
                    )}

                    {!loading && detailData && (
                        <div className="space-y-5">
                            <div className="grid gap-4 md:grid-cols-4">
                                <StatCard
                                    label="Total Pelamar"
                                    value={detailData?.total_pelamar || pelamarRows.length}
                                    description="dalam jadwal ini"
                                />

                                <StatCard
                                    label="Hadir"
                                    value={detailData?.total_hadir || 0}
                                    description="konfirmasi hadir"
                                />

                                <StatCard
                                    label="Tidak Hadir"
                                    value={detailData?.total_tidak_hadir || 0}
                                    description="konfirmasi tidak hadir"
                                />

                                <StatCard
                                    label="Belum Konfirmasi"
                                    value={detailData?.total_belum_konfirmasi || 0}
                                    description="belum memilih"
                                />
                            </div>

                            <div className="overflow-hidden rounded-3xl border border-slate-200">
                                <div className="overflow-x-auto">
                                    <table className="min-w-full">
                                        <thead>
                                            <tr className="bg-slate-50">
                                                <th className="whitespace-nowrap px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                                    No
                                                </th>
                                                <th className="whitespace-nowrap px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Pelamar
                                                </th>
                                                <th className="whitespace-nowrap px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Kontak
                                                </th>
                                                <th className="whitespace-nowrap px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Posisi
                                                </th>
                                                <th className="whitespace-nowrap px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Perusahaan
                                                </th>
                                                <th className="whitespace-nowrap px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Tanggal Skrining
                                                </th>
                                                <th className="whitespace-nowrap px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Link Zoom
                                                </th>
                                                <th className="whitespace-nowrap px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Kehadiran
                                                </th>
                                                <th className="whitespace-nowrap px-5 py-4 text-right text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Aksi
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody className="divide-y divide-slate-100 bg-white">
                                            {pelamarRows.length > 0 ? (
                                                pelamarRows.map((item, index) => {
                                                    const pelamar =
                                                        item?.data_riwayat_diri || {};

                                                    return (
                                                        <tr
                                                            key={item.id || index}
                                                            className="transition hover:bg-slate-50"
                                                        >
                                                            <td className="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-500">
                                                                {index + 1}
                                                            </td>

                                                            <td className="whitespace-nowrap px-5 py-4">
                                                                <div className="min-w-[220px]">
                                                                    <div className="font-black text-slate-950">
                                                                        {pelamar?.nama_lengkap || "-"}
                                                                    </div>
                                                                    <div className="mt-1 text-xs font-bold text-slate-500">
                                                                        {pelamar?.nama_panggil || "-"}
                                                                    </div>
                                                                </div>
                                                            </td>

                                                            <td className="whitespace-nowrap px-5 py-4">
                                                                <div className="min-w-[220px]">
                                                                    <div className="text-sm font-bold text-slate-700">
                                                                        {pelamar?.email || "-"}
                                                                    </div>
                                                                    <div className="mt-1 text-xs font-black text-teal-700">
                                                                        WA: {pelamar?.no_wa || "-"}
                                                                    </div>
                                                                    <div className="mt-1 text-xs font-bold text-slate-500">
                                                                        Token: {pelamar?.token || "-"}
                                                                    </div>
                                                                </div>
                                                            </td>

                                                            <td className="whitespace-nowrap px-5 py-4">
                                                                <span className="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700">
                                                                    {pelamar?.posisi?.nama_posisi || "-"}
                                                                </span>
                                                            </td>

                                                            <td className="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-600">
                                                                {pelamar?.perusahaan?.nama_perusahaan || "-"}
                                                            </td>

                                                            <td className="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-600">
                                                                {formatDate(pelamar?.tanggal_skrining)}
                                                            </td>

                                                            <td className="whitespace-nowrap px-5 py-4">
                                                                {item?.link_zoom ? (
                                                                    <a
                                                                        href={item.link_zoom}
                                                                        target="_blank"
                                                                        rel="noopener noreferrer"
                                                                        className="inline-flex rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-black text-cyan-700 transition hover:bg-cyan-100"
                                                                    >
                                                                        Buka Link
                                                                    </a>
                                                                ) : (
                                                                    <span className="text-sm font-bold text-slate-400">-</span>
                                                                )}
                                                            </td>

                                                            <td className="whitespace-nowrap px-5 py-4">
                                                                <KehadiranBadge
                                                                    value={
                                                                        item?.kehadiran_label ||
                                                                        item?.kehadiran
                                                                    }
                                                                />
                                                            </td>

                                                            <td className="whitespace-nowrap px-5 py-4 text-right">
                                                                <div className="flex justify-end gap-2">
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => onEdit(item)}
                                                                        className="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-2 text-xs font-black text-cyan-700 transition hover:bg-cyan-100"
                                                                    >
                                                                        Edit
                                                                    </button>

                                                                    <button
                                                                        type="button"
                                                                        onClick={() => onDelete(item.id)}
                                                                        className="rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 transition hover:bg-rose-50"
                                                                    >
                                                                        Hapus
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    );
                                                })
                                            ) : (
                                                <tr>
                                                    <td colSpan={9} className="px-6 py-14">
                                                        <div className="text-center">
                                                            <h3 className="text-lg font-black text-slate-900">
                                                                Tidak ada pelamar
                                                            </h3>
                                                            <p className="mt-1 text-sm font-medium text-slate-500">
                                                                Detail jadwal ini belum memiliki data pelamar.
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <div className="shrink-0 border-t border-slate-100 bg-white px-6 py-4">
                    <div className="flex justify-end">
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

function KehadiranBadge({ value }) {
    const normalized = String(value || "").toLowerCase();

    if (normalized.includes("hadir") && !normalized.includes("tidak")) {
        return (
            <span className="inline-flex rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">
                Hadir
            </span>
        );
    }

    if (normalized.includes("tidak")) {
        return (
            <span className="inline-flex rounded-full bg-rose-50 px-3 py-1.5 text-xs font-black text-rose-700">
                Tidak Hadir
            </span>
        );
    }

    return (
        <span className="inline-flex rounded-full bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-700">
            Belum Konfirmasi
        </span>
    );
}

function DataTable({
    data = [],
    columns = [],
    searchPlaceholder = "Cari data...",
    emptyTitle = "Data tidak ditemukan",
    emptyDescription = "Belum ada data atau kata kunci pencarian tidak cocok.",
    getSearchText,
}) {
    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage, data]);

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) return data;

        return data.filter((item) => {
            const searchableText = getSearchText
                ? getSearchText(item)
                : JSON.stringify(item || {});

            return String(searchableText || "")
                .toLowerCase()
                .includes(keyword);
        });
    }, [data, search, getSearchText]);

    const totalPages = Math.max(1, Math.ceil(filteredData.length / entriesPerPage));

    const paginatedData = useMemo(() => {
        const start = (currentPage - 1) * entriesPerPage;
        const end = start + entriesPerPage;

        return filteredData.slice(start, end);
    }, [filteredData, currentPage, entriesPerPage]);

    const showingFrom =
        filteredData.length === 0 ? 0 : (currentPage - 1) * entriesPerPage + 1;

    const showingTo = Math.min(currentPage * entriesPerPage, filteredData.length);

    return (
        <>
            <div className="border-b border-slate-100 px-6 py-4">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-2">
                        <span className="text-sm font-bold text-slate-600">
                            Show
                        </span>

                        <select
                            value={entriesPerPage}
                            onChange={(event) =>
                                setEntriesPerPage(Number(event.target.value))
                            }
                            className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        >
                            <option value={5}>5</option>
                            <option value={10}>10</option>
                            <option value={25}>25</option>
                            <option value={50}>50</option>
                        </select>

                        <span className="text-sm font-bold text-slate-600">
                            entries
                        </span>
                    </div>

                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <span className="text-sm font-bold text-slate-600">
                            Search:
                        </span>

                        <input
                            type="text"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={searchPlaceholder}
                            className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100 sm:w-96"
                        />
                    </div>
                </div>
            </div>

            <div className="overflow-x-auto">
                <table className="min-w-full">
                    <thead>
                        <tr className="bg-slate-50">
                            {columns.map((column) => {
                                const alignClass =
                                    column.align === "right"
                                        ? "text-right"
                                        : "text-left";

                                return (
                                    <th
                                        key={column.key}
                                        className={`whitespace-nowrap px-6 py-4 ${alignClass} text-xs font-black uppercase tracking-[0.12em] text-slate-500`}
                                    >
                                        {column.label}
                                    </th>
                                );
                            })}
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-100 bg-white">
                        {paginatedData.length > 0 ? (
                            paginatedData.map((item, index) => (
                                <tr
                                    key={item.id || item.group_key || index}
                                    className="transition hover:bg-slate-50"
                                >
                                    {columns.map((column) => {
                                        const alignClass =
                                            column.align === "right"
                                                ? "text-right"
                                                : "text-left";

                                        return (
                                            <td
                                                key={column.key}
                                                className={`whitespace-nowrap px-6 py-5 align-middle ${alignClass}`}
                                            >
                                                {column.render
                                                    ? column.render(
                                                          item,
                                                          showingFrom + index - 1
                                                      )
                                                    : "-"}
                                            </td>
                                        );
                                    })}
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={columns.length} className="px-6 py-16">
                                    <div className="mx-auto max-w-sm text-center">
                                        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                            ◷
                                        </div>

                                        <h3 className="mt-4 text-lg font-black text-slate-900">
                                            {emptyTitle}
                                        </h3>

                                        <p className="mt-2 text-sm font-medium text-slate-500">
                                            {emptyDescription}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="flex flex-col gap-4 border-t border-slate-100 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                <p className="text-sm font-bold text-slate-500">
                    Showing {showingFrom} to {showingTo} of {filteredData.length} entries
                </p>

                <div className="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        disabled={currentPage === 1}
                        onClick={() =>
                            setCurrentPage((prev) => Math.max(prev - 1, 1))
                        }
                        className="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Previous
                    </button>

                    <button
                        type="button"
                        disabled={currentPage === totalPages}
                        onClick={() =>
                            setCurrentPage((prev) =>
                                Math.min(prev + 1, totalPages)
                            )
                        }
                        className="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
            </div>
        </>
    );
}

function Select2Single({
    label,
    value,
    options,
    optionValue,
    optionLabel,
    placeholder,
    searchPlaceholder,
    onChange,
    disabled = false,
    required = false,
}) {
    const wrapperRef = useRef(null);
    const searchInputRef = useRef(null);
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState("");

    const selectedOption = useMemo(() => {
        return options.find((item) => String(item[optionValue]) === String(value));
    }, [options, optionValue, value]);

    const filteredOptions = useMemo(() => {
        const keyword = search.toLowerCase();

        return options.filter((item) => {
            const labelText =
                typeof optionLabel === "function"
                    ? optionLabel(item)
                    : item?.[optionLabel];

            return String(labelText || "").toLowerCase().includes(keyword);
        });
    }, [options, optionLabel, search]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
                setOpen(false);
                setSearch("");
            }
        };

        document.addEventListener("mousedown", handleClickOutside);

        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    useEffect(() => {
        if (open) {
            setTimeout(() => searchInputRef.current?.focus(), 50);
        }
    }, [open]);

    const selectedLabel = selectedOption
        ? typeof optionLabel === "function"
            ? optionLabel(selectedOption)
            : selectedOption?.[optionLabel]
        : "";

    return (
        <div ref={wrapperRef} className="relative">
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <button
                type="button"
                disabled={disabled}
                onClick={() => !disabled && setOpen((prev) => !prev)}
                className={`flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left text-sm font-bold shadow-sm outline-none transition ${
                    disabled
                        ? "cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400"
                        : open
                        ? "border-teal-500 bg-white ring-4 ring-teal-100"
                        : "border-slate-200 bg-white text-slate-700 hover:border-teal-300"
                }`}
            >
                <span className={selectedOption ? "text-slate-800" : "text-slate-400"}>
                    {selectedOption ? selectedLabel : placeholder}
                </span>

                <span className="text-slate-400">{open ? "⌃" : "⌄"}</span>
            </button>

            {open && !disabled && (
                <div className="absolute left-0 right-0 z-[70] mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div className="border-b border-slate-100 bg-slate-50 p-3">
                        <input
                            ref={searchInputRef}
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={searchPlaceholder}
                            className="w-full rounded-xl border border-teal-500 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none ring-4 ring-teal-50"
                        />
                    </div>

                    <div className="max-h-64 overflow-y-auto py-2">
                        {filteredOptions.length > 0 ? (
                            filteredOptions.map((item) => {
                                const itemValue = item[optionValue];
                                const labelText =
                                    typeof optionLabel === "function"
                                        ? optionLabel(item)
                                        : item?.[optionLabel];

                                return (
                                    <button
                                        key={itemValue}
                                        type="button"
                                        onClick={() => {
                                            onChange(itemValue);
                                            setOpen(false);
                                            setSearch("");
                                        }}
                                        className={`block w-full px-4 py-3 text-left text-sm font-bold transition ${
                                            String(itemValue) === String(value)
                                                ? "bg-teal-600 text-white"
                                                : "text-slate-700 hover:bg-teal-50 hover:text-teal-800"
                                        }`}
                                    >
                                        {labelText}
                                    </button>
                                );
                            })
                        ) : (
                            <div className="px-4 py-5 text-center text-sm font-bold text-slate-400">
                                Data tidak ditemukan
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

function Select2Multi({
    label,
    value = [],
    options = [],
    optionValue,
    optionLabel,
    placeholder,
    searchPlaceholder,
    onChange,
    disabled = false,
    required = false,
}) {
    const wrapperRef = useRef(null);
    const searchInputRef = useRef(null);
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState("");

    const selectedOptions = useMemo(() => {
        return options.filter((item) => value.includes(item[optionValue]));
    }, [options, optionValue, value]);

    const filteredOptions = useMemo(() => {
        const keyword = search.toLowerCase();

        return options.filter((item) => {
            const labelText =
                typeof optionLabel === "function"
                    ? optionLabel(item)
                    : item?.[optionLabel];

            return String(labelText || "").toLowerCase().includes(keyword);
        });
    }, [options, optionLabel, search]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
                setOpen(false);
                setSearch("");
            }
        };

        document.addEventListener("mousedown", handleClickOutside);

        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    useEffect(() => {
        if (open) {
            setTimeout(() => searchInputRef.current?.focus(), 50);
        }
    }, [open]);

    const toggleValue = (selectedValue) => {
        if (value.includes(selectedValue)) {
            onChange(value.filter((item) => item !== selectedValue));
            return;
        }

        onChange([...value, selectedValue]);
    };

    const removeValue = (selectedValue) => {
        onChange(value.filter((item) => item !== selectedValue));
    };

    const selectAll = () => {
        onChange(options.map((item) => item[optionValue]));
    };

    const clearAll = () => {
        onChange([]);
    };

    return (
        <div ref={wrapperRef} className="relative">
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <button
                type="button"
                disabled={disabled}
                onClick={() => !disabled && setOpen((prev) => !prev)}
                className={`min-h-[52px] w-full rounded-2xl border px-4 py-3 text-left text-sm font-bold shadow-sm outline-none transition ${
                    disabled
                        ? "cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400"
                        : open
                        ? "border-teal-500 bg-white ring-4 ring-teal-100"
                        : "border-slate-200 bg-white text-slate-700 hover:border-teal-300"
                }`}
            >
                {selectedOptions.length > 0 ? (
                    <div className="flex flex-wrap gap-2">
                        {selectedOptions.slice(0, 6).map((item) => {
                            const itemValue = item[optionValue];
                            const labelText =
                                typeof optionLabel === "function"
                                    ? optionLabel(item)
                                    : item?.[optionLabel];

                            return (
                                <span
                                    key={itemValue}
                                    className="inline-flex items-center gap-2 rounded-full bg-teal-50 px-3 py-1 text-xs font-black text-teal-700"
                                >
                                    {String(labelText).split(" • ")[0]}

                                    {!disabled && (
                                        <span
                                            role="button"
                                            tabIndex={0}
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                removeValue(itemValue);
                                            }}
                                            className="text-teal-500 hover:text-teal-900"
                                        >
                                            ×
                                        </span>
                                    )}
                                </span>
                            );
                        })}

                        {selectedOptions.length > 6 && (
                            <span className="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                                +{selectedOptions.length - 6} lainnya
                            </span>
                        )}
                    </div>
                ) : (
                    <span className="text-slate-400">{placeholder}</span>
                )}
            </button>

            {open && !disabled && (
                <div className="absolute left-0 right-0 z-[70] mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div className="border-b border-slate-100 bg-slate-50 p-3">
                        <input
                            ref={searchInputRef}
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={searchPlaceholder}
                            className="w-full rounded-xl border border-teal-500 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none ring-4 ring-teal-50"
                        />

                        <div className="mt-3 flex flex-wrap gap-2">
                            <button
                                type="button"
                                onClick={selectAll}
                                className="rounded-xl bg-teal-600 px-3 py-2 text-xs font-black text-white"
                            >
                                Pilih Semua
                            </button>

                            <button
                                type="button"
                                onClick={clearAll}
                                className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600"
                            >
                                Hapus Semua
                            </button>
                        </div>
                    </div>

                    <div className="max-h-72 overflow-y-auto py-2">
                        {filteredOptions.length > 0 ? (
                            filteredOptions.map((item) => {
                                const itemValue = item[optionValue];
                                const checked = value.includes(itemValue);
                                const labelText =
                                    typeof optionLabel === "function"
                                        ? optionLabel(item)
                                        : item?.[optionLabel];

                                return (
                                    <button
                                        key={itemValue}
                                        type="button"
                                        onClick={() => toggleValue(itemValue)}
                                        className={`flex w-full items-start gap-3 px-4 py-3 text-left text-sm font-bold transition ${
                                            checked
                                                ? "bg-teal-50 text-teal-800"
                                                : "text-slate-700 hover:bg-slate-50"
                                        }`}
                                    >
                                        <span
                                            className={`mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-md border text-xs ${
                                                checked
                                                    ? "border-teal-600 bg-teal-600 text-white"
                                                    : "border-slate-300 bg-white"
                                            }`}
                                        >
                                            {checked ? "✓" : ""}
                                        </span>

                                        <span>{labelText}</span>
                                    </button>
                                );
                            })
                        ) : (
                            <div className="px-4 py-5 text-center text-sm font-bold text-slate-400">
                                Data tidak ditemukan
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

function normalizeDate(value) {
    if (!value) return "";

    return String(value).slice(0, 10);
}

function formatDate(value) {
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
}

function formatTime(value) {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return String(value).slice(11, 16) || "-";
    }

    return date.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    });
}

function formatDateTime(value) {
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
}

function toDateTimeLocalValue(value) {
    if (!value) return "";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "";
    }

    const offset = date.getTimezoneOffset();
    const localDate = new Date(date.getTime() - offset * 60 * 1000);

    return localDate.toISOString().slice(0, 16);
}
