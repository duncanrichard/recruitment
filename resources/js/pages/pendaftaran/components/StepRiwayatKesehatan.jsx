import React from "react";
import Select2 from "react-select";

export default function StepRiwayatKesehatan({
    form,
    handleChange,
    errors = {},
    masterOptions = {},
}) {
    const showNamaAlergi = form.punya_alergi === "Ya";
    const showNamaPenyakitGenetik = form.punya_penyakit_genetik === "Ya";
    const showPengobatanPsikolog = form.pengobatan_psikolog === "Ya";
    const showKecelakaan = form.pernah_kecelakaan === "Ya";
    const showOperasi = form.pernah_operasi === "Ya";

    const opsiKacamataOptions =
        Array.isArray(masterOptions.opsi_kacamata) &&
        masterOptions.opsi_kacamata.length > 0
            ? masterOptions.opsi_kacamata
            : [];

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                <h3 className="text-base font-bold text-emerald-800">
                    Riwayat Kesehatan
                </h3>

                <p className="mt-1 text-sm text-emerald-600">
                    Lengkapi data kesehatan sesuai kondisi sebenarnya.
                </p>
            </div>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Pemeriksaan Umum
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <SelectField
                        label="Golongan Darah"
                        name="golongan_darah"
                        value={form.golongan_darah || form.gol_darah || ""}
                        onChange={handleChange}
                        error={errors.golongan_darah || errors.gol_darah}
                        options={[
                            { value: "A", label: "A" },
                            { value: "B", label: "B" },
                            { value: "AB", label: "AB" },
                            { value: "O", label: "O" },
                            { value: "Tidak Tahu", label: "Tidak Tahu" },
                        ]}
                    />

                    <Input
                        label="Tinggi Badan"
                        type="number"
                        name="tinggi_badan"
                        value={form.tinggi_badan}
                        onChange={handleChange}
                        placeholder="Contoh: 170"
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
                        error={errors.buta_warna}
                        options={[
                            "Ya, Buta Warna Total",
                            "Ya, Buta Warna Partial / Sebagian",
                            "Tidak",
                        ]}
                    />

                    <SelectField
                        label="Apakah Anda Menggunakan Kaca Mata?"
                        name="opsi_kacamata_id"
                        value={form.opsi_kacamata_id || ""}
                        onChange={handleChange}
                        error={errors.opsi_kacamata_id}
                        placeholder="Pilih opsi kacamata"
                        options={opsiKacamataOptions}
                    />

                    <RadioGroup
                        label="Apakah Anda Menggunakan Alat Bantu Pendengaran?"
                        name="alat_bantu_dengar"
                        value={
                            form.alat_bantu_dengar ||
                            form.alat_bantu_pendengaran ||
                            ""
                        }
                        onChange={handleChange}
                        error={
                            errors.alat_bantu_dengar ||
                            errors.alat_bantu_pendengaran
                        }
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
                        name="menulis_dengan_tangan"
                        value={
                            form.menulis_dengan_tangan ||
                            form.tangan_dominan ||
                            ""
                        }
                        onChange={handleChange}
                        error={
                            errors.menulis_dengan_tangan ||
                            errors.tangan_dominan
                        }
                        options={["Kanan", "Kiri"]}
                    />

                    <RadioGroup
                        label="Apakah Tangan Anda Sering Gemetar?"
                        name="sering_gemetar"
                        value={form.sering_gemetar || form.tangan_gemetar || ""}
                        onChange={handleChange}
                        error={errors.sering_gemetar || errors.tangan_gemetar}
                        options={["Ya", "Tidak"]}
                    />

                    <RadioGroup
                        label="Apakah Tangan Anda Sering Berkeringat?"
                        name="tangan_sering_berkeringat"
                        value={
                            form.tangan_sering_berkeringat ||
                            form.tangan_berkeringat ||
                            ""
                        }
                        onChange={handleChange}
                        error={
                            errors.tangan_sering_berkeringat ||
                            errors.tangan_berkeringat
                        }
                        options={["Ya", "Tidak"]}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Riwayat Penyakit
                </h4>

                <div className="space-y-5">
                    <RadioGroup
                        label="Apakah Anda Memiliki Riwayat Penyakit Menular? Contoh: TBC"
                        name="penyakit_menular"
                        value={
                            form.penyakit_menular ||
                            form.riwayat_penyakit_menular ||
                            ""
                        }
                        onChange={handleChange}
                        error={
                            errors.penyakit_menular ||
                            errors.riwayat_penyakit_menular
                        }
                        options={["Ya", "Tidak"]}
                    />

                    <RadioGroup
                        label="Apakah Anda Punya Penyakit Genetik?"
                        name="punya_penyakit_genetik"
                        value={form.punya_penyakit_genetik}
                        onChange={handleChange}
                        error={errors.punya_penyakit_genetik}
                        options={["Ya", "Tidak"]}
                    />

                    <Input
                        label="Nama Penyakit"
                        name="nama_penyakit"
                        value={form.nama_penyakit}
                        onChange={handleChange}
                        placeholder="Isi jika memilih Ya"
                        error={errors.nama_penyakit}
                        disabled={!showNamaPenyakitGenetik}
                    />

                    <RadioGroup
                        label="Apakah Anda Memiliki Riwayat Penyakit Kronis?"
                        name="riwayat_kronis"
                        value={form.riwayat_kronis}
                        onChange={handleChange}
                        error={errors.riwayat_kronis}
                        options={["Ya", "Tidak"]}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Alergi
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <RadioGroup
                        label="Apakah Anda Punya Alergi?"
                        name="punya_alergi"
                        value={form.punya_alergi || form.memiliki_alergi || ""}
                        onChange={handleChange}
                        error={errors.punya_alergi || errors.memiliki_alergi}
                        options={["Ya", "Tidak"]}
                    />

                    <Input
                        label="Nama Alergi"
                        name="nama_alergi"
                        value={form.nama_alergi || form.alergi || ""}
                        onChange={handleChange}
                        placeholder="Contoh: Obat, makanan, debu"
                        error={errors.nama_alergi || errors.alergi}
                        disabled={!showNamaAlergi}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Psikologis
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <RadioGroup
                        label="Apakah Anda Pernah Pengobatan Psikolog?"
                        name="pengobatan_psikolog"
                        value={form.pengobatan_psikolog}
                        onChange={handleChange}
                        error={errors.pengobatan_psikolog}
                        options={["Ya", "Tidak"]}
                    />

                    <Input
                        label="Kapan Dilakukan"
                        name="kapan_dilakukan"
                        value={form.kapan_dilakukan}
                        onChange={handleChange}
                        placeholder="Contoh: 2022 / 3 bulan lalu"
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
                    <RadioGroup
                        label="Apakah Anda Pernah Kecelakaan?"
                        name="pernah_kecelakaan"
                        value={form.pernah_kecelakaan}
                        onChange={handleChange}
                        error={errors.pernah_kecelakaan}
                        options={["Ya", "Tidak"]}
                    />

                    <Input
                        label="Bagian Tubuh yang Kecelakaan"
                        name="bagian_tubuh_kecelakaan"
                        value={form.bagian_tubuh_kecelakaan}
                        onChange={handleChange}
                        placeholder="Contoh: Tangan kanan, kaki kiri"
                        error={errors.bagian_tubuh_kecelakaan}
                        disabled={!showKecelakaan}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <RadioGroup
                        label="Apakah Anda Pernah Operasi?"
                        name="pernah_operasi"
                        value={form.pernah_operasi}
                        onChange={handleChange}
                        error={errors.pernah_operasi}
                        options={["Ya", "Tidak"]}
                    />

                    <Textarea
                        label="Diagnosa Dokter"
                        name="diagnosa_dokter"
                        value={form.diagnosa_dokter}
                        onChange={handleChange}
                        placeholder="Isi diagnosa dokter jika pernah operasi"
                        error={errors.diagnosa_dokter}
                        disabled={!showOperasi}
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
                    error={errors.program_kehamilan}
                    options={["Ya", "Tidak"]}
                />
            </section>
        </div>
    );
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
                item?.uuid ??
                item?.code ??
                item?.opsi ??
                item?.label ??
                "";

            const label =
                item?.label ??
                item?.name ??
                item?.nama ??
                item?.opsi ??
                item?.text ??
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

function FieldLabel({ label }) {
    return (
        <label className="mb-2 block text-sm font-semibold text-slate-700">
            {label}
        </label>
    );
}

function ErrorMessage({ message }) {
    if (!message) return null;

    return <p className="mt-2 text-xs font-semibold text-red-500">{message}</p>;
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
    error,
    suffix,
    disabled = false,
}) {
    return (
        <div>
            <FieldLabel label={label} />

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
                    autoComplete="off"
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

function SelectField({
    label,
    name,
    value,
    onChange,
    options = [],
    error,
    placeholder,
    disabled = false,
    isLoading = false,
}) {
    const normalizedOptions = normalizeOptions(options);
    const cleanValue = value ?? "";

    const selectedOption =
        normalizedOptions.find(
            (item) => String(item.value) === String(cleanValue)
        ) ||
        normalizedOptions.find(
            (item) => String(item.id) === String(cleanValue)
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
        <div>
            <FieldLabel label={label} />

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
                        minHeight: "48px",
                        borderRadius: "1rem",
                        borderColor: error
                            ? "#fca5a5"
                            : state.isFocused
                            ? "#10b981"
                            : "#e2e8f0",
                        backgroundColor: disabled
                            ? "#f1f5f9"
                            : error
                            ? "#fef2f2"
                            : "#ffffff",
                        boxShadow: state.isFocused
                            ? error
                                ? "0 0 0 4px #fee2e2"
                                : "0 0 0 4px #d1fae5"
                            : "none",
                        cursor: disabled ? "not-allowed" : "default",
                        "&:hover": {
                            borderColor: error ? "#ef4444" : "#10b981",
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
                        color: state.isFocused ? "#059669" : "#64748b",
                        "&:hover": {
                            color: "#059669",
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
                        borderRadius: "1rem",
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
                            ? "#059669"
                            : state.isFocused
                            ? "#ecfdf5"
                            : "#ffffff",
                        color: state.isSelected ? "#ffffff" : "#0f172a",
                        "&:active": {
                            backgroundColor: "#d1fae5",
                        },
                    }),
                }}
            />

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
    error,
}) {
    return (
        <div
            className={`rounded-2xl border p-4 ${
                error
                    ? "border-red-200 bg-red-50"
                    : "border-slate-200 bg-slate-50"
            }`}
        >
            <FieldLabel label={label} />

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

function Textarea({
    label,
    name,
    value,
    onChange,
    placeholder,
    rows = 3,
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
                rows={rows}
                placeholder={placeholder}
                disabled={disabled}
                className={`${fieldClass(error, disabled)} resize-none`}
            />

            <ErrorMessage message={error} />
        </div>
    );
}