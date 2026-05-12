import React from "react";

export default function StepRiwayatKesehatan({
    form,
    handleChange,
    errors = {},
    requiredFields = [],
}) {
    const isRequired = (name) => requiredFields.includes(name);

    const showRiwayatPenyakit =
        form.memiliki_riwayat_penyakit === "Ada" ||
        form.memiliki_riwayat_penyakit === "Ya";

    const showAlergi =
        form.memiliki_alergi === "Ada" ||
        form.memiliki_alergi === "Ya";

    const showTahunDirawat =
        form.pernah_dirawat === "Pernah" ||
        form.pernah_dirawat === "Ya";

    const showPenyakitGenetik = form.punya_penyakit_genetik === "Ya";
    const showPengobatanPsikolog = form.pengobatan_psikolog === "Ya";
    const showKecelakaan = form.pernah_kecelakaan === "Ya";
    const showOperasi = form.pernah_operasi === "Ya";

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                <h3 className="text-base font-bold text-emerald-800">
                    Riwayat Kesehatan
                </h3>
                <p className="mt-1 text-sm text-emerald-600">
                    Lengkapi data kesehatan sesuai kondisi sebenarnya. Field
                    bertanda <span className="font-bold text-red-500">*</span>{" "}
                    wajib diisi.
                </p>
            </div>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Pemeriksaan Umum
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <Select
                        label="Golongan Darah"
                        name="golongan_darah"
                        value={form.golongan_darah}
                        onChange={handleChange}
                        required={isRequired("golongan_darah")}
                        error={errors.golongan_darah}
                        options={["A", "B", "AB", "O", "Tidak Tahu"]}
                    />

                    <Input
                        label="Tinggi Badan"
                        type="number"
                        name="tinggi_badan"
                        value={form.tinggi_badan}
                        onChange={handleChange}
                        placeholder="Contoh: 170"
                        required={isRequired("tinggi_badan")}
                        error={errors.tinggi_badan}
                        suffix="cm"
                    />

                    <Input
                        label="Berat Badan"
                        type="number"
                        name="berat_badan"
                        value={form.berat_badan}
                        onChange={handleChange}
                        placeholder="Contoh: 60"
                        required={isRequired("berat_badan")}
                        error={errors.berat_badan}
                        suffix="kg"
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Penglihatan & Pendengaran
                </h4>

                <div className="space-y-5">
                    <RadioGroup
                        label="Apakah Anda Buta Warna?"
                        name="buta_warna"
                        value={form.buta_warna}
                        onChange={handleChange}
                        required={isRequired("buta_warna")}
                        error={errors.buta_warna}
                        options={[
                            "Ya, Buta Warna Total",
                            "Ya, Buta Warna Partial / Sebagian",
                            "Tidak",
                        ]}
                    />

                    <CheckboxGroup
                        label="Apakah Anda Menggunakan Kaca Mata?"
                        description="Dapat memilih lebih dari 1 opsi."
                        name="kacamata_digunakan"
                        value={form.kacamata_digunakan}
                        onChange={handleChange}
                        required={isRequired("kacamata_digunakan")}
                        error={errors.kacamata_digunakan}
                        options={[
                            "Plus",
                            "Minus",
                            "Silinder",
                            "Tidak Menggunakan Kaca Mata",
                        ]}
                    />

                    <RadioGroup
                        label="Apakah Anda Menggunakan Alat Bantu Pendengaran?"
                        name="alat_bantu_pendengaran"
                        value={form.alat_bantu_pendengaran}
                        onChange={handleChange}
                        required={isRequired("alat_bantu_pendengaran")}
                        error={errors.alat_bantu_pendengaran}
                        options={["Ya", "Tidak"]}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Kondisi Tangan
                </h4>

                <div className="space-y-5">
                    <RadioGroup
                        label="Ketika Menulis Anda Menggunakan Tangan?"
                        name="tangan_dominan"
                        value={form.tangan_dominan}
                        onChange={handleChange}
                        required={isRequired("tangan_dominan")}
                        error={errors.tangan_dominan}
                        options={["Kanan", "Kiri"]}
                    />

                    <RadioGroup
                        label="Apakah Tangan Anda Sering Gemetar?"
                        name="tangan_gemetar"
                        value={form.tangan_gemetar}
                        onChange={handleChange}
                        required={isRequired("tangan_gemetar")}
                        error={errors.tangan_gemetar}
                        options={["Ya", "Tidak"]}
                    />

                    <RadioGroup
                        label="Apakah Tangan Anda Sering Berkeringat?"
                        name="tangan_berkeringat"
                        value={form.tangan_berkeringat}
                        onChange={handleChange}
                        required={isRequired("tangan_berkeringat")}
                        error={errors.tangan_berkeringat}
                        options={["Ya", "Tidak"]}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Riwayat Penyakit
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Memiliki Riwayat Penyakit"
                        name="memiliki_riwayat_penyakit"
                        value={form.memiliki_riwayat_penyakit}
                        onChange={handleChange}
                        required={isRequired("memiliki_riwayat_penyakit")}
                        error={errors.memiliki_riwayat_penyakit}
                        options={["Tidak Ada", "Ada"]}
                    />

                    <Textarea
                        label="Detail Riwayat Penyakit"
                        name="riwayat_penyakit"
                        value={form.riwayat_penyakit}
                        onChange={handleChange}
                        placeholder="Contoh: Asma, diabetes, hipertensi"
                        required={showRiwayatPenyakit}
                        error={errors.riwayat_penyakit}
                        disabled={!showRiwayatPenyakit}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Punya Penyakit Genetik"
                        name="punya_penyakit_genetik"
                        value={form.punya_penyakit_genetik}
                        onChange={handleChange}
                        required={isRequired("punya_penyakit_genetik")}
                        error={errors.punya_penyakit_genetik}
                        options={["Ya", "Tidak"]}
                    />

                    <Input
                        label="Nama Penyakit"
                        name="nama_penyakit"
                        value={form.nama_penyakit}
                        onChange={handleChange}
                        placeholder="Isi jika memilih Ya"
                        required={showPenyakitGenetik}
                        error={errors.nama_penyakit}
                        disabled={!showPenyakitGenetik}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Riwayat Kronis"
                        name="riwayat_kronis"
                        value={form.riwayat_kronis}
                        onChange={handleChange}
                        required={isRequired("riwayat_kronis")}
                        error={errors.riwayat_kronis}
                        options={["Ya", "Tidak"]}
                    />

                    <RadioGroup
                        label="Apakah Anda Memiliki Riwayat Penyakit Menular? Contoh: TBC"
                        name="riwayat_penyakit_menular"
                        value={form.riwayat_penyakit_menular}
                        onChange={handleChange}
                        required={isRequired("riwayat_penyakit_menular")}
                        error={errors.riwayat_penyakit_menular}
                        options={["Ya", "Tidak"]}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Alergi & Obat
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Memiliki Alergi"
                        name="memiliki_alergi"
                        value={form.memiliki_alergi}
                        onChange={handleChange}
                        required={isRequired("memiliki_alergi")}
                        error={errors.memiliki_alergi}
                        options={["Tidak Ada", "Ada"]}
                    />

                    <Input
                        label="Detail Alergi"
                        name="alergi"
                        value={form.alergi}
                        onChange={handleChange}
                        placeholder="Contoh: Obat, makanan, debu"
                        required={showAlergi}
                        error={errors.alergi}
                        disabled={!showAlergi}
                    />
                </div>

                <div className="mt-5">
                    <Textarea
                        label="Obat yang Sedang Dikonsumsi"
                        name="obat_dikonsumsi"
                        value={form.obat_dikonsumsi}
                        onChange={handleChange}
                        placeholder="Tulis nama obat jika ada"
                        required={isRequired("obat_dikonsumsi")}
                        error={errors.obat_dikonsumsi}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Psikologis
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Pernah Pengobatan Psikolog"
                        name="pengobatan_psikolog"
                        value={form.pengobatan_psikolog}
                        onChange={handleChange}
                        required={isRequired("pengobatan_psikolog")}
                        error={errors.pengobatan_psikolog}
                        options={["Ya", "Tidak"]}
                    />

                    <Input
                        label="Kapan Dilakukan"
                        name="kapan_dilakukan"
                        value={form.kapan_dilakukan}
                        onChange={handleChange}
                        placeholder="Contoh: 2022 / 3 bulan lalu"
                        required={showPengobatanPsikolog}
                        error={errors.kapan_dilakukan}
                        disabled={!showPengobatanPsikolog}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Kecelakaan & Operasi
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Pernah Kecelakaan"
                        name="pernah_kecelakaan"
                        value={form.pernah_kecelakaan}
                        onChange={handleChange}
                        required={isRequired("pernah_kecelakaan")}
                        error={errors.pernah_kecelakaan}
                        options={["Ya", "Tidak"]}
                    />

                    <Input
                        label="Bagian Tubuh yang Kecelakaan"
                        name="bagian_tubuh_kecelakaan"
                        value={form.bagian_tubuh_kecelakaan}
                        onChange={handleChange}
                        placeholder="Contoh: Tangan kanan, kaki kiri"
                        required={showKecelakaan}
                        error={errors.bagian_tubuh_kecelakaan}
                        disabled={!showKecelakaan}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Pernah Operasi"
                        name="pernah_operasi"
                        value={form.pernah_operasi}
                        onChange={handleChange}
                        required={isRequired("pernah_operasi")}
                        error={errors.pernah_operasi}
                        options={["Ya", "Tidak"]}
                    />

                    <Textarea
                        label="Diagnosa Dokter"
                        name="diagnosa_dokter"
                        value={form.diagnosa_dokter}
                        onChange={handleChange}
                        placeholder="Isi diagnosa dokter jika pernah operasi"
                        required={showOperasi}
                        error={errors.diagnosa_dokter}
                        disabled={!showOperasi}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Riwayat Perawatan
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Pernah Dirawat di Rumah Sakit"
                        name="pernah_dirawat"
                        value={form.pernah_dirawat}
                        onChange={handleChange}
                        required={isRequired("pernah_dirawat")}
                        error={errors.pernah_dirawat}
                        options={["Tidak Pernah", "Pernah"]}
                    />

                    <Input
                        label="Tahun Dirawat"
                        name="tahun_dirawat"
                        value={form.tahun_dirawat}
                        onChange={handleChange}
                        placeholder="Contoh: 2022"
                        required={showTahunDirawat}
                        error={errors.tahun_dirawat}
                        disabled={!showTahunDirawat}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Program Kehamilan
                </h4>

                <RadioGroup
                    label="Apakah Anda Saat Ini Sedang Menjalani Program Kehamilan?"
                    name="program_kehamilan"
                    value={form.program_kehamilan}
                    onChange={handleChange}
                    required={isRequired("program_kehamilan")}
                    error={errors.program_kehamilan}
                    options={["Ya", "Tidak"]}
                />
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Catatan Tambahan
                </h4>

                <Textarea
                    label="Catatan Kesehatan Tambahan"
                    name="catatan_kesehatan"
                    value={form.catatan_kesehatan}
                    onChange={handleChange}
                    rows={4}
                    placeholder="Tulis catatan tambahan jika ada"
                    required={isRequired("catatan_kesehatan")}
                    error={errors.catatan_kesehatan}
                />
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
        <p className="mt-2 text-xs font-semibold text-red-500">
            {message}
        </p>
    );
}

function fieldClass(error, disabled = false) {
    return `w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 ${
        disabled
            ? "cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400"
            : error
            ? "border-red-300 bg-red-50 focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-100"
            : "border-slate-200 bg-white focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
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
    suffix,
    disabled = false,
}) {
    return (
        <div>
            <FieldLabel label={label} required={required} />

            <div className="relative">
                <input
                    type={type}
                    name={name}
                    value={value ?? ""}
                    onChange={onChange}
                    placeholder={placeholder || `Masukkan ${label.toLowerCase()}`}
                    disabled={disabled}
                    className={`${fieldClass(error, disabled)} ${
                        suffix ? "pr-14" : ""
                    }`}
                />

                {suffix && (
                    <span className="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">
                        {suffix}
                    </span>
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
    required = false,
    error,
    disabled = false,
}) {
    return (
        <div>
            <FieldLabel label={label} required={required} />

            <select
                name={name}
                value={value ?? ""}
                onChange={onChange}
                disabled={disabled}
                className={fieldClass(error, disabled)}
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

function RadioGroup({
    label,
    name,
    value,
    onChange,
    options = [],
    required = false,
    error,
}) {
    return (
        <div className={`rounded-2xl border p-4 ${
            error ? "border-red-200 bg-red-50" : "border-slate-200 bg-slate-50"
        }`}>
            <FieldLabel label={label} required={required} />

            <div className="mt-3 space-y-3">
                {options.map((item) => (
                    <label
                        key={item}
                        className="flex cursor-pointer items-center gap-3 text-sm font-medium text-slate-700"
                    >
                        <input
                            type="radio"
                            name={name}
                            value={item}
                            checked={(value ?? "") === item}
                            onChange={onChange}
                            className="h-4 w-4 accent-emerald-600"
                        />
                        <span>{item}</span>
                    </label>
                ))}
            </div>

            <ErrorMessage message={error} />
        </div>
    );
}

function CheckboxGroup({
    label,
    description,
    name,
    value,
    onChange,
    options = [],
    required = false,
    error,
}) {
    const selectedValues = Array.isArray(value) ? value : [];

    const handleCheckboxChange = (checkedValue) => {
        let nextValues = selectedValues.includes(checkedValue)
            ? selectedValues.filter((item) => item !== checkedValue)
            : [...selectedValues, checkedValue];

        if (checkedValue === "Tidak Menggunakan Kaca Mata") {
            nextValues = selectedValues.includes(checkedValue)
                ? []
                : ["Tidak Menggunakan Kaca Mata"];
        } else {
            nextValues = nextValues.filter(
                (item) => item !== "Tidak Menggunakan Kaca Mata"
            );
        }

        onChange({
            target: {
                name,
                value: nextValues,
            },
        });
    };

    return (
        <div className={`rounded-2xl border p-4 ${
            error ? "border-red-200 bg-red-50" : "border-slate-200 bg-slate-50"
        }`}>
            <FieldLabel label={label} required={required} />

            {description && (
                <p className="mt-1 text-xs font-semibold text-slate-500">
                    {description}
                </p>
            )}

            <div className="mt-3 space-y-3">
                {options.map((item) => (
                    <label
                        key={item}
                        className="flex cursor-pointer items-center gap-3 text-sm font-medium text-slate-700"
                    >
                        <input
                            type="checkbox"
                            name={name}
                            value={item}
                            checked={selectedValues.includes(item)}
                            onChange={() => handleCheckboxChange(item)}
                            className="h-4 w-4 rounded accent-emerald-600"
                        />
                        <span>{item}</span>
                    </label>
                ))}
            </div>

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
    disabled = false,
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
                disabled={disabled}
                className={`${fieldClass(error, disabled)} resize-none`}
            />

            <ErrorMessage message={error} />
        </div>
    );
}