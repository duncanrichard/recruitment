import React, { useEffect, useMemo, useState } from "react";
import { createRoot } from "react-dom/client";
import StepDataDiri from "./components/StepDataDiri";
import StepRiwayatKeluarga from "./components/StepRiwayatKeluarga";
import StepRiwayatKesehatan from "./components/StepRiwayatKesehatan";
import StepRiwayatPekerjaan from "./components/StepRiwayatPekerjaan";
import StepKesiapanBekerja from "./components/StepKesiapanBekerja";
import CekTahapanPelamar from "./components/CekTahapanPelamar";

function PendaftaranPage() {
    const emptySosialMedia = {
        platform: "",
        nama_akun: "",
    };

    const emptyKontakDarurat = {
        nama: "",
        status: "",
        nomor: "",
    };

    const emptySaudara = {
        nama: "",
        jenis_kelamin: "",
        hubungan: "",
        pekerjaan: "",
        no_hp: "",
        alamat: "",
    };

    const [activePage, setActivePage] = useState("pendaftaran");
    const [step, setStep] = useState(1);
    const [errors, setErrors] = useState({});
    const [loadingToken, setLoadingToken] = useState(false);
    const [loadingSubmit, setLoadingSubmit] = useState(false);
    const [pelamarAktif, setPelamarAktif] = useState(null);

    const [cekTahapanForm, setCekTahapanForm] = useState({
        token: "",
    });

    const [cekTahapanErrors, setCekTahapanErrors] = useState({});
    const [hasilCekTahapan, setHasilCekTahapan] = useState(null);

    const [form, setForm] = useState({
        token: "",

        nama: "",
        nama_panggilan: "",
        email: "",
        no_hp: "",
        nik: "",

        posisi_dilamar: "",
        perusahaan_dilamar: "",
        sumber_informasi: "",

        pendidikan: "",
        jurusan: "",
        nama_institusi: "",
        str_aktif: "",

        sosial_media: [{ ...emptySosialMedia }],

        tempat_lahir: "",
        tanggal_lahir: "",
        jenis_kelamin: "",
        agama: "",
        status_perkawinan: "",
        kewarganegaraan: "",

        alamat: "",
        alamat_ktp: "",
        alamat_domisili: "",
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
            description: "Identitas utama dan informasi lamaran.",
        },
        {
            id: 2,
            title: "Riwayat Keluarga",
            description: "Data keluarga dan kontak darurat.",
        },
        {
            id: 3,
            title: "Riwayat Kesehatan",
            description: "Informasi kesehatan pelamar.",
        },
        {
            id: 4,
            title: "Riwayat Pekerjaan",
            description: "Pengalaman kerja dan keahlian.",
        },
        {
            id: 5,
            title: "Kesiapan Bekerja",
            description: "Kesiapan penempatan dan mulai kerja.",
        },
    ];

    const requiredFieldsByStep = {
        1: [
            "nama",
            "nama_panggilan",
            "email",
            "no_hp",
            "posisi_dilamar",
            "perusahaan_dilamar",
            "pendidikan",
            "tanggal_lahir",
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
            "alat_bantu_pendengaran",
            "tangan_dominan",
            "memiliki_riwayat_penyakit",
            "punya_penyakit_genetik",
            "memiliki_alergi",
            "pengobatan_psikolog",
            "pernah_kecelakaan",
            "pernah_operasi",
        ],
        4: ["status_pekerjaan", "keahlian"],
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
        sumber_informasi: "Sumber Informasi",
        pendidikan: "Pendidikan Terakhir",
        jurusan: "Jurusan",
        nama_institusi: "Nama Institusi",
        str_aktif: "STR Aktif",
        tempat_lahir: "Tempat Lahir",
        tanggal_lahir: "Tanggal Lahir",
        jenis_kelamin: "Jenis Kelamin",
        agama: "Agama",
        status_perkawinan: "Status Perkawinan",
        kewarganegaraan: "Kewarganegaraan",
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
        alat_bantu_pendengaran: "Alat Bantu Pendengaran",
        tangan_dominan: "Tangan Dominan",
        memiliki_riwayat_penyakit: "Memiliki Riwayat Penyakit",
        punya_penyakit_genetik: "Punya Penyakit Genetik",
        memiliki_alergi: "Memiliki Alergi",
        pengobatan_psikolog: "Pengobatan Psikolog",
        pernah_kecelakaan: "Pernah Kecelakaan",
        pernah_operasi: "Pernah Operasi",
        status_pekerjaan: "Status Pekerjaan",
        posisi_pekerjaan: "Posisi / Jabatan",
        keahlian: "Keahlian",
        bersedia_ditempatkan: "Bersedia Ditempatkan",
        bersedia_shift: "Bersedia Shift",
        tanggal_siap_kerja: "Tanggal Siap Kerja",
    };

    const progressPercent = useMemo(() => {
        return Math.round((step / steps.length) * 100);
    }, [step, steps.length]);

    useEffect(() => {
        const initialToken = getInitialTokenFromPage();
        const initialPelamar = getInitialPelamarFromPage();

        if (initialToken) {
            setCekTahapanForm({
                token: initialToken,
            });

            setForm((prevForm) => ({
                ...prevForm,
                token: initialToken,
            }));
        }

        if (initialPelamar) {
            applyPelamarToPage(initialPelamar);
            return;
        }

        if (initialToken) {
            loadPelamarByToken(initialToken, {
                silent: true,
                showResult: false,
            });
        }
    }, []);

    const isEmpty = (value) => {
        if (Array.isArray(value)) {
            return value.length === 0;
        }

        return value === undefined || value === null || String(value).trim() === "";
    };

    function getInitialTokenFromPage() {
        const root = document.getElementById("pendaftaran-root");

        if (root?.dataset?.token) {
            return root.dataset.token;
        }

        const pathParts = window.location.pathname.split("/").filter(Boolean);
        const pendaftaranIndex = pathParts.indexOf("pendaftaran");

        if (pendaftaranIndex !== -1 && pathParts[pendaftaranIndex + 1]) {
            return decodeURIComponent(pathParts[pendaftaranIndex + 1]);
        }

        return "";
    }

    function getInitialPelamarFromPage() {
        const root = document.getElementById("pendaftaran-root");
        const rawPelamar = root?.dataset?.pelamar;

        if (!rawPelamar || rawPelamar === "null") {
            return null;
        }

        try {
            return JSON.parse(rawPelamar);
        } catch (error) {
            console.error("Gagal membaca data pelamar dari Blade:", error);
            return null;
        }
    }

    const getRelationName = (pelamar, relationName, fieldName, fallbackField = null) => {
        return (
            pelamar?.[relationName]?.[fieldName] ||
            pelamar?.[fallbackField || relationName]?.[fieldName] ||
            ""
        );
    };

    const mapPelamarToForm = (pelamar) => {
        return {
            token: pelamar?.token || "",

            nama: pelamar?.nama_lengkap || "",
            nama_panggilan: pelamar?.nama_panggil || "",
            email: pelamar?.email || "",
            no_hp: pelamar?.no_wa || "",
            nik: pelamar?.nik || "",

            posisi_dilamar:
                getRelationName(pelamar, "posisi", "nama_posisi") ||
                pelamar?.posisi_yang_dilamar ||
                "",

            perusahaan_dilamar:
                getRelationName(pelamar, "perusahaan", "nama_perusahaan") ||
                pelamar?.perusahaan_dilamar ||
                "",

            sumber_informasi:
                getRelationName(pelamar, "sumberInformasi", "informasi", "sumber_informasi") ||
                pelamar?.sumber_informasi_id ||
                "",

            pendidikan:
                getRelationName(pelamar, "pendidikan", "pendidikan") ||
                pelamar?.pendidikan_id ||
                "",

            jurusan: pelamar?.jurusan || "",
            nama_institusi: pelamar?.nama_institusi || "",
            str_aktif: pelamar?.str_aktif || "",

            tempat_lahir: pelamar?.tempat_lahir || "",
            tanggal_lahir: pelamar?.tanggal_lahir || "",
            jenis_kelamin: pelamar?.jenis_kelamin || "",

            agama:
                getRelationName(pelamar, "agama", "agama") ||
                pelamar?.agama_id ||
                "",

            status_perkawinan:
                getRelationName(pelamar, "statusPernikahan", "status_pernikahan", "status_pernikahan") ||
                pelamar?.status_pernikahan_id ||
                "",

            kewarganegaraan:
                getRelationName(pelamar, "kewarganegaraan", "kewarganegaraan") ||
                pelamar?.kewarganegaraan_id ||
                "",

            alamat:
                pelamar?.alamat_domisili ||
                pelamar?.alamat_ktp ||
                pelamar?.alamat ||
                "",

            alamat_ktp: pelamar?.alamat_ktp || "",
            alamat_domisili: pelamar?.alamat_domisili || "",
            provinsi: pelamar?.provinsi || "",
            kabupaten: pelamar?.kabupaten || "",
            kecamatan: pelamar?.kecamatan || "",
            kelurahan: pelamar?.kelurahan || "",
            rt: pelamar?.rt || "",
            rw: pelamar?.rw || "",

            golongan_darah: pelamar?.gol_darah || "",
            tinggi_badan: pelamar?.tinggi_badan || "",
            berat_badan: pelamar?.berat_badan || "",
        };
    };

    const applyPelamarToPage = (pelamar) => {
        const mappedData = mapPelamarToForm(pelamar);

        setPelamarAktif(pelamar);

        setForm((prevForm) => ({
            ...prevForm,
            ...mappedData,
        }));

        setCekTahapanForm({
            token: pelamar?.token || "",
        });

        setHasilCekTahapan(makeHasilCekTahapan(pelamar));
    };

    const loadPelamarByToken = async (
        token,
        options = {
            silent: false,
            showResult: true,
        }
    ) => {
        const cleanToken = String(token || "").trim();

        if (!cleanToken) {
            throw new Error("Token pelamar wajib diisi.");
        }

        setLoadingToken(true);

        try {
            const response = await fetch(
                `/pendaftaran/api/token/${encodeURIComponent(cleanToken)}`,
                {
                    headers: {
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || "Token pelamar tidak ditemukan.");
            }

            applyPelamarToPage(result.data);

            if (options.showResult) {
                setActivePage("cek-tahapan");
            }

            setCekTahapanErrors({});

            return result.data;
        } catch (error) {
            if (!options.silent) {
                setCekTahapanErrors({
                    token: error.message || "Token pelamar tidak ditemukan.",
                });
            }

            throw error;
        } finally {
            setLoadingToken(false);
        }
    };

    const makeHasilCekTahapan = (pelamar) => {
        const status = pelamar?.status_seleksi || "Administrasi";
        const tahapanTerakhir = pelamar?.tahapan_terakhir || status;

        return {
            token: pelamar?.token || "-",
            nama_pelamar: pelamar?.nama_lengkap || "-",
            posisi_dilamar:
                getRelationName(pelamar, "posisi", "nama_posisi") ||
                pelamar?.posisi_yang_dilamar ||
                "-",
            perusahaan_dilamar:
                getRelationName(pelamar, "perusahaan", "nama_perusahaan") ||
                pelamar?.perusahaan_dilamar ||
                "-",
            status,
            tahapan_terakhir: tahapanTerakhir,
            keterangan:
                pelamar?.keterangan_seleksi ||
                `Status seleksi kandidat saat ini berada pada tahap ${tahapanTerakhir}.`,
            saran:
                pelamar?.saran_seleksi ||
                "Silakan pantau halaman ini secara berkala untuk melihat perkembangan proses seleksi.",
            tahapan:
                Array.isArray(pelamar?.tahapan_seleksi) &&
                pelamar.tahapan_seleksi.length > 0
                    ? pelamar.tahapan_seleksi
                    : makeDefaultTahapanSeleksi(status),
        };
    };

    const makeDefaultTahapanSeleksi = (statusSeleksi) => {
        const tahapan = [
            "Administrasi",
            "Test Psikolog",
            "Test MMPI",
            "Interview",
            "Offering",
            "Diterima",
        ];

        let currentIndex = tahapan.findIndex(
            (item) => item.toLowerCase() === String(statusSeleksi || "").toLowerCase()
        );

        if (currentIndex === -1) {
            currentIndex = 0;
        }

        return tahapan.map((nama, index) => {
            let status = "Menunggu";

            if (index < currentIndex) {
                status = "Lolos";
            }

            if (index === currentIndex) {
                status = "Proses";
            }

            return {
                nama,
                status,
                keterangan:
                    status === "Lolos"
                        ? `Tahap ${nama} sudah selesai dan kandidat dinyatakan lolos.`
                        : status === "Proses"
                        ? `Kandidat sedang berada pada tahap ${nama}.`
                        : `Tahap ${nama} belum dimulai.`,
            };
        });
    };

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;

        setForm((prevForm) => {
            let nextValue = value;

            if (type === "checkbox") {
                const currentValues = Array.isArray(prevForm[name])
                    ? prevForm[name]
                    : [];

                nextValue = checked
                    ? [...currentValues, value]
                    : currentValues.filter((item) => item !== value);
            }

            const updatedForm = {
                ...prevForm,
                [name]: nextValue,
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

    const handleArrayChange = (field, index, name, value) => {
        setForm((prevForm) => {
            const list = Array.isArray(prevForm[field]) ? [...prevForm[field]] : [];

            list[index] = {
                ...list[index],
                [name]: value,
            };

            return {
                ...prevForm,
                [field]: list,
            };
        });

        setErrors((prevErrors) => {
            const updatedErrors = { ...prevErrors };

            if (!isEmpty(value)) {
                delete updatedErrors[`${field}_${index}_${name}`];

                if (field === "kontak_darurat" && index === 0) {
                    delete updatedErrors[`kontak_darurat_${name}`];
                }
            }

            return updatedErrors;
        });
    };

    const addArrayItem = (field, template) => {
        setForm((prevForm) => ({
            ...prevForm,
            [field]: [...prevForm[field], { ...template }],
        }));
    };

    const removeArrayItem = (field, index, template) => {
        setForm((prevForm) => {
            const nextItems = prevForm[field].filter((_, itemIndex) => itemIndex !== index);

            return {
                ...prevForm,
                [field]: nextItems.length > 0 ? nextItems : [{ ...template }],
            };
        });
    };


    const handleSosialMediaChange = (index, name, value) => {
        handleArrayChange("sosial_media", index, name, value);
    };

    const addSosialMedia = () => {
        addArrayItem("sosial_media", emptySosialMedia);
    };

    const removeSosialMedia = (index) => {
        removeArrayItem("sosial_media", index, emptySosialMedia);
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

    const handleCekTahapanSubmit = async (e) => {
        e.preventDefault();

        if (isEmpty(cekTahapanForm.token)) {
            setCekTahapanErrors({
                token: "Token pelamar wajib diisi.",
            });
            return;
        }

        try {
            await loadPelamarByToken(cekTahapanForm.token, {
                silent: false,
                showResult: true,
            });
        } catch (error) {
            setHasilCekTahapan(null);
        }
    };

    const openCekTahapan = async () => {
        setActivePage("cek-tahapan");
        setErrors({});
        setCekTahapanErrors({});

        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });

        const token = cekTahapanForm.token || form.token || getInitialTokenFromPage();

        if (!token) {
            setHasilCekTahapan(null);
            setCekTahapanErrors({
                token: "Token pelamar tidak tersedia. Silakan buka halaman melalui link pendaftaran yang diberikan HR.",
            });
            return;
        }

        setCekTahapanForm({
            token,
        });

        try {
            await loadPelamarByToken(token, {
                silent: false,
                showResult: true,
            });
        } catch (error) {
            setHasilCekTahapan(null);
            setCekTahapanErrors({
                token: error.message || "Token pelamar tidak ditemukan.",
            });
        }
    };

    const backToPendaftaran = () => {
        setActivePage("pendaftaran");

        window.scrollTo({
            top: 0,
            behavior: "smooth",
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
                    newErrors.kontak_darurat_nama = "Nama kontak darurat wajib diisi.";
                }

                if (!firstKontak || isEmpty(firstKontak.status)) {
                    newErrors.kontak_darurat_status =
                        "Status hubungan kontak darurat wajib diisi.";
                }

                if (!firstKontak || isEmpty(firstKontak.nomor)) {
                    newErrors.kontak_darurat_nomor =
                        "Nomor kontak darurat wajib diisi.";
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
                newErrors[fieldName] = `${fieldLabels[fieldName] || fieldName} wajib diisi.`;
            }
        });

        if (step === 3) {
            if (form.punya_penyakit_genetik === "Ya" && isEmpty(form.nama_penyakit)) {
                newErrors.nama_penyakit = "Nama penyakit wajib diisi jika memilih Ya.";
            }

            if (form.memiliki_riwayat_penyakit === "Ya" && isEmpty(form.riwayat_penyakit)) {
                newErrors.riwayat_penyakit =
                    "Riwayat penyakit wajib diisi jika memilih Ya.";
            }

            if (form.memiliki_alergi === "Ya" && isEmpty(form.alergi)) {
                newErrors.alergi = "Alergi wajib diisi jika memilih Ya.";
            }

            if (form.pengobatan_psikolog === "Ya" && isEmpty(form.kapan_dilakukan)) {
                newErrors.kapan_dilakukan =
                    "Kapan dilakukan wajib diisi jika memilih Ya.";
            }

            if (form.pernah_kecelakaan === "Ya" && isEmpty(form.bagian_tubuh_kecelakaan)) {
                newErrors.bagian_tubuh_kecelakaan =
                    "Bagian tubuh yang kecelakaan wajib diisi jika memilih Ya.";
            }

            if (form.pernah_operasi === "Ya" && isEmpty(form.diagnosa_dokter)) {
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

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateStep()) {
            return;
        }

        setLoadingSubmit(true);

        try {
            console.log("Data pendaftaran:", form);
            alert("Pendaftaran berhasil dikirim.");
        } catch (error) {
            console.error("Gagal mengirim pendaftaran:", error);
            alert("Terjadi kesalahan saat mengirim pendaftaran.");
        } finally {
            setLoadingSubmit(false);
        }
    };

    const renderStep = () => {
        if (step === 1) {
            return (
                <StepDataDiri
                    form={form}
                    handleChange={handleChange}
                    errors={errors}
                    requiredFields={requiredFieldsByStep[1]}
                    handleSosialMediaChange={handleSosialMediaChange}
                    addSosialMedia={addSosialMedia}
                    removeSosialMedia={removeSosialMedia}
                    pelamarAktif={pelamarAktif}
                />
            );
        }

        if (step === 2) {
            return (
                <StepRiwayatKeluarga
                    form={form}
                    errors={errors}
                    requiredFields={requiredFieldsByStep[2]}
                    handleChange={handleChange}
                    handleKontakDaruratChange={(index, name, value) =>
                        handleArrayChange("kontak_darurat", index, name, value)
                    }
                    addKontakDarurat={() =>
                        addArrayItem("kontak_darurat", emptyKontakDarurat)
                    }
                    removeKontakDarurat={(index) =>
                        removeArrayItem("kontak_darurat", index, emptyKontakDarurat)
                    }
                    handleSaudaraKandungChange={(index, name, value) =>
                        handleArrayChange("saudara_kandung", index, name, value)
                    }
                    addSaudaraKandung={() =>
                        addArrayItem("saudara_kandung", emptySaudara)
                    }
                    removeSaudaraKandung={(index) =>
                        removeArrayItem("saudara_kandung", index, emptySaudara)
                    }
                    handleSaudaraIparChange={(index, name, value) =>
                        handleArrayChange("saudara_ipar", index, name, value)
                    }
                    addSaudaraIpar={() =>
                        addArrayItem("saudara_ipar", emptySaudara)
                    }
                    removeSaudaraIpar={(index) =>
                        removeArrayItem("saudara_ipar", index, emptySaudara)
                    }
                />
            );
        }

        if (step === 3) {
            return (
                <StepRiwayatKesehatan
                    form={form}
                    errors={errors}
                    requiredFields={requiredFieldsByStep[3]}
                    handleChange={handleChange}
                />
            );
        }

        if (step === 4) {
            return (
                <StepRiwayatPekerjaan
                    form={form}
                    errors={errors}
                    requiredFields={requiredFieldsByStep[4]}
                    handleChange={handleChange}
                />
            );
        }

        if (step === 5) {
            return (
                <StepKesiapanBekerja
                    form={form}
                    errors={errors}
                    requiredFields={requiredFieldsByStep[5]}
                    handleChange={handleChange}
                />
            );
        }

        return null;
    };

    if (activePage === "cek-tahapan") {
        return (
            <CekTahapanPelamar
                errors={cekTahapanErrors}
                hasil={hasilCekTahapan}
                loading={loadingToken}
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

                            <div className="relative">
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
                                        Lengkapi data pribadi, keluarga, kesehatan,
                                        pengalaman kerja, dan kesiapan bekerja secara
                                        bertahap.
                                    </p>
                                </div>

                                {pelamarAktif && (
                                    <div className="mb-5 rounded-3xl border border-emerald-300/20 bg-emerald-300/10 p-4 shadow-lg backdrop-blur">
                                        <p className="text-xs font-bold uppercase tracking-wide text-emerald-100">
                                            Data Token Ditemukan
                                        </p>
                                        <p className="mt-2 text-lg font-black text-white">
                                            {pelamarAktif.nama_lengkap || "-"}
                                        </p>
                                        <p className="mt-1 break-all text-xs font-semibold text-emerald-100">
                                            Token: {pelamarAktif.token || "-"}
                                        </p>
                                    </div>
                                )}

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
                                            {progressPercent}%
                                        </div>
                                    </div>

                                    <div className="h-2 overflow-hidden rounded-full bg-white/10">
                                        <div
                                            className="h-full rounded-full bg-cyan-300 transition-all duration-500"
                                            style={{
                                                width: `${progressPercent}%`,
                                            }}
                                        />
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    onClick={openCekTahapan}
                                    className="group mb-7 inline-flex w-full items-center justify-between rounded-2xl bg-cyan-50 px-5 py-4 text-sm font-black text-slate-950 shadow-xl shadow-cyan-950/20 transition hover:-translate-y-0.5 hover:bg-white hover:shadow-2xl"
                                >
                                    <span>Cek Tahapan Seleksi</span>
                                    <span className="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-950 text-cyan-100 transition group-hover:bg-teal-700">
                                        →
                                    </span>
                                </button>

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
                                                <span className="font-bold text-amber-300">
                                                    *
                                                </span>{" "}
                                                wajib diisi. Jika membuka link token,
                                                data awal akan terisi otomatis.
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

                                    {form.token && (
                                        <span className="inline-flex w-fit rounded-full bg-slate-100 px-4 py-1 text-xs font-bold text-slate-600">
                                            Token: {form.token}
                                        </span>
                                    )}
                                </div>

                                <h2 className="mt-4 text-3xl font-black text-slate-950">
                                    {steps.find((item) => item.id === step)?.title}
                                </h2>

                                <p className="mt-2 text-sm leading-6 text-slate-500">
                                    {steps.find((item) => item.id === step)?.description}
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
                                            disabled={loadingSubmit}
                                            className="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-100 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:opacity-60"
                                        >
                                            {loadingSubmit
                                                ? "Mengirim..."
                                                : "Kirim Pendaftaran"}
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

function StepMenu({ steps, activeStep, setActiveStep }) {
    return (
        <div className="space-y-3">
            {steps.map((item) => {
                const active = item.id === activeStep;
                const complete = item.id < activeStep;

                return (
                    <button
                        key={item.id}
                        type="button"
                        onClick={() => setActiveStep(item.id)}
                        className={`flex w-full items-start gap-4 rounded-3xl border p-4 text-left transition ${
                            active
                                ? "border-cyan-300/40 bg-cyan-300/15"
                                : complete
                                ? "border-emerald-300/20 bg-emerald-300/10 hover:bg-emerald-300/15"
                                : "border-white/10 bg-white/5 hover:bg-white/10"
                        }`}
                    >
                        <div
                            className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black ${
                                active
                                    ? "bg-cyan-50 text-slate-950"
                                    : complete
                                    ? "bg-emerald-300 text-slate-950"
                                    : "bg-white/10 text-white"
                            }`}
                        >
                            {complete ? "✓" : item.id}
                        </div>

                        <div>
                            <h4 className="font-black text-white">{item.title}</h4>
                            <p className="mt-1 text-xs leading-5 text-slate-200">
                                {item.description}
                            </p>
                        </div>
                    </button>
                );
            })}
        </div>
    );
}

const rootElement = document.getElementById("pendaftaran-root");

if (rootElement) {
    createRoot(rootElement).render(<PendaftaranPage />);
}
