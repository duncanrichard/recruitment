import React from "react";
import Select2 from "react-select";

const CONTROLLER_REQUIRED_FIELDS = [
    // Sesuai validasi PendaftaranController::updateDataDiriByToken
    // Field required di controller:
    // nama, nama_panggilan, email, no_hp, pendidikan,
    // tanggal_lahir, agama, alamat_ktp, alamat_domisili,
    // sosial_media minimal 1 data.
    "nama",
    "nama_lengkap",
    "nama_panggilan",
    "nama_panggil",
    "email",
    "no_hp",
    "no_wa",
    "pendidikan",
    "pendidikan_id",
    "tanggal_lahir",
    "agama",
    "agama_id",
    "alamat_ktp",
    "alamat_domisili",
    "alamat",
    "sosial_media",
    "sosial_media.*.platform",
    "sosial_media.*.nama_akun",
    "sosial_media.*.nama_account",
];

// Field ini hanya wajib jika posisi mewajibkan STR
// sesuai logic controller: $wajibStr ? 'required' : 'nullable'.
const CONDITIONAL_REQUIRED_FIELDS = ["str_aktif"];

export default function StepDataDiri({
    form,
    handleChange,
    errors = {},
    handleSosialMediaChange,
    addSosialMedia,
    removeSosialMedia,
    masterOptions = {},
}) {
    /*
     * Required mengikuti controller Laravel, bukan semua field di form.
     * requiredFields dari parent tidak dipakai sebagai sumber utama agar
     * tanda * tidak salah tampil untuk field yang nullable di controller.
     */
    const controllerRequiredFieldSet = React.useMemo(() => {
        return new Set(CONTROLLER_REQUIRED_FIELDS);
    }, []);

    const isRequired = React.useCallback(
        (...names) => {
            return names.some((name) => controllerRequiredFieldSet.has(name));
        },
        [controllerRequiredFieldSet]
    );

    const isConditionalRequired = React.useCallback(
        (...names) => {
            return names.some((name) => CONDITIONAL_REQUIRED_FIELDS.includes(name));
        },
        []
    );
    const posisiStrAktif = String(form.posisi_str_aktif || "").toLowerCase();
    const tampilkanStrAktif = posisiStrAktif === "active";

    const sosialMediaItems = Array.isArray(form.sosial_media)
        ? form.sosial_media
        : [];

    const sosialMediaError =
        getNestedError(errors, "sosial_media") ||
        getNestedError(errors, "sosial_media.0") ||
        null;

    const hasSosialMediaError = Boolean(sosialMediaError);

    const selectedProvinsi = form.provinsi_id || form.provinsi || "";
    const selectedKabupaten = form.kabupaten_id || form.kabupaten || "";
    const selectedKecamatan = form.kecamatan_id || form.kecamatan || "";
    const selectedKelurahan = form.kelurahan_id || form.kelurahan || "";

    const [wilayahOptions, setWilayahOptions] = React.useState({
        provinces: [],
        regencies: [],
        districts: [],
        villages: [],
    });

    const [wilayahLoading, setWilayahLoading] = React.useState({
        provinces: false,
        regencies: false,
        districts: false,
        villages: false,
    });

    const fetchWilayah = React.useCallback(async (url) => {
        try {
            const response = await fetch(url, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                return [];
            }

            return Array.isArray(result.data) ? result.data : [];
        } catch (error) {
            return [];
        }
    }, []);

    const updateField = React.useCallback(
        (name, value) => {
            handleChange({
                target: {
                    name,
                    value,
                },
            });
        },
        [handleChange]
    );

    React.useEffect(() => {
        let mounted = true;

        setWilayahLoading((prev) => ({
            ...prev,
            provinces: true,
        }));

        fetchWilayah("/pendaftaran/api/wilayah/provinces")
            .then((data) => {
                if (!mounted) return;

                setWilayahOptions((prev) => ({
                    ...prev,
                    provinces: data,
                }));
            })
            .finally(() => {
                if (!mounted) return;

                setWilayahLoading((prev) => ({
                    ...prev,
                    provinces: false,
                }));
            });

        return () => {
            mounted = false;
        };
    }, [fetchWilayah]);

    React.useEffect(() => {
        let mounted = true;

        if (!selectedProvinsi) {
            setWilayahOptions((prev) => ({
                ...prev,
                regencies: [],
                districts: [],
                villages: [],
            }));

            return () => {
                mounted = false;
            };
        }

        setWilayahLoading((prev) => ({
            ...prev,
            regencies: true,
        }));

        fetchWilayah(
            `/pendaftaran/api/wilayah/regencies/${encodeURIComponent(
                selectedProvinsi
            )}`
        )
            .then((data) => {
                if (!mounted) return;

                setWilayahOptions((prev) => ({
                    ...prev,
                    regencies: data,
                }));
            })
            .finally(() => {
                if (!mounted) return;

                setWilayahLoading((prev) => ({
                    ...prev,
                    regencies: false,
                }));
            });

        return () => {
            mounted = false;
        };
    }, [selectedProvinsi, fetchWilayah]);

    React.useEffect(() => {
        let mounted = true;

        if (!selectedKabupaten) {
            setWilayahOptions((prev) => ({
                ...prev,
                districts: [],
                villages: [],
            }));

            return () => {
                mounted = false;
            };
        }

        setWilayahLoading((prev) => ({
            ...prev,
            districts: true,
        }));

        fetchWilayah(
            `/pendaftaran/api/wilayah/districts/${encodeURIComponent(
                selectedKabupaten
            )}`
        )
            .then((data) => {
                if (!mounted) return;

                setWilayahOptions((prev) => ({
                    ...prev,
                    districts: data,
                }));
            })
            .finally(() => {
                if (!mounted) return;

                setWilayahLoading((prev) => ({
                    ...prev,
                    districts: false,
                }));
            });

        return () => {
            mounted = false;
        };
    }, [selectedKabupaten, fetchWilayah]);

    React.useEffect(() => {
        let mounted = true;

        if (!selectedKecamatan) {
            setWilayahOptions((prev) => ({
                ...prev,
                villages: [],
            }));

            return () => {
                mounted = false;
            };
        }

        setWilayahLoading((prev) => ({
            ...prev,
            villages: true,
        }));

        fetchWilayah(
            `/pendaftaran/api/wilayah/villages/${encodeURIComponent(
                selectedKecamatan
            )}`
        )
            .then((data) => {
                if (!mounted) return;

                setWilayahOptions((prev) => ({
                    ...prev,
                    villages: data,
                }));
            })
            .finally(() => {
                if (!mounted) return;

                setWilayahLoading((prev) => ({
                    ...prev,
                    villages: false,
                }));
            });

        return () => {
            mounted = false;
        };
    }, [selectedKecamatan, fetchWilayah]);

    const handleWilayahChange = (event) => {
        const { name, value } = event.target;

        updateField(name, value);

        if (name === "provinsi_id") {
            updateField("provinsi", value);

            updateField("kabupaten_id", "");
            updateField("kabupaten", "");

            updateField("kecamatan_id", "");
            updateField("kecamatan", "");

            updateField("kelurahan_id", "");
            updateField("kelurahan", "");

            setWilayahOptions((prev) => ({
                ...prev,
                regencies: [],
                districts: [],
                villages: [],
            }));
        }

        if (name === "kabupaten_id") {
            updateField("kabupaten", value);

            updateField("kecamatan_id", "");
            updateField("kecamatan", "");

            updateField("kelurahan_id", "");
            updateField("kelurahan", "");

            setWilayahOptions((prev) => ({
                ...prev,
                districts: [],
                villages: [],
            }));
        }

        if (name === "kecamatan_id") {
            updateField("kecamatan", value);

            updateField("kelurahan_id", "");
            updateField("kelurahan", "");

            setWilayahOptions((prev) => ({
                ...prev,
                villages: [],
            }));
        }

        if (name === "kelurahan_id") {
            updateField("kelurahan", value);
        }
    };

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                <h3 className="text-base font-bold text-blue-800">
                    Data Diri
                </h3>

                <p className="mt-1 text-sm text-blue-600">
                    Lengkapi informasi pribadi sesuai identitas resmi. Field
                    bertanda <span className="font-bold text-red-500">*</span>{" "}
                    wajib diisi sesuai validasi sistem.
                </p>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 className="mb-4 text-sm font-bold uppercase tracking-wide text-slate-700">
                    Informasi Lamaran
                </h3>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Posisi yang Dilamar"
                        name="posisi_dilamar_label"
                        value={form.posisi_dilamar_label || ""}
                        onChange={() => {}}
                        placeholder="Posisi yang dilamar"
                        required={isRequired("posisi_dilamar", "posisi_yang_dilamar")}
                        error={errors.posisi_dilamar}
                        disabled
                    />

                    <Input
                        label="Perusahaan yang Dilamar"
                        name="perusahaan_dilamar_label"
                        value={form.perusahaan_dilamar_label || ""}
                        onChange={() => {}}
                        placeholder="Perusahaan yang dilamar"
                        required={isRequired("perusahaan_dilamar")}
                        error={errors.perusahaan_dilamar}
                        disabled
                    />
                </div>

                <p className="mt-3 text-xs font-medium text-slate-500">
                    Posisi dan perusahaan mengikuti link pendaftaran yang
                    diberikan HR. Data yang disimpan tetap memakai UUID relasi.
                </p>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 className="mb-4 text-sm font-bold uppercase tracking-wide text-slate-700">
                    Identitas Pribadi
                </h3>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Lengkap"
                        name="nama"
                        value={form.nama}
                        onChange={handleChange}
                        placeholder="Masukkan nama lengkap"
                        required={isRequired("nama", "nama_lengkap")}
                        error={errors.nama}
                    />

                    <Input
                        label="Nama Panggilan"
                        name="nama_panggilan"
                        value={form.nama_panggilan}
                        onChange={handleChange}
                        placeholder="Contoh: Rani"
                        required={isRequired("nama_panggilan", "nama_panggil")}
                        error={errors.nama_panggilan}
                    />

                    <SelectField
                        label="Pendidikan Terakhir"
                        name="pendidikan"
                        value={form.pendidikan}
                        onChange={handleChange}
                        required={isRequired("pendidikan", "pendidikan_id")}
                        error={errors.pendidikan}
                        options={masterOptions.pendidikan || []}
                    />

                    <Input
                        label="Jurusan"
                        name="jurusan"
                        value={form.jurusan}
                        onChange={handleChange}
                        placeholder="Contoh: Teknik Informatika"
                        required={isRequired("jurusan")}
                        error={errors.jurusan}
                    />

                    <Input
                        label="Nama Institusi"
                        name="nama_institusi"
                        value={form.nama_institusi}
                        onChange={handleChange}
                        placeholder="Contoh: Universitas Indonesia"
                        required={isRequired("nama_institusi")}
                        error={errors.nama_institusi}
                    />

                    <Input
                        label="Tempat Lahir"
                        name="tempat_lahir"
                        value={form.tempat_lahir}
                        onChange={handleChange}
                        placeholder="Contoh: Jakarta"
                        required={isRequired("tempat_lahir")}
                        error={errors.tempat_lahir}
                    />

                    <Input
                        label="Tanggal Lahir"
                        type="date"
                        name="tanggal_lahir"
                        value={form.tanggal_lahir}
                        onChange={handleChange}
                        required={isRequired("tanggal_lahir")}
                        error={errors.tanggal_lahir}
                        onlyPicker
                    />

                    <SelectField
                        label="Jenis Kelamin"
                        name="jenis_kelamin"
                        value={form.jenis_kelamin}
                        onChange={handleChange}
                        required={isRequired("jenis_kelamin")}
                        error={errors.jenis_kelamin}
                        options={masterOptions.jenis_kelamin || []}
                    />

                    <SelectField
                        label="Agama"
                        name="agama"
                        value={form.agama}
                        onChange={handleChange}
                        required={isRequired("agama", "agama_id")}
                        error={errors.agama}
                        options={masterOptions.agama || []}
                    />

                    <SelectField
                        label="Status Perkawinan"
                        name="status_pernikahan_id"
                        value={
                            form.status_pernikahan_id ||
                            form.status_perkawinan ||
                            ""
                        }
                        onChange={handleChange}
                        required={
                            isRequired("status_pernikahan_id") ||
                            isRequired("status_perkawinan")
                        }
                        error={
                            errors.status_pernikahan_id ||
                            errors.status_perkawinan
                        }
                        options={masterOptions.status_pernikahan || []}
                    />

                    <SelectField
                        label="Kewarganegaraan"
                        name="kewarganegaraan"
                        value={form.kewarganegaraan}
                        onChange={handleChange}
                        required={isRequired("kewarganegaraan", "kewarganegaraan_id")}
                        error={errors.kewarganegaraan}
                        options={masterOptions.kewarganegaraan || []}
                    />

                    {tampilkanStrAktif && (
                        <SelectField
                            label="STR Aktif"
                            name="str_aktif"
                            value={form.str_aktif}
                            onChange={handleChange}
                            required={
                                isRequired("str_aktif") ||
                                (isConditionalRequired("str_aktif") && tampilkanStrAktif)
                            }
                            error={errors.str_aktif}
                            options={masterOptions.str_aktif || []}
                        />
                    )}
                </div>
            </div>

            <div
                className={`rounded-2xl border bg-white p-5 shadow-sm ${
                    hasSosialMediaError
                        ? "border-red-300"
                        : "border-slate-200"
                }`}
            >
                <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 className="text-sm font-bold uppercase tracking-wide text-slate-700">
                            Sosial Media
                            <span className="ml-1 text-red-500">*</span>
                        </h3>

                        <p className="mt-1 text-xs text-slate-500">
                            Wajib isi minimal 1 sosial media. Pilih platform,
                            lalu masukkan nama akun.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={addSosialMedia}
                        className="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700"
                    >
                        + Tambah Sosial Media
                    </button>
                </div>

                {sosialMediaItems.length === 0 && (
                    <div
                        className={`rounded-2xl border border-dashed px-4 py-6 text-center ${
                            hasSosialMediaError
                                ? "border-red-300 bg-red-50"
                                : "border-slate-300 bg-slate-50"
                        }`}
                    >
                        <p
                            className={`text-sm font-semibold ${
                                hasSosialMediaError
                                    ? "text-red-600"
                                    : "text-slate-500"
                            }`}
                        >
                            {sosialMediaError || "Belum ada sosial media."}
                        </p>

                        <button
                            type="button"
                            onClick={addSosialMedia}
                            className="mt-3 inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700"
                        >
                            Tambah Sosial Media
                        </button>
                    </div>
                )}

                <div className="space-y-4">
                    {sosialMediaItems.map((item, index) => {
                        const platformValue = item?.platform ?? "";
                        const namaAkunValue =
                            item?.nama_akun ?? item?.nama_account ?? "";

                        return (
                            <div
                                key={item?.id || index}
                                className="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <div className="mb-3 flex items-center justify-between gap-3">
                                    <h4 className="text-sm font-bold text-slate-700">
                                        Sosial Media {index + 1}
                                    </h4>

                                    <button
                                        type="button"
                                        onClick={() =>
                                            removeSosialMedia(index)
                                        }
                                        className="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-100"
                                    >
                                        Hapus
                                    </button>
                                </div>

                                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <SelectField
                                        label="Platform"
                                        name={`sosial_media.${index}.platform`}
                                        value={platformValue}
                                        onChange={(event) =>
                                            handleSosialMediaChange(
                                                index,
                                                "platform",
                                                event.target.value
                                            )
                                        }
                                        options={
                                            masterOptions.sosial_media || []
                                        }
                                        placeholder="Pilih platform"
                                        required
                                        error={getNestedError(
                                            errors,
                                            `sosial_media.${index}.platform`
                                        )}
                                    />

                                    <Input
                                        label="Nama Akun"
                                        name={`sosial_media.${index}.nama_akun`}
                                        value={namaAkunValue}
                                        onChange={(event) =>
                                            handleSosialMediaChange(
                                                index,
                                                "nama_akun",
                                                event.target.value
                                            )
                                        }
                                        placeholder="Contoh: @namaakun"
                                        required
                                        error={
                                            getNestedError(
                                                errors,
                                                `sosial_media.${index}.nama_akun`
                                            ) ||
                                            getNestedError(
                                                errors,
                                                `sosial_media.${index}.nama_account`
                                            )
                                        }
                                    />
                                </div>
                            </div>
                        );
                    })}
                </div>

                {sosialMediaItems.length > 0 && sosialMediaError && (
                    <p className="mt-3 text-xs font-semibold text-red-500">
                        {sosialMediaError}
                    </p>
                )}
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 className="mb-4 text-sm font-bold uppercase tracking-wide text-slate-700">
                    Kontak
                </h3>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Email"
                        type="email"
                        name="email"
                        value={form.email}
                        onChange={handleChange}
                        placeholder="nama@email.com"
                        required={isRequired("email")}
                        error={errors.email}
                    />

                    <Input
                        label="Nomor HP"
                        type="tel"
                        name="no_hp"
                        value={form.no_hp}
                        onChange={handleChange}
                        placeholder="08xxxxxxxxxx"
                        required={isRequired("no_hp", "no_wa")}
                        error={errors.no_hp}
                    />
                </div>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 className="mb-4 text-sm font-bold uppercase tracking-wide text-slate-700">
                    Alamat
                </h3>

                <Textarea
                    label="Alamat KTP"
                    name="alamat_ktp"
                    value={form.alamat_ktp ?? ""}
                    onChange={handleChange}
                    placeholder="Masukkan alamat sesuai KTP"
                    required={isRequired("alamat_ktp")}
                    error={errors.alamat_ktp}
                    rows={4}
                />

                <div className="mt-5">
                    <Textarea
                        label="Alamat Domisili"
                        name="alamat_domisili"
                        value={form.alamat_domisili ?? form.alamat ?? ""}
                        onChange={handleChange}
                        placeholder="Masukkan alamat domisili"
                        required={isRequired("alamat_domisili")}
                        error={errors.alamat_domisili ?? errors.alamat}
                        rows={4}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <SelectField
                        label="Provinsi"
                        name="provinsi_id"
                        value={selectedProvinsi}
                        onChange={handleWilayahChange}
                        placeholder={
                            wilayahLoading.provinces
                                ? "Memuat provinsi..."
                                : "Pilih provinsi"
                        }
                        required={isRequired("provinsi_id") || isRequired("provinsi")}
                        error={errors.provinsi_id || errors.provinsi}
                        options={wilayahOptions.provinces}
                        disabled={wilayahLoading.provinces}
                        isLoading={wilayahLoading.provinces}
                    />

                    <SelectField
                        label="Kabupaten / Kota"
                        name="kabupaten_id"
                        value={selectedKabupaten}
                        onChange={handleWilayahChange}
                        placeholder={
                            !selectedProvinsi
                                ? "Pilih provinsi dulu"
                                : wilayahLoading.regencies
                                ? "Memuat kabupaten / kota..."
                                : "Pilih kabupaten / kota"
                        }
                        required={isRequired("kabupaten_id") || isRequired("kabupaten")}
                        error={errors.kabupaten_id || errors.kabupaten}
                        options={wilayahOptions.regencies}
                        disabled={!selectedProvinsi || wilayahLoading.regencies}
                        isLoading={wilayahLoading.regencies}
                    />

                    <SelectField
                        label="Kecamatan"
                        name="kecamatan_id"
                        value={selectedKecamatan}
                        onChange={handleWilayahChange}
                        placeholder={
                            !selectedKabupaten
                                ? "Pilih kabupaten / kota dulu"
                                : wilayahLoading.districts
                                ? "Memuat kecamatan..."
                                : "Pilih kecamatan"
                        }
                        required={isRequired("kecamatan_id") || isRequired("kecamatan")}
                        error={errors.kecamatan_id || errors.kecamatan}
                        options={wilayahOptions.districts}
                        disabled={!selectedKabupaten || wilayahLoading.districts}
                        isLoading={wilayahLoading.districts}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <SelectField
                        label="Kelurahan / Desa"
                        name="kelurahan_id"
                        value={selectedKelurahan}
                        onChange={handleWilayahChange}
                        placeholder={
                            !selectedKecamatan
                                ? "Pilih kecamatan dulu"
                                : wilayahLoading.villages
                                ? "Memuat kelurahan / desa..."
                                : "Pilih kelurahan / desa"
                        }
                        required={isRequired("kelurahan_id") || isRequired("kelurahan")}
                        error={errors.kelurahan_id || errors.kelurahan}
                        options={wilayahOptions.villages}
                        disabled={!selectedKecamatan || wilayahLoading.villages}
                        isLoading={wilayahLoading.villages}
                    />
                </div>
            </div>
        </div>
    );
}

function getNestedError(errors, path) {
    if (!errors || !path) return null;

    if (errors[path]) {
        return Array.isArray(errors[path]) ? errors[path][0] : errors[path];
    }

    const normalizedPath = path.replace(/\.(\d+)\./g, ".$1.");

    if (errors[normalizedPath]) {
        return Array.isArray(errors[normalizedPath])
            ? errors[normalizedPath][0]
            : errors[normalizedPath];
    }

    return null;
}

function normalizeOptions(options = []) {
    if (!Array.isArray(options)) {
        return [];
    }

    return options
        .map((item) => {
            if (typeof item === "string") {
                return {
                    value: item,
                    label: item,
                    id: item,
                };
            }

            const value =
                item?.value ??
                item?.id ??
                item?.code ??
                item?.kode ??
                item?.platform ??
                item?.label ??
                "";

            const label =
                item?.label ??
                item?.name ??
                item?.nama ??
                item?.platform ??
                item?.pendidikan ??
                item?.agama ??
                item?.status_pernikahan ??
                item?.kewarganegaraan ??
                item?.informasi ??
                value;

            return {
                ...item,
                value: String(value ?? ""),
                label: String(label ?? ""),
                id: item?.id ?? value,
            };
        })
        .filter((item) => item.value !== "" && item.label !== "");
}

function FieldLabel({ label, required }) {
    return (
        <label className="mb-2 block text-sm font-semibold text-slate-700">
            {label}
            {required && <span className="ml-1 text-red-500">*</span>}
        </label>
    );
}

function ErrorMessage({ message }) {
    if (!message) return null;

    return <p className="mt-2 text-xs font-semibold text-red-500">{message}</p>;
}

function fieldClass(error, type = "text", disabled = false) {
    return `w-full rounded-xl border px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 ${
        type === "date" ? "cursor-pointer pr-12" : ""
    } ${
        disabled
            ? "cursor-not-allowed border-slate-200 bg-slate-100 text-slate-600"
            : error
            ? "border-red-300 bg-red-50 focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-100"
            : "border-slate-300 bg-white focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
    }`;
}

function Input({
    label,
    type = "text",
    name,
    value,
    onChange,
    placeholder,
    required = false,
    error,
    onlyPicker = false,
    disabled = false,
}) {
    const inputValue = value ?? "";
    const inputRef = React.useRef(null);

    const openDatePicker = () => {
        if (disabled || type !== "date") return;

        const input = inputRef.current;

        if (!input) return;

        input.focus();

        if (typeof input.showPicker === "function") {
            try {
                input.showPicker();
            } catch (error) {
                // fallback browser
            }
        }
    };

    const handleKeyDown = (event) => {
        if (disabled) {
            event.preventDefault();
            return;
        }

        if (onlyPicker && type === "date") {
            const allowedKeys = [
                "Tab",
                "Shift",
                "Escape",
                "ArrowLeft",
                "ArrowRight",
                "ArrowUp",
                "ArrowDown",
            ];

            if (!allowedKeys.includes(event.key)) {
                event.preventDefault();
            }
        }
    };

    return (
        <div className="min-w-0">
            <FieldLabel label={label} required={required} />

            <div className="relative">
                <input
                    ref={inputRef}
                    type={type}
                    name={name}
                    value={inputValue}
                    onChange={onChange}
                    onClick={type === "date" ? openDatePicker : undefined}
                    onKeyDown={handleKeyDown}
                    placeholder={type === "date" ? undefined : placeholder}
                    className={fieldClass(error, type, disabled)}
                    autoComplete="off"
                    disabled={disabled}
                    readOnly={disabled}
                    required={required && !disabled}
                    aria-required={required}
                />

                {type === "date" && !disabled && (
                    <button
                        type="button"
                        onClick={openDatePicker}
                        className="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-blue-50 text-blue-700 transition hover:bg-blue-100"
                        aria-label="Pilih tanggal"
                    >
                        📅
                    </button>
                )}
            </div>

            <ErrorMessage message={error} />
        </div>
    );
}

function SelectField({
    label,
    name,
    value,
    onChange,
    options = [],
    required = false,
    error,
    placeholder,
    disabled = false,
    isLoading = false,
}) {
    const cleanValue = value ?? "";
    const normalizedOptions = normalizeOptions(options);

    const selectedOption =
        normalizedOptions.find(
            (item) => String(item.value) === String(cleanValue)
        ) ||
        normalizedOptions.find(
            (item) => String(item.id) === String(cleanValue)
        ) ||
        normalizedOptions.find(
            (item) => String(item.code) === String(cleanValue)
        ) ||
        normalizedOptions.find(
            (item) => String(item.label) === String(cleanValue)
        ) ||
        null;

    const handleSelectChange = (selected) => {
        onChange({
            target: {
                name,
                value: selected ? selected.value : "",
            },
        });
    };

    return (
        <div className="min-w-0">
            <FieldLabel label={label} required={required} />

            <Select2
                inputId={name}
                name={name}
                value={selectedOption}
                onChange={handleSelectChange}
                options={normalizedOptions}
                isClearable
                isSearchable
                isDisabled={disabled}
                isLoading={isLoading}
                required={required}
                aria-required={required}
                placeholder={placeholder || `Pilih ${label.toLowerCase()}`}
                noOptionsMessage={() =>
                    isLoading ? "Memuat data..." : "Data tidak ditemukan"
                }
                loadingMessage={() => "Memuat data..."}
                classNamePrefix="select2"
                menuPortalTarget={
                    typeof document !== "undefined" ? document.body : null
                }
                styles={{
                    control: (base, state) => ({
                        ...base,
                        minHeight: "46px",
                        borderRadius: "0.75rem",
                        borderColor: error
                            ? "#fca5a5"
                            : state.isFocused
                            ? "#3b82f6"
                            : "#cbd5e1",
                        backgroundColor: disabled
                            ? "#f1f5f9"
                            : error
                            ? "#fef2f2"
                            : "#ffffff",
                        boxShadow: state.isFocused
                            ? error
                                ? "0 0 0 4px #fee2e2"
                                : "0 0 0 4px #dbeafe"
                            : "0 1px 2px 0 rgb(0 0 0 / 0.05)",
                        cursor: disabled ? "not-allowed" : "default",
                        "&:hover": {
                            borderColor: error ? "#ef4444" : "#3b82f6",
                        },
                    }),
                    valueContainer: (base) => ({
                        ...base,
                        padding: "0 14px",
                    }),
                    input: (base) => ({
                        ...base,
                        color: "#0f172a",
                        fontSize: "0.875rem",
                    }),
                    singleValue: (base) => ({
                        ...base,
                        color: "#0f172a",
                        fontSize: "0.875rem",
                    }),
                    placeholder: (base) => ({
                        ...base,
                        color: "#94a3b8",
                        fontSize: "0.875rem",
                    }),
                    indicatorSeparator: () => ({
                        display: "none",
                    }),
                    dropdownIndicator: (base, state) => ({
                        ...base,
                        color: state.isFocused ? "#2563eb" : "#64748b",
                        "&:hover": {
                            color: "#2563eb",
                        },
                    }),
                    clearIndicator: (base) => ({
                        ...base,
                        color: "#94a3b8",
                        "&:hover": {
                            color: "#ef4444",
                        },
                    }),
                    menu: (base) => ({
                        ...base,
                        zIndex: 9999,
                        borderRadius: "0.75rem",
                        overflow: "hidden",
                        boxShadow:
                            "0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)",
                    }),
                    menuPortal: (base) => ({
                        ...base,
                        zIndex: 9999,
                    }),
                    option: (base, state) => ({
                        ...base,
                        fontSize: "0.875rem",
                        cursor: "pointer",
                        backgroundColor: state.isSelected
                            ? "#2563eb"
                            : state.isFocused
                            ? "#eff6ff"
                            : "#ffffff",
                        color: state.isSelected ? "#ffffff" : "#0f172a",
                        "&:active": {
                            backgroundColor: "#dbeafe",
                        },
                    }),
                }}
            />

            <ErrorMessage message={error} />
        </div>
    );
}

function Textarea({
    label,
    name,
    value,
    onChange,
    placeholder,
    rows = 3,
    required = false,
    error,
}) {
    return (
        <div className="min-w-0">
            <FieldLabel label={label} required={required} />

            <textarea
                name={name}
                value={value ?? ""}
                onChange={onChange}
                rows={rows}
                placeholder={placeholder}
                required={required}
                aria-required={required}
                className={`${fieldClass(error)} resize-none`}
            />

            <ErrorMessage message={error} />
        </div>
    );
}