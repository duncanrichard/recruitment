import React, { useEffect, useMemo, useState } from "react";
import Select2 from "react-select";

const STAGE_COLORS = {
    administrasi: "bg-slate-100 text-slate-700 ring-slate-200",
    jadwal_test_zoom: "bg-blue-50 text-blue-700 ring-blue-100",
    hasil_test_zoom_lolos: "bg-emerald-50 text-emerald-700 ring-emerald-100",
    hasil_test_zoom_gagal: "bg-rose-50 text-rose-700 ring-rose-100",
    jadwal_test_mmpi: "bg-indigo-50 text-indigo-700 ring-indigo-100",
    hasil_test_mmpi_lolos: "bg-emerald-50 text-emerald-700 ring-emerald-100",
    hasil_test_mmpi_gagal: "bg-rose-50 text-rose-700 ring-rose-100",
    jadwal_interview: "bg-violet-50 text-violet-700 ring-violet-100",
    interview_reschedule: "bg-sky-50 text-sky-700 ring-sky-100",
    interview_lolos: "bg-emerald-50 text-emerald-700 ring-emerald-100",
    interview_tidak_lolos: "bg-rose-50 text-rose-700 ring-rose-100",
    interview_dipertimbangkan: "bg-amber-50 text-amber-700 ring-amber-100",
};

const STAGE_BAR_COLORS = {
    administrasi: "from-slate-400 to-slate-600",
    jadwal_test_zoom: "from-blue-400 to-blue-600",
    hasil_test_zoom_lolos: "from-emerald-400 to-emerald-600",
    hasil_test_zoom_gagal: "from-rose-400 to-rose-600",
    jadwal_test_mmpi: "from-indigo-400 to-indigo-600",
    hasil_test_mmpi_lolos: "from-emerald-400 to-emerald-600",
    hasil_test_mmpi_gagal: "from-rose-400 to-rose-600",
    jadwal_interview: "from-violet-400 to-violet-600",
    interview_reschedule: "from-sky-400 to-sky-600",
    interview_lolos: "from-emerald-400 to-emerald-600",
    interview_tidak_lolos: "from-rose-400 to-rose-600",
    interview_dipertimbangkan: "from-amber-400 to-amber-600",
};

const EMPTY_FILTERS = { date_from: "", date_to: "", company_id: "", position_id: "", source_id: "" };

export default function DashboardPage() {
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(false);
    const [errorMessage, setErrorMessage] = useState("");
    const [filters, setFilters] = useState(EMPTY_FILTERS);
    const [aiResult, setAiResult] = useState(null);
    const [aiLoading, setAiLoading] = useState(false);
    const [aiError, setAiError] = useState("");

    const fetchSummary = async (nextFilters = filters) => {
        setLoading(true);
        setErrorMessage("");
        setAiResult(null);
        setAiError("");

        try {
            const query = new URLSearchParams(Object.entries(nextFilters).filter(([, value]) => value));
            const response = await fetch(`/admin/dashboard/summary${query.size ? `?${query.toString()}` : ""}`, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                setErrorMessage(result.message || "Gagal mengambil data dashboard.");
                return;
            }

            setSummary(result.data || null);
        } catch (error) {
            console.error("Gagal mengambil data dashboard:", error);
            setErrorMessage("Terjadi kesalahan saat mengambil data dashboard.");
        } finally {
            setLoading(false);
        }
    };

    const runAiAnalysis = async () => {
        setAiLoading(true);
        setAiError("");

        try {
            const response = await fetch("/admin/dashboard/ai-insights", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
                },
                body: JSON.stringify(summary?.active_filters || EMPTY_FILTERS),
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                setAiError(result.message || "Analisis AI belum dapat dibuat.");
                return;
            }

            setAiResult(result.data || null);
        } catch (error) {
            console.error("Gagal membuat insight AI dashboard:", error);
            setAiError("9Router tidak dapat dihubungi. Silakan coba kembali.");
        } finally {
            setAiLoading(false);
        }
    };

    useEffect(() => {
        fetchSummary(EMPTY_FILTERS);
    }, []);

    const stages = useMemo(() => summary?.stages || [], [summary]);
    const monthlyApplicants = useMemo(() => summary?.monthly_applicants || [], [summary]);
    const insights = summary?.insights || {};
    const companyDistribution = summary?.company_distribution || [];
    const funnel = summary?.funnel || { steps: [], bottleneck: null };
    const filterOptions = summary?.filter_options || { companies: [], positions: [], sources: [] };

    const highestStage = useMemo(() => {
        if (!stages.length) return null;
        return [...stages].sort((a, b) => Number(b.total || 0) - Number(a.total || 0))[0];
    }, [stages]);

    const totalFinal =
        Number(summary?.stage_counts?.interview_lolos || 0) +
        Number(summary?.stage_counts?.interview_tidak_lolos || 0) +
        Number(summary?.stage_counts?.interview_dipertimbangkan || 0);

    return (
        <div className="min-h-screen bg-slate-50/70">
            <div className="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <HeroSection
                    summary={summary}
                    loading={loading}
                    onRefresh={() => fetchSummary(filters)}
                    highestStage={highestStage}
                />

                {errorMessage && (
                    <div className="rounded-3xl border border-rose-100 bg-rose-50 px-5 py-4 shadow-sm">
                        <p className="text-sm font-bold text-rose-700">{errorMessage}</p>
                    </div>
                )}

                <DashboardFilters
                    filters={filters}
                    options={filterOptions}
                    loading={loading}
                    onChange={(name, value) => setFilters((current) => ({ ...current, [name]: value }))}
                    onApply={() => fetchSummary(filters)}
                    onReset={() => { setFilters(EMPTY_FILTERS); fetchSummary(EMPTY_FILTERS); }}
                />

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <SummaryCard
                        label="Total Pelamar"
                        value={summary?.total_pelamar || 0}
                        description="Semua data pelamar"
                        icon="K"
                        accent="from-indigo-500 to-violet-500"
                    />
                    <SummaryCard
                        label="Jadwal Test Zoom"
                        value={summary?.total_jadwal_test_zoom || 0}
                        description="Pelamar dijadwalkan Zoom"
                        icon="Z"
                        accent="from-blue-500 to-violet-500"
                    />
                    <SummaryCard
                        label="Jadwal Test MMPI"
                        value={summary?.total_jadwal_test_mmpi || 0}
                        description="Pelamar dijadwalkan MMPI"
                        icon="M"
                        accent="from-indigo-500 to-violet-500"
                    />
                    <SummaryCard
                        label="Jadwal Interview"
                        value={summary?.total_jadwal_interview || 0}
                        description="Pelamar masuk interview"
                        icon="I"
                        accent="from-amber-500 to-orange-500"
                    />
                </div>

                {loading && !summary ? (
                    <LoadingState />
                ) : (
                    <>
                        <InsightStrip insights={insights} />

                        <FunnelAnalysis funnel={funnel} />

                        <AiDashboardInsight result={aiResult} loading={aiLoading} error={aiError} disabled={!summary || loading} onAnalyze={runAiAnalysis} />

                        <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                            <AttentionPanel items={insights.attention_items || []} health={insights.health} />
                            <CompanyDistribution data={companyDistribution} />
                        </div>

                        <div className="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
                            <MonthlyApplicantsChart data={monthlyApplicants} />
                            <FinalStageCard summary={summary} totalFinal={totalFinal} />
                        </div>

                        <StageOverview stages={stages} totalPelamar={summary?.total_pelamar || 0} highestStage={highestStage} />
                    </>
                )}
            </div>
        </div>
    );
}

function AiDashboardInsight({ result, loading, error, disabled, onAnalyze }) {
    const groups = result ? [
        { title: "Sinyal Positif", items: result.strengths || [], tone: "border-indigo-100 bg-indigo-50/70", dot: "bg-indigo-500" },
        { title: "Risiko & Bottleneck", items: result.gaps || [], tone: "border-rose-100 bg-rose-50/60", dot: "bg-rose-500" },
        { title: "Rekomendasi Prioritas", items: result.follow_up || [], tone: "border-violet-100 bg-violet-50/70", dot: "bg-violet-500" },
    ] : [];

    return (
        <section className="relative overflow-hidden rounded-[2rem] border border-indigo-100 bg-white shadow-lg shadow-indigo-100/40">
            <div className="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-violet-200/30 blur-3xl" />
            <div className="relative border-b border-indigo-100 bg-gradient-to-r from-indigo-950 via-indigo-900 to-violet-900 p-6 text-white sm:p-7">
                <div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-start gap-4">
                        <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-lg font-black ring-1 ring-white/20">AI</div>
                        <div>
                            <div className="flex flex-wrap items-center gap-2"><p className="text-[11px] font-black uppercase tracking-[0.18em] text-violet-200">AI Executive Brief</p><span className="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-black text-indigo-100 ring-1 ring-white/15">9Router</span></div>
                            <h2 className="mt-2 text-2xl font-black">Analisis pipeline untuk keputusan HR</h2>
                            <p className="mt-1 max-w-2xl text-sm font-semibold leading-6 text-indigo-100/80">AI membaca funnel, tren, drop-off, dan antrean operasional dari data agregat sesuai filter dashboard.</p>
                        </div>
                    </div>
                    <button type="button" onClick={onAnalyze} disabled={disabled || loading} className="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-5 text-xs font-black text-indigo-950 shadow-lg transition hover:-translate-y-0.5 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-60"><span className={loading ? "animate-spin" : ""}>{loading ? "◌" : "✦"}</span>{loading ? "Menganalisis..." : result ? "Analisis Ulang" : "Buat Analisis AI"}</button>
                </div>
            </div>
            <div className="relative p-6 sm:p-7">
                {error && <div className="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold leading-6 text-rose-700">{error}</div>}
                {loading ? (
                    <div className="grid gap-4 md:grid-cols-3">{[0, 1, 2].map((item) => <div key={item} className="h-40 animate-pulse rounded-3xl bg-slate-100" />)}</div>
                ) : result ? (
                    <>
                        <div className="mb-5 rounded-3xl border border-slate-200 bg-slate-50/80 p-5"><p className="text-[10px] font-black uppercase tracking-[0.18em] text-indigo-600">Ringkasan AI</p><p className="mt-2 text-sm font-semibold leading-7 text-slate-700">{result.summary || "AI belum memberikan ringkasan."}</p></div>
                        <div className="grid gap-4 md:grid-cols-3">{groups.map((group) => <div key={group.title} className={`rounded-3xl border p-5 ${group.tone}`}><h3 className="text-sm font-black text-slate-900">{group.title}</h3>{group.items.length ? <ul className="mt-4 space-y-3">{group.items.map((item, index) => <li key={`${group.title}-${index}`} className="flex gap-3 text-xs font-semibold leading-5 text-slate-700"><span className={`mt-1.5 h-2 w-2 shrink-0 rounded-full ${group.dot}`} />{item}</li>)}</ul> : <p className="mt-4 text-xs font-semibold text-slate-500">Tidak ada temuan khusus.</p>}</div>)}</div>
                        <div className="mt-5 flex flex-col gap-2 border-t border-slate-100 pt-4 text-[11px] font-semibold text-slate-500 sm:flex-row sm:items-center sm:justify-between"><span>{result.disclaimer || "Insight AI merupakan pendukung keputusan; keputusan akhir tetap pada HR."}</span><span className="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 font-black text-slate-600">Model: {result.model || "9Router"}</span></div>
                    </>
                ) : (
                    <div className="rounded-3xl border border-dashed border-indigo-200 bg-indigo-50/40 px-6 py-8 text-center"><div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-indigo-600 shadow-sm ring-1 ring-indigo-100">✦</div><p className="mt-4 text-sm font-black text-slate-800">Analisis belum dibuat</p><p className="mx-auto mt-1 max-w-xl text-xs font-semibold leading-5 text-slate-500">Terapkan filter bila diperlukan, lalu jalankan analisis. Nama, email, nomor WhatsApp, dan identitas kandidat tidak dikirim ke AI.</p></div>
                )}
            </div>
        </section>
    );
}

function DashboardFilters({ filters, options, loading, onChange, onApply, onReset }) {
    const activeCount = Object.values(filters).filter(Boolean).length;

    return (
        <section className="overflow-visible rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
            <div className="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <p className="text-[10px] font-black uppercase tracking-[0.18em] text-indigo-600">Filter Insight</p>
                    <h2 className="mt-1 text-lg font-black text-slate-950">Fokuskan data HR</h2>
                </div>
                <span className={`w-fit rounded-full px-3 py-1.5 text-[11px] font-black ${activeCount ? "bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100" : "bg-slate-100 text-slate-500"}`}>{activeCount ? `${activeCount} filter aktif` : "Semua data"}</span>
            </div>
            <div className="p-5 sm:p-6">
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-12">
                    <div className="xl:col-span-2"><FilterInput label="Dari tanggal" type="date" value={filters.date_from} onChange={(value) => onChange("date_from", value)} /></div>
                    <div className="xl:col-span-2"><FilterInput label="Sampai tanggal" type="date" value={filters.date_to} min={filters.date_from} onChange={(value) => onChange("date_to", value)} /></div>
                    <div className="xl:col-span-3"><FilterSelect label="Perusahaan" value={filters.company_id} options={options.companies} placeholder="Semua perusahaan" onChange={(value) => onChange("company_id", value)} /></div>
                    <div className="xl:col-span-3"><FilterSelect label="Posisi" value={filters.position_id} options={options.positions} placeholder="Semua posisi" onChange={(value) => onChange("position_id", value)} /></div>
                    <div className="xl:col-span-2"><FilterSelect label="Sumber" value={filters.source_id} options={options.sources} placeholder="Semua sumber" onChange={(value) => onChange("source_id", value)} /></div>
                </div>
                <div className="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-xs font-semibold text-slate-500">Pencarian pada pilihan tersedia. Kosongkan filter untuk menampilkan seluruh data yang dapat diakses.</p>
                    <div className="flex gap-2 sm:shrink-0">
                        <button type="button" onClick={onReset} disabled={loading || !activeCount} className="h-11 flex-1 rounded-xl border border-slate-200 bg-white px-5 text-xs font-black text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 disabled:cursor-not-allowed disabled:opacity-40 sm:flex-none">Reset</button>
                        <button type="button" onClick={onApply} disabled={loading} className="h-11 flex-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 text-xs font-black text-white shadow-lg shadow-indigo-100 transition hover:-translate-y-0.5 hover:shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-60 sm:flex-none">{loading ? "Memuat..." : "Terapkan Filter"}</button>
                    </div>
                </div>
            </div>
        </section>
    );
}

function FilterInput({ label, value, onChange, ...props }) {
    return <label className="block"><span className="mb-2 block text-[10px] font-black uppercase tracking-[0.1em] text-slate-500">{label}</span><input {...props} value={value} onChange={(event) => onChange(event.target.value)} className="h-12 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 text-xs font-bold text-slate-700 outline-none transition hover:border-slate-300 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-50" /></label>;
}

function FilterSelect({ label, value, options = [], placeholder, onChange }) {
    const selectOptions = options.map((option) => ({ value: String(option.id), label: option.label }));
    const selected = selectOptions.find((option) => option.value === String(value)) || null;

    return <div><span className="mb-2 block text-[10px] font-black uppercase tracking-[0.1em] text-slate-500">{label}</span><Select2 isClearable isSearchable value={selected} options={selectOptions} placeholder={placeholder} noOptionsMessage={() => "Data tidak ditemukan"} onChange={(option) => onChange(option?.value || "")} menuPortalTarget={document.body} menuPosition="fixed" styles={filterSelectStyles} /></div>;
}

const filterSelectStyles = {
    control: (base, state) => ({ ...base, minHeight: 48, borderRadius: 12, borderColor: state.isFocused ? "#818cf8" : "#e2e8f0", backgroundColor: state.isFocused ? "#ffffff" : "rgba(248,250,252,.7)", boxShadow: state.isFocused ? "0 0 0 4px #eef2ff" : "none", cursor: "pointer", fontSize: 12, fontWeight: 700, ":hover": { borderColor: state.isFocused ? "#818cf8" : "#cbd5e1" } }),
    valueContainer: (base) => ({ ...base, padding: "0 14px" }),
    placeholder: (base) => ({ ...base, color: "#94a3b8" }),
    singleValue: (base) => ({ ...base, color: "#334155" }),
    indicatorSeparator: () => ({ display: "none" }),
    dropdownIndicator: (base) => ({ ...base, color: "#64748b", paddingRight: 12 }),
    clearIndicator: (base) => ({ ...base, color: "#94a3b8" }),
    menuPortal: (base) => ({ ...base, zIndex: 9999 }),
    menu: (base) => ({ ...base, borderRadius: 14, overflow: "hidden", border: "1px solid #e2e8f0", boxShadow: "0 18px 45px rgba(30,41,59,.16)" }),
    option: (base, state) => ({ ...base, padding: "11px 14px", cursor: "pointer", fontSize: 12, fontWeight: 700, color: state.isSelected ? "#ffffff" : "#334155", backgroundColor: state.isSelected ? "#4f46e5" : state.isFocused ? "#eef2ff" : "#ffffff", ":active": { backgroundColor: state.isSelected ? "#4f46e5" : "#e0e7ff" } }),
};

function FunnelAnalysis({ funnel }) {
    const steps = funnel?.steps || [];
    const bottleneck = funnel?.bottleneck;

    return (
        <SectionCard>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div><p className="text-xs font-black uppercase tracking-[0.18em] text-indigo-600">Conversion Funnel</p><h2 className="mt-2 text-2xl font-black text-slate-950">Konversi Antar Tahap</h2><p className="mt-1 text-sm font-semibold text-slate-500">Kandidat yang mencapai tahap berikutnya dan jumlah drop-off.</p></div>
                {bottleneck && Number(bottleneck.drop_off_rate || 0) > 0 && <div className="max-w-xs rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3"><p className="text-[10px] font-black uppercase tracking-wide text-violet-600">Titik perhatian terbesar</p><p className="mt-1 text-sm font-black text-violet-900">Menuju {bottleneck.label}</p><p className="mt-1 text-xs font-semibold text-violet-700">Drop-off {bottleneck.drop_off_rate}% ({formatNumber(bottleneck.drop_off)} kandidat)</p></div>}
            </div>
            <div className="mt-6 grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                {steps.map((step, index) => (
                    <div key={step.key} className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        <div className="flex items-center justify-between"><span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-[10px] font-black text-indigo-700">{String(index + 1).padStart(2, "0")}</span>{index > 0 && <span className="rounded-full bg-white px-2 py-1 text-[10px] font-black text-slate-500 ring-1 ring-slate-200">{step.step_rate}%</span>}</div>
                        <p className="mt-4 min-h-10 text-xs font-black leading-5 text-slate-700">{step.label}</p>
                        <p className="mt-1 text-3xl font-black text-slate-950">{formatNumber(step.total)}</p>
                        <div className="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200"><div className="h-full rounded-full bg-gradient-to-r from-indigo-600 to-violet-500" style={{ width: `${Math.min(Number(step.overall_rate || 0), 100)}%` }} /></div>
                        <p className="mt-2 text-[10px] font-bold text-slate-400">{step.overall_rate}% dari pelamar</p>
                        {index > 0 && Number(step.drop_off || 0) > 0 && <p className="mt-2 text-[10px] font-black text-rose-500">−{formatNumber(step.drop_off)} belum lanjut</p>}
                    </div>
                ))}
            </div>
            <p className="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-[11px] font-semibold leading-5 text-slate-500">Konversi dihitung dari kandidat yang tercatat mencapai setiap tahap dalam periode dan filter terpilih. Gunakan satu periode yang sama untuk perbandingan yang adil.</p>
        </SectionCard>
    );
}

function InsightStrip({ insights }) {
    const items = [
        ["Conversion Rate", String(Number(insights.conversion_rate || 0)) + "%", "Pelamar hingga lolos interview", "border-indigo-100 bg-indigo-50 text-indigo-700"],
        ["Kandidat Diterima", formatNumber(insights.accepted_candidates), "Lolos tahap interview", "border-emerald-100 bg-emerald-50 text-emerald-700"],
        ["Aktivitas Audit", formatNumber(insights.audits_today), String(insights.downloads_today || 0) + " download hari ini", "border-blue-100 bg-blue-50 text-blue-700"],
        ["Perlu Perhatian", formatNumber(Number(insights.failed_integrations || 0) + Number(insights.stale_integrations || 0)), "Integrasi gagal atau tertunda", "border-rose-100 bg-rose-50 text-rose-700"],
    ];

    return (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {items.map(([label, value, note, tone]) => (
                <div key={label} className={"rounded-3xl border p-5 " + tone}>
                    <p className="text-[11px] font-black uppercase tracking-[0.16em] opacity-70">{label}</p>
                    <p className="mt-2 text-3xl font-black">{value}</p>
                    <p className="mt-1 text-xs font-bold opacity-75">{note}</p>
                </div>
            ))}
        </div>
    );
}

function AttentionPanel({ items, health }) {
    return (
        <SectionCard>
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-xs font-black uppercase tracking-[0.18em] text-rose-600">Action Center</p>
                    <h2 className="mt-2 text-2xl font-black text-slate-950">Perlu Ditindaklanjuti</h2>
                </div>
                <span className={"rounded-full px-3 py-1 text-xs font-black " + (health === "healthy" ? "bg-emerald-50 text-emerald-700" : "bg-amber-50 text-amber-700")}>
                    {health === "healthy" ? "Sistem Sehat" : "Perlu Perhatian"}
                </span>
            </div>
            <div className="mt-5 space-y-3">
                {items.length ? items.map((item) => (
                    <button
                        key={item.key}
                        type="button"
                        onClick={() => item.menu && window.dispatchEvent(new CustomEvent("admin:navigate", { detail: { key: item.menu } }))}
                        className="flex w-full items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-left transition hover:border-indigo-200 hover:bg-white"
                    >
                        <span className="text-sm font-bold text-slate-700">{item.label}</span>
                        <span className="rounded-xl bg-white px-3 py-1 text-sm font-black text-slate-950 shadow-sm">{formatNumber(item.total)}</span>
                    </button>
                )) : (
                    <div className="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                        <p className="font-black text-emerald-800">Tidak ada anomali operasional.</p>
                        <p className="mt-1 text-sm font-semibold text-emerald-700">Queue dan proses recruitment tidak memiliki alert aktif.</p>
                    </div>
                )}
            </div>
        </SectionCard>
    );
}

function CompanyDistribution({ data }) {
    const max = Math.max(...data.map((item) => Number(item.total || 0)), 1);
    return (
        <SectionCard>
            <p className="text-xs font-black uppercase tracking-[0.18em] text-indigo-600">Distribusi</p>
            <h2 className="mt-2 text-2xl font-black text-slate-950">Pelamar per Perusahaan</h2>
            <p className="mt-1 text-sm font-semibold text-slate-500">Hanya menampilkan perusahaan yang dapat diakses akun ini.</p>
            <div className="mt-6 space-y-4">
                {data.length ? data.map((item) => (
                    <div key={item.id || item.name}>
                        <div className="mb-2 flex justify-between gap-3 text-sm">
                            <span className="truncate font-black text-slate-700">{item.name}</span>
                            <span className="font-black text-slate-950">{formatNumber(item.total)}</span>
                        </div>
                        <div className="h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div className="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-400" style={{ width: String(Math.max((Number(item.total || 0) / max) * 100, 4)) + "%" }} />
                        </div>
                    </div>
                )) : <EmptyState text="Belum ada distribusi perusahaan." />}
            </div>
        </SectionCard>
    );
}

function HeroSection({ summary, loading, onRefresh, highestStage }) {
    const insights = summary?.insights || {};
    const totalPelamar = Number(summary?.total_pelamar || 0);
    const interview = Number(summary?.total_jadwal_interview || 0);
    const accepted = Number(insights.accepted_candidates || 0);
    const attentionCount = Number(insights.failed_integrations || 0) +
        Number(insights.stale_integrations || 0) +
        Number(insights.offering_pending || 0) +
        Number(summary?.stage_counts?.interview_reschedule || 0);
    const interviewRate = totalPelamar > 0
        ? Math.round((interview / totalPelamar) * 100)
        : 0;
    const conversionRate = Number(insights.conversion_rate || 0);

    return (
        <section className="relative overflow-hidden rounded-[2rem] border border-indigo-200/70 bg-white shadow-xl shadow-indigo-100/50">
            <div className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-600 via-violet-500 to-fuchsia-500" />
            <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-violet-100/70 blur-3xl" />

            <div className="relative grid lg:grid-cols-[0.92fr_1.08fr]">
                <div className="flex flex-col justify-between bg-gradient-to-br from-indigo-950 via-indigo-900 to-violet-900 px-6 py-7 text-white lg:px-8 lg:py-8">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-indigo-100 backdrop-blur">
                            <span className="h-2 w-2 rounded-full bg-emerald-400 ring-4 ring-emerald-400/15" />
                            Executive Recruitment Overview
                        </div>

                        <h1 className="mt-5 max-w-xl text-3xl font-black leading-[1.12] sm:text-4xl">
                            Kendalikan pipeline kandidat dari satu ringkasan.
                        </h1>

                        <p className="mt-3 max-w-xl text-sm font-medium leading-6 text-indigo-100/80">
                            Fokus pada progres seleksi, tingkat konversi, dan kandidat yang membutuhkan tindakan berikutnya.
                        </p>
                    </div>

                    <div className="mt-7 flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            onClick={onRefresh}
                            disabled={loading}
                            className="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-indigo-950 shadow-lg shadow-indigo-950/20 transition hover:-translate-y-0.5 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span className={loading ? "animate-spin" : ""}>↻</span>
                            {loading ? "Memperbarui..." : "Perbarui Data"}
                        </button>

                        {highestStage && (
                            <div className="rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-bold text-indigo-50 backdrop-blur">
                                Fokus pipeline: <span className="font-black text-white">{highestStage.label}</span>
                            </div>
                        )}
                    </div>
                </div>

                <div className="p-6 lg:p-8">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <p className="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-600">Snapshot Pipeline</p>
                            <h2 className="mt-2 text-xl font-black text-slate-950">Kinerja rekrutmen saat ini</h2>
                        </div>
                        <span className={`rounded-full px-3 py-1.5 text-xs font-black ${attentionCount > 0 ? "bg-amber-50 text-amber-700 ring-1 ring-amber-100" : "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"}`}>
                            {attentionCount > 0 ? `${attentionCount} perlu tindakan` : "Pipeline sehat"}
                        </span>
                    </div>

                    <div className="mt-6 grid gap-3 sm:grid-cols-3">
                        <HeroMetric label="Total Pelamar" value={totalPelamar} note="Basis kandidat aktif" tone="indigo" />
                        <HeroMetric label="Masuk Interview" value={interview} note={`${interviewRate}% dari pelamar`} tone="violet" />
                        <HeroMetric label="Kandidat Lolos" value={accepted} note={`${conversionRate}% conversion`} tone="emerald" />
                    </div>

                    <div className="mt-5 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <div className="flex items-center justify-between gap-4 text-sm">
                            <span className="font-bold text-slate-600">Konversi pelamar menjadi kandidat lolos</span>
                            <span className="font-black text-indigo-700">{conversionRate}%</span>
                        </div>
                        <div className="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                            <div className="h-full rounded-full bg-gradient-to-r from-indigo-600 to-violet-500 transition-all duration-700" style={{ width: `${Math.min(conversionRate, 100)}%` }} />
                        </div>
                        <p className="mt-3 text-xs font-semibold text-slate-500">
                            {accepted > 0
                                ? `${accepted} dari ${totalPelamar} pelamar telah mencapai hasil interview lolos.`
                                : "Belum ada kandidat yang mencapai hasil interview lolos."}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    );
}

function HeroMetric({ label, value, note, tone = "indigo" }) {
    const tones = {
        indigo: "border-indigo-100 bg-indigo-50/70 text-indigo-700",
        violet: "border-violet-100 bg-violet-50/70 text-violet-700",
        emerald: "border-emerald-100 bg-emerald-50/70 text-emerald-700",
    };

    return (
        <div className={`rounded-2xl border p-4 ${tones[tone] || tones.indigo}`}>
            <p className="text-[10px] font-black uppercase tracking-[0.16em] opacity-75">{label}</p>
            <p className="mt-2 text-3xl font-black text-slate-950">{formatNumber(value)}</p>
            <p className="mt-1 text-xs font-bold opacity-75">{note}</p>
        </div>
    );
}

function MonthlyApplicantsChart({ data }) {
    const items = data || [];
    const maxValue = Math.max(...items.map((item) => Number(item.total || 0)), 0);
    const total = items.reduce((sum, item) => sum + Number(item.total || 0), 0);
    const average = items.length > 0 ? Math.round(total / items.length) : 0;
    const bestMonth = items.length ? [...items].sort((a, b) => Number(b.total || 0) - Number(a.total || 0))[0] : null;

    return (
        <SectionCard className="p-0">
            <div className="border-b border-slate-100 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-black uppercase tracking-[0.18em] text-indigo-600">Tren Bulanan</p>
                        <h2 className="mt-2 text-2xl font-black text-slate-950">Pelamar Masuk per Bulan</h2>
                        <p className="mt-1 text-sm font-semibold text-slate-500">Rekap berdasarkan tanggal daftar kandidat.</p>
                    </div>

                    <div className="grid grid-cols-2 gap-2 sm:min-w-64">
                        <SmallStat label="Total" value={total} />
                        <SmallStat label="Rata-rata" value={average} />
                    </div>
                </div>
            </div>

            {items.length > 0 ? (
                <div className="p-6">
                    {bestMonth && maxValue > 0 && (
                        <div className="mb-5 rounded-3xl bg-gradient-to-r from-indigo-50 to-violet-50 px-4 py-3 text-sm font-bold text-indigo-800 ring-1 ring-indigo-100">
                            Bulan tertinggi: <span className="font-black">{bestMonth.label}</span> dengan <span className="font-black">{bestMonth.total}</span> pelamar.
                        </div>
                    )}

                    <div className="overflow-x-auto pb-2">
                        <div className="flex min-w-[760px] items-end gap-3 rounded-3xl bg-slate-50/80 px-4 pt-6">
                            {items.map((item) => {
                                const currentTotal = Number(item.total || 0);
                                const height = maxValue > 0 ? Math.max((currentTotal / maxValue) * 220, currentTotal > 0 ? 18 : 8) : 8;
                                const isHighest = maxValue > 0 && currentTotal === maxValue;

                                return (
                                    <div key={item.month} className="group flex flex-1 flex-col items-center gap-3">
                                        <div className="rounded-full bg-white px-2 py-1 text-xs font-black text-slate-700 opacity-90 shadow-sm transition group-hover:-translate-y-1">
                                            {currentTotal}
                                        </div>
                                        <div className="flex h-60 w-full items-end justify-center rounded-t-3xl px-1">
                                            <div
                                                className={`w-full max-w-12 rounded-t-2xl bg-gradient-to-t ${
                                                    isHighest ? "from-violet-500 to-indigo-400" : "from-slate-300 to-slate-400"
                                                } shadow-sm transition-all duration-300 group-hover:scale-105 group-hover:shadow-lg`}
                                                style={{ height: `${height}px` }}
                                                title={`${item.label}: ${currentTotal} pelamar`}
                                            />
                                        </div>
                                        <div className="h-12 text-center text-[11px] font-black uppercase leading-4 text-slate-500">
                                            {item.label}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            ) : (
                <div className="p-6">
                    <EmptyState text="Belum ada data pelamar bulanan." />
                </div>
            )}
        </SectionCard>
    );
}

function StageOverview({ stages, totalPelamar, highestStage }) {
    const completedCount = stages.filter((stage) => Number(stage.total || 0) > 0).length;

    return (
        <SectionCard className="border-0 bg-gradient-to-br from-white via-white to-slate-50 shadow-lg shadow-slate-200/60">
            <div className="mb-5 flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p className="text-xs font-black uppercase tracking-[0.18em] text-indigo-600">Pipeline Seleksi</p>
                    <h2 className="mt-2 text-2xl font-black text-slate-950">Kandidat per Tahapan</h2>
                    <p className="mt-1 text-sm font-semibold text-slate-500">
                        Posisi terakhir setiap kandidat dalam proses recruitment.
                    </p>
                </div>

                <div className="flex flex-wrap gap-2">
                    <span className="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600">
                        {completedCount} tahap aktif
                    </span>
                    {highestStage && (
                        <span className="rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-black text-indigo-700 ring-1 ring-indigo-100">
                            Terbanyak: {highestStage.label}
                        </span>
                    )}
                </div>
            </div>

            <div className="grid gap-3 md:grid-cols-2">
                {stages.length > 0 ? (
                    stages.map((stage, index) => (
                        <StageProgress
                            key={stage.key}
                            stage={stage}
                            totalPelamar={totalPelamar}
                            index={index}
                        />
                    ))
                ) : (
                    <EmptyState text="Belum ada data tahapan." />
                )}
            </div>
        </SectionCard>
    );
}

function FinalStageCard({ summary, totalFinal }) {
    const metrics = [
        {
            label: "Lolos Interview",
            value: summary?.stage_counts?.interview_lolos || 0,
            className: "bg-emerald-50 text-emerald-700 ring-emerald-100",
            icon: "✓",
        },
        {
            label: "Tidak Lolos",
            value: summary?.stage_counts?.interview_tidak_lolos || 0,
            className: "bg-rose-50 text-rose-700 ring-rose-100",
            icon: "×",
        },
        {
            label: "Dipertimbangkan",
            value: summary?.stage_counts?.interview_dipertimbangkan || 0,
            className: "bg-amber-50 text-amber-700 ring-amber-100",
            icon: "?",
        },
        {
            label: "Reschedule",
            value: summary?.stage_counts?.interview_reschedule || 0,
            className: "bg-sky-50 text-sky-700 ring-sky-100",
            icon: "↻",
        },
    ];

    return (
        <SectionCard>
            <div className="mb-6">
                <p className="text-xs font-black uppercase tracking-[0.18em] text-indigo-600">Hasil Akhir</p>
                <h2 className="mt-2 text-2xl font-black text-slate-950">Ringkasan Interview</h2>
                <p className="mt-1 text-sm font-semibold text-slate-500">Status akhir kandidat setelah proses interview.</p>
            </div>

            <div className="rounded-[2rem] bg-gradient-to-br from-slate-950 to-indigo-900 p-5 text-white shadow-lg shadow-slate-200">
                <p className="text-xs font-black uppercase tracking-[0.18em] text-violet-100">Total Keputusan</p>
                <p className="mt-2 text-5xl font-black">{formatNumber(totalFinal)}</p>
                <p className="mt-2 text-sm font-semibold text-slate-200">Kandidat dengan hasil interview final.</p>
            </div>

            <div className="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                {metrics.map((metric) => (
                    <MiniMetric key={metric.label} {...metric} />
                ))}
            </div>
        </SectionCard>
    );
}

function SummaryCard({ label, value, description, icon, accent }) {
    return (
        <div className="group relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200">
            <div className={`absolute -right-8 -top-8 h-28 w-28 rounded-full bg-gradient-to-br ${accent} opacity-10 transition group-hover:scale-125`} />
            <div className="relative flex items-start justify-between gap-4">
                <div>
                    <p className="text-xs font-black uppercase tracking-[0.18em] text-slate-400">{label}</p>
                    <p className="mt-3 text-4xl font-black text-slate-950">{formatNumber(value)}</p>
                    <p className="mt-2 text-sm font-semibold text-slate-500">{description}</p>
                </div>
                <div className={`flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br ${accent} text-xl font-black text-white shadow-lg`}>
                    {icon}
                </div>
            </div>
        </div>
    );
}

function StageProgress({ stage, totalPelamar, index }) {
    const percentage = Number(stage.percentage || 0);
    const color = STAGE_COLORS[stage.key] || "bg-slate-100 text-slate-700 ring-slate-200";
    const barColor = STAGE_BAR_COLORS[stage.key] || "from-indigo-400 to-indigo-600";
    const hasData = Number(stage.total || 0) > 0;

    return (
        <div className={"group rounded-2xl border p-4 transition duration-200 hover:-translate-y-0.5 hover:shadow-md " + (hasData ? "border-slate-200 bg-white" : "border-slate-100 bg-slate-50/60")}>
            <div className="flex items-center gap-3">
                <span className={"flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-black ring-1 " + color}>
                    {String(index + 1).padStart(2, "0")}
                </span>
                <div className="min-w-0 flex-1">
                    <div className="flex items-center justify-between gap-3">
                        <p className={"truncate text-sm font-black " + (hasData ? "text-slate-800" : "text-slate-500")}>
                            {stage.label}
                        </p>
                        <p className="shrink-0 text-sm font-black text-slate-950">
                            {formatNumber(stage.total)}
                            <span className="ml-1 font-bold text-slate-400">/ {formatNumber(totalPelamar)}</span>
                        </p>
                    </div>
                    <div className="mt-2 flex items-center gap-3">
                        <div className="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 ring-1 ring-slate-200/60">
                            <div
                                className={`h-full rounded-full bg-gradient-to-r ${barColor} transition-all duration-500`}
                                style={{ width: `${Math.min(percentage, 100)}%` }}
                            />
                        </div>
                        <span className="w-12 text-right text-xs font-black text-slate-500">
                            {percentage}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}

function MiniMetric({ label, value, className, icon }) {
    return (
        <div className={`flex items-center justify-between rounded-3xl px-4 py-3 ring-1 ${className}`}>
            <div className="flex items-center gap-3">
                <span className="flex h-9 w-9 items-center justify-center rounded-2xl bg-white/70 text-base font-black">{icon}</span>
                <span className="text-sm font-black">{label}</span>
            </div>
            <span className="text-2xl font-black">{formatNumber(value)}</span>
        </div>
    );
}

function SmallStat({ label, value }) {
    return (
        <div className="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
            <p className="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">{label}</p>
            <p className="mt-1 text-2xl font-black text-slate-950">{formatNumber(value)}</p>
        </div>
    );
}

function SectionCard({ children, className = "" }) {
    return (
        <div className={`overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm ${className}`}>
            {children}
        </div>
    );
}

function LoadingState() {
    return (
        <div className="rounded-[2rem] border border-slate-200 bg-white p-8 text-center shadow-sm">
            <div className="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-slate-100 border-t-indigo-600" />
            <p className="mt-4 text-sm font-black text-slate-500">Memuat data dashboard...</p>
        </div>
    );
}

function EmptyState({ text }) {
    return (
        <div className="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-sm font-black shadow-sm">DATA</div>
            <p className="mt-3 text-sm font-black text-slate-500">{text}</p>
        </div>
    );
}

function formatNumber(value) {
    return new Intl.NumberFormat("id-ID").format(Number(value || 0));
}

function formatDateTime(value) {
    if (!value) return "-";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}
