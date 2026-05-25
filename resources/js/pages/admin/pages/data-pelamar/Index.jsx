import React, { useEffect, useMemo, useRef, useState } from "react";

export default function DataPelamarPage({
    actionSignals,
    onOpenDetailPelamar,
}) {
    const [dataPelamar, setDataPelamar] = useState([]);
    const [dataPosisi, setDataPosisi] = useState([]);
    const [dataPerusahaan, setDataPerusahaan] = useState([]);
    const [dataSumberInformasi, setDataSumberInformasi] = useState([]);
    const [dataPendidikan, setDataPendidikan] = useState([]);
    const [dataAgama, setDataAgama] = useState([]);
    const [dataKewarganegaraan, setDataKewarganegaraan] = useState([]);
    const [dataStatusPernikahan, setDataStatusPernikahan] = useState([]);

    const [modalOpen, setModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [alamatSama, setAlamatSama] = useState(false);

    const isFirstActionSignalRender = useRef(true);

    const [form, setForm] = useState({
        posisi_yang_dilamar: "",
        perusahaan_dilamar: "",
        sumber_informasi_id: "",
        nama_lengkap: "",
        nama_panggil: "",
        email: "",
        pendidikan_id: "",
        jurusan: "",
        nama_institusi: "",
        agama_id: "",
        tanggal_lahir: "",
        tanggal_skrining: "",
        alamat_ktp: "",
        alamat_domisili: "",
        kewarganegaraan_id: "",
        status_pernikahan_id: "",
        no_wa: "",
        sosial_media_id: "",
        gol_darah: "",
        tinggi_badan: "",
        berat_badan: "",
    });

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const resetForm = () => {
        setAlamatSama(false);

        setForm({
            posisi_yang_dilamar: "",
            perusahaan_dilamar: "",
            sumber_informasi_id: "",
            nama_lengkap: "",
            nama_panggil: "",
            email: "",
            pendidikan_id: "",
            jurusan: "",
            nama_institusi: "",
            agama_id: "",
            tanggal_lahir: "",
            tanggal_skrining: "",
            alamat_ktp: "",
            alamat_domisili: "",
            kewarganegaraan_id: "",
            status_pernikahan_id: "",
            no_wa: "",
            sosial_media_id: "",
            gol_darah: "",
            tinggi_badan: "",
            berat_badan: "",
        });
    };

    const fetchJson = async (url) => {
        const response = await fetch(url, {
            headers: {
                Accept: "application/json",
            },
        });

        return response.json();
    };

    const fetchData = async () => {
        try {
            const result = await fetchJson("/admin/data-pelamar/list");

            if (result.success) {
                setDataPelamar(result.data || []);
            }
        } catch (error) {
            console.error("Gagal mengambil data pelamar:", error);
        }
    };

    const fetchPosisi = async () => {
        try {
            const result = await fetchJson("/admin/data-pelamar/posisi/list");

            if (result.success) {
                setDataPosisi(result.data || []);
            }
        } catch (error) {
            console.error("Gagal mengambil data posisi:", error);
        }
    };

    const fetchPerusahaan = async () => {
        try {
            const result = await fetchJson(
                "/admin/data-pelamar/perusahaan/list"
            );

            if (result.success) {
                setDataPerusahaan(result.data || []);
            }
        } catch (error) {
            console.error("Gagal mengambil data perusahaan:", error);
        }
    };

    const fetchSumberInformasi = async () => {
        try {
            const result = await fetchJson(
                "/admin/data-pelamar/sumber-informasi/list"
            );

            if (result.success) {
                setDataSumberInformasi(result.data || []);
            }
        } catch (error) {
            console.error("Gagal mengambil data sumber informasi:", error);
        }
    };

    const fetchPendidikan = async () => {
        try {
            const result = await fetchJson(
                "/admin/data-pelamar/pendidikan/list"
            );

            if (result.success) {
                setDataPendidikan(result.data || []);
            }
        } catch (error) {
            console.error("Gagal mengambil data pendidikan:", error);
        }
    };

    const fetchAgama = async () => {
        try {
            const result = await fetchJson("/admin/data-pelamar/agama/list");

            if (result.success) {
                setDataAgama(result.data || []);
            }
        } catch (error) {
            console.error("Gagal mengambil data agama:", error);
        }
    };

    const fetchKewarganegaraan = async () => {
        try {
            const result = await fetchJson(
                "/admin/data-pelamar/kewarganegaraan/list"
            );

            if (result.success) {
                setDataKewarganegaraan(result.data || []);
            }
        } catch (error) {
            console.error("Gagal mengambil data kewarganegaraan:", error);
        }
    };

    const fetchStatusPernikahan = async () => {
        try {
            const result = await fetchJson(
                "/admin/data-pelamar/status-pernikahan/list"
            );

            if (result.success) {
                setDataStatusPernikahan(result.data || []);
            }
        } catch (error) {
            console.error("Gagal mengambil data status pernikahan:", error);
        }
    };

    useEffect(() => {
        fetchData();
        fetchPosisi();
        fetchPerusahaan();
        fetchSumberInformasi();
        fetchPendidikan();
        fetchAgama();
        fetchKewarganegaraan();
        fetchStatusPernikahan();
    }, []);

    useEffect(() => {
        if (isFirstActionSignalRender.current) {
            isFirstActionSignalRender.current = false;
            return;
        }

        if (actionSignals?.dataPelamar > 0) {
            resetForm();
            setModalOpen(true);
        }
    }, [actionSignals?.dataPelamar]);

    const handleChange = (e) => {
        const { name, value } = e.target;

        setForm((prev) => {
            const nextForm = {
                ...prev,
                [name]: value,
            };

            if (name === "alamat_ktp" && alamatSama) {
                nextForm.alamat_domisili = value;
            }

            return nextForm;
        });
    };

    const handleSelectChange = (name, value) => {
        setForm((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleAlamatSamaChange = (e) => {
        const checked = e.target.checked;

        setAlamatSama(checked);

        setForm((prev) => ({
            ...prev,
            alamat_domisili: checked ? prev.alamat_ktp : "",
        }));
    };

    const closeModal = () => {
        setModalOpen(false);
    };

    const validateInformasiLamaran = () => {
        const requiredFields = [
            {
                name: "posisi_yang_dilamar",
                label: "Posisi Yang Dilamar",
            },
            {
                name: "perusahaan_dilamar",
                label: "Perusahaan Dilamar",
            },
            {
                name: "sumber_informasi_id",
                label: "Sumber Informasi",
            },
        ];

        const emptyField = requiredFields.find((field) => {
            return !String(form[field.name] || "").trim();
        });

        if (emptyField) {
            alert(`${emptyField.label} wajib diisi.`);
            return false;
        }

        return true;
    };

    const copyText = async (text) => {
        if (!text) return false;

        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (error) {
            console.error("Clipboard gagal:", error);
            return false;
        }
    };

    const getPendaftaranUrl = (item) => {
        if (item?.pendaftaran_url) {
            return item.pendaftaran_url;
        }

        if (!item?.token) {
            return "";
        }

        return `${window.location.origin}/pendaftaran/${item.token}`;
    };

    const handleCopyPendaftaranUrl = async (item) => {
        const url = getPendaftaranUrl(item);

        if (!url) {
            alert("Token kandidat belum tersedia.");
            return;
        }

        const copied = await copyText(url);

        if (copied) {
            alert("URL pendaftaran berhasil disalin.");
            return;
        }

        prompt("Copy URL pendaftaran:", url);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateInformasiLamaran()) {
            return;
        }

        setLoading(true);

        try {
            const response = await fetch("/admin/data-pelamar", {
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
                alert(result.message || "Data gagal disimpan.");
                return;
            }

            alert(result.message || "Data pelamar berhasil disimpan.");

            if (result.pendaftaran_url) {
                const copied = await copyText(result.pendaftaran_url);

                if (copied) {
                    alert("URL pendaftaran berhasil disalin otomatis.");
                } else {
                    prompt("Copy URL pendaftaran:", result.pendaftaran_url);
                }
            }

            closeModal();
            resetForm();
            fetchData();
        } catch (error) {
            console.error("Gagal menyimpan data:", error);
            alert("Terjadi kesalahan saat menyimpan data.");
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin ingin menghapus data ini?");

        if (!confirmDelete) return;

        try {
            const response = await fetch(`/admin/data-pelamar/${id}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message || "Data berhasil dihapus.");
                fetchData();
            } else {
                alert(result.message || "Data gagal dihapus.");
            }
        } catch (error) {
            console.error("Gagal menghapus data:", error);
            alert("Terjadi kesalahan saat menghapus data.");
        }
    };

    const getNamaPosisi = (posisiId, item = null) => {
        if (item?.posisi?.nama_posisi) {
            return item.posisi.nama_posisi;
        }

        const posisi = dataPosisi.find(
            (data) => String(data.id) === String(posisiId)
        );

        return posisi?.nama_posisi || "-";
    };

    const getNamaPerusahaan = (perusahaanId, item = null) => {
        if (item?.perusahaan?.nama_perusahaan) {
            return item.perusahaan.nama_perusahaan;
        }

        const perusahaan = dataPerusahaan.find(
            (data) => String(data.id) === String(perusahaanId)
        );

        return perusahaan?.nama_perusahaan || "-";
    };

    const getNamaSumberInformasi = (sumberInformasiId, item = null) => {
        if (item?.sumber_informasi?.informasi) {
            return item.sumber_informasi.informasi;
        }

        if (item?.sumberInformasi?.informasi) {
            return item.sumberInformasi.informasi;
        }

        const sumberInformasi = dataSumberInformasi.find(
            (data) => String(data.id) === String(sumberInformasiId)
        );

        return sumberInformasi?.informasi || "-";
    };

    const getKelengkapanForm = (item) => {
        const completion = item?.kelengkapan_form || {};

        return {
            percentage: Number(
                completion?.percentage ?? item?.persentase_kelengkapan ?? 0
            ),
            completed_steps: Number(
                completion?.completed_steps ??
                    item?.total_step_terisi ??
                    item?.total_field_terisi ??
                    0
            ),
            total_steps: Number(
                completion?.total_steps ??
                    item?.total_step_form ??
                    item?.total_field_form ??
                    5
            ),
            last_completed_label:
                completion?.last_completed_label ??
                item?.tahap_terakhir_form ??
                "-",
            steps: Array.isArray(completion?.steps) ? completion.steps : [],
        };
    };

    const formatTanggal = (value) => {
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

    const columnsPelamar = useMemo(() => {
        return [
            {
                key: "no",
                label: "No",
                sortable: false,
                searchable: false,
                render: (_item, meta) => (
                    <span className="text-sm font-black text-slate-500">
                        {meta.rowNumber}
                    </span>
                ),
            },
            {
                key: "token",
                label: "Token",
                accessor: "token",
                render: (item) => (
                    <div className="space-y-2">
                        <div className="inline-flex rounded-2xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-700">
                            {item.token || "-"}
                        </div>
                    </div>
                ),
            },
            {
                key: "pelamar",
                label: "Pelamar",
                accessor: (item) =>
                    `${item.nama_lengkap || ""} ${item.nama_panggil || ""}`,
                render: (item) => (
                    <div className="flex min-w-[220px] items-center gap-3">
                        <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-500 text-sm font-black uppercase text-white shadow-lg shadow-teal-100">
                            {(item.nama_lengkap || "P")
                                .charAt(0)
                                .toUpperCase()}
                        </div>

                        <div>
                            <div className="font-black text-slate-950">
                                {item.nama_lengkap || "-"}
                            </div>

                            <div className="mt-0.5 text-sm font-medium text-slate-500">
                                {item.nama_panggil ||
                                    "Nama panggil belum diisi"}
                            </div>
                        </div>
                    </div>
                ),
            },
            {
                key: "kelengkapan_form",
                label: "Kelengkapan Form",
                accessor: (item) => getKelengkapanForm(item).percentage,
                render: (item) => {
                    const completion = getKelengkapanForm(item);

                    return (
                        <CompletionProgress
                            percentage={completion.percentage}
                            completedSteps={completion.completed_steps}
                            totalSteps={completion.total_steps}
                            lastCompletedLabel={completion.last_completed_label}
                            steps={completion.steps}
                        />
                    );
                },
            },
            {
                key: "kontak",
                label: "Kontak",
                accessor: (item) => `${item.email || ""} ${item.no_wa || ""}`,
                render: (item) => (
                    <div className="min-w-[240px]">
                        <div className="text-sm font-bold text-slate-700">
                            {item.email || "-"}
                        </div>

                        <div className="mt-0.5 text-sm font-bold text-teal-700">
                            WA: {item.no_wa || "-"}
                        </div>
                    </div>
                ),
            },
            {
                key: "posisi",
                label: "Posisi",
                accessor: (item) =>
                    getNamaPosisi(item.posisi_yang_dilamar, item),
                render: (item) => (
                    <span className="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700">
                        {getNamaPosisi(item.posisi_yang_dilamar, item)}
                    </span>
                ),
            },
            {
                key: "perusahaan",
                label: "Perusahaan",
                accessor: (item) =>
                    getNamaPerusahaan(item.perusahaan_dilamar, item),
                render: (item) => (
                    <span className="text-sm font-bold text-slate-600">
                        {getNamaPerusahaan(item.perusahaan_dilamar, item)}
                    </span>
                ),
            },
            {
                key: "sumber_informasi",
                label: "Sumber Informasi",
                accessor: (item) =>
                    getNamaSumberInformasi(item.sumber_informasi_id, item),
                render: (item) => (
                    <span className="inline-flex rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-black text-cyan-700">
                        {getNamaSumberInformasi(item.sumber_informasi_id, item)}
                    </span>
                ),
            },
            {
                key: "tanggal_skrining",
                label: "Tanggal Skrining",
                accessor: "tanggal_skrining",
                render: (item) => (
                    <span className="text-sm font-bold text-slate-600">
                        {formatTanggal(item.tanggal_skrining)}
                    </span>
                ),
            },
            {
                key: "url",
                label: "Pendaftaran",
                accessor: (item) => getPendaftaranUrl(item),
                sortable: false,
                searchable: false,
                render: (item) => {
                    const url = getPendaftaranUrl(item);

                    return (
                        <div className="flex flex-col gap-2">
                            <a
                                href={url || "#"}
                                target="_blank"
                                rel="noreferrer"
                                className={`w-fit rounded-xl px-3 py-2 text-xs font-black transition ${
                                    url
                                        ? "bg-cyan-50 text-cyan-700 hover:bg-cyan-100"
                                        : "pointer-events-none bg-slate-100 text-slate-400"
                                }`}
                            >
                                Buka
                            </a>

                            <button
                                type="button"
                                onClick={() => handleCopyPendaftaranUrl(item)}
                                className="w-fit rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                Salin
                            </button>
                        </div>
                    );
                },
            },
            {
                key: "aksi",
                label: "Aksi",
                align: "right",
                sortable: false,
                searchable: false,
                render: (item) => (
                    <div className="flex justify-end gap-2">
                        <button
                            type="button"
                            onClick={() => onOpenDetailPelamar?.(item.id)}
                            className="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-2 text-xs font-black text-cyan-700 shadow-sm transition hover:bg-cyan-100"
                        >
                            Detail
                        </button>

                        <button
                            type="button"
                            onClick={() => handleDelete(item.id)}
                            className="rounded-2xl border border-rose-100 bg-white px-4 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:bg-rose-50 hover:text-rose-700"
                        >
                            Hapus
                        </button>
                    </div>
                ),
            },
        ];
    }, [
        dataPosisi,
        dataPerusahaan,
        dataSumberInformasi,
        onOpenDetailPelamar,
    ]);

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 className="text-lg font-black text-slate-950">
                            Data Pelamar
                        </h3>
                        <p className="mt-1 text-sm font-medium text-slate-500">
                            Daftar data pelamar yang sudah dibuat oleh admin.
                        </p>
                    </div>
                </div>

                <DataTable
                    data={dataPelamar}
                    columns={columnsPelamar}
                    rowKey="id"
                    defaultSortKey="token"
                    defaultSortDirection="desc"
                    searchPlaceholder="Cari token, nama, email, posisi, perusahaan, sumber informasi, tanggal skrining..."
                    emptyTitle="Data pelamar tidak ditemukan"
                    emptyDescription="Belum ada data pelamar atau kata kunci pencarian tidak cocok."
                />
            </div>

            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
                    <div className="flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div className="shrink-0 border-b border-slate-200 bg-white">
                            <div className="flex items-center justify-between gap-4 px-6 py-5">
                                <div>
                                    <div className="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-teal-700">
                                        Form Skrining Pelamar
                                    </div>

                                    <h2 className="mt-2 text-2xl font-black text-slate-950">
                                        Input Data Pelamar
                                    </h2>

                                    <p className="mt-1 text-sm font-medium text-slate-500">
                                        Setelah disimpan, sistem akan membuat
                                        token dan URL pendaftaran kandidat.
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
                                <div className="space-y-8 pb-2">
                                    <FormSection
                                        title="Informasi Lamaran"
                                        description="Data posisi, perusahaan, dan sumber informasi pelamar."
                                    >
                                        <Select2Single
                                            label="Posisi Yang Dilamar"
                                            name="posisi_yang_dilamar"
                                            value={form.posisi_yang_dilamar}
                                            options={dataPosisi}
                                            optionLabel="nama_posisi"
                                            optionValue="id"
                                            placeholder="Pilih posisi"
                                            searchPlaceholder="Cari posisi..."
                                            onChange={handleSelectChange}
                                            required
                                        />

                                        <Select2Single
                                            label="Perusahaan Dilamar"
                                            name="perusahaan_dilamar"
                                            value={form.perusahaan_dilamar}
                                            options={dataPerusahaan}
                                            optionLabel="nama_perusahaan"
                                            optionValue="id"
                                            placeholder="Pilih perusahaan"
                                            searchPlaceholder="Cari perusahaan..."
                                            onChange={handleSelectChange}
                                            required
                                        />

                                        <Select2Single
                                            label="Sumber Informasi"
                                            name="sumber_informasi_id"
                                            value={form.sumber_informasi_id}
                                            options={dataSumberInformasi}
                                            optionLabel="informasi"
                                            optionValue="id"
                                            placeholder="Pilih sumber informasi"
                                            searchPlaceholder="Cari sumber informasi..."
                                            onChange={handleSelectChange}
                                            required
                                        />
                                    </FormSection>

                                    <FormSection
                                        title="Data Pribadi"
                                        description="Informasi identitas utama pelamar."
                                    >
                                        <Input
                                            label="Nama Lengkap"
                                            name="nama_lengkap"
                                            value={form.nama_lengkap}
                                            onChange={handleChange}
                                            placeholder="Masukkan nama lengkap"
                                            required
                                        />

                                        <Input
                                            label="Nama Panggil"
                                            name="nama_panggil"
                                            value={form.nama_panggil}
                                            onChange={handleChange}
                                            placeholder="Masukkan nama panggil"
                                        />

                                        <Input
                                            label="Email"
                                            name="email"
                                            type="email"
                                            value={form.email}
                                            onChange={handleChange}
                                            placeholder="nama@email.com"
                                        />

                                        <Input
                                            label="No. WhatsApp Aktif"
                                            name="no_wa"
                                            type="number"
                                            value={form.no_wa}
                                            onChange={handleChange}
                                            placeholder="Contoh: 081234567890"
                                            info="Pastikan nomor WhatsApp aktif dan bisa dihubungi oleh HR."
                                            required
                                        />

                                        <Input
                                            label="Tanggal Lahir"
                                            name="tanggal_lahir"
                                            type="date"
                                            value={form.tanggal_lahir}
                                            onChange={handleChange}
                                        />

                                        <Input
                                            label="Tanggal Skrining"
                                            name="tanggal_skrining"
                                            type="date"
                                            value={form.tanggal_skrining}
                                            onChange={handleChange}
                                            required
                                        />

                                        <Input
                                            label="Golongan Darah"
                                            name="gol_darah"
                                            value={form.gol_darah}
                                            onChange={handleChange}
                                            placeholder="A / B / AB / O"
                                        />
                                    </FormSection>

                                    <FormSection
                                        title="Pendidikan & Referensi"
                                        description="Data pendidikan, agama, kewarganegaraan, dan informasi tambahan."
                                    >
                                        <Select2Single
                                            label="Pendidikan"
                                            name="pendidikan_id"
                                            value={form.pendidikan_id}
                                            options={dataPendidikan}
                                            optionLabel="pendidikan"
                                            optionValue="id"
                                            placeholder="Pilih pendidikan"
                                            searchPlaceholder="Cari pendidikan..."
                                            onChange={handleSelectChange}
                                        />

                                        <Input
                                            label="Jurusan"
                                            name="jurusan"
                                            value={form.jurusan}
                                            onChange={handleChange}
                                            placeholder="Contoh: Teknik Informatika"
                                        />

                                        <Input
                                            label="Nama Institusi"
                                            name="nama_institusi"
                                            value={form.nama_institusi}
                                            onChange={handleChange}
                                            placeholder="Nama kampus/sekolah"
                                        />

                                        <Select2Single
                                            label="Agama"
                                            name="agama_id"
                                            value={form.agama_id}
                                            options={dataAgama}
                                            optionLabel="agama"
                                            optionValue="id"
                                            placeholder="Pilih agama"
                                            searchPlaceholder="Cari agama..."
                                            onChange={handleSelectChange}
                                        />

                                        <Select2Single
                                            label="Kewarganegaraan"
                                            name="kewarganegaraan_id"
                                            value={form.kewarganegaraan_id}
                                            options={dataKewarganegaraan}
                                            optionLabel="kewarganegaraan"
                                            optionValue="id"
                                            placeholder="Pilih kewarganegaraan"
                                            searchPlaceholder="Cari kewarganegaraan..."
                                            onChange={handleSelectChange}
                                        />

                                        <Select2Single
                                            label="Status Pernikahan"
                                            name="status_pernikahan_id"
                                            value={form.status_pernikahan_id}
                                            options={dataStatusPernikahan}
                                            optionLabel="status_pernikahan"
                                            optionValue="id"
                                            placeholder="Pilih status pernikahan"
                                            searchPlaceholder="Cari status pernikahan..."
                                            onChange={handleSelectChange}
                                        />

                                        <Input
                                            label="Tinggi Badan"
                                            name="tinggi_badan"
                                            type="number"
                                            value={form.tinggi_badan}
                                            onChange={handleChange}
                                            placeholder="cm"
                                        />

                                        <Input
                                            label="Berat Badan"
                                            name="berat_badan"
                                            type="number"
                                            value={form.berat_badan}
                                            onChange={handleChange}
                                            placeholder="kg"
                                        />
                                    </FormSection>

                                    <FormSection
                                        title="Alamat"
                                        description="Alamat sesuai KTP dan domisili saat ini."
                                        columns="md:grid-cols-2"
                                    >
                                        <div className="space-y-3">
                                            <Textarea
                                                label="Alamat KTP"
                                                name="alamat_ktp"
                                                value={form.alamat_ktp}
                                                onChange={handleChange}
                                                placeholder="Masukkan alamat sesuai KTP"
                                            />

                                            <label className="flex cursor-pointer items-start gap-3 rounded-2xl border border-teal-100 bg-teal-50/70 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-teal-50">
                                                <input
                                                    type="checkbox"
                                                    checked={alamatSama}
                                                    onChange={
                                                        handleAlamatSamaChange
                                                    }
                                                    className="mt-1 h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                                />

                                                <span>
                                                    Alamat domisili sama dengan
                                                    alamat KTP
                                                    <span className="mt-0.5 block text-xs font-semibold text-slate-500">
                                                        Jika dicentang, alamat
                                                        domisili akan otomatis
                                                        mengikuti alamat KTP.
                                                    </span>
                                                </span>
                                            </label>
                                        </div>

                                        <Textarea
                                            label="Alamat Domisili"
                                            name="alamat_domisili"
                                            value={form.alamat_domisili}
                                            onChange={handleChange}
                                            placeholder="Masukkan alamat domisili"
                                            disabled={alamatSama}
                                        />
                                    </FormSection>
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

function CompletionProgress({
    percentage = 0,
    completedSteps = 0,
    totalSteps = 5,
    lastCompletedLabel = "-",
    steps = [],
}) {
    const safePercentage = Math.min(
        100,
        Math.max(0, Number(percentage || 0))
    );

    const safeTotalSteps = Number(totalSteps || 5);

    const safeCompletedSteps = Math.min(
        safeTotalSteps,
        Math.max(0, Number(completedSteps || 0))
    );

    const fixedSteps =
        Array.isArray(steps) && steps.length > 0
            ? steps
            : [
                  {
                      key: "data_diri",
                      label: "Data Diri",
                      completed: safePercentage >= 20,
                  },
                  {
                      key: "riwayat_keluarga",
                      label: "Riwayat Keluarga",
                      completed: safePercentage >= 40,
                  },
                  {
                      key: "riwayat_kesehatan",
                      label: "Riwayat Kesehatan",
                      completed: safePercentage >= 60,
                  },
                  {
                      key: "riwayat_pekerjaan",
                      label: "Riwayat Pekerjaan",
                      completed: safePercentage >= 80,
                  },
                  {
                      key: "kesiapan_bekerja",
                      label: "Kesiapan Bekerja",
                      completed: safePercentage >= 100,
                  },
              ];

    const statusLabel =
        safePercentage >= 100
            ? "Lengkap"
            : safePercentage >= 80
            ? "Sampai Riwayat Pekerjaan"
            : safePercentage >= 60
            ? "Sampai Riwayat Kesehatan"
            : safePercentage >= 40
            ? "Sampai Riwayat Keluarga"
            : safePercentage >= 20
            ? "Sampai Data Diri"
            : "Belum Lengkap";

    const colorClass =
        safePercentage >= 100
            ? "from-emerald-500 to-teal-500"
            : safePercentage >= 80
            ? "from-teal-500 to-cyan-500"
            : safePercentage >= 60
            ? "from-blue-500 to-cyan-500"
            : safePercentage >= 40
            ? "from-amber-500 to-orange-500"
            : "from-rose-500 to-red-500";

    const badgeClass =
        safePercentage >= 100
            ? "bg-emerald-50 text-emerald-700"
            : safePercentage >= 80
            ? "bg-teal-50 text-teal-700"
            : safePercentage >= 60
            ? "bg-blue-50 text-blue-700"
            : safePercentage >= 40
            ? "bg-amber-50 text-amber-700"
            : "bg-rose-50 text-rose-700";

    return (
        <div className="min-w-[300px]">
            <div className="mb-2 flex items-center justify-between gap-3">
                <div>
                    <span className="text-lg font-black text-slate-950">
                        {safePercentage}%
                    </span>

                    <p className="mt-0.5 text-xs font-bold text-slate-500">
                        {safeCompletedSteps} dari {safeTotalSteps} tahapan
                        selesai
                    </p>
                </div>

                <span
                    className={`rounded-full px-3 py-1 text-xs font-black ${badgeClass}`}
                >
                    {statusLabel}
                </span>
            </div>

            <div className="h-3 overflow-hidden rounded-full bg-slate-100">
                <div
                    className={`h-full rounded-full bg-gradient-to-r ${colorClass} transition-all duration-500`}
                    style={{
                        width: `${safePercentage}%`,
                    }}
                />
            </div>

            <div className="mt-3 grid grid-cols-5 gap-1">
                {fixedSteps.map((step, index) => (
                    <div
                        key={step.key || index}
                        title={step.label}
                        className={`h-2 rounded-full ${
                            step.completed ? "bg-teal-500" : "bg-slate-200"
                        }`}
                    />
                ))}
            </div>

            <p className="mt-2 text-xs font-bold text-slate-500">
                Tahap terakhir:{" "}
                <span className="text-slate-800">
                    {lastCompletedLabel || "-"}
                </span>
            </p>
        </div>
    );
}

function DataTable({
    data = [],
    columns = [],
    rowKey = "id",
    defaultSortKey = "",
    defaultSortDirection = "asc",
    searchPlaceholder = "Cari data...",
    emptyTitle = "Data tidak ditemukan",
    emptyDescription = "Belum ada data atau kata kunci pencarian tidak cocok.",
    initialEntriesPerPage = 10,
}) {
    const [search, setSearch] = useState("");
    const [entriesPerPage, setEntriesPerPage] = useState(initialEntriesPerPage);
    const [currentPage, setCurrentPage] = useState(1);

    const [sortConfig, setSortConfig] = useState({
        key: defaultSortKey || columns?.[0]?.key || "",
        direction: defaultSortDirection || "asc",
    });

    useEffect(() => {
        setCurrentPage(1);
    }, [search, entriesPerPage, data]);

    const getColumnValue = (item, column) => {
        if (!column) return "";

        if (typeof column.accessor === "function") {
            return column.accessor(item);
        }

        if (typeof column.accessor === "string") {
            return item?.[column.accessor];
        }

        if (column.key) {
            return item?.[column.key];
        }

        return "";
    };

    const searchableColumns = useMemo(() => {
        return columns.filter((column) => column.searchable !== false);
    }, [columns]);

    const sortableColumns = useMemo(() => {
        return columns.filter((column) => column.sortable !== false);
    }, [columns]);

    const filteredData = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) return data;

        return data.filter((item) => {
            return searchableColumns.some((column) => {
                const value = getColumnValue(item, column);

                return String(value || "")
                    .toLowerCase()
                    .includes(keyword);
            });
        });
    }, [data, search, searchableColumns]);

    const sortedData = useMemo(() => {
        const selectedColumn = sortableColumns.find(
            (column) => column.key === sortConfig.key
        );

        if (!selectedColumn) return filteredData;

        const result = [...filteredData];

        result.sort((a, b) => {
            const valueA = getColumnValue(a, selectedColumn);
            const valueB = getColumnValue(b, selectedColumn);

            const cleanA = String(valueA || "").toLowerCase();
            const cleanB = String(valueB || "").toLowerCase();

            if (cleanA < cleanB) {
                return sortConfig.direction === "asc" ? -1 : 1;
            }

            if (cleanA > cleanB) {
                return sortConfig.direction === "asc" ? 1 : -1;
            }

            return 0;
        });

        return result;
    }, [filteredData, sortableColumns, sortConfig]);

    const totalPages = Math.max(
        1,
        Math.ceil(sortedData.length / entriesPerPage)
    );

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

    const handleSort = (column) => {
        if (column.sortable === false) return;

        setSortConfig((prev) => {
            if (prev.key === column.key) {
                return {
                    key: column.key,
                    direction: prev.direction === "asc" ? "desc" : "asc",
                };
            }

            return {
                key: column.key,
                direction: "asc",
            };
        });
    };

    const getSortIcon = (column) => {
        if (column.sortable === false) return null;

        if (sortConfig.key !== column.key) {
            return "⇅";
        }

        return sortConfig.direction === "asc" ? "↑" : "↓";
    };

    const getRowKey = (item, index) => {
        if (typeof rowKey === "function") {
            return rowKey(item, index);
        }

        return item?.[rowKey] || index;
    };

    return (
        <>
            <div className="border-b border-slate-100 px-6 py-4">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex flex-wrap items-center gap-2">
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
                            <option value={100}>100</option>
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
                        <tr className="bg-slate-50/80">
                            {columns.map((column) => {
                                const alignClass =
                                    column.align === "right"
                                        ? "text-right"
                                        : column.align === "center"
                                        ? "text-center"
                                        : "text-left";

                                const sortIcon = getSortIcon(column);

                                return (
                                    <th
                                        key={column.key}
                                        className={`whitespace-nowrap px-6 py-4 ${alignClass} text-xs font-black uppercase tracking-[0.12em] text-slate-500`}
                                    >
                                        {column.sortable === false ? (
                                            <span className="inline-flex items-center gap-2 whitespace-nowrap">
                                                {column.label}
                                            </span>
                                        ) : (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    handleSort(column)
                                                }
                                                className="inline-flex items-center gap-2 whitespace-nowrap font-black uppercase tracking-[0.12em] text-slate-500 transition hover:text-slate-800"
                                            >
                                                <span>{column.label}</span>
                                                <span className="text-xs">
                                                    {sortIcon}
                                                </span>
                                            </button>
                                        )}
                                    </th>
                                );
                            })}
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-100 bg-white">
                        {paginatedData.length > 0 ? (
                            paginatedData.map((item, index) => (
                                <tr
                                    key={getRowKey(item, index)}
                                    className="group transition hover:bg-slate-50"
                                >
                                    {columns.map((column) => {
                                        const alignClass =
                                            column.align === "right"
                                                ? "text-right"
                                                : column.align === "center"
                                                ? "text-center"
                                                : "text-left";

                                        return (
                                            <td
                                                key={column.key}
                                                className={`whitespace-nowrap px-6 py-5 align-middle ${alignClass}`}
                                            >
                                                {column.render
                                                    ? column.render(item, {
                                                          index,
                                                          rowNumber:
                                                              showingFrom +
                                                              index,
                                                      })
                                                    : String(
                                                          getColumnValue(
                                                              item,
                                                              column
                                                          ) || "-"
                                                      )}
                                            </td>
                                        );
                                    })}
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td
                                    colSpan={columns.length}
                                    className="px-6 py-16"
                                >
                                    <div className="mx-auto max-w-sm text-center">
                                        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                            ▦
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
                    Showing {showingFrom} to {showingTo} of {sortedData.length}{" "}
                    entries
                    {search && (
                        <span>
                            {" "}
                            filtered from {data.length} total entries
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
                                    ? "bg-gradient-to-r from-teal-600 to-cyan-600 text-white shadow-teal-100"
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
        </>
    );
}

function FormSection({
    title,
    description,
    children,
    columns = "md:grid-cols-2 xl:grid-cols-3",
}) {
    return (
        <section className="rounded-[1.5rem] border border-slate-200 bg-slate-50/60 p-5">
            <div className="mb-5">
                <h3 className="text-base font-black text-slate-950">
                    {title}
                </h3>

                <p className="mt-1 text-sm font-medium text-slate-500">
                    {description}
                </p>
            </div>

            <div className={`grid gap-4 ${columns}`}>{children}</div>
        </section>
    );
}

function Select2Single({
    label,
    name,
    value,
    options,
    optionLabel,
    optionValue,
    placeholder = "Pilih data",
    searchPlaceholder = "Cari data...",
    onChange,
    required = false,
}) {
    const wrapperRef = useRef(null);
    const searchInputRef = useRef(null);

    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState("");

    const selectedOption = useMemo(() => {
        return options.find(
            (item) => String(item[optionValue]) === String(value)
        );
    }, [options, optionValue, value]);

    const filteredOptions = useMemo(() => {
        const keyword = search.toLowerCase();

        return options.filter((item) =>
            String(item[optionLabel] || "")
                .toLowerCase()
                .includes(keyword)
        );
    }, [options, optionLabel, search]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                wrapperRef.current &&
                !wrapperRef.current.contains(event.target)
            ) {
                setOpen(false);
                setSearch("");
            }
        };

        document.addEventListener("mousedown", handleClickOutside);

        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, []);

    useEffect(() => {
        if (open) {
            setTimeout(() => {
                searchInputRef.current?.focus();
            }, 50);
        }
    }, [open]);

    const handleSelect = (selectedValue) => {
        onChange(name, selectedValue);
        setOpen(false);
        setSearch("");
    };

    const handleClear = (e) => {
        e.stopPropagation();
        onChange(name, "");
        setSearch("");
    };

    return (
        <div ref={wrapperRef} className="relative">
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
                {required && <span className="text-rose-500"> *</span>}
            </label>

            <button
                type="button"
                onClick={() => setOpen((prev) => !prev)}
                className={`flex w-full items-center justify-between rounded-2xl border bg-white px-4 py-3 text-left text-sm font-bold shadow-sm outline-none transition ${
                    open
                        ? "border-teal-500 ring-4 ring-teal-100"
                        : required && !value
                        ? "border-rose-200 hover:border-rose-300"
                        : "border-slate-200 hover:border-teal-300"
                }`}
            >
                <span
                    className={
                        selectedOption ? "text-slate-800" : "text-slate-400"
                    }
                >
                    {selectedOption ? selectedOption[optionLabel] : placeholder}
                </span>

                <span className="flex items-center gap-2">
                    {selectedOption && (
                        <span
                            role="button"
                            tabIndex={0}
                            onClick={handleClear}
                            onKeyDown={(e) => {
                                if (e.key === "Enter") handleClear(e);
                            }}
                            className="rounded-full px-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                        >
                            ×
                        </span>
                    )}

                    <span className="text-slate-400">
                        {open ? "⌃" : "⌄"}
                    </span>
                </span>
            </button>

            {required && !value && (
                <p className="mt-2 text-xs font-bold text-rose-500">
                    Wajib diisi
                </p>
            )}

            {open && (
                <div className="absolute left-0 right-0 z-[60] mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
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

                    <div className="max-h-60 overflow-y-auto py-2">
                        {filteredOptions.length > 0 ? (
                            filteredOptions.map((item) => {
                                const itemValue = item[optionValue];
                                const isSelected =
                                    String(itemValue) === String(value);

                                return (
                                    <button
                                        key={itemValue}
                                        type="button"
                                        onClick={() =>
                                            handleSelect(itemValue)
                                        }
                                        className={`block w-full px-4 py-3 text-left text-sm font-bold transition ${
                                            isSelected
                                                ? "bg-teal-600 text-white"
                                                : "text-slate-700 hover:bg-teal-50 hover:text-teal-800"
                                        }`}
                                    >
                                        {item[optionLabel]}
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

function Input({
    label,
    name,
    value,
    onChange,
    type = "text",
    required = false,
    placeholder = "",
    info = "",
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

            {info && (
                <p className="mt-2 rounded-xl bg-teal-50 px-3 py-2 text-xs font-bold leading-5 text-teal-700">
                    {info}
                </p>
            )}
        </div>
    );
}

function Textarea({
    label,
    name,
    value,
    onChange,
    placeholder = "",
    disabled = false,
}) {
    return (
        <div>
            <label className="mb-2 block text-sm font-black text-slate-700">
                {label}
            </label>

            <textarea
                name={name}
                value={value}
                onChange={onChange}
                rows="4"
                placeholder={placeholder}
                disabled={disabled}
                className={`w-full rounded-2xl border px-4 py-3 text-sm font-bold shadow-sm outline-none transition placeholder:text-slate-300 ${
                    disabled
                        ? "cursor-not-allowed border-slate-200 bg-slate-100 text-slate-500"
                        : "border-slate-200 bg-white text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                }`}
            />
        </div>
    );
}