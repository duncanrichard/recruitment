import React from "react";

export default function StepDataDiri({
    form,
    handleChange,
    errors = {},
    requiredFields = [],
    handleSosialMediaChange,
    addSosialMedia,
    removeSosialMedia,
}) {
    const isRequired = (name) => requiredFields.includes(name);

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                <h3 className="text-base font-bold text-blue-800">
                    Data Diri
                </h3>
                <p className="mt-1 text-sm text-blue-600">
                    Lengkapi informasi pribadi sesuai identitas resmi. Field
                    bertanda <span className="font-bold text-red-500">*</span>{" "}
                    wajib diisi.
                </p>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 className="mb-4 text-sm font-bold uppercase tracking-wide text-slate-700">
                    Informasi Lamaran
                </h3>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Posisi yang Dilamar"
                        name="posisi_dilamar"
                        value={form.posisi_dilamar}
                        onChange={handleChange}
                        placeholder="Contoh: Operator Produksi"
                        required={isRequired("posisi_dilamar")}
                        error={errors.posisi_dilamar}
                    />

                    <Input
                        label="Perusahaan yang Dilamar"
                        name="perusahaan_dilamar"
                        value={form.perusahaan_dilamar}
                        onChange={handleChange}
                        placeholder="Contoh: PT Maju Sejahtera"
                        required={isRequired("perusahaan_dilamar")}
                        error={errors.perusahaan_dilamar}
                    />
                </div>
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
                        required={isRequired("nama")}
                        error={errors.nama}
                    />

                    <Input
                        label="Nama Panggilan"
                        name="nama_panggilan"
                        value={form.nama_panggilan}
                        onChange={handleChange}
                        placeholder="Contoh: Rani"
                        required={isRequired("nama_panggilan")}
                        error={errors.nama_panggilan}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="NIK"
                        name="nik"
                        value={form.nik}
                        onChange={handleChange}
                        placeholder="Masukkan NIK"
                        required={isRequired("nik")}
                        error={errors.nik}
                    />

                    <Select
                        label="Pendidikan Terakhir"
                        name="pendidikan"
                        value={form.pendidikan}
                        onChange={handleChange}
                        required={isRequired("pendidikan")}
                        error={errors.pendidikan}
                        options={[
                            { value: "SD", label: "SD" },
                            { value: "SMP", label: "SMP" },
                            { value: "SMA / SMK", label: "SMA / SMK" },
                            { value: "D1", label: "D1" },
                            { value: "D2", label: "D2" },
                            { value: "D3", label: "D3" },
                            { value: "D4", label: "D4" },
                            { value: "S1", label: "S1" },
                            { value: "S2", label: "S2" },
                            { value: "S3", label: "S3" },
                        ]}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
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
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Jenis Kelamin"
                        name="jenis_kelamin"
                        value={form.jenis_kelamin}
                        onChange={handleChange}
                        required={isRequired("jenis_kelamin")}
                        error={errors.jenis_kelamin}
                        options={[
                            { value: "Laki-laki", label: "Laki-laki" },
                            { value: "Perempuan", label: "Perempuan" },
                        ]}
                    />

                    <Select
                        label="Agama"
                        name="agama"
                        value={form.agama}
                        onChange={handleChange}
                        required={isRequired("agama")}
                        error={errors.agama}
                        options={[
                            { value: "Islam", label: "Islam" },
                            { value: "Kristen", label: "Kristen" },
                            { value: "Katolik", label: "Katolik" },
                            { value: "Hindu", label: "Hindu" },
                            { value: "Buddha", label: "Buddha" },
                            { value: "Konghucu", label: "Konghucu" },
                        ]}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Status Perkawinan"
                        name="status_perkawinan"
                        value={form.status_perkawinan}
                        onChange={handleChange}
                        required={isRequired("status_perkawinan")}
                        error={errors.status_perkawinan}
                        options={[
                            { value: "Belum Kawin", label: "Belum Kawin" },
                            { value: "Kawin", label: "Kawin" },
                            { value: "Cerai Hidup", label: "Cerai Hidup" },
                            { value: "Cerai Mati", label: "Cerai Mati" },
                        ]}
                    />

                    <Select
                        label="Kewarganegaraan"
                        name="kewarganegaraan"
                        value={form.kewarganegaraan}
                        onChange={handleChange}
                        required={isRequired("kewarganegaraan")}
                        error={errors.kewarganegaraan}
                        options={[
                            { value: "WNI", label: "WNI" },
                            { value: "WNA", label: "WNA" },
                        ]}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="STR Aktif"
                        name="str_aktif"
                        value={form.str_aktif}
                        onChange={handleChange}
                        required={isRequired("str_aktif")}
                        error={errors.str_aktif}
                        options={[
                            { value: "Ya", label: "Ya" },
                            { value: "Tidak", label: "Tidak" },
                        ]}
                    />
                </div>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 className="text-sm font-bold uppercase tracking-wide text-slate-700">
                            Sosial Media
                        </h3>
                        <p className="mt-1 text-xs text-slate-500">
                            Pilih platform sosial media, lalu masukkan nama akun.
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

                <div className="space-y-4">
                    {(form.sosial_media || []).map((item, index) => (
                        <div
                            key={index}
                            className="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <div className="mb-3 flex items-center justify-between gap-3">
                                <h4 className="text-sm font-bold text-slate-700">
                                    Sosial Media {index + 1}
                                </h4>

                                <button
                                    type="button"
                                    onClick={() => removeSosialMedia(index)}
                                    className="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-100"
                                >
                                    Hapus
                                </button>
                            </div>

                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <Select
                                    label="Platform"
                                    name={`sosial_media_${index}_platform`}
                                    value={item.platform}
                                    onChange={(event) =>
                                        handleSosialMediaChange(
                                            index,
                                            "platform",
                                            event.target.value
                                        )
                                    }
                                    options={[
                                        { value: "Instagram", label: "Instagram" },
                                        { value: "Facebook", label: "Facebook" },
                                        { value: "TikTok", label: "TikTok" },
                                        { value: "X / Twitter", label: "X / Twitter" },
                                        { value: "LinkedIn", label: "LinkedIn" },
                                        { value: "YouTube", label: "YouTube" },
                                        { value: "Lainnya", label: "Lainnya" },
                                    ]}
                                />

                                <Input
                                    label="Nama Akun"
                                    name={`sosial_media_${index}_nama_akun`}
                                    value={item.nama_akun}
                                    onChange={(event) =>
                                        handleSosialMediaChange(
                                            index,
                                            "nama_akun",
                                            event.target.value
                                        )
                                    }
                                    placeholder="Contoh: @namaakun"
                                />
                            </div>
                        </div>
                    ))}
                </div>
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
                        required={isRequired("no_hp")}
                        error={errors.no_hp}
                    />
                </div>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 className="mb-4 text-sm font-bold uppercase tracking-wide text-slate-700">
                    Alamat
                </h3>

                <Textarea
                    label="Alamat Lengkap"
                    name="alamat"
                    value={form.alamat}
                    onChange={handleChange}
                    placeholder="Masukkan alamat lengkap"
                    required={isRequired("alamat")}
                    error={errors.alamat}
                    rows={4}
                />

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <Input
                        label="Provinsi"
                        name="provinsi"
                        value={form.provinsi}
                        onChange={handleChange}
                        placeholder="Provinsi"
                        required={isRequired("provinsi")}
                        error={errors.provinsi}
                    />

                    <Input
                        label="Kabupaten / Kota"
                        name="kabupaten"
                        value={form.kabupaten}
                        onChange={handleChange}
                        placeholder="Kabupaten / Kota"
                        required={isRequired("kabupaten")}
                        error={errors.kabupaten}
                    />

                    <Input
                        label="Kecamatan"
                        name="kecamatan"
                        value={form.kecamatan}
                        onChange={handleChange}
                        placeholder="Kecamatan"
                        required={isRequired("kecamatan")}
                        error={errors.kecamatan}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <Input
                        label="Kelurahan / Desa"
                        name="kelurahan"
                        value={form.kelurahan}
                        onChange={handleChange}
                        placeholder="Kelurahan / Desa"
                        required={isRequired("kelurahan")}
                        error={errors.kelurahan}
                    />

                    <Input
                        label="RT"
                        name="rt"
                        value={form.rt}
                        onChange={handleChange}
                        placeholder="RT"
                        required={isRequired("rt")}
                        error={errors.rt}
                    />

                    <Input
                        label="RW"
                        name="rw"
                        value={form.rw}
                        onChange={handleChange}
                        placeholder="RW"
                        required={isRequired("rw")}
                        error={errors.rw}
                    />
                </div>
            </div>
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
        <p className="mt-2 text-xs font-semibold text-red-500">
            {message}
        </p>
    );
}

function fieldClass(error, type) {
    return `w-full rounded-xl border px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 ${
        type === "date" ? "cursor-pointer pr-12" : ""
    } ${
        error
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
}) {
    const inputValue = value ?? "";
    const inputRef = React.useRef(null);

    const openDatePicker = () => {
        if (type !== "date") return;

        const input = inputRef.current;

        if (!input) return;

        input.focus();

        if (typeof input.showPicker === "function") {
            try {
                input.showPicker();
            } catch (error) {
                // Fallback browser.
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
                    className={fieldClass(error, type)}
                    autoComplete="off"
                />

                {type === "date" && (
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

           {/*  {type === "date" && (
                <p className="mt-1 text-xs text-slate-400">
                    Klik kolom atau ikon kalender untuk memilih tanggal.
                </p>
            )} */}

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
                    <option key={item.value} value={item.value}>
                        {item.label}
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
    rows = 3,
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
                rows={rows}
                placeholder={placeholder}
                className={`${fieldClass(error)} resize-none`}
            />

            <ErrorMessage message={error} />
        </div>
    );
}