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
        id: "",
        platform: "",
        nama_akun: "",
        nama_account: "",
    };

    const emptyKontakDarurat = {
        nama: "",
        status: "",
        nomor: "",
    };

    const emptySaudara = {
        id: "",
        nama: "",
        jenis_kelamin: "",
        hubungan: "",
        pekerjaan: "",
        no_hp: "",
        alamat: "",
    };

    const [activePage, setActivePage] = useState("pendaftaran");
    const [step, setStep] = useState(1);
    const [progressStep, setProgressStep] = useState(1);
    const [errors, setErrors] = useState({});
    const [loadingToken, setLoadingToken] = useState(false);
    const [loadingSubmit, setLoadingSubmit] = useState(false);
    const [pelamarAktif, setPelamarAktif] = useState(null);
    const [notification, setNotification] = useState(null);

    const [cekTahapanForm, setCekTahapanForm] = useState({
        token: "",
    });

    const [cekTahapanErrors, setCekTahapanErrors] = useState({});
    const [hasilCekTahapan, setHasilCekTahapan] = useState(null);

    const [masterOptions, setMasterOptions] = useState({
        pendidikan: [],
        agama: [],
        status_pernikahan: [],
        posisi: [],
        kewarganegaraan: [],
        jenis_kelamin: [],
        str_aktif: [],
        sosial_media: [],
        opsi_kacamata: [],
    });

    const [form, setForm] = useState({
        token: "",

        nama: "",
        nama_panggilan: "",
        email: "",
        no_hp: "",

        posisi_dilamar: "",
        posisi_dilamar_label: "",
        posisi_str_aktif: "",

        perusahaan_dilamar: "",
        perusahaan_dilamar_label: "",

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
        status_pernikahan_id: "",
        status_perkawinan: "",
        kewarganegaraan: "",

        alamat: "",
        alamat_ktp: "",
        alamat_domisili: "",

        provinsi_id: "",
        kabupaten_id: "",
        kecamatan_id: "",
        kelurahan_id: "",

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

        hubungan_kerabat_instansi: [],

        golongan_darah: "",
        gol_darah: "",
        tinggi_badan: "",
        berat_badan: "",

        buta_warna: "",
        opsi_kacamata_id: "",
        opsi_kacamata_label: "",

        alat_bantu_dengar: "",
        alat_bantu_pendengaran: "",

        menulis_dengan_tangan: "",
        tangan_dominan: "",

        sering_gemetar: "",
        tangan_gemetar: "",

        tangan_sering_berkeringat: "",
        tangan_berkeringat: "",

        penyakit_menular: "",
        riwayat_penyakit_menular: "",

        program_kehamilan: "",

        punya_alergi: "",
        memiliki_alergi: "",
        nama_alergi: "",
        alergi: "",

        punya_penyakit_genetik: "",
        nama_penyakit: "",
        riwayat_kronis: "",

        pengobatan_psikolog: "",
        kapan_dilakukan: "",

        pernah_kecelakaan: "",
        bagian_tubuh_kecelakaan: "",

        pernah_operasi: "",
        diagnosa_dokter: "",

        memiliki_riwayat_penyakit: "",
        riwayat_penyakit: "",
        obat_dikonsumsi: "",
        pernah_dirawat: "",
        tahun_dirawat: "",
        catatan_kesehatan: "",

        status_pekerjaan: "",
        riwayat_pekerjaan: [],
        nama_perusahaan: "",
        posisi_pekerjaan: "",
        posisi_pekerjaan_terakhir: "",
        bidang_pekerjaan: "",
        lokasi_perusahaan: "",
        tahun_mulai_bekerja: "",
        tahun_selesai_bekerja: "",
        periode_kerja_awal: "",
        periode_kerja_akhir: "",
        lama_bekerja: "",
        deskripsi_pekerjaan: "",
        alasan_berhenti: "",
        gaji_terakhir: "",
        keahlian: "",
        catatan_pekerjaan: "",

        referensi_kerja: "",
        nama_refrensi: "",
        telp_refrensi: "",
        refrensi_rekan_kerja: "",
        nama_refrensi_rekan: "",
        telp_refrensi_rekan: "",
        refrensi_kerabat: "",
        nama_refrensi_kerabat: "",
        telp_refrensi_kerabat: "",

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

        // Field utama tabel data_kesiapan_bekerja sesuai Google Form
        kapan_siap_bekerja: "",
        ekpetasi_gaji: "",
        penempatan: [],
        proses_bkhang: [],
        dapat_dipertanggung_jawabkan: [],
        bersedia_training: "",
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

    const progressPercent = useMemo(() => {
        return Math.round((progressStep / steps.length) * 100);
    }, [progressStep, steps.length]);

    useEffect(() => {
        loadMasterOptions();

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

    const markProgressStep = (completedStep) => {
        setProgressStep((currentProgressStep) =>
            Math.max(currentProgressStep, Number(completedStep) || 1)
        );
    };

    const hasMeaningfulValue = (value) => {
        if (Array.isArray(value)) {
            return value.some((item) => hasMeaningfulValue(item));
        }

        if (value && typeof value === "object") {
            const ignoredKeys = new Set([
                "id",
                "token",
                "data_riwayat_diri_id",
                "data_riwayat_keluarga_id",
                "created_at",
                "updated_at",
                "deleted_at",
            ]);

            return Object.entries(value).some(([key, item]) => {
                if (ignoredKeys.has(key)) {
                    return false;
                }

                return hasMeaningfulValue(item);
            });
        }

        return value !== undefined && value !== null && String(value).trim() !== "";
    };

    const pickMeaningful = (source, fields = []) => {
        if (!source) {
            return false;
        }

        return fields.some((field) => hasMeaningfulValue(source?.[field]));
    };

    const hasMeaningfulRows = (rows, fields = []) => {
        if (!Array.isArray(rows)) {
            return false;
        }

        return rows.some((row) => pickMeaningful(row, fields));
    };

    const keluargaFields = [
        "nama_ayah_kandung",
        "pekerjaan_ayah_kandung",
        "nama_ibu_kandung",
        "pekerjaan_ibu_kandung",
        "nama_ayah",
        "nik_ayah",
        "tempat_lahir_ayah",
        "tanggal_lahir_ayah",
        "pekerjaan_ayah",
        "no_hp_ayah",
        "alamat_ayah",
        "nama_ibu",
        "nik_ibu",
        "tempat_lahir_ibu",
        "tanggal_lahir_ibu",
        "pekerjaan_ibu",
        "no_hp_ibu",
        "alamat_ibu",
        "nama_suami_istri",
        "pekerjaan_suami_istri",
        "pekerjaan_sumi_istri",
        "tlpn_suami_istri",
        "nama_bapak_mertua",
        "pekerjaan_bapak_mertua",
        "nama_ibu_mertua",
        "pekerjaan_ibu_mertua",
        "hubungan_kerabat_instansi",
        "kerabat_bekerja_diinstansi",
        "kontak_darurat",
        "tlpn_darurat",
    ];

    const saudaraFields = [
        "nama",
        "nama_saudara_kandung",
        "nama_saudara_ipar",
        "jenis_kelamin",
        "hubungan",
        "pekerjaan",
        "no_hp",
        "alamat",
    ];

    const kontakDaruratFields = ["nama", "status", "nomor", "no_hp", "telepon"];

    const kesehatanFields = [
        "gol_darah",
        "golongan_darah",
        "tinggi_badan",
        "berat_badan",
        "buta_warna",
        "opsi_kacamata_id",
        "alat_bantu_dengar",
        "alat_bantu_pendengaran",
        "menulis_dengan_tangan",
        "tangan_dominan",
        "sering_gemetar",
        "tangan_gemetar",
        "tangan_sering_berkeringat",
        "tangan_berkeringat",
        "penyakit_menular",
        "riwayat_penyakit_menular",
        "program_kehamilan",
        "punya_alergi",
        "memiliki_alergi",
        "nama_alergi",
        "alergi",
        "punya_penyakit_genetik",
        "nama_penyakit",
        "riwayat_kronis",
        "pengobatan_psikolog",
        "kapan_dilakukan",
        "pernah_kecelakaan",
        "bagian_tubuh_kecelakaan",
        "pernah_operasi",
        "diagnosa_dokter",
    ];

    const pekerjaanFields = [
        "status_pekerjaan",
        "nama_perusahaan",
        "posisi_pekerjaan",
        "posisi_pekerjaan_terakhir",
        "bidang_pekerjaan",
        "lokasi_perusahaan",
        "tahun_mulai_bekerja",
        "tahun_selesai_bekerja",
        "periode_kerja_awal",
        "periode_kerja_akhir",
        "lama_bekerja",
        "deskripsi_pekerjaan",
        "alasan_berhenti",
        "gaji_terakhir",
        "keahlian",
        "catatan_pekerjaan",
        "referensi_kerja",
        "nama_refrensi",
        "telp_refrensi",
        "refrensi_rekan_kerja",
        "nama_refrensi_rekan",
        "telp_refrensi_rekan",
        "refrensi_kerabat",
        "nama_refrensi_kerabat",
        "telp_refrensi_kerabat",
    ];

    const kesiapanFields = [
        "kapan_siap_bekerja",
        "tanggal_siap_kerja",
        "ekpetasi_gaji",
        "gaji_diharapkan",
        "penempatan",
        "penempatan_luar_jawa_tengah",
        "bersedia_ditempatkan",
        "proses_bkhang",
        "background_checking",
        "bersedia_shift",
        "dapat_dipertanggung_jawabkan",
        "pernyataan_data_benar",
        "bersedia_lembur",
        "bersedia_training",
        "bersedia_pelatihan",
        "bersedia_hari_libur",
        "lokasi_kerja_diinginkan",
        "memiliki_kendaraan",
        "memiliki_sim",
        "status_ikatan_kerja",
        "alasan_melamar",
        "catatan_kesiapan",
    ];

    const isKeluargaFilled = (source) => {
        const keluarga =
            source?.riwayatKeluarga ||
            source?.riwayat_keluarga ||
            source?.riwayatKeluargaData ||
            source;

        return (
            pickMeaningful(source, keluargaFields) ||
            pickMeaningful(keluarga, keluargaFields) ||
            hasMeaningfulRows(source?.kontak_darurat, kontakDaruratFields) ||
            hasMeaningfulRows(keluarga?.kontak_darurat, kontakDaruratFields) ||
            hasMeaningfulRows(source?.saudara_kandung || source?.saudaraKandung, saudaraFields) ||
            hasMeaningfulRows(source?.saudara_ipar || source?.saudaraIpar, saudaraFields)
        );
    };

    const isKesehatanFilled = (source) => {
        const kesehatan =
            source?.riwayatKesehatan ||
            source?.riwayat_kesehatan ||
            source;

        return pickMeaningful(source, kesehatanFields) || pickMeaningful(kesehatan, kesehatanFields);
    };

    const isPekerjaanFilled = (source) => {
        const pekerjaan =
            source?.riwayatPekerjaan ||
            source?.riwayat_pekerjaan ||
            source;

        return pickMeaningful(source, pekerjaanFields) || pickMeaningful(pekerjaan, pekerjaanFields);
    };

    const isKesiapanFilled = (source) => {
        const kesiapan =
            source?.kesiapanBekerja ||
            source?.kesiapan_bekerja ||
            source?.dataKesiapanBekerja ||
            source;

        return pickMeaningful(source, kesiapanFields) || pickMeaningful(kesiapan, kesiapanFields);
    };

    const getCompletedStepFromPelamar = (pelamar) => {
        if (!pelamar) {
            return 1;
        }

        if (isKesiapanFilled(pelamar)) {
            return 5;
        }

        if (isPekerjaanFilled(pelamar)) {
            return 4;
        }

        if (isKesehatanFilled(pelamar)) {
            return 3;
        }

        if (isKeluargaFilled(pelamar)) {
            return 2;
        }

        return 1;
    };

    const getCompletedStepFromForm = () => {
        if (isKesiapanFilled(form)) {
            return 5;
        }

        if (isPekerjaanFilled(form)) {
            return 4;
        }

        if (isKesehatanFilled(form)) {
            return 3;
        }

        if (isKeluargaFilled(form)) {
            return 2;
        }

        return 1;
    };

    const syncProgressFromPelamarOrForm = (pelamar = null) => {
        const completedStep = pelamar
            ? getCompletedStepFromPelamar(pelamar)
            : getCompletedStepFromForm();

        markProgressStep(completedStep);
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

    const getCsrfToken = () => {
        const token = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        if (!token) {
            throw new Error(
                "CSRF token tidak ditemukan. Pastikan meta csrf-token sudah ada di Blade."
            );
        }

        return token;
    };

    const normalizeLaravelErrors = (laravelErrors = {}) => {
        const nextErrors = {};

        Object.entries(laravelErrors).forEach(([key, messages]) => {
            nextErrors[key] = Array.isArray(messages) ? messages[0] : messages;
        });

        return nextErrors;
    };

    const makeRequestError = (message, laravelErrors = {}) => {
        const error = new Error(message);
        error.validationErrors = normalizeLaravelErrors(laravelErrors);
        return error;
    };

    const showErrorNotification = (error, fallback) => {
        setNotification({
            type: "error",
            title: "Data Belum Lengkap",
            message: error?.message || fallback,
            details: Object.values(error?.validationErrors || {}).filter(Boolean),
        });
    };

    const parseResponseJson = async (response) => {
        const text = await response.text();

        if (!text) {
            return {};
        }

        try {
            return JSON.parse(text);
        } catch (error) {
            throw new Error(
                response.status === 419
                    ? "Sesi CSRF kedaluwarsa. Refresh halaman lalu coba lagi."
                    : "Response server bukan JSON. Cek Laravel log dan tab Network."
            );
        }
    };

    const toSelectOptions = (items = []) => {
        if (!Array.isArray(items)) {
            return [];
        }

        return items
            .map((item) => ({
                value: String(item.value ?? item.id ?? item.code ?? ""),
                label: String(
                    item.label ??
                        item.name ??
                        item.pendidikan ??
                        item.agama ??
                        item.status_pernikahan ??
                        item.kewarganegaraan ??
                        item.platform ??
                        item.opsi ??
                        item.nama_posisi ??
                        item.posisi ??
                        item.nama ??
                        ""
                ),
                id: String(item.id ?? item.value ?? item.code ?? ""),
                str_aktif: String(item.str_aktif ?? ""),
            }))
            .filter((item) => item.value && item.label);
    };

    const loadMasterOptions = async () => {
        try {
            const response = await fetch("/pendaftaran/api/master/pendaftaran", {
                credentials: "same-origin",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await parseResponseJson(response);

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Gagal mengambil data master pendaftaran."
                );
            }

            const data = result.data || {};

            setMasterOptions({
                pendidikan: toSelectOptions(data.pendidikan),
                agama: toSelectOptions(data.agama),
                status_pernikahan: toSelectOptions(data.status_pernikahan),
                posisi: toSelectOptions(data.posisi),
                kewarganegaraan: toSelectOptions(data.kewarganegaraan),
                jenis_kelamin: toSelectOptions(data.jenis_kelamin),
                str_aktif: toSelectOptions(data.str_aktif),
                sosial_media: toSelectOptions(data.sosial_media),
                opsi_kacamata: toSelectOptions(data.opsi_kacamata),
            });
        } catch (error) {
            console.error("Gagal memuat master pendaftaran:", error);
        }
    };

    const normalizeDateInput = (value) => {
        if (!value) {
            return "";
        }

        return String(value).slice(0, 10);
    };

    const normalizeDateForPayload = (value) => {
        if (!value) {
            return "";
        }

        const stringValue = String(value).trim();

        if (!stringValue) {
            return "";
        }

        if (/^\d{4}$/.test(stringValue)) {
            return `${stringValue}-01-01`;
        }

        if (/^\d{4}-\d{2}-\d{2}$/.test(stringValue)) {
            return stringValue;
        }

        const match = stringValue.match(/^(\d{4}-\d{2}-\d{2})/);

        if (match) {
            return match[1];
        }

        return "";
    };

    const getYearFromDate = (value) => {
        const normalized = normalizeDateForPayload(value);

        if (!normalized) {
            return "";
        }

        return normalized.slice(0, 4);
    };

    const normalizeYearForPayload = (value) => {
        if (value === undefined || value === null || value === "") {
            return "";
        }

        const year = String(value).replace(/\D/g, "").slice(0, 4);

        return year.length === 4 ? year : "";
    };

    const normalizeDecimalForPayload = (value) => {
        if (value === undefined || value === null || value === "") {
            return "";
        }

        const cleaned = String(value).replace(/[^0-9.]/g, "");
        const firstDotIndex = cleaned.indexOf(".");

        const normalized =
            firstDotIndex === -1
                ? cleaned
                : cleaned.slice(0, firstDotIndex + 1) +
                  cleaned.slice(firstDotIndex + 1).replace(/\./g, "");

        if (!normalized || normalized === ".") {
            return "";
        }

        return normalized;
    };

    const normalizeArrayValue = (value) => {
        if (Array.isArray(value)) {
            return value.filter(Boolean);
        }

        if (typeof value === "string") {
            try {
                const parsed = JSON.parse(value);

                if (Array.isArray(parsed)) {
                    return parsed.filter(Boolean);
                }
            } catch (error) {
                return value
                    .split(",")
                    .map((item) => item.trim())
                    .filter(Boolean);
            }
        }

        return [];
    };

    const getRelationName = (
        pelamar,
        relationName,
        fieldName,
        fallbackField = null
    ) => {
        return (
            pelamar?.[relationName]?.[fieldName] ||
            pelamar?.[fallbackField || relationName]?.[fieldName] ||
            ""
        );
    };


    const firstFilledValue = (...values) => {
        for (const value of values) {
            if (value !== undefined && value !== null && String(value).trim() !== "") {
                return value;
            }
        }

        return "";
    };

    const isTidakValue = (value) => {
        return String(value || "").trim().toLowerCase() === "tidak";
    };

    const getStatusPernikahanLabel = (pelamar) => {
        return (
            getRelationName(
                pelamar,
                "statusPernikahan",
                "status_pernikahan",
                "status_pernikahan"
            ) ||
            getRelationName(
                pelamar,
                "statusPernikahan",
                "nama",
                "status_pernikahan"
            ) ||
            getRelationName(
                pelamar,
                "statusPernikahan",
                "status",
                "status_pernikahan"
            ) ||
            getRelationName(
                pelamar,
                "status_pernikahan",
                "status_pernikahan"
            ) ||
            getRelationName(pelamar, "status_pernikahan", "nama") ||
            getRelationName(pelamar, "status_pernikahan", "status") ||
            ""
        );
    };

    const normalizeSosialMediaFromPelamar = (pelamar) => {
        const rows = pelamar?.sosialMedia || pelamar?.sosial_media || [];

        if (!Array.isArray(rows) || rows.length === 0) {
            return [{ ...emptySosialMedia }];
        }

        const mappedRows = rows
            .map((item) => ({
                id: item?.id || "",
                platform: item?.platform || "",
                nama_akun: item?.nama_akun || item?.nama_account || "",
                nama_account: item?.nama_account || item?.nama_akun || "",
            }))
            .filter((item) => item.platform || item.nama_akun || item.nama_account);

        return mappedRows.length > 0 ? mappedRows : [{ ...emptySosialMedia }];
    };

    const normalizeKontakDaruratFromPelamar = (pelamar) => {
        const rows =
            pelamar?.kontak_darurat ||
            pelamar?.riwayatKeluarga?.kontak_darurat ||
            [];

        if (Array.isArray(rows) && rows.length > 0) {
            const mappedRows = rows
                .map((item) => ({
                    nama: item?.nama || "",
                    status: item?.status || "",
                    nomor: item?.nomor || item?.no_hp || item?.telepon || "",
                }))
                .filter((item) => item.nama || item.status || item.nomor);

            return mappedRows.length > 0 ? mappedRows : [{ ...emptyKontakDarurat }];
        }

        const tlpnDarurat =
            pelamar?.tlpn_darurat ||
            pelamar?.riwayatKeluarga?.tlpn_darurat ||
            "";

        if (tlpnDarurat) {
            return [
                {
                    nama: "",
                    status: "",
                    nomor: tlpnDarurat,
                },
            ];
        }

        return [{ ...emptyKontakDarurat }];
    };

    const normalizeSaudaraKandungFromPelamar = (pelamar) => {
        const rows = pelamar?.saudara_kandung || pelamar?.saudaraKandung || [];

        if (!Array.isArray(rows) || rows.length === 0) {
            return [{ ...emptySaudara }];
        }

        const mappedRows = rows
            .map((item) => ({
                id: item?.id || "",
                nama: item?.nama || item?.nama_saudara_kandung || "",
                jenis_kelamin: item?.jenis_kelamin || "",
                hubungan: item?.hubungan || "",
                pekerjaan: item?.pekerjaan || "",
                no_hp: item?.no_hp || "",
                alamat: item?.alamat || "",
            }))
            .filter((item) => {
                return (
                    item.nama ||
                    item.jenis_kelamin ||
                    item.hubungan ||
                    item.pekerjaan ||
                    item.no_hp ||
                    item.alamat
                );
            });

        return mappedRows.length > 0 ? mappedRows : [{ ...emptySaudara }];
    };

    const normalizeSaudaraIparFromPelamar = (pelamar) => {
        const rows = pelamar?.saudara_ipar || pelamar?.saudaraIpar || [];

        if (!Array.isArray(rows) || rows.length === 0) {
            return [{ ...emptySaudara }];
        }

        const mappedRows = rows
            .map((item) => ({
                id: item?.id || "",
                nama: item?.nama || item?.nama_saudara_ipar || "",
                jenis_kelamin: item?.jenis_kelamin || "",
                hubungan: item?.hubungan || "",
                pekerjaan: item?.pekerjaan || "",
                no_hp: item?.no_hp || "",
                alamat: item?.alamat || "",
            }))
            .filter((item) => {
                return (
                    item.nama ||
                    item.jenis_kelamin ||
                    item.hubungan ||
                    item.pekerjaan ||
                    item.no_hp ||
                    item.alamat
                );
            });

        return mappedRows.length > 0 ? mappedRows : [{ ...emptySaudara }];
    };


    const EMPTY_RIWAYAT_PEKERJAAN = {
        id: "",
        nama_perusahaan: "",
        posisi_pekerjaan_terakhir: "",
        posisi_pekerjaan: "",
        periode_kerja_awal: "",
        periode_kerja_akhir: "",
        gaji_terakhir: "",
        bidang_pekerjaan: "",
        lokasi_perusahaan: "",
        deskripsi_pekerjaan: "",
        alasan_berhenti: "",
        keahlian: "",
        referensi_kerja: "",
        nama_refrensi: "",
        telp_refrensi: "",
        refrensi_rekan_kerja: "",
        nama_refrensi_rekan: "",
        telp_refrensi_rekan: "",
        refrensi_kerabat: "",
        nama_refrensi_kerabat: "",
        telp_refrensi_kerabat: "",
    };

    const normalizeRiwayatPekerjaanRows = (rows) => {
        if (!Array.isArray(rows)) {
            return [];
        }

        return rows
            .map((item) => {
                const referensiKerja = firstFilledValue(
                    item?.referensi_kerja,
                    item?.refrensi_kerja
                );

                const posisiPekerjaan = firstFilledValue(
                    item?.posisi_pekerjaan,
                    item?.posisi_pekerjaan_terakhir
                );

                return {
                    ...EMPTY_RIWAYAT_PEKERJAAN,
                    id: item?.id || "",
                    nama_perusahaan: item?.nama_perusahaan || "",
                    posisi_pekerjaan_terakhir: firstFilledValue(
                        item?.posisi_pekerjaan_terakhir,
                        posisiPekerjaan
                    ),
                    posisi_pekerjaan: posisiPekerjaan,
                    periode_kerja_awal: normalizeDateInput(item?.periode_kerja_awal),
                    periode_kerja_akhir: normalizeDateInput(item?.periode_kerja_akhir),
                    gaji_terakhir: item?.gaji_terakhir || "",
                    bidang_pekerjaan: item?.bidang_pekerjaan || "",
                    lokasi_perusahaan: item?.lokasi_perusahaan || "",
                    deskripsi_pekerjaan: item?.deskripsi_pekerjaan || "",
                    alasan_berhenti: item?.alasan_berhenti || "",
                    keahlian: item?.keahlian || "",
                    referensi_kerja: referensiKerja,
                    nama_refrensi: isTidakValue(referensiKerja) ? "" : (item?.nama_refrensi || ""),
                    telp_refrensi: isTidakValue(referensiKerja) ? "" : (item?.telp_refrensi || ""),
                    refrensi_rekan_kerja: item?.refrensi_rekan_kerja || "",
                    nama_refrensi_rekan: isTidakValue(item?.refrensi_rekan_kerja) ? "" : (item?.nama_refrensi_rekan || ""),
                    telp_refrensi_rekan: isTidakValue(item?.refrensi_rekan_kerja) ? "" : (item?.telp_refrensi_rekan || ""),
                    refrensi_kerabat: item?.refrensi_kerabat || "",
                    nama_refrensi_kerabat: isTidakValue(item?.refrensi_kerabat) ? "" : (item?.nama_refrensi_kerabat || ""),
                    telp_refrensi_kerabat: isTidakValue(item?.refrensi_kerabat) ? "" : (item?.telp_refrensi_kerabat || ""),
                };
            })
            .filter((item) => {
                return Object.entries(item).some(([key, value]) => {
                    if (key === "id") {
                        return false;
                    }

                    return value !== undefined && value !== null && String(value).trim() !== "";
                });
            });
    };

    const normalizeRiwayatPekerjaanFromPelamar = (pelamar) => {
        const rows =
            pelamar?.riwayat_pekerjaan ||
            pelamar?.riwayatPekerjaan ||
            pelamar?.dataRiwayatPekerjaan ||
            [];

        if (Array.isArray(rows) && rows.length > 0) {
            return normalizeRiwayatPekerjaanRows(rows);
        }

        const single = {
            id: pelamar?.riwayat_pekerjaan_id || pelamar?.pekerjaan_id || "",
            nama_perusahaan: pelamar?.nama_perusahaan || "",
            posisi_pekerjaan_terakhir: firstFilledValue(
                pelamar?.posisi_pekerjaan_terakhir,
                pelamar?.posisi_pekerjaan
            ),
            posisi_pekerjaan: firstFilledValue(
                pelamar?.posisi_pekerjaan,
                pelamar?.posisi_pekerjaan_terakhir
            ),
            periode_kerja_awal: pelamar?.periode_kerja_awal || "",
            periode_kerja_akhir: pelamar?.periode_kerja_akhir || "",
            gaji_terakhir: pelamar?.gaji_terakhir || "",
            bidang_pekerjaan: pelamar?.bidang_pekerjaan || "",
            lokasi_perusahaan: pelamar?.lokasi_perusahaan || "",
            deskripsi_pekerjaan: pelamar?.deskripsi_pekerjaan || "",
            alasan_berhenti: pelamar?.alasan_berhenti || "",
            keahlian: pelamar?.keahlian || "",
            referensi_kerja: firstFilledValue(pelamar?.referensi_kerja, pelamar?.refrensi_kerja),
            nama_refrensi: pelamar?.nama_refrensi || "",
            telp_refrensi: pelamar?.telp_refrensi || "",
            refrensi_rekan_kerja: pelamar?.refrensi_rekan_kerja || "",
            nama_refrensi_rekan: pelamar?.nama_refrensi_rekan || "",
            telp_refrensi_rekan: pelamar?.telp_refrensi_rekan || "",
            refrensi_kerabat: pelamar?.refrensi_kerabat || "",
            nama_refrensi_kerabat: pelamar?.nama_refrensi_kerabat || "",
            telp_refrensi_kerabat: pelamar?.telp_refrensi_kerabat || "",
        };

        return normalizeRiwayatPekerjaanRows([single]);
    };

    const mapPelamarToForm = (pelamar) => {
        const posisiId =
            pelamar?.posisi_dilamar || pelamar?.posisi_yang_dilamar || "";
        const perusahaanId = pelamar?.perusahaan_dilamar || "";

        const posisiLabel =
            getRelationName(pelamar, "posisi", "nama_posisi") ||
            getRelationName(pelamar, "posisi", "posisi") ||
            getRelationName(pelamar, "posisi", "nama") ||
            getRelationName(pelamar, "posisi", "nama_jabatan") ||
            getRelationName(pelamar, "posisi", "jabatan") ||
            getRelationName(pelamar, "posisi", "posisi_dilamar") ||
            "";

        const perusahaanLabel =
            getRelationName(pelamar, "perusahaan", "nama_perusahaan") ||
            getRelationName(pelamar, "perusahaan", "perusahaan") ||
            getRelationName(pelamar, "perusahaan", "nama") ||
            perusahaanId ||
            "";

        const keluarga = pelamar?.riwayatKeluarga || {};
        const kesehatan = pelamar?.riwayatKesehatan || {};
        const pekerjaanRows = normalizeRiwayatPekerjaanFromPelamar(pelamar);
        const pekerjaan = pekerjaanRows[0] ||
            pelamar?.riwayatPekerjaan ||
            pelamar?.riwayat_pekerjaan ||
            {};

        const kesiapan =
            pelamar?.kesiapanBekerja ||
            pelamar?.kesiapan_bekerja ||
            pelamar?.dataKesiapanBekerja ||
            {};

        const alamatDomisili = pelamar?.alamat_domisili || pelamar?.alamat || "";

        const statusPernikahanId = pelamar?.status_pernikahan_id || "";
        const statusPernikahanLabel = getStatusPernikahanLabel(pelamar);

        const statusPernikahanValue =
            statusPernikahanId ||
            statusPernikahanLabel ||
            pelamar?.status_perkawinan ||
            "";

        const provinsiId = pelamar?.provinsi_id || pelamar?.provinsi || "";
        const kabupatenId = pelamar?.kabupaten_id || pelamar?.kabupaten || "";
        const kecamatanId = pelamar?.kecamatan_id || pelamar?.kecamatan || "";
        const kelurahanId = pelamar?.kelurahan_id || pelamar?.kelurahan || "";

        const alatBantuDengar =
            pelamar?.alat_bantu_dengar ||
            kesehatan?.alat_bantu_dengar ||
            pelamar?.alat_bantu_pendengaran ||
            "";

        const menulisDenganTangan =
            pelamar?.menulis_dengan_tangan ||
            kesehatan?.menulis_dengan_tangan ||
            pelamar?.tangan_dominan ||
            "";

        const seringGemetar =
            pelamar?.sering_gemetar ||
            kesehatan?.sering_gemetar ||
            pelamar?.tangan_gemetar ||
            "";

        const tanganSeringBerkeringat =
            pelamar?.tangan_sering_berkeringat ||
            kesehatan?.tangan_sering_berkeringat ||
            pelamar?.tangan_berkeringat ||
            "";

        const penyakitMenular =
            pelamar?.penyakit_menular ||
            kesehatan?.penyakit_menular ||
            pelamar?.riwayat_penyakit_menular ||
            "";

        const punyaAlergi =
            pelamar?.punya_alergi ||
            kesehatan?.punya_alergi ||
            pelamar?.memiliki_alergi ||
            "";

        const namaAlergi =
            pelamar?.nama_alergi ||
            kesehatan?.nama_alergi ||
            pelamar?.alergi ||
            "";

        const referensiAtasanStatus = firstFilledValue(
            pelamar?.referensi_kerja,
            pelamar?.refrensi_kerja,
            pekerjaan?.referensi_kerja,
            pekerjaan?.refrensi_kerja
        );

        const namaReferensiAtasan = isTidakValue(referensiAtasanStatus)
            ? ""
            : firstFilledValue(pelamar?.nama_refrensi, pekerjaan?.nama_refrensi);

        const telpReferensiAtasan = isTidakValue(referensiAtasanStatus)
            ? ""
            : firstFilledValue(pelamar?.telp_refrensi, pekerjaan?.telp_refrensi);

        const referensiRekanStatus = firstFilledValue(
            pelamar?.refrensi_rekan_kerja,
            pekerjaan?.refrensi_rekan_kerja
        );

        const namaReferensiRekan = isTidakValue(referensiRekanStatus)
            ? ""
            : firstFilledValue(pelamar?.nama_refrensi_rekan, pekerjaan?.nama_refrensi_rekan);

        const telpReferensiRekan = isTidakValue(referensiRekanStatus)
            ? ""
            : firstFilledValue(pelamar?.telp_refrensi_rekan, pekerjaan?.telp_refrensi_rekan);

        const referensiKerabatStatus = firstFilledValue(
            pelamar?.refrensi_kerabat,
            pekerjaan?.refrensi_kerabat
        );

        const namaReferensiKerabat = isTidakValue(referensiKerabatStatus)
            ? ""
            : firstFilledValue(pelamar?.nama_refrensi_kerabat, pekerjaan?.nama_refrensi_kerabat);

        const telpReferensiKerabat = isTidakValue(referensiKerabatStatus)
            ? ""
            : firstFilledValue(pelamar?.telp_refrensi_kerabat, pekerjaan?.telp_refrensi_kerabat);

        return {
            token: pelamar?.token || "",

            nama: pelamar?.nama_lengkap || "",
            nama_panggilan: pelamar?.nama_panggil || "",
            email: pelamar?.email || "",
            no_hp: pelamar?.no_wa || "",

            posisi_dilamar: posisiId,
            posisi_dilamar_label: posisiLabel,
            posisi_str_aktif: pelamar?.posisi?.str_aktif || "",

            perusahaan_dilamar: perusahaanId,
            perusahaan_dilamar_label: perusahaanLabel,

            sumber_informasi:
                getRelationName(
                    pelamar,
                    "sumberInformasi",
                    "informasi",
                    "sumber_informasi"
                ) ||
                pelamar?.sumber_informasi_id ||
                "",

            pendidikan:
                pelamar?.pendidikan_id ||
                getRelationName(pelamar, "pendidikan", "id") ||
                getRelationName(pelamar, "pendidikan", "pendidikan") ||
                "",

            jurusan: pelamar?.jurusan || "",
            nama_institusi: pelamar?.nama_institusi || "",
            str_aktif: pelamar?.str_aktif || "",

            sosial_media: normalizeSosialMediaFromPelamar(pelamar),

            tempat_lahir: pelamar?.tempat_lahir || "",
            tanggal_lahir: normalizeDateInput(pelamar?.tanggal_lahir),
            jenis_kelamin: pelamar?.jenis_kelamin || "",

            agama:
                pelamar?.agama_id ||
                getRelationName(pelamar, "agama", "id") ||
                getRelationName(pelamar, "agama", "agama") ||
                "",

            status_pernikahan_id: statusPernikahanValue,
            status_perkawinan: statusPernikahanValue,

            kewarganegaraan:
                pelamar?.kewarganegaraan_id ||
                getRelationName(pelamar, "kewarganegaraan", "id") ||
                getRelationName(pelamar, "kewarganegaraan", "kewarganegaraan") ||
                "",

            alamat_ktp: pelamar?.alamat_ktp || "",
            alamat_domisili: alamatDomisili,
            alamat: alamatDomisili,

            provinsi_id: provinsiId,
            kabupaten_id: kabupatenId,
            kecamatan_id: kecamatanId,
            kelurahan_id: kelurahanId,

            provinsi: provinsiId,
            kabupaten: kabupatenId,
            kecamatan: kecamatanId,
            kelurahan: kelurahanId,

            rt: pelamar?.rt || "",
            rw: pelamar?.rw || "",

            nama_ayah: pelamar?.nama_ayah || keluarga?.nama_ayah || "",
            nik_ayah: pelamar?.nik_ayah || keluarga?.nik_ayah || "",
            tempat_lahir_ayah:
                pelamar?.tempat_lahir_ayah || keluarga?.tempat_lahir_ayah || "",
            tanggal_lahir_ayah: normalizeDateInput(
                pelamar?.tanggal_lahir_ayah || keluarga?.tanggal_lahir_ayah
            ),
            pekerjaan_ayah:
                pelamar?.pekerjaan_ayah || keluarga?.pekerjaan_ayah || "",
            no_hp_ayah: pelamar?.no_hp_ayah || keluarga?.no_hp_ayah || "",
            alamat_ayah: pelamar?.alamat_ayah || keluarga?.alamat_ayah || "",

            nama_ibu: pelamar?.nama_ibu || keluarga?.nama_ibu || "",
            nik_ibu: pelamar?.nik_ibu || keluarga?.nik_ibu || "",
            tempat_lahir_ibu:
                pelamar?.tempat_lahir_ibu || keluarga?.tempat_lahir_ibu || "",
            tanggal_lahir_ibu: normalizeDateInput(
                pelamar?.tanggal_lahir_ibu || keluarga?.tanggal_lahir_ibu
            ),
            pekerjaan_ibu:
                pelamar?.pekerjaan_ibu || keluarga?.pekerjaan_ibu || "",
            no_hp_ibu: pelamar?.no_hp_ibu || keluarga?.no_hp_ibu || "",
            alamat_ibu: pelamar?.alamat_ibu || keluarga?.alamat_ibu || "",

            nama_ayah_kandung:
                pelamar?.nama_ayah_kandung || keluarga?.nama_ayah_kandung || "",
            pekerjaan_ayah_kandung:
                pelamar?.pekerjaan_ayah_kandung ||
                keluarga?.pekerjaan_ayah_kandung ||
                "",
            nama_ibu_kandung:
                pelamar?.nama_ibu_kandung || keluarga?.nama_ibu_kandung || "",
            pekerjaan_ibu_kandung:
                pelamar?.pekerjaan_ibu_kandung ||
                keluarga?.pekerjaan_ibu_kandung ||
                "",

            nama_suami_istri:
                pelamar?.nama_suami_istri || keluarga?.nama_suami_istri || "",
            pekerjaan_suami_istri:
                pelamar?.pekerjaan_suami_istri ||
                keluarga?.pekerjaan_suami_istri ||
                keluarga?.pekerjaan_sumi_istri ||
                "",
            tlpn_suami_istri:
                pelamar?.tlpn_suami_istri || keluarga?.tlpn_suami_istri || "",

            nama_bapak_mertua:
                pelamar?.nama_bapak_mertua || keluarga?.nama_bapak_mertua || "",
            pekerjaan_bapak_mertua:
                pelamar?.pekerjaan_bapak_mertua ||
                keluarga?.pekerjaan_bapak_mertua ||
                "",
            nama_ibu_mertua:
                pelamar?.nama_ibu_mertua || keluarga?.nama_ibu_mertua || "",
            pekerjaan_ibu_mertua:
                pelamar?.pekerjaan_ibu_mertua ||
                keluarga?.pekerjaan_ibu_mertua ||
                "",

            kontak_darurat: normalizeKontakDaruratFromPelamar(pelamar),
            saudara_kandung: normalizeSaudaraKandungFromPelamar(pelamar),
            saudara_ipar: normalizeSaudaraIparFromPelamar(pelamar),

            hubungan_kerabat_instansi: normalizeArrayValue(
                pelamar?.hubungan_kerabat_instansi ||
                    keluarga?.hubungan_kerabat_instansi ||
                    keluarga?.kerabat_bekerja_diinstansi
            ),

            golongan_darah:
                pelamar?.gol_darah ||
                pelamar?.golongan_darah ||
                "",
            gol_darah:
                pelamar?.gol_darah ||
                pelamar?.golongan_darah ||
                "",

            tinggi_badan: pelamar?.tinggi_badan || "",
            berat_badan: pelamar?.berat_badan || "",

            buta_warna: pelamar?.buta_warna || kesehatan?.buta_warna || "",

            opsi_kacamata_id:
                pelamar?.opsi_kacamata_id ||
                kesehatan?.opsi_kacamata_id ||
                "",

            opsi_kacamata_label:
                pelamar?.opsi_kacamata_label ||
                kesehatan?.opsiKacamata?.opsi ||
                "",

            alat_bantu_dengar: alatBantuDengar,
            alat_bantu_pendengaran: alatBantuDengar,

            menulis_dengan_tangan: menulisDenganTangan,
            tangan_dominan: menulisDenganTangan,

            sering_gemetar: seringGemetar,
            tangan_gemetar: seringGemetar,

            tangan_sering_berkeringat: tanganSeringBerkeringat,
            tangan_berkeringat: tanganSeringBerkeringat,

            penyakit_menular: penyakitMenular,
            riwayat_penyakit_menular: penyakitMenular,

            program_kehamilan:
                pelamar?.program_kehamilan ||
                kesehatan?.program_kehamilan ||
                "",

            punya_alergi: punyaAlergi,
            memiliki_alergi: punyaAlergi,

            nama_alergi: namaAlergi,
            alergi: namaAlergi,

            punya_penyakit_genetik:
                pelamar?.punya_penyakit_genetik ||
                kesehatan?.punya_penyakit_genetik ||
                "",

            nama_penyakit:
                pelamar?.nama_penyakit ||
                kesehatan?.nama_penyakit ||
                "",

            riwayat_kronis:
                pelamar?.riwayat_kronis ||
                kesehatan?.riwayat_kronis ||
                "",

            pengobatan_psikolog:
                pelamar?.pengobatan_psikolog ||
                kesehatan?.pengobatan_psikolog ||
                "",

            kapan_dilakukan:
                pelamar?.kapan_dilakukan ||
                kesehatan?.kapan_dilakukan ||
                "",

            pernah_kecelakaan:
                pelamar?.pernah_kecelakaan ||
                kesehatan?.pernah_kecelakaan ||
                "",

            bagian_tubuh_kecelakaan:
                pelamar?.bagian_tubuh_kecelakaan ||
                kesehatan?.bagian_tubuh_kecelakaan ||
                "",

            pernah_operasi:
                pelamar?.pernah_operasi ||
                kesehatan?.pernah_operasi ||
                "",

            diagnosa_dokter:
                pelamar?.diagnosa_dokter ||
                kesehatan?.diagnosa_dokter ||
                "",

            status_pekerjaan:
                pelamar?.status_pekerjaan ||
                pekerjaan?.status_pekerjaan ||
                "",

            riwayat_pekerjaan: pekerjaanRows,

            nama_perusahaan:
                pelamar?.nama_perusahaan ||
                pekerjaan?.nama_perusahaan ||
                "",

            posisi_pekerjaan:
                pelamar?.posisi_pekerjaan ||
                pekerjaan?.posisi_pekerjaan ||
                pekerjaan?.posisi_pekerjaan_terakhir ||
                "",

            posisi_pekerjaan_terakhir:
                pelamar?.posisi_pekerjaan_terakhir ||
                pekerjaan?.posisi_pekerjaan_terakhir ||
                pelamar?.posisi_pekerjaan ||
                pekerjaan?.posisi_pekerjaan ||
                "",

            bidang_pekerjaan:
                pelamar?.bidang_pekerjaan ||
                pekerjaan?.bidang_pekerjaan ||
                "",

            lokasi_perusahaan:
                pelamar?.lokasi_perusahaan ||
                pekerjaan?.lokasi_perusahaan ||
                "",

            periode_kerja_awal:
                normalizeDateInput(
                    pelamar?.periode_kerja_awal ||
                        pekerjaan?.periode_kerja_awal
                ) || "",

            periode_kerja_akhir:
                normalizeDateInput(
                    pelamar?.periode_kerja_akhir ||
                        pekerjaan?.periode_kerja_akhir
                ) || "",

            tahun_mulai_bekerja:
                pelamar?.tahun_mulai_bekerja ||
                pekerjaan?.tahun_mulai_bekerja ||
                getYearFromDate(
                    pelamar?.periode_kerja_awal ||
                        pekerjaan?.periode_kerja_awal
                ) ||
                "",

            tahun_selesai_bekerja:
                pelamar?.tahun_selesai_bekerja ||
                pekerjaan?.tahun_selesai_bekerja ||
                getYearFromDate(
                    pelamar?.periode_kerja_akhir ||
                        pekerjaan?.periode_kerja_akhir
                ) ||
                "",

            lama_bekerja:
                pelamar?.lama_bekerja ||
                pekerjaan?.lama_bekerja ||
                hitungLamaBekerja(
                    pelamar?.tahun_mulai_bekerja ||
                        pekerjaan?.tahun_mulai_bekerja ||
                        getYearFromDate(
                            pelamar?.periode_kerja_awal ||
                                pekerjaan?.periode_kerja_awal
                        ),
                    pelamar?.tahun_selesai_bekerja ||
                        pekerjaan?.tahun_selesai_bekerja ||
                        getYearFromDate(
                            pelamar?.periode_kerja_akhir ||
                                pekerjaan?.periode_kerja_akhir
                        )
                ) ||
                "",

            deskripsi_pekerjaan:
                pelamar?.deskripsi_pekerjaan ||
                pekerjaan?.deskripsi_pekerjaan ||
                "",

            alasan_berhenti:
                pelamar?.alasan_berhenti ||
                pekerjaan?.alasan_berhenti ||
                "",

            gaji_terakhir:
                pelamar?.gaji_terakhir ||
                pekerjaan?.gaji_terakhir ||
                "",

            keahlian:
                pelamar?.keahlian ||
                pekerjaan?.keahlian ||
                "",

            catatan_pekerjaan:
                pelamar?.catatan_pekerjaan ||
                pekerjaan?.catatan_pekerjaan ||
                "",

            referensi_kerja: referensiAtasanStatus,

            nama_refrensi: namaReferensiAtasan,

            telp_refrensi: telpReferensiAtasan,

            refrensi_rekan_kerja: referensiRekanStatus,

            nama_refrensi_rekan: namaReferensiRekan,

            telp_refrensi_rekan: telpReferensiRekan,

            refrensi_kerabat: referensiKerabatStatus,

            nama_refrensi_kerabat: namaReferensiKerabat,

            telp_refrensi_kerabat: telpReferensiKerabat,

            bersedia_ditempatkan:
                pelamar?.bersedia_ditempatkan ||
                kesiapan?.bersedia_ditempatkan ||
                "",

            bersedia_shift:
                pelamar?.bersedia_shift ||
                kesiapan?.bersedia_shift ||
                "",

            bersedia_lembur:
                pelamar?.bersedia_lembur ||
                kesiapan?.bersedia_lembur ||
                "",

            bersedia_hari_libur:
                pelamar?.bersedia_hari_libur ||
                kesiapan?.bersedia_hari_libur ||
                "",

            tanggal_siap_kerja:
                pelamar?.tanggal_siap_kerja ||
                kesiapan?.tanggal_siap_kerja ||
                pelamar?.kapan_siap_bekerja ||
                kesiapan?.kapan_siap_bekerja ||
                "",

            gaji_diharapkan:
                pelamar?.gaji_diharapkan ||
                kesiapan?.gaji_diharapkan ||
                pelamar?.ekpetasi_gaji ||
                kesiapan?.ekpetasi_gaji ||
                "",

            lokasi_kerja_diinginkan:
                pelamar?.lokasi_kerja_diinginkan ||
                kesiapan?.lokasi_kerja_diinginkan ||
                pelamar?.penempatan ||
                kesiapan?.penempatan ||
                "",

            memiliki_kendaraan:
                pelamar?.memiliki_kendaraan ||
                kesiapan?.memiliki_kendaraan ||
                "",

            memiliki_sim:
                pelamar?.memiliki_sim ||
                kesiapan?.memiliki_sim ||
                "",

            bersedia_pelatihan:
                pelamar?.bersedia_pelatihan ||
                kesiapan?.bersedia_pelatihan ||
                pelamar?.bersedia_training ||
                kesiapan?.bersedia_training ||
                "",

            status_ikatan_kerja:
                pelamar?.status_ikatan_kerja ||
                kesiapan?.status_ikatan_kerja ||
                "",

            alasan_melamar:
                pelamar?.alasan_melamar ||
                kesiapan?.alasan_melamar ||
                "",

            catatan_kesiapan:
                pelamar?.catatan_kesiapan ||
                kesiapan?.catatan_kesiapan ||
                "",

            kapan_siap_bekerja:
                pelamar?.kapan_siap_bekerja ||
                kesiapan?.kapan_siap_bekerja ||
                pelamar?.tanggal_siap_kerja ||
                kesiapan?.tanggal_siap_kerja ||
                "",

            ekpetasi_gaji:
                pelamar?.ekpetasi_gaji ||
                kesiapan?.ekpetasi_gaji ||
                pelamar?.gaji_diharapkan ||
                kesiapan?.gaji_diharapkan ||
                "",

            penempatan: normalizeArrayValue(
                pelamar?.penempatan ||
                    kesiapan?.penempatan ||
                    pelamar?.penempatan_luar_jawa_tengah ||
                    kesiapan?.penempatan_luar_jawa_tengah
            ),

            proses_bkhang: normalizeArrayValue(
                pelamar?.proses_bkhang ||
                    kesiapan?.proses_bkhang ||
                    pelamar?.background_checking ||
                    kesiapan?.background_checking
            ),

            dapat_dipertanggung_jawabkan: normalizeArrayValue(
                pelamar?.dapat_dipertanggung_jawabkan ||
                    kesiapan?.dapat_dipertanggung_jawabkan ||
                    pelamar?.pernyataan_data_benar ||
                    kesiapan?.pernyataan_data_benar
            ),

            bersedia_training:
                pelamar?.bersedia_training ||
                kesiapan?.bersedia_training ||
                pelamar?.bersedia_pelatihan ||
                kesiapan?.bersedia_pelatihan ||
                "",

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

        setProgressStep((currentProgressStep) =>
            Math.max(currentProgressStep, getCompletedStepFromPelamar(pelamar))
        );
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
                    credentials: "same-origin",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                }
            );

            const result = await parseResponseJson(response);

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

    const makeSosialMediaPayload = () => {
        if (!Array.isArray(form.sosial_media)) {
            return [];
        }

        return form.sosial_media
            .map((item) => ({
                id: item?.id || undefined,
                platform: item?.platform || "",
                nama_akun: item?.nama_akun || item?.nama_account || "",
                nama_account: item?.nama_account || item?.nama_akun || "",
            }))
            .filter((item) => item.platform || item.nama_akun || item.nama_account);
    };

    const normalizeKontakDaruratPayload = () => {
        if (!Array.isArray(form.kontak_darurat)) {
            return [];
        }

        return form.kontak_darurat
            .map((item) => ({
                nama: item?.nama || "",
                status: item?.status || "",
                nomor: item?.nomor || "",
            }))
            .filter((item) => item.nama || item.status || item.nomor);
    };

    const normalizeSaudaraPayload = (field) => {
        if (!Array.isArray(form[field])) {
            return [];
        }

        return form[field]
            .map((item) => ({
                id: item?.id || undefined,
                nama: item?.nama || "",
                jenis_kelamin: item?.jenis_kelamin || "",
                hubungan: item?.hubungan || "",
                pekerjaan: item?.pekerjaan || "",
                no_hp: item?.no_hp || "",
                alamat: item?.alamat || "",
            }))
            .filter((item) => {
                return (
                    item.nama ||
                    item.jenis_kelamin ||
                    item.hubungan ||
                    item.pekerjaan ||
                    item.no_hp ||
                    item.alamat
                );
            });
    };

    const saveDataDiri = async () => {
        const token = form.token || getInitialTokenFromPage();

        if (!token) {
            throw new Error("Token pelamar tidak tersedia.");
        }

        const alamatDomisili = form.alamat_domisili || form.alamat || "";

        const statusPernikahan =
            form.status_pernikahan_id || form.status_perkawinan || "";

        const provinsiId = form.provinsi_id || form.provinsi || "";
        const kabupatenId = form.kabupaten_id || form.kabupaten || "";
        const kecamatanId = form.kecamatan_id || form.kecamatan || "";
        const kelurahanId = form.kelurahan_id || form.kelurahan || "";

        const payload = {
            ...form,
            token,

            posisi_dilamar: form.posisi_dilamar,
            perusahaan_dilamar: form.perusahaan_dilamar,

            alamat_ktp: form.alamat_ktp || "",
            alamat_domisili: alamatDomisili,
            alamat: alamatDomisili,

            status_pernikahan_id: statusPernikahan,
            status_perkawinan: statusPernikahan,

            provinsi_id: provinsiId,
            kabupaten_id: kabupatenId,
            kecamatan_id: kecamatanId,
            kelurahan_id: kelurahanId,

            provinsi: provinsiId,
            kabupaten: kabupatenId,
            kecamatan: kecamatanId,
            kelurahan: kelurahanId,

            sosial_media: makeSosialMediaPayload(),
        };

        delete payload.posisi_dilamar_label;
        delete payload.perusahaan_dilamar_label;
        delete payload.posisi_str_aktif;

        const response = await fetch(
            `/pendaftaran/api/token/${encodeURIComponent(token)}/data-diri`,
            {
                method: "PATCH",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify(payload),
            }
        );

        const result = await parseResponseJson(response);

        if (!response.ok || !result.success) {
            if (result.errors) {
                setErrors(normalizeLaravelErrors(result.errors));
            }

            throw makeRequestError(result.message || "Gagal menyimpan data diri.", result.errors);
        }

        if (result.data) {
            applyPelamarToPage(result.data);
        }

        return result.data;
    };

    const saveRiwayatKeluarga = async () => {
        const token = form.token || getInitialTokenFromPage();

        if (!token) {
            throw new Error("Token pelamar tidak tersedia.");
        }

        const payload = {
            nama_ayah_kandung: form.nama_ayah_kandung || "",
            pekerjaan_ayah_kandung: form.pekerjaan_ayah_kandung || "",
            nama_ibu_kandung: form.nama_ibu_kandung || "",
            pekerjaan_ibu_kandung: form.pekerjaan_ibu_kandung || "",

            nama_ayah: form.nama_ayah || "",
            nik_ayah: form.nik_ayah || "",
            tempat_lahir_ayah: form.tempat_lahir_ayah || "",
            tanggal_lahir_ayah: form.tanggal_lahir_ayah || "",
            pekerjaan_ayah: form.pekerjaan_ayah || "",
            no_hp_ayah: form.no_hp_ayah || "",
            alamat_ayah: form.alamat_ayah || "",

            nama_ibu: form.nama_ibu || "",
            nik_ibu: form.nik_ibu || "",
            tempat_lahir_ibu: form.tempat_lahir_ibu || "",
            tanggal_lahir_ibu: form.tanggal_lahir_ibu || "",
            pekerjaan_ibu: form.pekerjaan_ibu || "",
            no_hp_ibu: form.no_hp_ibu || "",
            alamat_ibu: form.alamat_ibu || "",

            nama_suami_istri: form.nama_suami_istri || "",
            pekerjaan_suami_istri: form.pekerjaan_suami_istri || "",
            tlpn_suami_istri: form.tlpn_suami_istri || "",

            nama_bapak_mertua: form.nama_bapak_mertua || "",
            pekerjaan_bapak_mertua: form.pekerjaan_bapak_mertua || "",
            nama_ibu_mertua: form.nama_ibu_mertua || "",
            pekerjaan_ibu_mertua: form.pekerjaan_ibu_mertua || "",

            hubungan_kerabat_instansi: Array.isArray(
                form.hubungan_kerabat_instansi
            )
                ? form.hubungan_kerabat_instansi
                : normalizeArrayValue(form.hubungan_kerabat_instansi),

            kontak_darurat: normalizeKontakDaruratPayload(),
            saudara_kandung: normalizeSaudaraPayload("saudara_kandung"),
            saudara_ipar: normalizeSaudaraPayload("saudara_ipar"),
        };

        const response = await fetch(
            `/pendaftaran/api/token/${encodeURIComponent(token)}/riwayat-keluarga`,
            {
                method: "PATCH",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify(payload),
            }
        );

        const result = await parseResponseJson(response);

        if (!response.ok || !result.success) {
            if (result.errors) {
                setErrors(normalizeLaravelErrors(result.errors));
            }

            throw makeRequestError(result.message || "Gagal menyimpan riwayat keluarga.", result.errors);
        }

        if (result.data) {
            applyPelamarToPage(result.data);
        }

        return result.data;
    };

    const saveRiwayatKesehatan = async () => {
        const token = form.token || getInitialTokenFromPage();

        if (!token) {
            throw new Error("Token pelamar tidak tersedia.");
        }

        const golDarah = form.gol_darah || form.golongan_darah || "";

        const alatBantuDengar =
            form.alat_bantu_dengar ||
            form.alat_bantu_pendengaran ||
            "";

        const menulisDenganTangan =
            form.menulis_dengan_tangan ||
            form.tangan_dominan ||
            "";

        const seringGemetar =
            form.sering_gemetar ||
            form.tangan_gemetar ||
            "";

        const tanganSeringBerkeringat =
            form.tangan_sering_berkeringat ||
            form.tangan_berkeringat ||
            "";

        const penyakitMenular =
            form.penyakit_menular ||
            form.riwayat_penyakit_menular ||
            "";

        const punyaAlergi =
            form.punya_alergi ||
            form.memiliki_alergi ||
            "";

        const namaAlergi =
            form.nama_alergi ||
            form.alergi ||
            "";

        const payload = {
            gol_darah: golDarah,
            golongan_darah: golDarah,
            tinggi_badan: form.tinggi_badan || "",
            berat_badan: form.berat_badan || "",

            buta_warna: form.buta_warna || "",
            opsi_kacamata_id: form.opsi_kacamata_id || "",

            alat_bantu_dengar: alatBantuDengar,
            alat_bantu_pendengaran: alatBantuDengar,

            menulis_dengan_tangan: menulisDenganTangan,
            tangan_dominan: menulisDenganTangan,

            sering_gemetar: seringGemetar,
            tangan_gemetar: seringGemetar,

            tangan_sering_berkeringat: tanganSeringBerkeringat,
            tangan_berkeringat: tanganSeringBerkeringat,

            penyakit_menular: penyakitMenular,
            riwayat_penyakit_menular: penyakitMenular,

            program_kehamilan: form.program_kehamilan || "",

            punya_alergi: punyaAlergi,
            memiliki_alergi: punyaAlergi,

            nama_alergi: namaAlergi,
            alergi: namaAlergi,

            punya_penyakit_genetik: form.punya_penyakit_genetik || "",
            nama_penyakit: form.nama_penyakit || "",
            riwayat_kronis: form.riwayat_kronis || "",

            pengobatan_psikolog: form.pengobatan_psikolog || "",
            kapan_dilakukan: form.kapan_dilakukan || "",

            pernah_kecelakaan: form.pernah_kecelakaan || "",
            bagian_tubuh_kecelakaan: form.bagian_tubuh_kecelakaan || "",

            pernah_operasi: form.pernah_operasi || "",
            diagnosa_dokter: form.diagnosa_dokter || "",
        };

        const response = await fetch(
            `/pendaftaran/api/token/${encodeURIComponent(token)}/riwayat-kesehatan`,
            {
                method: "PATCH",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify(payload),
            }
        );

        const result = await parseResponseJson(response);

        if (!response.ok || !result.success) {
            if (result.errors) {
                setErrors(normalizeLaravelErrors(result.errors));
            }

            throw makeRequestError(result.message || "Gagal menyimpan riwayat kesehatan.", result.errors);
        }

        if (result.data) {
            applyPelamarToPage(result.data);
        }

        return result.data;
    };

    const buildRiwayatPekerjaanPayloadRows = () => {
        const rowsFromArray = Array.isArray(form.riwayat_pekerjaan)
            ? form.riwayat_pekerjaan
            : [];

        const sourceRows = rowsFromArray.length > 0
            ? rowsFromArray
            : [
                  {
                      id: form.riwayat_pekerjaan_id || form.pekerjaan_id || "",
                      nama_perusahaan: form.nama_perusahaan || "",
                      posisi_pekerjaan_terakhir:
                          form.posisi_pekerjaan_terakhir || form.posisi_pekerjaan || "",
                      posisi_pekerjaan:
                          form.posisi_pekerjaan || form.posisi_pekerjaan_terakhir || "",
                      periode_kerja_awal: form.periode_kerja_awal || "",
                      periode_kerja_akhir: form.periode_kerja_akhir || "",
                      gaji_terakhir: form.gaji_terakhir || "",
                      bidang_pekerjaan: form.bidang_pekerjaan || "",
                      lokasi_perusahaan: form.lokasi_perusahaan || "",
                      deskripsi_pekerjaan: form.deskripsi_pekerjaan || "",
                      alasan_berhenti: form.alasan_berhenti || "",
                      keahlian: form.keahlian || "",
                      referensi_kerja: form.referensi_kerja || form.refrensi_kerja || "",
                      nama_refrensi: form.nama_refrensi || "",
                      telp_refrensi: form.telp_refrensi || "",
                      refrensi_rekan_kerja: form.refrensi_rekan_kerja || "",
                      nama_refrensi_rekan: form.nama_refrensi_rekan || "",
                      telp_refrensi_rekan: form.telp_refrensi_rekan || "",
                      refrensi_kerabat: form.refrensi_kerabat || "",
                      nama_refrensi_kerabat: form.nama_refrensi_kerabat || "",
                      telp_refrensi_kerabat: form.telp_refrensi_kerabat || "",
                  },
              ];

        return sourceRows
            .map((item) => {
                const posisiPekerjaan =
                    item?.posisi_pekerjaan || item?.posisi_pekerjaan_terakhir || "";
                const referensiKerja =
                    item?.referensi_kerja || item?.refrensi_kerja || "";
                const referensiAtasanTidak = isTidakValue(referensiKerja);
                const referensiRekanTidak = isTidakValue(item?.refrensi_rekan_kerja);
                const referensiKerabatTidak = isTidakValue(item?.refrensi_kerabat);

                return {
                    id: item?.id || "",
                    nama_perusahaan: item?.nama_perusahaan || "",
                    posisi_pekerjaan_terakhir: posisiPekerjaan,
                    posisi_pekerjaan: posisiPekerjaan,
                    periode_kerja_awal: normalizeDateForPayload(item?.periode_kerja_awal),
                    periode_kerja_akhir: normalizeDateForPayload(item?.periode_kerja_akhir),
                    gaji_terakhir: normalizeDecimalForPayload(item?.gaji_terakhir),
                    bidang_pekerjaan: item?.bidang_pekerjaan || "",
                    lokasi_perusahaan: item?.lokasi_perusahaan || "",
                    deskripsi_pekerjaan: item?.deskripsi_pekerjaan || "",
                    alasan_berhenti: item?.alasan_berhenti || "",
                    keahlian: item?.keahlian || "",
                    referensi_kerja: referensiKerja,
                    nama_refrensi: referensiAtasanTidak ? "" : (item?.nama_refrensi || ""),
                    telp_refrensi: referensiAtasanTidak ? "" : (item?.telp_refrensi || ""),
                    refrensi_rekan_kerja: item?.refrensi_rekan_kerja || "",
                    nama_refrensi_rekan: referensiRekanTidak ? "" : (item?.nama_refrensi_rekan || ""),
                    telp_refrensi_rekan: referensiRekanTidak ? "" : (item?.telp_refrensi_rekan || ""),
                    refrensi_kerabat: item?.refrensi_kerabat || "",
                    nama_refrensi_kerabat: referensiKerabatTidak ? "" : (item?.nama_refrensi_kerabat || ""),
                    telp_refrensi_kerabat: referensiKerabatTidak ? "" : (item?.telp_refrensi_kerabat || ""),
                };
            })
            .filter((item) => {
                return Object.entries(item).some(([key, value]) => {
                    if (key === "id") {
                        return false;
                    }

                    return value !== undefined && value !== null && String(value).trim() !== "";
                });
            });
    };

    const saveRiwayatPekerjaan = async () => {
        const token = form.token || getInitialTokenFromPage();

        if (!token) {
            throw new Error("Token pelamar tidak tersedia.");
        }

        const riwayatPekerjaanRows =
            String(form.status_pekerjaan || "").trim().toLowerCase() === "belum bekerja"
                ? []
                : buildRiwayatPekerjaanPayloadRows();

        const firstRow = riwayatPekerjaanRows[0] || {};

        const payload = {
            status_pekerjaan: form.status_pekerjaan || "",
            riwayat_pekerjaan: riwayatPekerjaanRows,

            // Fallback field lama tetap dikirim dari baris pertama saja.
            // Controller akan menyimpan semua data dari riwayat_pekerjaan.
            nama_perusahaan: firstRow.nama_perusahaan || "",
            posisi_pekerjaan: firstRow.posisi_pekerjaan || "",
            posisi_pekerjaan_terakhir: firstRow.posisi_pekerjaan_terakhir || "",
            bidang_pekerjaan: firstRow.bidang_pekerjaan || "",
            lokasi_perusahaan: firstRow.lokasi_perusahaan || "",
            periode_kerja_awal: firstRow.periode_kerja_awal || "",
            periode_kerja_akhir: firstRow.periode_kerja_akhir || "",
            deskripsi_pekerjaan: firstRow.deskripsi_pekerjaan || "",
            alasan_berhenti: firstRow.alasan_berhenti || "",
            gaji_terakhir: firstRow.gaji_terakhir || "",
            keahlian: firstRow.keahlian || "",
            referensi_kerja: firstRow.referensi_kerja || "",
            nama_refrensi: firstRow.nama_refrensi || "",
            telp_refrensi: firstRow.telp_refrensi || "",
            refrensi_rekan_kerja: firstRow.refrensi_rekan_kerja || "",
            nama_refrensi_rekan: firstRow.nama_refrensi_rekan || "",
            telp_refrensi_rekan: firstRow.telp_refrensi_rekan || "",
            refrensi_kerabat: firstRow.refrensi_kerabat || "",
            nama_refrensi_kerabat: firstRow.nama_refrensi_kerabat || "",
            telp_refrensi_kerabat: firstRow.telp_refrensi_kerabat || "",
        };

        const response = await fetch(
            `/pendaftaran/api/token/${encodeURIComponent(token)}/riwayat-pekerjaan`,
            {
                method: "PATCH",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify(payload),
            }
        );

        const result = await parseResponseJson(response);

        if (!response.ok || !result.success) {
            if (result.errors) {
                setErrors(normalizeLaravelErrors(result.errors));
            }

            throw makeRequestError(result.message || "Gagal menyimpan riwayat pekerjaan.", result.errors);
        }

        if (result.data) {
            applyPelamarToPage(result.data);
        }

        return result.data;
    };

    const saveKesiapanBekerja = async () => {
        const token = form.token || getInitialTokenFromPage();

        if (!token) {
            throw new Error("Token pelamar tidak tersedia.");
        }

        const penempatanValues = normalizeArrayValue(form.penempatan);
        const prosesBkhangValues = normalizeArrayValue(form.proses_bkhang);
        const pernyataanValues = normalizeArrayValue(
            form.dapat_dipertanggung_jawabkan
        );

        const payload = {
            kapan_siap_bekerja:
                form.kapan_siap_bekerja || form.tanggal_siap_kerja || "",

            ekpetasi_gaji: normalizeDecimalForPayload(
                form.ekpetasi_gaji || form.gaji_diharapkan
            ),

            penempatan: penempatanValues.join(", "),

            proses_bkhang:
                prosesBkhangValues[0] ||
                form.proses_bkhang ||
                form.background_checking ||
                "",

            dapat_dipertanggung_jawabkan:
                pernyataanValues[0] ||
                form.dapat_dipertanggung_jawabkan ||
                form.pernyataan_data_benar ||
                "",

            bersedia_training:
                form.bersedia_training ||
                form.bersedia_pelatihan ||
                "",
        };

        const response = await fetch(
            `/pendaftaran/api/token/${encodeURIComponent(token)}/kesiapan-bekerja`,
            {
                method: "PATCH",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify(payload),
            }
        );

        const result = await parseResponseJson(response);

        if (!response.ok || !result.success) {
            if (result.errors) {
                setErrors(normalizeLaravelErrors(result.errors));
            }

            throw makeRequestError(result.message || "Gagal menyimpan kesiapan bekerja.", result.errors);
        }

        if (result.data) {
            applyPelamarToPage(result.data);
        }

        return result.data;
    };

    const makeHasilCekTahapan = (pelamar) => {
        const status = pelamar?.status_seleksi || "Administrasi";
        const tahapanTerakhir = pelamar?.tahapan_terakhir || status;

        return {
            token: pelamar?.token || "-",
            nama_pelamar: pelamar?.nama_lengkap || "-",
            posisi_dilamar:
                getRelationName(pelamar, "posisi", "nama_posisi") ||
                getRelationName(pelamar, "posisi", "posisi") ||
                getRelationName(pelamar, "posisi", "nama") ||
                getRelationName(pelamar, "posisi", "nama_jabatan") ||
                getRelationName(pelamar, "posisi", "jabatan") ||
                getRelationName(pelamar, "posisi", "posisi_dilamar") ||
                "-",
            perusahaan_dilamar:
                getRelationName(pelamar, "perusahaan", "nama_perusahaan") ||
                getRelationName(pelamar, "perusahaan", "perusahaan") ||
                getRelationName(pelamar, "perusahaan", "nama") ||
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
            (item) =>
                item.toLowerCase() === String(statusSeleksi || "").toLowerCase()
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

    const hitungLamaBekerja = (tahunMulai, tahunSelesai) => {
        const tahunMulaiNormal = normalizeYearForPayload(tahunMulai);
        const tahunSelesaiNormal = normalizeYearForPayload(tahunSelesai);

        if (!tahunMulaiNormal || !tahunSelesaiNormal) {
            return "";
        }

        const mulai = parseInt(tahunMulaiNormal, 10);
        const selesai = parseInt(tahunSelesaiNormal, 10);

        if (!mulai || !selesai || selesai < mulai) {
            return "";
        }

        return String(selesai - mulai);
    };

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;

        if (
            name === "posisi_dilamar" ||
            name === "perusahaan_dilamar" ||
            name === "posisi_dilamar_label" ||
            name === "perusahaan_dilamar_label"
        ) {
            return;
        }

        const incomingValue =
            type === "checkbox-group" && Array.isArray(value) ? value : value;

        setForm((prevForm) => {
            let nextValue = incomingValue;

            if (type === "checkbox-group") {
                nextValue = Array.isArray(value) ? value : normalizeArrayValue(value);
            }

            if (type === "checkbox") {
                const currentValues = Array.isArray(prevForm[name])
                    ? prevForm[name]
                    : [];

                nextValue = checked
                    ? [...currentValues, value]
                    : currentValues.filter((item) => item !== value);
            }

            if (
                name === "tahun_mulai_bekerja" ||
                name === "tahun_selesai_bekerja"
            ) {
                nextValue = String(nextValue).replace(/\D/g, "").slice(0, 4);
            }

            const updatedForm = {
                ...prevForm,
                [name]: nextValue,
            };

            if (name === "alamat_domisili") {
                updatedForm.alamat = nextValue;
            }

            if (name === "alamat") {
                updatedForm.alamat_domisili = nextValue;
            }

            if (name === "status_pernikahan_id") {
                updatedForm.status_perkawinan = nextValue;
            }

            if (name === "status_perkawinan") {
                updatedForm.status_pernikahan_id = nextValue;
            }

            if (name === "provinsi_id") {
                updatedForm.provinsi = nextValue;
            }

            if (name === "provinsi") {
                updatedForm.provinsi_id = nextValue;
            }

            if (name === "kabupaten_id") {
                updatedForm.kabupaten = nextValue;
            }

            if (name === "kabupaten") {
                updatedForm.kabupaten_id = nextValue;
            }

            if (name === "kecamatan_id") {
                updatedForm.kecamatan = nextValue;
            }

            if (name === "kecamatan") {
                updatedForm.kecamatan_id = nextValue;
            }

            if (name === "kelurahan_id") {
                updatedForm.kelurahan = nextValue;
            }

            if (name === "kelurahan") {
                updatedForm.kelurahan_id = nextValue;
            }

            if (name === "golongan_darah") {
                updatedForm.gol_darah = nextValue;
            }

            if (name === "gol_darah") {
                updatedForm.golongan_darah = nextValue;
            }

            if (name === "alat_bantu_dengar") {
                updatedForm.alat_bantu_pendengaran = nextValue;
            }

            if (name === "alat_bantu_pendengaran") {
                updatedForm.alat_bantu_dengar = nextValue;
            }

            if (name === "menulis_dengan_tangan") {
                updatedForm.tangan_dominan = nextValue;
            }

            if (name === "tangan_dominan") {
                updatedForm.menulis_dengan_tangan = nextValue;
            }

            if (name === "sering_gemetar") {
                updatedForm.tangan_gemetar = nextValue;
            }

            if (name === "tangan_gemetar") {
                updatedForm.sering_gemetar = nextValue;
            }

            if (name === "tangan_sering_berkeringat") {
                updatedForm.tangan_berkeringat = nextValue;
            }

            if (name === "tangan_berkeringat") {
                updatedForm.tangan_sering_berkeringat = nextValue;
            }

            if (name === "penyakit_menular") {
                updatedForm.riwayat_penyakit_menular = nextValue;
            }

            if (name === "riwayat_penyakit_menular") {
                updatedForm.penyakit_menular = nextValue;
            }

            if (name === "punya_alergi") {
                updatedForm.memiliki_alergi = nextValue;

                if (nextValue !== "Ya") {
                    updatedForm.nama_alergi = "";
                    updatedForm.alergi = "";
                }
            }

            if (name === "memiliki_alergi") {
                updatedForm.punya_alergi = nextValue;

                if (nextValue !== "Ya") {
                    updatedForm.nama_alergi = "";
                    updatedForm.alergi = "";
                }
            }

            if (name === "nama_alergi") {
                updatedForm.alergi = nextValue;
            }

            if (name === "alergi") {
                updatedForm.nama_alergi = nextValue;
            }

            if (name === "punya_penyakit_genetik" && nextValue !== "Ya") {
                updatedForm.nama_penyakit = "";
            }

            if (name === "pengobatan_psikolog" && nextValue !== "Ya") {
                updatedForm.kapan_dilakukan = "";
            }

            if (name === "pernah_kecelakaan" && nextValue !== "Ya") {
                updatedForm.bagian_tubuh_kecelakaan = "";
            }

            if (name === "pernah_operasi" && nextValue !== "Ya") {
                updatedForm.diagnosa_dokter = "";
            }

            if (name === "status_pekerjaan" && String(nextValue).trim().toLowerCase() === "belum bekerja") {
                updatedForm.riwayat_pekerjaan = [];
                updatedForm.posisi_pekerjaan = "";
                updatedForm.posisi_pekerjaan_terakhir = "";
                updatedForm.nama_perusahaan = "";
                updatedForm.bidang_pekerjaan = "";
                updatedForm.lokasi_perusahaan = "";
                updatedForm.tahun_mulai_bekerja = "";
                updatedForm.tahun_selesai_bekerja = "";
                updatedForm.periode_kerja_awal = "";
                updatedForm.periode_kerja_akhir = "";
                updatedForm.lama_bekerja = "";
                updatedForm.deskripsi_pekerjaan = "";
                updatedForm.alasan_berhenti = "";
                updatedForm.gaji_terakhir = "";
                updatedForm.keahlian = "";
                updatedForm.catatan_pekerjaan = "";
                updatedForm.referensi_kerja = "";
                updatedForm.nama_refrensi = "";
                updatedForm.telp_refrensi = "";
                updatedForm.refrensi_rekan_kerja = "";
                updatedForm.nama_refrensi_rekan = "";
                updatedForm.telp_refrensi_rekan = "";
                updatedForm.refrensi_kerabat = "";
                updatedForm.nama_refrensi_kerabat = "";
                updatedForm.telp_refrensi_kerabat = "";
            }

            if (name === "posisi_pekerjaan_terakhir") {
                updatedForm.posisi_pekerjaan = nextValue;
            }

            if (name === "posisi_pekerjaan") {
                updatedForm.posisi_pekerjaan_terakhir = nextValue;
            }

            if (name === "periode_kerja_awal") {
                updatedForm.tahun_mulai_bekerja = getYearFromDate(nextValue);
            }

            if (name === "periode_kerja_akhir") {
                updatedForm.tahun_selesai_bekerja = getYearFromDate(nextValue);
            }

            if (name === "tahun_mulai_bekerja") {
                updatedForm.periode_kerja_awal = normalizeDateForPayload(nextValue);
            }

            if (name === "tahun_selesai_bekerja") {
                updatedForm.periode_kerja_akhir = normalizeDateForPayload(nextValue);
            }

            if (
                name === "tahun_mulai_bekerja" ||
                name === "tahun_selesai_bekerja" ||
                name === "periode_kerja_awal" ||
                name === "periode_kerja_akhir"
            ) {
                const tahunMulai =
                    name === "tahun_mulai_bekerja"
                        ? nextValue
                        : name === "periode_kerja_awal"
                        ? getYearFromDate(nextValue)
                        : updatedForm.tahun_mulai_bekerja;

                const tahunSelesai =
                    name === "tahun_selesai_bekerja"
                        ? nextValue
                        : name === "periode_kerja_akhir"
                        ? getYearFromDate(nextValue)
                        : updatedForm.tahun_selesai_bekerja;

                updatedForm.lama_bekerja = hitungLamaBekerja(
                    tahunMulai,
                    tahunSelesai
                );
            }

            return updatedForm;
        });

        setErrors((prevErrors) => {
            const updatedErrors = { ...prevErrors };

            if (!isEmpty(value)) {
                delete updatedErrors[name];

                if (name === "alamat_domisili" || name === "alamat") {
                    delete updatedErrors.alamat;
                    delete updatedErrors.alamat_domisili;
                }

                if (name === "alamat_ktp") {
                    delete updatedErrors.alamat_ktp;
                }

                if (
                    name === "status_pernikahan_id" ||
                    name === "status_perkawinan"
                ) {
                    delete updatedErrors.status_pernikahan_id;
                    delete updatedErrors.status_perkawinan;
                }

                if (name === "provinsi_id" || name === "provinsi") {
                    delete updatedErrors.provinsi_id;
                    delete updatedErrors.provinsi;
                }

                if (name === "kabupaten_id" || name === "kabupaten") {
                    delete updatedErrors.kabupaten_id;
                    delete updatedErrors.kabupaten;
                }

                if (name === "kecamatan_id" || name === "kecamatan") {
                    delete updatedErrors.kecamatan_id;
                    delete updatedErrors.kecamatan;
                }

                if (name === "kelurahan_id" || name === "kelurahan") {
                    delete updatedErrors.kelurahan_id;
                    delete updatedErrors.kelurahan;
                }

                if (name === "hubungan_kerabat_instansi") {
                    delete updatedErrors.hubungan_kerabat_instansi;
                }

                if (
                    name === "opsi_kacamata_id" ||
                    name === "golongan_darah" ||
                    name === "gol_darah"
                ) {
                    delete updatedErrors.opsi_kacamata_id;
                    delete updatedErrors.golongan_darah;
                    delete updatedErrors.gol_darah;
                }
            }

            if (name === "status_pekerjaan" && value === "Belum Bekerja") {
                delete updatedErrors.nama_perusahaan;
                delete updatedErrors.posisi_pekerjaan;
                delete updatedErrors.posisi_pekerjaan_terakhir;
                delete updatedErrors.bidang_pekerjaan;
                delete updatedErrors.lokasi_perusahaan;
                delete updatedErrors.tahun_mulai_bekerja;
                delete updatedErrors.tahun_selesai_bekerja;
                delete updatedErrors.periode_kerja_awal;
                delete updatedErrors.periode_kerja_akhir;
                delete updatedErrors.lama_bekerja;
                delete updatedErrors.deskripsi_pekerjaan;
                delete updatedErrors.alasan_berhenti;
                delete updatedErrors.gaji_terakhir;
                delete updatedErrors.keahlian;
                delete updatedErrors.catatan_pekerjaan;
            }

            if (
                name === "tahun_mulai_bekerja" ||
                name === "tahun_selesai_bekerja" ||
                name === "periode_kerja_awal" ||
                name === "periode_kerja_akhir"
            ) {
                delete updatedErrors.lama_bekerja;
                delete updatedErrors.tahun_mulai_bekerja;
                delete updatedErrors.tahun_selesai_bekerja;
                delete updatedErrors.periode_kerja_awal;
                delete updatedErrors.periode_kerja_akhir;
            }

            if (
                name === "kapan_siap_bekerja" ||
                name === "ekpetasi_gaji" ||
                name === "penempatan" ||
                name === "proses_bkhang" ||
                name === "dapat_dipertanggung_jawabkan"
            ) {
                delete updatedErrors.kapan_siap_bekerja;
                delete updatedErrors.tanggal_siap_kerja;
                delete updatedErrors.ekpetasi_gaji;
                delete updatedErrors.gaji_diharapkan;
                delete updatedErrors.penempatan;
                delete updatedErrors.proses_bkhang;
                delete updatedErrors.background_checking;
                delete updatedErrors.dapat_dipertanggung_jawabkan;
                delete updatedErrors.pernyataan_data_benar;
            }

            return updatedErrors;
        });
    };

    const handleArrayChange = (field, index, name, value) => {
        setForm((prevForm) => {
            const list = Array.isArray(prevForm[field])
                ? [...prevForm[field]]
                : [];

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
                delete updatedErrors[`${field}.${index}.${name}`];

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
            [field]: [
                ...(Array.isArray(prevForm[field]) ? prevForm[field] : []),
                { ...template },
            ],
        }));
    };

    const removeArrayItem = (field, index, template) => {
        setForm((prevForm) => {
            const list = Array.isArray(prevForm[field]) ? prevForm[field] : [];

            const nextItems = list.filter(
                (_, itemIndex) => itemIndex !== index
            );

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

        const token =
            cekTahapanForm.token || form.token || getInitialTokenFromPage();

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
        setErrors({});
        return true;
    };

    const saveCurrentStep = async () => {
        if (step === 1) {
            return await saveDataDiri();
        }

        if (step === 2) {
            return await saveRiwayatKeluarga();
        }

        if (step === 3) {
            return await saveRiwayatKesehatan();
        }

        if (step === 4) {
            return await saveRiwayatPekerjaan();
        }

        if (step === 5) {
            return await saveKesiapanBekerja();
        }

        return null;
    };

    const nextStep = async () => {
        if (!validateStep()) {
            return;
        }

        if (step >= steps.length) {
            return;
        }

        setLoadingSubmit(true);

        try {
            const savedPelamar = await saveCurrentStep();
            syncProgressFromPelamarOrForm(savedPelamar);

            setStep((currentStep) => currentStep + 1);

            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        } catch (error) {
            console.error("Gagal menyimpan data step:", error);
            showErrorNotification(error, "Gagal menyimpan data step ini.");
        } finally {
            setLoadingSubmit(false);
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
            const savedPelamar = await saveCurrentStep();
            syncProgressFromPelamarOrForm(savedPelamar);
            setNotification({ type: "success", title: "Data Tersimpan", message: "Data pendaftaran berhasil diperbarui.", details: [] });
        } catch (error) {
            console.error("Gagal memperbarui data:", error);
            showErrorNotification(error, "Terjadi kesalahan saat memperbarui data.");
        } finally {
            setLoadingSubmit(false);
        }
    };

    const goToStep = async (selectedStep) => {
        if (selectedStep === step || loadingSubmit) {
            return;
        }

        if (selectedStep <= progressStep) {
            setErrors({});
            setStep(selectedStep);

            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });

            return;
        }

        if (!validateStep()) {
            return;
        }

        setLoadingSubmit(true);

        try {
            const savedPelamar = await saveCurrentStep();
            syncProgressFromPelamarOrForm(savedPelamar);
        } catch (error) {
            console.error("Gagal menyimpan data step:", error);
            showErrorNotification(error, "Gagal menyimpan data step ini.");
            setLoadingSubmit(false);
            return;
        }

        setLoadingSubmit(false);
        setErrors({});
        setStep(selectedStep);

        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    };

    const getStepDataDiriForm = () => {
        const statusPernikahan =
            form.status_pernikahan_id ||
            form.status_perkawinan ||
            "";

        const alamatDomisili =
            form.alamat_domisili ||
            form.alamat ||
            "";

        const provinsiId = form.provinsi_id || form.provinsi || "";
        const kabupatenId = form.kabupaten_id || form.kabupaten || "";
        const kecamatanId = form.kecamatan_id || form.kecamatan || "";
        const kelurahanId = form.kelurahan_id || form.kelurahan || "";

        return {
            ...form,

            posisi_dilamar: form.posisi_dilamar_label || "",
            perusahaan_dilamar:
                form.perusahaan_dilamar_label ||
                form.perusahaan_dilamar ||
                "",

            alamat_domisili: alamatDomisili,
            alamat: alamatDomisili,

            status_pernikahan_id: statusPernikahan,
            status_perkawinan: statusPernikahan,

            provinsi_id: provinsiId,
            kabupaten_id: kabupatenId,
            kecamatan_id: kecamatanId,
            kelurahan_id: kelurahanId,

            provinsi: provinsiId,
            kabupaten: kabupatenId,
            kecamatan: kecamatanId,
            kelurahan: kelurahanId,

            sosial_media:
                Array.isArray(form.sosial_media) && form.sosial_media.length > 0
                    ? form.sosial_media
                    : [{ ...emptySosialMedia }],
        };
    };

    const renderStep = () => {
        if (step === 1) {
            return (
                <StepDataDiri
                    form={getStepDataDiriForm()}
                    handleChange={handleChange}
                    errors={errors}
                    handleSosialMediaChange={handleSosialMediaChange}
                    addSosialMedia={addSosialMedia}
                    removeSosialMedia={removeSosialMedia}
                    pelamarAktif={pelamarAktif}
                    masterOptions={masterOptions}
                />
            );
        }

        if (step === 2) {
            return (
                <StepRiwayatKeluarga
                    form={form}
                    errors={errors}
                    handleChange={handleChange}
                    handleKontakDaruratChange={(index, name, value) =>
                        handleArrayChange(
                            "kontak_darurat",
                            index,
                            name,
                            value
                        )
                    }
                    addKontakDarurat={() =>
                        addArrayItem("kontak_darurat", emptyKontakDarurat)
                    }
                    removeKontakDarurat={(index) =>
                        removeArrayItem(
                            "kontak_darurat",
                            index,
                            emptyKontakDarurat
                        )
                    }
                    handleSaudaraKandungChange={(index, name, value) =>
                        handleArrayChange(
                            "saudara_kandung",
                            index,
                            name,
                            value
                        )
                    }
                    addSaudaraKandung={() =>
                        addArrayItem("saudara_kandung", emptySaudara)
                    }
                    removeSaudaraKandung={(index) =>
                        removeArrayItem(
                            "saudara_kandung",
                            index,
                            emptySaudara
                        )
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
                    handleChange={handleChange}
                    masterOptions={masterOptions}
                />
            );
        }

        if (step === 4) {
            return (
                <StepRiwayatPekerjaan
                    form={form}
                    errors={errors}
                    handleChange={handleChange}
                />
            );
        }

        if (step === 5) {
            return (
                <StepKesiapanBekerja
                    form={form}
                    errors={errors}
                    handleChange={handleChange}
                />
            );
        }

        return null;
    };

    if (activePage === "cek-tahapan") {
        return (
            <CekTahapanPelamar
                token={cekTahapanForm.token}
                form={cekTahapanForm}
                errors={cekTahapanErrors}
                hasil={hasilCekTahapan}
                loading={loadingToken}
                onChange={handleCekTahapanChange}
                onSubmit={handleCekTahapanSubmit}
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
                                        Lengkapi data pribadi, keluarga,
                                        kesehatan, pengalaman kerja, dan kesiapan
                                        bekerja secara bertahap.
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
                                                Langkah {progressStep} dari{" "}
                                                {steps.length}
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
                                    completedStep={progressStep}
                                    setActiveStep={goToStep}
                                />

                                <div className="mt-8 rounded-3xl border border-white/10 bg-white/10 p-5 shadow-lg backdrop-blur">
                                    <div className="flex items-start gap-3">
                                        <div className="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-300 text-lg font-black text-slate-950">!</div>

                                        <div>
                                            <h3 className="font-bold text-white">
                                                Panduan Pengisian
                                            </h3>
                                            <p className="mt-1 text-sm leading-6 text-slate-200">
                                                Isi data sesuai kondisi
                                                sebenarnya. Jika membuka link
                                                token, data awal akan terisi
                                                otomatis.
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
                                    {
                                        steps.find((item) => item.id === step)
                                            ?.title
                                    }
                                </h2>

                                <p className="mt-2 text-sm leading-6 text-slate-500">
                                    {
                                        steps.find((item) => item.id === step)
                                            ?.description
                                    }
                                </p>

                                {Object.keys(errors).length > 0 && (
                                    <div className="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
                                        Terjadi kesalahan saat menyimpan data.
                                        Silakan periksa kembali isian form.
                                    </div>
                                )}
                            </div>

                            <form onSubmit={handleSubmit} noValidate>
                                {renderStep()}

                                <div className="mt-8 flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-between">
                                    <button
                                        type="button"
                                        onClick={prevStep}
                                        disabled={step === 1 || loadingSubmit}
                                        className={`rounded-2xl px-6 py-3 text-sm font-bold transition ${
                                            step === 1 || loadingSubmit
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
                                            disabled={loadingSubmit}
                                            className="rounded-2xl bg-teal-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-teal-100 transition hover:bg-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-100 disabled:cursor-not-allowed disabled:opacity-60"
                                        >
                                            {loadingSubmit
                                                ? "Menyimpan..."
                                                : "Selanjutnya"}
                                        </button>
                                    ) : (
                                        <button
                                            type="submit"
                                            disabled={loadingSubmit}
                                            className="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-100 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:opacity-60"
                                        >
                                            {loadingSubmit
                                                ? "Menyimpan..."
                                                : "Simpan / Kirim Pendaftaran"}
                                        </button>
                                    )}
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>

            {notification && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="candidate-notification-title">
                    <div className="flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div className={`h-2 shrink-0 ${notification.type === "success" ? "bg-emerald-500" : "bg-gradient-to-r from-rose-500 to-red-600"}`} />
                        <div className="min-h-0 flex-1 overflow-y-auto p-6 sm:p-8">
                            <div className="flex items-start gap-4">
                                <div className={`flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-2xl font-black ${notification.type === "success" ? "bg-emerald-50 text-emerald-600" : "bg-rose-50 text-rose-600"}`}>
                                    {notification.type === "success" ? "✓" : "!"}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className={`text-xs font-black uppercase tracking-[0.16em] ${notification.type === "success" ? "text-emerald-600" : "text-rose-600"}`}>
                                        {notification.type === "success" ? "Berhasil" : "Perlu diperbaiki"}
                                    </p>
                                    <h2 id="candidate-notification-title" className="mt-1 text-2xl font-black text-slate-950">{notification.title}</h2>
                                    <p className="mt-2 text-sm font-medium leading-6 text-slate-600">{notification.message}</p>
                                </div>
                            </div>

                            {notification.details?.length > 0 && (
                                <div className="mt-6 rounded-2xl border border-rose-100 bg-rose-50 p-4">
                                    <p className="text-sm font-black text-rose-800">Silakan periksa isian berikut:</p>
                                    <ul className="mt-3 space-y-2">
                                        {notification.details.map((detail, index) => (
                                            <li key={`${detail}-${index}`} className="flex gap-3 text-sm font-semibold leading-5 text-rose-700">
                                                <span className="mt-1 h-2 w-2 shrink-0 rounded-full bg-rose-500" />
                                                <span>{detail}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </div>
                        <div className="shrink-0 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:px-8">
                            <button type="button" autoFocus onClick={() => setNotification(null)} className={`w-full rounded-2xl px-6 py-3 text-sm font-black text-white shadow-lg transition ${notification.type === "success" ? "bg-emerald-600 shadow-emerald-100 hover:bg-emerald-700" : "bg-slate-900 shadow-slate-200 hover:bg-slate-800"}`}>
                                {notification.type === "success" ? "Lanjutkan" : "Periksa Form"}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </main>
    );
}

function StepMenu({ steps, activeStep, completedStep = 1, setActiveStep }) {
    return (
        <div className="space-y-3">
            {steps.map((item) => {
                const active = item.id === activeStep;
                const complete = item.id <= completedStep && item.id !== activeStep;

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
                            <h4 className="font-black text-white">
                                {item.title}
                            </h4>
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
