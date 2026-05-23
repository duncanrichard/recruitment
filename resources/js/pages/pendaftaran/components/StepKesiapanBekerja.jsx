import React from "react";

export default function StepKesiapanBekerja({
    form = {},
    handleChange,
    errors = {},
}) {
    const penempatanValue =
        form.penempatan || form.penempatan_luar_jawa_tengah || [];

    const backgroundCheckingValue =
        form.proses_bkhang || form.background_checking || [];

    const pernyataanValue =
        form.dapat_dipertanggung_jawabkan ||
        form.pernyataan_data_benar ||
        [];

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-cyan-100 bg-cyan-50 p-4">
                <h3 className="text-base font-bold text-cyan-800">
                    Kesiapan Bekerja
                </h3>

                <p className="mt-1 text-sm text-cyan-600">
                    Lengkapi informasi kesiapan bekerja sesuai pertanyaan berikut.
                </p>
            </div>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Informasi Kesiapan
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Kapan Anda siap bekerja di perusahaan ini jika diterima?"
                        name="kapan_siap_bekerja"
                        value={form.kapan_siap_bekerja || form.tanggal_siap_kerja || ""}
                        onChange={handleChange}
                        placeholder="Contoh: Secepatnya / 1 minggu setelah diterima"
                        error={errors.kapan_siap_bekerja || errors.tanggal_siap_kerja}
                        required
                    />

                    <Input
                        label="Berapa ekspetasi gaji yang Anda harapkan? Sebutkan dalam bentuk angka"
                        name="ekpetasi_gaji"
                        value={form.ekpetasi_gaji || form.gaji_diharapkan || ""}
                        onChange={handleChange}
                        placeholder="Contoh: 4000000"
                        error={errors.ekpetasi_gaji || errors.gaji_diharapkan}
                        inputMode="numeric"
                        required
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Penempatan
                </h4>

                <CheckboxGroup
                    label="Bersedia kah penempatan di luar Jawa Tengah?"
                    name="penempatan"
                    value={penempatanValue}
                    onChange={handleChange}
                    error={errors.penempatan || errors.penempatan_luar_jawa_tengah}
                    options={["JAWA BARAT", "JAWA TIMUR", "TIDAK BERSEDIA"]}
                    required
                />
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Background Checking
                </h4>

                <CheckboxGroup
                    label="Sebagai bagian dari prosedur rekrutmen perusahaan, kami melakukan proses background checking bagi kandidat yang lolos tahapan seleksi. Apakah Anda bersedia mengikuti proses tersebut?"
                    name="proses_bkhang"
                    value={backgroundCheckingValue}
                    onChange={handleChange}
                    error={errors.proses_bkhang || errors.background_checking}
                    options={["BERSEDIA", "TIDAK BERSEDIA"]}
                    singleChoice
                    required
                />
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Pernyataan
                </h4>

                <CheckboxGroup
                    label="Saya menyatakan bahwa data yang saya isi adalah benar adanya dan dapat dipertanggung jawabkan"
                    name="dapat_dipertanggung_jawabkan"
                    value={pernyataanValue}
                    onChange={handleChange}
                    error={
                        errors.dapat_dipertanggung_jawabkan ||
                        errors.pernyataan_data_benar
                    }
                    options={["YA"]}
                    singleChoice
                    required
                />
            </section>
        </div>
    );
}

function FieldLabel({ label, required = false }) {
    return (
        <label className="mb-2 block text-sm font-semibold text-slate-700">
            {label}
            {required && <span className="ml-1 text-red-500">*</span>}
        </label>
    );
}

function ErrorMessage({ message }) {
    if (!message) {
        return null;
    }

    return (
        <p className="mt-2 text-xs font-semibold text-red-500">
            {message}
        </p>
    );
}

function fieldClass(error, disabled = false) {
    return `w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 ${
        disabled
            ? "cursor-not-allowed border-slate-200 bg-slate-100 text-slate-500 placeholder:text-slate-400"
            : error
            ? "border-red-300 bg-red-50 focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-100"
            : "border-slate-200 bg-slate-50 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
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
    disabled = false,
    inputMode,
    required = false,
}) {
    return (
        <div>
            <FieldLabel label={label} required={required} />

            <input
                type={type}
                name={name}
                value={value ?? ""}
                onChange={onChange}
                placeholder={placeholder || `Masukkan ${label.toLowerCase()}`}
                className={fieldClass(error, disabled)}
                disabled={disabled}
                inputMode={inputMode}
                autoComplete="off"
            />

            <ErrorMessage message={error} />
        </div>
    );
}

function CheckboxGroup({
    label,
    name,
    value,
    onChange,
    options = [],
    error,
    required = false,
    singleChoice = false,
}) {
    const selectedValues = normalizeCheckboxValue(value);

    const handleCheckboxChange = (option) => (event) => {
        const checked = event.target.checked;

        let nextValues = checked
            ? [...selectedValues, option]
            : selectedValues.filter((item) => item !== option);

        if (singleChoice && checked) {
            nextValues = [option];
        }

        if (option === "TIDAK BERSEDIA" && checked) {
            nextValues = ["TIDAK BERSEDIA"];
        }

        if (option !== "TIDAK BERSEDIA" && checked) {
            nextValues = nextValues.filter((item) => item !== "TIDAK BERSEDIA");
        }

        onChange({
            target: {
                name,
                value: nextValues,
                type: "checkbox-group",
                checked,
            },
        });
    };

    return (
        <div>
            <FieldLabel label={label} required={required} />

            <div className="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                {options.map((option) => (
                    <label
                        key={option}
                        className={`flex cursor-pointer items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold transition ${
                            selectedValues.includes(option)
                                ? "border-cyan-400 bg-cyan-50 text-cyan-800"
                                : "border-slate-200 bg-slate-50 text-slate-700 hover:border-cyan-200 hover:bg-cyan-50"
                        }`}
                    >
                        <input
                            type="checkbox"
                            checked={selectedValues.includes(option)}
                            onChange={handleCheckboxChange(option)}
                            className="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                        />

                        <span>{option}</span>
                    </label>
                ))}
            </div>

            <ErrorMessage message={error} />
        </div>
    );
}

function normalizeCheckboxValue(value) {
    if (Array.isArray(value)) {
        return value.filter(Boolean);
    }

    if (typeof value === "string" && value.trim() !== "") {
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