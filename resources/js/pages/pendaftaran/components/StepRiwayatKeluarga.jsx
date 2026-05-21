import React from "react";

const HUBUNGAN_KERABAT_INSTANSI_OPTIONS = [
    "TNI",
    "KEPOLISIAN",
    "ASN",
    "BUMN",
    "KESEHATAN",
    "PEMKOT/PEMPROV",
    "TIDAK ADA HUBUNGAN KEKERABATAN DENGAN INSTANSI",
];

const TIDAK_ADA_HUBUNGAN = "TIDAK ADA HUBUNGAN KEKERABATAN DENGAN INSTANSI";

export default function StepRiwayatKeluarga({
    form,
    errors = {},
    handleChange,

    handleKontakDaruratChange,
    addKontakDarurat,
    removeKontakDarurat,

    handleSaudaraKandungChange,
    addSaudaraKandung,
    removeSaudaraKandung,

    handleSaudaraIparChange,
    addSaudaraIpar,
    removeSaudaraIpar,
}) {
    const kontakDaruratList =
        Array.isArray(form.kontak_darurat) && form.kontak_darurat.length > 0
            ? form.kontak_darurat
            : [{ nama: "", status: "", nomor: "" }];

    const saudaraKandungList =
        Array.isArray(form.saudara_kandung) && form.saudara_kandung.length > 0
            ? form.saudara_kandung
            : [
                  {
                      nama: "",
                      jenis_kelamin: "",
                      hubungan: "",
                      pekerjaan: "",
                      no_hp: "",
                      alamat: "",
                  },
              ];

    const saudaraIparList =
        Array.isArray(form.saudara_ipar) && form.saudara_ipar.length > 0
            ? form.saudara_ipar
            : [
                  {
                      nama: "",
                      jenis_kelamin: "",
                      hubungan: "",
                      pekerjaan: "",
                      no_hp: "",
                      alamat: "",
                  },
              ];

    const hubunganKerabatInstansi = normalizeCheckboxValue(
        form.hubungan_kerabat_instansi
    );

    const handleHubunganKerabatInstansiChange = (option) => {
        const currentValues = normalizeCheckboxValue(
            form.hubungan_kerabat_instansi
        );

        let nextValues = [];

        if (option === TIDAK_ADA_HUBUNGAN) {
            nextValues = currentValues.includes(TIDAK_ADA_HUBUNGAN)
                ? []
                : [TIDAK_ADA_HUBUNGAN];
        } else {
            const withoutTidakAda = currentValues.filter(
                (item) => item !== TIDAK_ADA_HUBUNGAN
            );

            if (withoutTidakAda.includes(option)) {
                nextValues = withoutTidakAda.filter((item) => item !== option);
            } else {
                nextValues = [...withoutTidakAda, option];
            }
        }

        handleChange({
            target: {
                name: "hubungan_kerabat_instansi",
                value: nextValues,
            },
        });
    };

    return (
        <div className="space-y-8">
            <div className="rounded-2xl border border-pink-100 bg-pink-50 p-4">
                <h3 className="text-base font-bold text-pink-800">
                    Riwayat Keluarga
                </h3>
                <p className="mt-1 text-sm text-pink-600">
                    Lengkapi data keluarga, pasangan, mertua, kontak darurat,
                    saudara kandung, saudara ipar, dan hubungan kekerabatan
                    dengan instansi.
                </p>
            </div>

            <Card title="Hubungan Kekerabatan dengan Instansi">
                <CheckboxGroup
                    label="Apakah Anda memiliki hubungan kekerabatan (kerabat dekat maupun jauh) yang bekerja di instansi"
                    helper="Dapat memilih lebih dari 1 opsi"
                    required
                    name="hubungan_kerabat_instansi"
                    options={HUBUNGAN_KERABAT_INSTANSI_OPTIONS}
                    value={hubunganKerabatInstansi}
                    onChange={handleHubunganKerabatInstansiChange}
                    error={errors.hubungan_kerabat_instansi}
                />
            </Card>

            <Card title="Orang Tua Kandung">
                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Ayah Kandung"
                        name="nama_ayah_kandung"
                        value={form.nama_ayah_kandung}
                        onChange={handleChange}
                        error={errors.nama_ayah_kandung}
                    />

                    <Input
                        label="Pekerjaan Ayah Kandung"
                        name="pekerjaan_ayah_kandung"
                        value={form.pekerjaan_ayah_kandung}
                        onChange={handleChange}
                        error={errors.pekerjaan_ayah_kandung}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Ibu Kandung"
                        name="nama_ibu_kandung"
                        value={form.nama_ibu_kandung}
                        onChange={handleChange}
                        error={errors.nama_ibu_kandung}
                    />

                    <Input
                        label="Pekerjaan Ibu Kandung"
                        name="pekerjaan_ibu_kandung"
                        value={form.pekerjaan_ibu_kandung}
                        onChange={handleChange}
                        error={errors.pekerjaan_ibu_kandung}
                    />
                </div>
            </Card>

            <Card title="Data Ayah">
                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Ayah"
                        name="nama_ayah"
                        value={form.nama_ayah}
                        onChange={handleChange}
                        error={errors.nama_ayah}
                    />

                    <Input
                        label="NIK Ayah"
                        name="nik_ayah"
                        value={form.nik_ayah}
                        onChange={handleChange}
                        error={errors.nik_ayah}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Tempat Lahir Ayah"
                        name="tempat_lahir_ayah"
                        value={form.tempat_lahir_ayah}
                        onChange={handleChange}
                        error={errors.tempat_lahir_ayah}
                    />

                    <Input
                        label="Tanggal Lahir Ayah"
                        type="date"
                        name="tanggal_lahir_ayah"
                        value={form.tanggal_lahir_ayah}
                        onChange={handleChange}
                        error={errors.tanggal_lahir_ayah}
                        onlyPicker
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Pekerjaan Ayah"
                        name="pekerjaan_ayah"
                        value={form.pekerjaan_ayah}
                        onChange={handleChange}
                        error={errors.pekerjaan_ayah}
                    />

                    <Input
                        label="No HP Ayah"
                        name="no_hp_ayah"
                        value={form.no_hp_ayah}
                        onChange={handleChange}
                        placeholder="Contoh: 08xxxxxxxxxx"
                        error={errors.no_hp_ayah}
                    />
                </div>

                <div className="mt-5">
                    <Textarea
                        label="Alamat Ayah"
                        name="alamat_ayah"
                        value={form.alamat_ayah}
                        onChange={handleChange}
                        placeholder="Masukkan alamat ayah"
                        error={errors.alamat_ayah}
                    />
                </div>
            </Card>

            <Card title="Data Ibu">
                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Ibu"
                        name="nama_ibu"
                        value={form.nama_ibu}
                        onChange={handleChange}
                        error={errors.nama_ibu}
                    />

                    <Input
                        label="NIK Ibu"
                        name="nik_ibu"
                        value={form.nik_ibu}
                        onChange={handleChange}
                        error={errors.nik_ibu}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Tempat Lahir Ibu"
                        name="tempat_lahir_ibu"
                        value={form.tempat_lahir_ibu}
                        onChange={handleChange}
                        error={errors.tempat_lahir_ibu}
                    />

                    <Input
                        label="Tanggal Lahir Ibu"
                        type="date"
                        name="tanggal_lahir_ibu"
                        value={form.tanggal_lahir_ibu}
                        onChange={handleChange}
                        error={errors.tanggal_lahir_ibu}
                        onlyPicker
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Pekerjaan Ibu"
                        name="pekerjaan_ibu"
                        value={form.pekerjaan_ibu}
                        onChange={handleChange}
                        error={errors.pekerjaan_ibu}
                    />

                    <Input
                        label="No HP Ibu"
                        name="no_hp_ibu"
                        value={form.no_hp_ibu}
                        onChange={handleChange}
                        placeholder="Contoh: 08xxxxxxxxxx"
                        error={errors.no_hp_ibu}
                    />
                </div>

                <div className="mt-5">
                    <Textarea
                        label="Alamat Ibu"
                        name="alamat_ibu"
                        value={form.alamat_ibu}
                        onChange={handleChange}
                        placeholder="Masukkan alamat ibu"
                        error={errors.alamat_ibu}
                    />
                </div>
            </Card>

            <Card title="Pasangan">
                <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <Input
                        label="Nama Suami / Istri"
                        name="nama_suami_istri"
                        value={form.nama_suami_istri}
                        onChange={handleChange}
                        error={errors.nama_suami_istri}
                    />

                    <Input
                        label="Pekerjaan Suami / Istri"
                        name="pekerjaan_suami_istri"
                        value={form.pekerjaan_suami_istri}
                        onChange={handleChange}
                        error={errors.pekerjaan_suami_istri}
                    />

                    <Input
                        label="Telepon Suami / Istri"
                        name="tlpn_suami_istri"
                        value={form.tlpn_suami_istri}
                        onChange={handleChange}
                        placeholder="Contoh: 08xxxxxxxxxx"
                        error={errors.tlpn_suami_istri}
                    />
                </div>
            </Card>

            <Card title="Mertua">
                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Bapak Mertua"
                        name="nama_bapak_mertua"
                        value={form.nama_bapak_mertua}
                        onChange={handleChange}
                        error={errors.nama_bapak_mertua}
                    />

                    <Input
                        label="Pekerjaan Bapak Mertua"
                        name="pekerjaan_bapak_mertua"
                        value={form.pekerjaan_bapak_mertua}
                        onChange={handleChange}
                        error={errors.pekerjaan_bapak_mertua}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Ibu Mertua"
                        name="nama_ibu_mertua"
                        value={form.nama_ibu_mertua}
                        onChange={handleChange}
                        error={errors.nama_ibu_mertua}
                    />

                    <Input
                        label="Pekerjaan Ibu Mertua"
                        name="pekerjaan_ibu_mertua"
                        value={form.pekerjaan_ibu_mertua}
                        onChange={handleChange}
                        error={errors.pekerjaan_ibu_mertua}
                    />
                </div>
            </Card>

            <Card title="Kontak Darurat">
                <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-sm text-slate-500">
                        Tambahkan kontak darurat yang bisa dihubungi.
                    </p>

                    <button
                        type="button"
                        onClick={addKontakDarurat}
                        className="rounded-2xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-blue-100 transition hover:bg-blue-700"
                    >
                        + Tambah Kontak
                    </button>
                </div>

                <div className="space-y-4">
                    {kontakDaruratList.map((item, index) => (
                        <div
                            key={index}
                            className="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <h5 className="font-bold text-slate-700">
                                    Kontak Darurat {index + 1}
                                </h5>

                                {kontakDaruratList.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            removeKontakDarurat(index)
                                        }
                                        className="rounded-xl bg-red-100 px-3 py-1 text-xs font-bold text-red-600 hover:bg-red-200"
                                    >
                                        Hapus
                                    </button>
                                )}
                            </div>

                            <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                                <Input
                                    label="Nama Kontak"
                                    name={`kontak_darurat_${index}_nama`}
                                    value={item.nama}
                                    onChange={(e) =>
                                        handleKontakDaruratChange(
                                            index,
                                            "nama",
                                            e.target.value
                                        )
                                    }
                                    placeholder="Contoh: Budi Santoso"
                                    error={
                                        index === 0
                                            ? errors.kontak_darurat_nama
                                            : errors[
                                                  `kontak_darurat_${index}_nama`
                                              ]
                                    }
                                />

                                <Select
                                    label="Status Hubungan"
                                    name={`kontak_darurat_${index}_status`}
                                    value={item.status}
                                    onChange={(e) =>
                                        handleKontakDaruratChange(
                                            index,
                                            "status",
                                            e.target.value
                                        )
                                    }
                                    options={[
                                        "Ayah",
                                        "Ibu",
                                        "Suami",
                                        "Istri",
                                        "Kakak",
                                        "Adik",
                                        "Anak",
                                        "Saudara",
                                        "Teman",
                                        "Tetangga",
                                        "Lainnya",
                                    ]}
                                    error={
                                        index === 0
                                            ? errors.kontak_darurat_status
                                            : errors[
                                                  `kontak_darurat_${index}_status`
                                              ]
                                    }
                                />

                                <Input
                                    label="Nomor Telepon"
                                    name={`kontak_darurat_${index}_nomor`}
                                    value={item.nomor}
                                    onChange={(e) =>
                                        handleKontakDaruratChange(
                                            index,
                                            "nomor",
                                            e.target.value
                                        )
                                    }
                                    placeholder="Contoh: 08xxxxxxxxxx"
                                    error={
                                        index === 0
                                            ? errors.kontak_darurat_nomor
                                            : errors[
                                                  `kontak_darurat_${index}_nomor`
                                              ]
                                    }
                                />
                            </div>
                        </div>
                    ))}
                </div>
            </Card>

            <FamilyList
                title="Saudara Kandung"
                buttonLabel="+ Tambah Saudara"
                items={saudaraKandungList}
                onAdd={addSaudaraKandung}
                onRemove={removeSaudaraKandung}
                onChange={handleSaudaraKandungChange}
                nameLabel="Nama Saudara"
                alamatPlaceholder="Masukkan alamat saudara"
            />

            <FamilyList
                title="Saudara Ipar"
                buttonLabel="+ Tambah Ipar"
                items={saudaraIparList}
                onAdd={addSaudaraIpar}
                onRemove={removeSaudaraIpar}
                onChange={handleSaudaraIparChange}
                nameLabel="Nama Saudara Ipar"
                alamatPlaceholder="Masukkan alamat saudara ipar"
                buttonColor="pink"
            />
        </div>
    );
}

function normalizeCheckboxValue(value) {
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
}

function CheckboxGroup({
    label,
    helper,
    required = false,
    options = [],
    value = [],
    onChange,
    error,
}) {
    const selectedValues = normalizeCheckboxValue(value);

    return (
        <div>
            <div className="mb-5">
                <p className="text-base font-semibold uppercase leading-relaxed text-slate-900">
                    {label}
                    {required && <span className="ml-1 text-red-500">*</span>}
                </p>

                {helper && (
                    <p className="mt-1 text-sm font-bold text-slate-900">
                        ({helper})
                    </p>
                )}
            </div>

            <div className="space-y-4">
                {options.map((option) => {
                    const checked = selectedValues.includes(option);

                    return (
                        <label
                            key={option}
                            className="flex cursor-pointer items-start gap-3 text-sm font-medium text-slate-900"
                        >
                            <input
                                type="checkbox"
                                checked={checked}
                                onChange={() => onChange(option)}
                                className="mt-0.5 h-5 w-5 rounded border-slate-400 text-purple-600 focus:ring-4 focus:ring-purple-100"
                            />

                            <span className="leading-6">{option}</span>
                        </label>
                    );
                })}
            </div>

            <ErrorMessage message={error} />
        </div>
    );
}

function FamilyList({
    title,
    buttonLabel,
    items,
    onAdd,
    onRemove,
    onChange,
    nameLabel,
    alamatPlaceholder,
    buttonColor = "blue",
}) {
    const buttonClass =
        buttonColor === "pink"
            ? "rounded-2xl bg-pink-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-pink-100 transition hover:bg-pink-700"
            : "rounded-2xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-blue-100 transition hover:bg-blue-700";

    return (
        <Card title={title}>
            <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p className="text-sm text-slate-500">
                    Tambahkan data {title.toLowerCase()}. Bisa lebih dari satu.
                </p>

                <button type="button" onClick={onAdd} className={buttonClass}>
                    {buttonLabel}
                </button>
            </div>

            <div className="space-y-5">
                {items.map((item, index) => (
                    <div
                        key={index}
                        className="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                    >
                        <div className="mb-4 flex items-center justify-between">
                            <h5 className="font-bold text-slate-700">
                                {title} {index + 1}
                            </h5>

                            {items.length > 1 && (
                                <button
                                    type="button"
                                    onClick={() => onRemove(index)}
                                    className="rounded-xl bg-red-100 px-3 py-1 text-xs font-bold text-red-600 hover:bg-red-200"
                                >
                                    Hapus
                                </button>
                            )}
                        </div>

                        <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <Input
                                label={nameLabel}
                                name="nama"
                                value={item.nama}
                                onChange={(e) =>
                                    onChange(
                                        index,
                                        e.target.name,
                                        e.target.value
                                    )
                                }
                            />

                            <Select
                                label="Jenis Kelamin"
                                name="jenis_kelamin"
                                value={item.jenis_kelamin}
                                onChange={(e) =>
                                    onChange(
                                        index,
                                        e.target.name,
                                        e.target.value
                                    )
                                }
                                options={["Laki-laki", "Perempuan"]}
                            />
                        </div>

                        <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                            <Input
                                label="Hubungan"
                                name="hubungan"
                                value={item.hubungan}
                                onChange={(e) =>
                                    onChange(
                                        index,
                                        e.target.name,
                                        e.target.value
                                    )
                                }
                                placeholder="Contoh: Kakak / Adik"
                            />

                            <Input
                                label="Pekerjaan"
                                name="pekerjaan"
                                value={item.pekerjaan}
                                onChange={(e) =>
                                    onChange(
                                        index,
                                        e.target.name,
                                        e.target.value
                                    )
                                }
                            />

                            <Input
                                label="Nomor HP"
                                name="no_hp"
                                value={item.no_hp}
                                onChange={(e) =>
                                    onChange(
                                        index,
                                        e.target.name,
                                        e.target.value
                                    )
                                }
                                placeholder="Contoh: 08xxxxxxxxxx"
                            />
                        </div>

                        <div className="mt-5">
                            <Textarea
                                label="Alamat"
                                name="alamat"
                                value={item.alamat}
                                onChange={(e) =>
                                    onChange(
                                        index,
                                        e.target.name,
                                        e.target.value
                                    )
                                }
                                placeholder={alamatPlaceholder}
                            />
                        </div>
                    </div>
                ))}
            </div>
        </Card>
    );
}

function Card({ title, children }) {
    return (
        <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h4 className="mb-5 text-lg font-bold text-slate-800">{title}</h4>
            {children}
        </section>
    );
}

function FieldLabel({ label }) {
    return (
        <label className="mb-2 block text-sm font-semibold text-slate-700">
            {label}
        </label>
    );
}

function ErrorMessage({ message }) {
    if (!message) return null;

    return <p className="mt-1 text-xs font-semibold text-red-500">{message}</p>;
}

function fieldClass(error, type, disabled = false) {
    return `w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition ${
        type === "date" ? "cursor-pointer pr-12" : ""
    } ${
        disabled
            ? "cursor-not-allowed border-slate-200 bg-slate-100 text-slate-500"
            : error
            ? "border-red-300 bg-red-50 focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-100"
            : "border-slate-200 bg-white focus:border-pink-500 focus:bg-white focus:ring-4 focus:ring-pink-100"
    }`;
}

function Input({
    label,
    type = "text",
    name,
    value,
    onChange,
    placeholder,
    error,
    onlyPicker = false,
    disabled = false,
}) {
    const inputRef = React.useRef(null);

    const openDatePicker = () => {
        if (type !== "date" || disabled) return;

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
        <div>
            <FieldLabel label={label} />

            <div className="relative">
                <input
                    ref={inputRef}
                    type={type}
                    name={name}
                    value={value ?? ""}
                    onChange={onChange}
                    onClick={type === "date" ? openDatePicker : undefined}
                    onKeyDown={handleKeyDown}
                    placeholder={
                        type === "date"
                            ? undefined
                            : placeholder || `Masukkan ${label.toLowerCase()}`
                    }
                    className={fieldClass(error, type, disabled)}
                    disabled={disabled}
                    autoComplete="off"
                />

                {type === "date" && !disabled && (
                    <button
                        type="button"
                        onClick={openDatePicker}
                        className="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-pink-50 text-pink-700 transition hover:bg-pink-100"
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

function Select({
    label,
    name,
    value,
    onChange,
    options = [],
    error,
    disabled = false,
}) {
    return (
        <div>
            <FieldLabel label={label} />

            <select
                name={name}
                value={value ?? ""}
                onChange={onChange}
                disabled={disabled}
                className={fieldClass(error, "select", disabled)}
            >
                <option value="">Pilih {label.toLowerCase()}</option>
                {options.map((item) => (
                    <option key={item} value={item}>
                        {item}
                    </option>
                ))}
            </select>

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
    error,
    disabled = false,
}) {
    return (
        <div>
            <FieldLabel label={label} />

            <textarea
                name={name}
                value={value ?? ""}
                onChange={onChange}
                rows={3}
                placeholder={placeholder || `Masukkan ${label.toLowerCase()}`}
                disabled={disabled}
                className={`${fieldClass(error, "textarea", disabled)} resize-none`}
            />

            <ErrorMessage message={error} />
        </div>
    );
}