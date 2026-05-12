import React from "react";

export default function StepRiwayatKeluarga({
    form,
    errors = {},
    requiredFields = [],
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
    const isRequired = (name) => requiredFields.includes(name);

    const kontakDaruratList =
        Array.isArray(form.kontak_darurat) && form.kontak_darurat.length > 0
            ? form.kontak_darurat
            : [{ nama: "", status: "", nomor: "" }];

    return (
        <div className="space-y-8">
            <div className="rounded-2xl border border-pink-100 bg-pink-50 p-4">
                <h3 className="text-base font-bold text-pink-800">
                    Riwayat Keluarga
                </h3>
                <p className="mt-1 text-sm text-pink-600">
                    Lengkapi data keluarga, pasangan, mertua, kontak darurat,
                    saudara kandung, dan saudara ipar.
                </p>
            </div>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Data Orang Tua Kandung
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Ayah Kandung"
                        name="nama_ayah_kandung"
                        value={form.nama_ayah_kandung}
                        onChange={handleChange}
                        required={isRequired("nama_ayah_kandung")}
                        error={errors.nama_ayah_kandung}
                    />

                    <Input
                        label="Pekerjaan Ayah Kandung"
                        name="pekerjaan_ayah_kandung"
                        value={form.pekerjaan_ayah_kandung}
                        onChange={handleChange}
                        required={isRequired("pekerjaan_ayah_kandung")}
                        error={errors.pekerjaan_ayah_kandung}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Ibu Kandung"
                        name="nama_ibu_kandung"
                        value={form.nama_ibu_kandung}
                        onChange={handleChange}
                        required={isRequired("nama_ibu_kandung")}
                        error={errors.nama_ibu_kandung}
                    />

                    <Input
                        label="Pekerjaan Ibu Kandung"
                        name="pekerjaan_ibu_kandung"
                        value={form.pekerjaan_ibu_kandung}
                        onChange={handleChange}
                        required={isRequired("pekerjaan_ibu_kandung")}
                        error={errors.pekerjaan_ibu_kandung}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Data Pasangan
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <Input
                        label="Nama Suami / Istri"
                        name="nama_suami_istri"
                        value={form.nama_suami_istri}
                        onChange={handleChange}
                        required={isRequired("nama_suami_istri")}
                        error={errors.nama_suami_istri}
                    />

                    <Input
                        label="Pekerjaan Suami / Istri"
                        name="pekerjaan_suami_istri"
                        value={form.pekerjaan_suami_istri}
                        onChange={handleChange}
                        required={isRequired("pekerjaan_suami_istri")}
                        error={errors.pekerjaan_suami_istri}
                    />

                    <Input
                        label="Telepon Suami / Istri"
                        name="tlpn_suami_istri"
                        value={form.tlpn_suami_istri}
                        onChange={handleChange}
                        placeholder="Contoh: 08xxxxxxxxxx"
                        required={isRequired("tlpn_suami_istri")}
                        error={errors.tlpn_suami_istri}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Data Mertua
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Bapak Mertua"
                        name="nama_bapak_mertua"
                        value={form.nama_bapak_mertua}
                        onChange={handleChange}
                        required={isRequired("nama_bapak_mertua")}
                        error={errors.nama_bapak_mertua}
                    />

                    <Input
                        label="Pekerjaan Bapak Mertua"
                        name="pekerjaan_bapak_mertua"
                        value={form.pekerjaan_bapak_mertua}
                        onChange={handleChange}
                        required={isRequired("pekerjaan_bapak_mertua")}
                        error={errors.pekerjaan_bapak_mertua}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Ibu Mertua"
                        name="nama_ibu_mertua"
                        value={form.nama_ibu_mertua}
                        onChange={handleChange}
                        required={isRequired("nama_ibu_mertua")}
                        error={errors.nama_ibu_mertua}
                    />

                    <Input
                        label="Pekerjaan Ibu Mertua"
                        name="pekerjaan_ibu_mertua"
                        value={form.pekerjaan_ibu_mertua}
                        onChange={handleChange}
                        required={isRequired("pekerjaan_ibu_mertua")}
                        error={errors.pekerjaan_ibu_mertua}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 className="text-lg font-bold text-slate-800">
                            Kontak Darurat
                        </h4>
                        <p className="mt-1 text-sm text-slate-500">
                            Tambahkan Kontak Darurat.
                        </p>
                    </div>

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
                                        onClick={() => removeKontakDarurat(index)}
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
                                    required={isRequired("kontak_darurat")}
                                    error={
                                        index === 0
                                            ? errors.kontak_darurat_nama
                                            : ""
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
                                    required={isRequired("kontak_darurat")}
                                    error={
                                        index === 0
                                            ? errors.kontak_darurat_status
                                            : ""
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
                                    required={isRequired("kontak_darurat")}
                                    error={
                                        index === 0
                                            ? errors.kontak_darurat_nomor
                                            : ""
                                    }
                                />
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 className="text-lg font-bold text-slate-800">
                            Saudara Kandung
                        </h4>
                        <p className="mt-1 text-sm text-slate-500">
                            Tambahkan data saudara kandung. Bisa lebih dari satu.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={addSaudaraKandung}
                        className="rounded-2xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-blue-100 transition hover:bg-blue-700"
                    >
                        + Tambah Saudara
                    </button>
                </div>

                <div className="space-y-5">
                    {(form.saudara_kandung || []).map((item, index) => (
                        <div
                            key={index}
                            className="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <div className="mb-4 flex items-center justify-between">
                                <h5 className="font-bold text-slate-700">
                                    Saudara Kandung {index + 1}
                                </h5>

                                {form.saudara_kandung.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            removeSaudaraKandung(index)
                                        }
                                        className="rounded-xl bg-red-100 px-3 py-1 text-xs font-bold text-red-600 hover:bg-red-200"
                                    >
                                        Hapus
                                    </button>
                                )}
                            </div>

                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <Input
                                    label="Nama Saudara"
                                    name="nama"
                                    value={item.nama}
                                    onChange={(e) =>
                                        handleSaudaraKandungChange(
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
                                        handleSaudaraKandungChange(
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
                                        handleSaudaraKandungChange(
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
                                        handleSaudaraKandungChange(
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
                                        handleSaudaraKandungChange(
                                            index,
                                            e.target.name,
                                            e.target.value
                                        )
                                    }
                                />
                            </div>

                            <div className="mt-5">
                                <Textarea
                                    label="Alamat"
                                    name="alamat"
                                    value={item.alamat}
                                    onChange={(e) =>
                                        handleSaudaraKandungChange(
                                            index,
                                            e.target.name,
                                            e.target.value
                                        )
                                    }
                                    placeholder="Masukkan alamat saudara"
                                />
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 className="text-lg font-bold text-slate-800">
                            Saudara Ipar
                        </h4>
                        <p className="mt-1 text-sm text-slate-500">
                            Tambahkan data saudara ipar. Bisa lebih dari satu.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={addSaudaraIpar}
                        className="rounded-2xl bg-pink-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-pink-100 transition hover:bg-pink-700"
                    >
                        + Tambah Ipar
                    </button>
                </div>

                <div className="space-y-5">
                    {(form.saudara_ipar || []).map((item, index) => (
                        <div
                            key={index}
                            className="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <div className="mb-4 flex items-center justify-between">
                                <h5 className="font-bold text-slate-700">
                                    Saudara Ipar {index + 1}
                                </h5>

                                {form.saudara_ipar.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={() => removeSaudaraIpar(index)}
                                        className="rounded-xl bg-red-100 px-3 py-1 text-xs font-bold text-red-600 hover:bg-red-200"
                                    >
                                        Hapus
                                    </button>
                                )}
                            </div>

                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <Input
                                    label="Nama Saudara Ipar"
                                    name="nama"
                                    value={item.nama}
                                    onChange={(e) =>
                                        handleSaudaraIparChange(
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
                                        handleSaudaraIparChange(
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
                                        handleSaudaraIparChange(
                                            index,
                                            e.target.name,
                                            e.target.value
                                        )
                                    }
                                    placeholder="Contoh: Ipar dari kakak"
                                />

                                <Input
                                    label="Pekerjaan"
                                    name="pekerjaan"
                                    value={item.pekerjaan}
                                    onChange={(e) =>
                                        handleSaudaraIparChange(
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
                                        handleSaudaraIparChange(
                                            index,
                                            e.target.name,
                                            e.target.value
                                        )
                                    }
                                />
                            </div>

                            <div className="mt-5">
                                <Textarea
                                    label="Alamat"
                                    name="alamat"
                                    value={item.alamat}
                                    onChange={(e) =>
                                        handleSaudaraIparChange(
                                            index,
                                            e.target.name,
                                            e.target.value
                                        )
                                    }
                                    placeholder="Masukkan alamat saudara ipar"
                                />
                            </div>
                        </div>
                    ))}
                </div>
            </section>
        </div>
    );
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

    return (
        <p className="mt-1 text-xs font-semibold text-red-500">
            {message}
        </p>
    );
}

function fieldClass(error) {
    return `w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition ${
        error
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
    required = false,
    error,
}) {
    return (
        <div>
            <FieldLabel label={label} required={required} />

            <input
                type={type}
                name={name}
                value={value ?? ""}
                onChange={onChange}
                placeholder={
                    type === "date"
                        ? undefined
                        : placeholder || `Masukkan ${label.toLowerCase()}`
                }
                className={fieldClass(error)}
            />

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
    required = false,
    error,
}) {
    return (
        <div>
            <FieldLabel label={label} required={required} />

            <select
                name={name}
                value={value ?? ""}
                onChange={onChange}
                className={fieldClass(error)}
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
    required = false,
    error,
}) {
    return (
        <div>
            <FieldLabel label={label} required={required} />

            <textarea
                name={name}
                value={value ?? ""}
                onChange={onChange}
                rows="3"
                placeholder={placeholder}
                className={`${fieldClass(error)} resize-none`}
            />

            <ErrorMessage message={error} />
        </div>
    );
}