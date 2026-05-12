import React, { useState } from "react";
import { createRoot } from "react-dom/client";

import StepMenu from "./components/StepMenu";
import StepDataDiri from "./components/StepDataDiri";
import StepRiwayatKeluarga from "./components/StepRiwayatKeluarga";
import StepRiwayatKesehatan from "./components/StepRiwayatKesehatan";
import StepRiwayatPekerjaan from "./components/StepRiwayatPekerjaan";
import StepKesiapanBekerja from "./components/StepKesiapanBekerja";

function PendaftaranPage() {
    const emptySaudara = {
        nama: "",
        jenis_kelamin: "",
        hubungan: "",
        pekerjaan: "",
        no_hp: "",
        alamat: "",
    };

    const emptySosialMedia = {
        platform: "",
        nama_akun: "",
    };

    const emptyKontakDarurat = {
        nama: "",
        status: "",
        nomor: "",
    };

    const [activePage, setActivePage] = useState("pendaftaran");
    const [step, setStep] = useState(1);
    const [errors, setErrors] = useState({});

    const [cekTahapanForm, setCekTahapanForm] = useState({
        token: "",
    });

    const [cekTahapanErrors, setCekTahapanErrors] = useState({});
    const [hasilCekTahapan, setHasilCekTahapan] = useState(null);

    const [form, setForm] = useState({
        nama: "",
        nama_panggilan: "",
        email: "",
        no_hp: "",
        nik: "",

        posisi_dilamar: "",
        perusahaan_dilamar: "",

        pendidikan: "",
        str_aktif: "",

        sosial_media: [{ ...emptySosialMedia }],

        tempat_lahir: "",
        tanggal_lahir: "",
        jenis_kelamin: "",
        agama: "",
        status_perkawinan: "",
        kewarganegaraan: "",
        alamat: "",
        provinsi: "",
        kabupaten: "",
        kecamatan: "",
        kelurahan: "",
        rt: "",
        rw: "",

        nama_ayah: "",
        nik_ayah: "",
        tempat_lahir_ayah: "",
        tanggal_lahir_ayah: "",
        pekerjaan_ayah: "",
        no_hp_ayah: "",
        alamat_ayah: "",

        nama_ibu: "",
        nik_ibu: "",
        tempat_lahir_ibu: "",
        tanggal_lahir_ibu: "",
        pekerjaan_ibu: "",
        no_hp_ibu: "",
        alamat_ibu: "",

        nama_ayah_kandung: "",
        pekerjaan_ayah_kandung: "",
        nama_ibu_kandung: "",
        pekerjaan_ibu_kandung: "",

        nama_suami_istri: "",
        pekerjaan_suami_istri: "",
        tlpn_suami_istri: "",

        nama_bapak_mertua: "",
        pekerjaan_bapak_mertua: "",
        nama_ibu_mertua: "",
        pekerjaan_ibu_mertua: "",

        kontak_darurat: [{ ...emptyKontakDarurat }],

        saudara_kandung: [{ ...emptySaudara }],
        saudara_ipar: [{ ...emptySaudara }],

        golongan_darah: "",
        tinggi_badan: "",
        berat_badan: "",

        buta_warna: "",
        kacamata_digunakan: [],
        alat_bantu_pendengaran: "",
        tangan_dominan: "",
        tangan_gemetar: "",
        tangan_berkeringat: "",

        memiliki_riwayat_penyakit: "",
        riwayat_penyakit: "",
        punya_penyakit_genetik: "",
        nama_penyakit: "",
        riwayat_kronis: "",
        riwayat_penyakit_menular: "",

        memiliki_alergi: "",
        alergi: "",
        obat_dikonsumsi: "",

        pengobatan_psikolog: "",
        kapan_dilakukan: "",
        pernah_kecelakaan: "",
        bagian_tubuh_kecelakaan: "",
        pernah_operasi: "",
        diagnosa_dokter: "",

        pernah_dirawat: "",
        tahun_dirawat: "",
        program_kehamilan: "",
        catatan_kesehatan: "",

        status_pekerjaan: "",
        nama_perusahaan: "",
        posisi_pekerjaan: "",
        bidang_pekerjaan: "",
        lokasi_perusahaan: "",
        tahun_mulai_bekerja: "",
        tahun_selesai_bekerja: "",
        lama_bekerja: "",
        deskripsi_pekerjaan: "",
        alasan_berhenti: "",
        gaji_terakhir: "",
        keahlian: "",
        catatan_pekerjaan: "",

        bersedia_ditempatkan: "",
        bersedia_shift: "",
        bersedia_lembur: "",
        bersedia_hari_libur: "",
        tanggal_siap_kerja: "",
        gaji_diharapkan: "",
        lokasi_kerja_diinginkan: "",
        memiliki_kendaraan: "",
        memiliki_sim: "",
        bersedia_pelatihan: "",
        status_ikatan_kerja: "",
        alasan_melamar: "",
        catatan_kesiapan: "",
    });

    const steps = [
        {
            id: 1,
            title: "Data Diri",
            description: "Identitas utama pelamar.",
        },
        {
            id: 2,
            title: "Riwayat Keluarga",
            description: "Data keluarga pelamar.",
        },
        {
            id: 3,
            title: "Riwayat Kesehatan",
            description: "Kondisi kesehatan pelamar.",
        },
        {
            id: 4,
            title: "Riwayat Pekerjaan",
            description: "Pengalaman kerja pelamar.",
        },
        {
            id: 5,
            title: "Kesiapan Bekerja",
            description: "Kesiapan mulai bekerja.",
        },
    ];

    const stepTitle = {
        1: "Data Diri",
        2: "Riwayat Keluarga",
        3: "Riwayat Kesehatan",
        4: "Riwayat Pekerjaan",
        5: "Data Kesiapan Bekerja",
    };

    const stepDescription = {
        1: "Isi informasi identitas pribadi dan data lamaran dengan lengkap.",
        2: "Lengkapi informasi keluarga serta kontak darurat yang dapat dihubungi.",
        3: "Lengkapi informasi kondisi kesehatan dan riwayat medis Anda.",
        4: "Isi pengalaman kerja, keahlian, dan riwayat pekerjaan Anda.",
        5: "Lengkapi informasi kesiapan kerja sebelum mengirim pendaftaran.",
    };

    const requiredFieldsByStep = {
        1: [
            "nama",
            "nama_panggilan",
            "email",
            "no_hp",
            "nik",
            "posisi_dilamar",
            "perusahaan_dilamar",
            "pendidikan",
            "str_aktif",
            "tempat_lahir",
            "tanggal_lahir",
            "jenis_kelamin",
            "agama",
            "alamat",
        ],
        2: [
            "nama_ayah_kandung",
            "pekerjaan_ayah_kandung",
            "nama_ibu_kandung",
            "pekerjaan_ibu_kandung",
            "kontak_darurat",
        ],
        3: [
            "golongan_darah",
            "tinggi_badan",
            "berat_badan",
            "buta_warna",
            "kacamata_digunakan",
            "alat_bantu_pendengaran",
            "tangan_dominan",
            "tangan_gemetar",
            "tangan_berkeringat",
            "memiliki_riwayat_penyakit",
            "punya_penyakit_genetik",
            "riwayat_kronis",
            "riwayat_penyakit_menular",
            "memiliki_alergi",
            "pengobatan_psikolog",
            "pernah_kecelakaan",
            "pernah_operasi",
            "program_kehamilan",
        ],
        4: ["status_pekerjaan", "posisi_pekerjaan", "keahlian"],
        5: ["bersedia_ditempatkan", "bersedia_shift", "tanggal_siap_kerja"],
    };

    const fieldLabels = {
        nama: "Nama Lengkap",
        nama_panggilan: "Nama Panggilan",
        email: "Email",
        no_hp: "Nomor HP",
        nik: "NIK",
        posisi_dilamar: "Posisi yang Dilamar",
        perusahaan_dilamar: "Perusahaan yang Dilamar",
        pendidikan: "Pendidikan Terakhir",
        str_aktif: "STR Aktif",
        tempat_lahir: "Tempat Lahir",
        tanggal_lahir: "Tanggal Lahir",
        jenis_kelamin: "Jenis Kelamin",
        agama: "Agama",
        alamat: "Alamat Lengkap",

        nama_ayah_kandung: "Nama Ayah Kandung",
        pekerjaan_ayah_kandung: "Pekerjaan Ayah Kandung",
        nama_ibu_kandung: "Nama Ibu Kandung",
        pekerjaan_ibu_kandung: "Pekerjaan Ibu Kandung",

        kontak_darurat: "Kontak Darurat",

        golongan_darah: "Golongan Darah",
        tinggi_badan: "Tinggi Badan",
        berat_badan: "Berat Badan",

        buta_warna: "Buta Warna",
        kacamata_digunakan: "Penggunaan Kaca Mata",
        alat_bantu_pendengaran: "Alat Bantu Pendengaran",
        tangan_dominan: "Tangan yang Digunakan Saat Menulis",
        tangan_gemetar: "Tangan Sering Gemetar",
        tangan_berkeringat: "Tangan Sering Berkeringat",

        memiliki_riwayat_penyakit: "Riwayat Penyakit",
        punya_penyakit_genetik: "Punya Penyakit Genetik",
        nama_penyakit: "Nama Penyakit",
        riwayat_kronis: "Riwayat Kronis",
        riwayat_penyakit_menular: "Riwayat Penyakit Menular",

        memiliki_alergi: "Alergi",
        pengobatan_psikolog: "Pengobatan Psikolog",
        kapan_dilakukan: "Kapan Dilakukan",
        pernah_kecelakaan: "Pernah Kecelakaan",
        bagian_tubuh_kecelakaan: "Bagian Tubuh yang Kecelakaan",
        pernah_operasi: "Pernah Operasi",
        diagnosa_dokter: "Diagnosa Dokter",
        program_kehamilan: "Program Kehamilan",

        status_pekerjaan: "Status Pekerjaan",
        posisi_pekerjaan: "Posisi / Jabatan",
        keahlian: "Keahlian / Skill",

        bersedia_ditempatkan: "Bersedia Ditempatkan",
        bersedia_shift: "Bersedia Shift",
        tanggal_siap_kerja: "Tanggal Siap Kerja",
    };

    const isEmpty = (value) => {
        if (Array.isArray(value)) {
            return value.length === 0;
        }

        return value === undefined || value === null || String(value).trim() === "";
    };

    const resetCekTahapan = () => {
        setCekTahapanForm({
            token: "",
        });

        setCekTahapanErrors({});
        setHasilCekTahapan(null);
    };

    const openCekTahapan = () => {
        resetCekTahapan();
        setActivePage("cek-tahapan");

        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    };

    const backToPendaftaran = () => {
        resetCekTahapan();
        setActivePage("pendaftaran");

        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    };

    const handleCekTahapanChange = (e) => {
        const { name, value } = e.target;

        setCekTahapanForm((prevForm) => ({
            ...prevForm,
            [name]: value,
        }));

        setCekTahapanErrors((prevErrors) => {
            const updatedErrors = { ...prevErrors };

            if (!isEmpty(value)) {
                delete updatedErrors[name];
            }

            return updatedErrors;
        });

        setHasilCekTahapan(null);
    };

    const handleCekTahapanSubmit = (e) => {
        e.preventDefault();

        const newErrors = {};

        if (isEmpty(cekTahapanForm.token)) {
            newErrors.token = "Token pelamar wajib diisi.";
        }

        setCekTahapanErrors(newErrors);

        if (Object.keys(newErrors).length > 0) {
            return;
        }

        setHasilCekTahapan({
            token: cekTahapanForm.token,
            nama_pelamar: "Andi Saputra",
            posisi_dilamar: "Staff Administrasi",
            status: "Gagal Interview",
            tahapan_terakhir: "Interview",
            keterangan:
                "Terima kasih telah mengikuti proses seleksi. Berdasarkan hasil evaluasi, Anda telah lolos tahap Administrasi, Test Psikolog, dan Test MMPI. Namun, untuk saat ini Anda belum dapat melanjutkan ke tahap berikutnya karena hasil interview belum memenuhi kualifikasi yang dibutuhkan.",
            saran:
                "Anda dapat mencoba kembali di lain waktu apabila terdapat lowongan yang sesuai. Tetap tingkatkan kemampuan komunikasi, kesiapan kerja, dan pemahaman terhadap posisi yang dilamar.",
            tahapan: [
                {
                    nama: "Administrasi",
                    status: "Lolos",
                    keterangan:
                        "Data pendaftaran dan kelengkapan berkas telah diverifikasi serta dinyatakan sesuai dengan persyaratan administrasi.",
                },
                {
                    nama: "Test Psikolog",
                    status: "Lolos",
                    keterangan:
                        "Pelamar telah mengikuti test psikolog dan hasilnya memenuhi standar penilaian awal perusahaan.",
                },
                {
                    nama: "Test MMPI",
                    status: "Lolos",
                    keterangan:
                        "Pelamar telah mengikuti test MMPI dan hasilnya memenuhi ketentuan proses seleksi.",
                },
                {
                    nama: "Interview",
                    status: "Gagal",
                    keterangan:
                        "Pelamar belum memenuhi kriteria penilaian pada tahap interview untuk posisi yang dilamar saat ini.",
                    saran:
                        "Silakan mencoba kembali di lain waktu apabila tersedia kesempatan rekrutmen berikutnya.",
                },
            ],
        });
    };

    const validateStep = () => {
        const currentRequiredFields = requiredFieldsByStep[step] || [];
        const newErrors = {};

        currentRequiredFields.forEach((fieldName) => {
            if (fieldName === "kontak_darurat") {
                const firstKontak = Array.isArray(form.kontak_darurat)
                    ? form.kontak_darurat[0]
                    : null;

                if (!firstKontak || isEmpty(firstKontak.nama)) {
                    newErrors.kontak_darurat_nama =
                        "Nama kontak darurat wajib diisi.";
                }

                if (!firstKontak || isEmpty(firstKontak.status)) {
                    newErrors.kontak_darurat_status =
                        "Status hubungan kontak darurat wajib dipilih.";
                }

                if (!firstKontak || isEmpty(firstKontak.nomor)) {
                    newErrors.kontak_darurat_nomor =
                        "Nomor telepon kontak darurat wajib diisi.";
                }

                return;
            }

            if (
                fieldName === "posisi_pekerjaan" &&
                form.status_pekerjaan === "Belum Bekerja"
            ) {
                return;
            }

            if (isEmpty(form[fieldName])) {
                newErrors[fieldName] = `${
                    fieldLabels[fieldName] || fieldName
                } wajib diisi.`;
            }
        });

        if (step === 3) {
            if (
                form.punya_penyakit_genetik === "Ya" &&
                isEmpty(form.nama_penyakit)
            ) {
                newErrors.nama_penyakit =
                    "Nama penyakit wajib diisi jika memilih Ya.";
            }

            if (
                form.pengobatan_psikolog === "Ya" &&
                isEmpty(form.kapan_dilakukan)
            ) {
                newErrors.kapan_dilakukan =
                    "Kapan dilakukan wajib diisi jika memilih Ya.";
            }

            if (
                form.pernah_kecelakaan === "Ya" &&
                isEmpty(form.bagian_tubuh_kecelakaan)
            ) {
                newErrors.bagian_tubuh_kecelakaan =
                    "Bagian tubuh yang kecelakaan wajib diisi jika memilih Ya.";
            }

            if (
                form.pernah_operasi === "Ya" &&
                isEmpty(form.diagnosa_dokter)
            ) {
                newErrors.diagnosa_dokter =
                    "Diagnosa dokter wajib diisi jika memilih Ya.";
            }
        }

        setErrors(newErrors);

        if (Object.keys(newErrors).length > 0) {
            const firstErrorField = Object.keys(newErrors)[0];

            const selectorMap = {
                kontak_darurat_nama: `[name="kontak_darurat_0_nama"]`,
                kontak_darurat_status: `[name="kontak_darurat_0_status"]`,
                kontak_darurat_nomor: `[name="kontak_darurat_0_nomor"]`,
            };

            const selector =
                selectorMap[firstErrorField] || `[name="${firstErrorField}"]`;

            const element = document.querySelector(selector);

            if (element) {
                element.focus();
                element.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }

            return false;
        }

        return true;
    };

    const handleChange = (e) => {
        const { name, value } = e.target;

        setForm((prevForm) => {
            const updatedForm = {
                ...prevForm,
                [name]: value,
            };

            if (name === "status_pekerjaan" && value === "Belum Bekerja") {
                updatedForm.posisi_pekerjaan = "";
            }

            return updatedForm;
        });

        setErrors((prevErrors) => {
            const updatedErrors = { ...prevErrors };

            if (!isEmpty(value)) {
                delete updatedErrors[name];
            }

            if (name === "status_pekerjaan" && value === "Belum Bekerja") {
                delete updatedErrors.posisi_pekerjaan;
            }

            return updatedErrors;
        });
    };

    const handleSosialMediaChange = (index, name, value) => {
        setForm((prevForm) => {
            const updatedSosialMedia = [...prevForm.sosial_media];

            updatedSosialMedia[index] = {
                ...updatedSosialMedia[index],
                [name]: value,
            };

            return {
                ...prevForm,
                sosial_media: updatedSosialMedia,
            };
        });
    };

    const addSosialMedia = () => {
        setForm((prevForm) => ({
            ...prevForm,
            sosial_media: [
                ...prevForm.sosial_media,
                { ...emptySosialMedia },
            ],
        }));
    };

    const removeSosialMedia = (index) => {
        setForm((prevForm) => {
            const updatedSosialMedia = prevForm.sosial_media.filter(
                (_, itemIndex) => itemIndex !== index
            );

            return {
                ...prevForm,
                sosial_media: updatedSosialMedia.length
                    ? updatedSosialMedia
                    : [{ ...emptySosialMedia }],
            };
        });
    };

    const handleKontakDaruratChange = (index, name, value) => {
        setForm((prevForm) => {
            const updatedKontakDarurat = [...prevForm.kontak_darurat];

            updatedKontakDarurat[index] = {
                ...updatedKontakDarurat[index],
                [name]: value,
            };

            return {
                ...prevForm,
                kontak_darurat: updatedKontakDarurat,
            };
        });

        setErrors((prevErrors) => {
            const updatedErrors = { ...prevErrors };

            if (!isEmpty(value)) {
                if (name === "nama") {
                    delete updatedErrors.kontak_darurat_nama;
                }

                if (name === "status") {
                    delete updatedErrors.kontak_darurat_status;
                }

                if (name === "nomor") {
                    delete updatedErrors.kontak_darurat_nomor;
                }
            }

            return updatedErrors;
        });
    };

    const addKontakDarurat = () => {
        setForm((prevForm) => ({
            ...prevForm,
            kontak_darurat: [
                ...prevForm.kontak_darurat,
                { ...emptyKontakDarurat },
            ],
        }));
    };

    const removeKontakDarurat = (index) => {
        setForm((prevForm) => {
            const updatedKontakDarurat = prevForm.kontak_darurat.filter(
                (_, itemIndex) => itemIndex !== index
            );

            return {
                ...prevForm,
                kontak_darurat: updatedKontakDarurat.length
                    ? updatedKontakDarurat
                    : [{ ...emptyKontakDarurat }],
            };
        });
    };

    const handleSaudaraKandungChange = (index, name, value) => {
        setForm((prevForm) => {
            const updatedSaudara = [...prevForm.saudara_kandung];

            updatedSaudara[index] = {
                ...updatedSaudara[index],
                [name]: value,
            };

            return {
                ...prevForm,
                saudara_kandung: updatedSaudara,
            };
        });
    };

    const addSaudaraKandung = () => {
        setForm((prevForm) => ({
            ...prevForm,
            saudara_kandung: [
                ...prevForm.saudara_kandung,
                { ...emptySaudara },
            ],
        }));
    };

    const removeSaudaraKandung = (index) => {
        setForm((prevForm) => {
            const updatedSaudara = prevForm.saudara_kandung.filter(
                (_, itemIndex) => itemIndex !== index
            );

            return {
                ...prevForm,
                saudara_kandung: updatedSaudara.length
                    ? updatedSaudara
                    : [{ ...emptySaudara }],
            };
        });
    };

    const handleSaudaraIparChange = (index, name, value) => {
        setForm((prevForm) => {
            const updatedSaudara = [...prevForm.saudara_ipar];

            updatedSaudara[index] = {
                ...updatedSaudara[index],
                [name]: value,
            };

            return {
                ...prevForm,
                saudara_ipar: updatedSaudara,
            };
        });
    };

    const addSaudaraIpar = () => {
        setForm((prevForm) => ({
            ...prevForm,
            saudara_ipar: [
                ...prevForm.saudara_ipar,
                { ...emptySaudara },
            ],
        }));
    };

    const removeSaudaraIpar = (index) => {
        setForm((prevForm) => {
            const updatedSaudara = prevForm.saudara_ipar.filter(
                (_, itemIndex) => itemIndex !== index
            );

            return {
                ...prevForm,
                saudara_ipar: updatedSaudara.length
                    ? updatedSaudara
                    : [{ ...emptySaudara }],
            };
        });
    };

    const nextStep = () => {
        if (!validateStep()) {
            return;
        }

        if (step < steps.length) {
            setStep((currentStep) => currentStep + 1);
            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        }
    };

    const prevStep = () => {
        if (step > 1) {
            setErrors({});
            setStep((currentStep) => currentStep - 1);
            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (!validateStep()) {
            return;
        }

        console.log("Data pendaftaran:", form);
        alert("Pendaftaran berhasil dikirim!");
    };

    const renderStep = () => {
        if (step === 1) {
            return (
                <StepDataDiri
                    form={form}
                    errors={errors}
                    handleChange={handleChange}
                    requiredFields={requiredFieldsByStep[1]}
                    handleSosialMediaChange={handleSosialMediaChange}
                    addSosialMedia={addSosialMedia}
                    removeSosialMedia={removeSosialMedia}
                />
            );
        }

        if (step === 2) {
            return (
                <StepRiwayatKeluarga
                    form={form}
                    errors={errors}
                    handleChange={handleChange}
                    requiredFields={requiredFieldsByStep[2]}
                    handleKontakDaruratChange={handleKontakDaruratChange}
                    addKontakDarurat={addKontakDarurat}
                    removeKontakDarurat={removeKontakDarurat}
                    handleSaudaraKandungChange={handleSaudaraKandungChange}
                    addSaudaraKandung={addSaudaraKandung}
                    removeSaudaraKandung={removeSaudaraKandung}
                    handleSaudaraIparChange={handleSaudaraIparChange}
                    addSaudaraIpar={addSaudaraIpar}
                    removeSaudaraIpar={removeSaudaraIpar}
                />
            );
        }

        if (step === 3) {
            return (
                <StepRiwayatKesehatan
                    form={form}
                    errors={errors}
                    handleChange={handleChange}
                    requiredFields={requiredFieldsByStep[3]}
                />
            );
        }

        if (step === 4) {
            return (
                <StepRiwayatPekerjaan
                    form={form}
                    errors={errors}
                    handleChange={handleChange}
                    requiredFields={requiredFieldsByStep[4]}
                />
            );
        }

        if (step === 5) {
            return (
                <StepKesiapanBekerja
                    form={form}
                    errors={errors}
                    handleChange={handleChange}
                    requiredFields={requiredFieldsByStep[5]}
                />
            );
        }

        return null;
    };

    if (activePage === "cek-tahapan") {
        return (
            <CekTahapanPelamar
                form={cekTahapanForm}
                errors={cekTahapanErrors}
                hasil={hasilCekTahapan}
                handleChange={handleCekTahapanChange}
                handleSubmit={handleCekTahapanSubmit}
                onBack={backToPendaftaran}
            />
        );
    }

    return (
        <main className="min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-cyan-50 px-3 py-5 sm:px-4 sm:py-10">
            <div className="mx-auto max-w-7xl">
                <div className="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 sm:rounded-[2rem]">
                    <div className="grid lg:grid-cols-5">
                        <aside className="relative overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-teal-900 p-6 text-white sm:p-8 lg:col-span-2">
                            <div className="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl" />
                            <div className="absolute -bottom-28 -left-28 h-64 w-64 rounded-full bg-teal-400/10 blur-3xl" />
                            <div className="absolute right-8 top-1/2 h-32 w-32 rounded-full bg-white/5 blur-2xl" />

                            <div className="relative">
                                <div className="mb-8">
                                    <div className="mb-7 rounded-3xl border border-white/10 bg-white/10 p-4 shadow-xl backdrop-blur">
                                        <div className="flex items-center gap-4">
                                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-sm font-black text-slate-950 shadow-lg">
                                                HR
                                            </div>

                                            <div className="min-w-0">
                                                <p className="text-[10px] font-bold uppercase tracking-[0.22em] text-cyan-100">
                                                    Sistem Rekrutmen
                                                </p>
                                                <h2 className="mt-1 text-base font-black leading-5 text-white sm:text-lg">
                                                    Portal Kandidat
                                                </h2>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mb-6">
                                        <span className="inline-flex rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-1.5 text-[11px] font-bold uppercase tracking-wide text-cyan-100">
                                            Pendaftaran Online
                                        </span>

                                        <h1 className="mt-5 text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl">
                                            Pusat Pendaftaran Kandidat
                                        </h1>

                                        <p className="mt-4 max-w-md text-sm leading-7 text-slate-200">
                                            Lengkapi data pribadi, riwayat keluarga,
                                            kesehatan, pengalaman kerja, dan kesiapan
                                            bekerja secara bertahap.
                                        </p>
                                    </div>

                                    <div className="mb-5 rounded-3xl border border-white/10 bg-white/10 p-4 shadow-lg backdrop-blur">
                                        <div className="mb-3 flex items-center justify-between gap-3">
                                            <div>
                                                <p className="text-xs font-semibold text-cyan-100">
                                                    Progress Pengisian
                                                </p>
                                                <p className="mt-1 text-sm font-black text-white">
                                                    Langkah {step} dari {steps.length}
                                                </p>
                                            </div>

                                            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-sm font-black text-slate-950">
                                                {Math.round((step / steps.length) * 100)}%
                                            </div>
                                        </div>

                                        <div className="h-2 overflow-hidden rounded-full bg-white/10">
                                            <div
                                                className="h-full rounded-full bg-cyan-300 transition-all duration-500"
                                                style={{
                                                    width: `${(step / steps.length) * 100}%`,
                                                }}
                                            />
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        onClick={openCekTahapan}
                                        className="group inline-flex w-full items-center justify-between rounded-2xl bg-cyan-50 px-5 py-4 text-sm font-black text-slate-950 shadow-xl shadow-cyan-950/20 transition hover:-translate-y-0.5 hover:bg-white hover:shadow-2xl"
                                    >
                                        <span>Cek Tahapan Seleksi</span>
                                        <span className="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-950 text-cyan-100 transition group-hover:bg-teal-700">
                                            →
                                        </span>
                                    </button>
                                </div>

                                <StepMenu
                                    steps={steps}
                                    activeStep={step}
                                    setActiveStep={(selectedStep) => {
                                        if (selectedStep <= step) {
                                            setErrors({});
                                            setStep(selectedStep);
                                            return;
                                        }

                                        if (validateStep()) {
                                            setStep(selectedStep);
                                        }
                                    }}
                                />

                                <div className="mt-8 rounded-3xl border border-white/10 bg-white/10 p-5 shadow-lg backdrop-blur">
                                    <div className="flex items-start gap-3">
                                        <div className="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-300 text-lg font-black text-slate-950">
                                            !
                                        </div>

                                        <div>
                                            <h3 className="font-bold text-white">
                                                Panduan Pengisian
                                            </h3>
                                            <p className="mt-1 text-sm leading-6 text-slate-200">
                                                Kolom bertanda{" "}
                                                <span className="font-bold text-amber-300">*</span>{" "}
                                                wajib diisi. Pastikan data yang Anda
                                                masukkan sesuai dengan identitas resmi.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>

                        <section className="bg-white p-5 sm:p-8 lg:col-span-3 lg:p-10">
                            <div className="mb-8">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <span className="inline-flex w-fit rounded-full bg-cyan-50 px-4 py-1 text-xs font-bold uppercase tracking-wide text-teal-700 ring-1 ring-cyan-100">
                                        Pendaftaran Online
                                    </span>
                                </div>

                                <h2 className="mt-4 text-3xl font-black text-slate-950">
                                    {stepTitle[step]}
                                </h2>

                                <p className="mt-2 text-sm leading-6 text-slate-500">
                                    {stepDescription[step]}
                                </p>

                                {Object.keys(errors).length > 0 && (
                                    <div className="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
                                        Mohon lengkapi semua data wajib yang bertanda{" "}
                                        <span className="font-bold">*</span>.
                                    </div>
                                )}
                            </div>

                            <form onSubmit={handleSubmit} noValidate>
                                {renderStep()}

                                <div className="mt-8 flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-between">
                                    <button
                                        type="button"
                                        onClick={prevStep}
                                        disabled={step === 1}
                                        className={`rounded-2xl px-6 py-3 text-sm font-bold transition ${
                                            step === 1
                                                ? "cursor-not-allowed bg-slate-100 text-slate-400"
                                                : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                                        }`}
                                    >
                                        Kembali
                                    </button>

                                    {step < steps.length ? (
                                        <button
                                            type="button"
                                            onClick={nextStep}
                                            className="rounded-2xl bg-teal-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-teal-100 transition hover:bg-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-100"
                                        >
                                            Selanjutnya
                                        </button>
                                    ) : (
                                        <button
                                            type="submit"
                                            className="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-100 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                                        >
                                            Kirim Pendaftaran
                                        </button>
                                    )}
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    );
}

function CekTahapanPelamar({
    form,
    errors,
    hasil,
    handleChange,
    handleSubmit,
    onBack,
}) {
    return (
        <main className="min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-cyan-50 px-4 py-10">
            <div className="mx-auto max-w-5xl">
                <div className="mb-8 text-center">
                    <span className="inline-flex rounded-full bg-cyan-50 px-4 py-1 text-xs font-bold uppercase tracking-wide text-teal-700 ring-1 ring-cyan-100">
                        Cek Tahapan Seleksi
                    </span>

                    <h1 className="mt-4 text-3xl font-black text-slate-950">
                        Cek Status Pendaftaran Kandidat
                    </h1>

                    <p className="mt-3 text-sm leading-6 text-slate-500">
                        Masukkan token pelamar untuk melihat status dan tahapan
                        seleksi pendaftaran Anda.
                    </p>
                </div>

                <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl shadow-slate-200/70">
                    <div className="border-b border-white/10 bg-gradient-to-r from-slate-950 via-blue-950 to-teal-900 px-6 py-6 text-white sm:px-8">
                        <h2 className="text-xl font-black">
                            Form Pengecekan Tahapan Seleksi
                        </h2>
                        <p className="mt-2 text-sm text-slate-200">
                            Gunakan token pelamar untuk memantau perkembangan
                            proses seleksi Anda.
                        </p>
                    </div>

                    <form onSubmit={handleSubmit} className="p-6 sm:p-8">
                        <div>
                            <label className="mb-2 block text-sm font-semibold text-slate-700">
                                Token Pelamar <span className="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="token"
                                value={form.token}
                                onChange={handleChange}
                                placeholder="Masukkan token pelamar"
                                className={`w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 ${
                                    errors.token
                                        ? "border-red-300 bg-red-50 focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-100"
                                        : "border-slate-200 bg-slate-50 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100"
                                }`}
                            />

                            {errors.token && (
                                <p className="mt-2 text-xs font-semibold text-red-500">
                                    {errors.token}
                                </p>
                            )}
                        </div>

                        <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-between">
                            <button
                                type="button"
                                onClick={onBack}
                                className="rounded-2xl bg-slate-100 px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200"
                            >
                                Kembali ke Pendaftaran
                            </button>

                            <button
                                type="submit"
                                className="rounded-2xl bg-teal-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-teal-100 transition hover:bg-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-100"
                            >
                                Cek Tahapan
                            </button>
                        </div>
                    </form>

                    {hasil && (
                        <div className="border-t border-slate-100 bg-slate-50 p-6 sm:p-8">
                            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p className="text-xs font-bold uppercase tracking-wide text-teal-700">
                                            Hasil Pengecekan
                                        </p>

                                        <h3
                                            className={`mt-2 text-2xl font-black ${
                                                hasil.status.includes("Gagal")
                                                    ? "text-red-600"
                                                    : "text-emerald-600"
                                            }`}
                                        >
                                            {hasil.status}
                                        </h3>

                                        <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                                            {hasil.keterangan}
                                        </p>
                                    </div>

                                    <span
                                        className={`w-fit rounded-full px-4 py-2 text-xs font-bold ${
                                            hasil.status.includes("Gagal")
                                                ? "bg-red-100 text-red-700"
                                                : "bg-emerald-100 text-emerald-700"
                                        }`}
                                    >
                                        Tahap Terakhir: {hasil.tahapan_terakhir}
                                    </span>
                                </div>

                                <div className="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                                    <div className="rounded-2xl bg-slate-50 p-4">
                                        <p className="text-xs font-bold uppercase text-slate-400">
                                            Nama Pelamar
                                        </p>
                                        <p className="mt-1 font-semibold text-slate-800">
                                            {hasil.nama_pelamar}
                                        </p>
                                    </div>

                                    <div className="rounded-2xl bg-slate-50 p-4">
                                        <p className="text-xs font-bold uppercase text-slate-400">
                                            Posisi Dilamar
                                        </p>
                                        <p className="mt-1 font-semibold text-slate-800">
                                            {hasil.posisi_dilamar}
                                        </p>
                                    </div>

                                    <div className="rounded-2xl bg-slate-50 p-4">
                                        <p className="text-xs font-bold uppercase text-slate-400">
                                            Token Pelamar
                                        </p>
                                        <p className="mt-1 break-all font-semibold text-slate-800">
                                            {hasil.token}
                                        </p>
                                    </div>
                                </div>

                                {hasil.status.includes("Gagal") && (
                                    <div className="mt-6 rounded-3xl border border-red-200 bg-red-50 p-5">
                                        <div className="flex items-start gap-4">
                                            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-500 text-lg font-black text-white">
                                                !
                                            </div>

                                            <div>
                                                <h4 className="font-black text-red-800">
                                                    Belum Dapat Melanjutkan Proses Seleksi
                                                </h4>

                                                <p className="mt-2 text-sm leading-6 text-red-700">
                                                    {hasil.saran}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div className="mt-8">
                                    <h4 className="mb-5 text-lg font-black text-slate-950">
                                        Tahapan Seleksi Pelamar
                                    </h4>

                                    <div className="relative">
                                        <div className="absolute left-5 top-8 hidden h-[calc(100%-4rem)] w-1 rounded-full bg-slate-200 md:block" />

                                        <div className="space-y-5">
                                            {hasil.tahapan.map((item, index) => {
                                                const isLolos = item.status === "Lolos";
                                                const isGagal = item.status === "Gagal";

                                                return (
                                                    <div
                                                        key={index}
                                                        className={`relative flex gap-4 rounded-3xl border p-4 shadow-sm ${
                                                            isGagal
                                                                ? "border-red-200 bg-red-50"
                                                                : "border-slate-200 bg-white"
                                                        }`}
                                                    >
                                                        <div
                                                            className={`z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-black text-white shadow-lg ${
                                                                isLolos
                                                                    ? "bg-emerald-500 shadow-emerald-100"
                                                                    : isGagal
                                                                    ? "bg-red-500 shadow-red-100"
                                                                    : "bg-slate-400 shadow-slate-100"
                                                            }`}
                                                        >
                                                            {isLolos ? "✓" : isGagal ? "!" : index + 1}
                                                        </div>

                                                        <div className="min-w-0 flex-1">
                                                            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                                <h5
                                                                    className={`font-black ${
                                                                        isGagal
                                                                            ? "text-red-800"
                                                                            : "text-slate-950"
                                                                    }`}
                                                                >
                                                                    {item.nama}
                                                                </h5>

                                                                <span
                                                                    className={`w-fit rounded-full px-3 py-1 text-xs font-bold ${
                                                                        isLolos
                                                                            ? "bg-emerald-100 text-emerald-700"
                                                                            : isGagal
                                                                            ? "bg-red-100 text-red-700"
                                                                            : "bg-slate-100 text-slate-700"
                                                                    }`}
                                                                >
                                                                    {item.status}
                                                                </span>
                                                            </div>

                                                            <p
                                                                className={`mt-2 text-sm leading-6 ${
                                                                    isGagal
                                                                        ? "text-red-700"
                                                                        : "text-slate-500"
                                                                }`}
                                                            >
                                                                {item.keterangan}
                                                            </p>

                                                            {item.saran && (
                                                                <div className="mt-3 rounded-2xl border border-red-200 bg-white p-4">
                                                                    <p className="text-sm font-semibold leading-6 text-red-700">
                                                                        {item.saran}
                                                                    </p>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </main>
    );
}

const rootElement = document.getElementById("pendaftaran-root");

if (rootElement) {
    createRoot(rootElement).render(<PendaftaranPage />);
}