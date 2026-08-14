import React, { useEffect, useMemo, useState } from "react";
import Select2 from "react-select";

const taskOptions = [
    { value: "candidate_summary", icon: "01", label: "Ringkasan Kandidat", description: "Kekuatan, gap kualifikasi, dan tindak lanjut HR." },
    { value: "interview_questions", icon: "02", label: "Pertanyaan Interview", description: "Pertanyaan relevan berdasarkan profil profesional." },
    { value: "data_review", icon: "03", label: "Review Kelengkapan", description: "Data yang tidak konsisten atau perlu dikonfirmasi." },
];

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

export default function AiRecruitmentPage() {
    const [candidates, setCandidates] = useState([]);
    const [candidateId, setCandidateId] = useState("");
    const [task, setTask] = useState("candidate_summary");
    const [loadingCandidates, setLoadingCandidates] = useState(true);
    const [analyzing, setAnalyzing] = useState(false);
    const [error, setError] = useState("");
    const [result, setResult] = useState(null);

    useEffect(() => {
        let active = true;
        fetch("/admin/ai-recruitment/candidates", { headers: { Accept: "application/json" } })
            .then(async (response) => {
                const json = await response.json();
                if (!response.ok) throw new Error(json.message || "Data kandidat gagal dimuat.");
                if (active) setCandidates(Array.isArray(json.data) ? json.data : []);
            })
            .catch((requestError) => active && setError(requestError.message))
            .finally(() => active && setLoadingCandidates(false));
        return () => { active = false; };
    }, []);

    const selectedCandidate = useMemo(
        () => candidates.find((candidate) => candidate.id === candidateId),
        [candidateId, candidates],
    );
    const candidateOptions = useMemo(
        () => candidates.map((candidate) => ({
            value: candidate.id,
            label: candidate.name,
            position: candidate.position,
            company: candidate.company,
        })),
        [candidates],
    );
    const selectedCandidateOption = useMemo(
        () => candidateOptions.find((option) => option.value === candidateId) || null,
        [candidateId, candidateOptions],
    );

    const runAnalysis = async () => {
        if (!candidateId) {
            setError("Pilih kandidat yang akan dianalisis.");
            return;
        }
        setAnalyzing(true);
        setError("");
        setResult(null);
        try {
            const response = await fetch("/admin/ai-recruitment/analyze", {
                method: "POST",
                headers: { Accept: "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken() },
                body: JSON.stringify({ candidate_id: candidateId, task }),
            });
            const json = await response.json();
            if (!response.ok) throw new Error(json.message || "Analisis AI gagal dibuat.");
            setResult(json.data || null);
        } catch (requestError) {
            setError(requestError.message || "9Router tidak dapat dihubungi.");
        } finally {
            setAnalyzing(false);
        }
    };

    const renderList = (title, items, tone) => (
        <section className="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50">
            <div className="flex items-center gap-3"><span className={`h-2.5 w-2.5 rounded-full ${tone}`} /><h3 className="font-black text-slate-950">{title}</h3></div>
            {items?.length ? (
                <ul className="mt-4 space-y-3">{items.map((item, index) => <li key={`${title}-${index}`} className="flex gap-3 text-sm leading-6 text-slate-600"><span className="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-300" /><span>{item}</span></li>)}</ul>
            ) : <p className="mt-4 text-sm text-slate-400">Tidak ada catatan dari AI.</p>}
        </section>
    );

    return (
        <div className="min-h-full bg-[#f6f7fb] p-3 sm:p-5 lg:p-6">
            <div className="mx-auto max-w-[1440px] space-y-5">
                <section className="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-[#201b5c] via-indigo-700 to-violet-700 px-6 py-6 text-white shadow-xl shadow-indigo-100/70 sm:px-8">
                    <div className="absolute -right-16 -top-20 h-64 w-64 rounded-full border border-white/10 bg-white/5" />
                    <div className="relative grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div className="max-w-3xl">
                            <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] text-indigo-100"><span className="h-2 w-2 rounded-full bg-violet-300" /> AI Recruitment Workspace</span>
                            <h1 className="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Analisis kandidat lebih cepat, keputusan tetap di HR.</h1>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-indigo-100/80">Ringkas profil, siapkan pertanyaan interview, dan temukan data yang perlu dikonfirmasi dalam satu workspace.</p>
                        </div>
                        <div className="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                            <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-sm font-black text-indigo-700">9R</span>
                            <div><p className="text-[10px] font-bold uppercase tracking-widest text-indigo-200">AI Engine</p><p className="mt-0.5 text-sm font-black">9Router Proxy</p><p className="mt-0.5 flex items-center gap-1.5 text-[10px] text-indigo-100/70"><span className="h-1.5 w-1.5 rounded-full bg-violet-300" /> Terhubung melalui backend</p></div>
                        </div>
                    </div>
                </section>

                <section className="grid items-start gap-5 xl:grid-cols-[390px_minmax(0,1fr)]">
                    <div className="h-fit rounded-[1.75rem] border border-slate-200 bg-white shadow-sm shadow-slate-200/60 xl:sticky xl:top-5">
                        <div className="border-b border-slate-100 px-5 py-5 sm:px-6"><p className="text-[10px] font-black uppercase tracking-[0.17em] text-indigo-600">Buat Analisis</p><h2 className="mt-1.5 text-xl font-black text-slate-950">Siapkan insight kandidat</h2><p className="mt-1 text-xs leading-5 text-slate-500">Pilih satu kandidat dan tujuan analisis.</p></div>
                        <div className="px-5 py-5 sm:px-6">
                            <label className="block text-xs font-black uppercase tracking-wide text-slate-600" htmlFor="ai-candidate">Kandidat</label>
                            <div className="mt-2">
                                <Select2
                                    inputId="ai-candidate"
                                    value={selectedCandidateOption}
                                    options={candidateOptions}
                                    onChange={(option) => { setCandidateId(option?.value || ""); setResult(null); setError(""); }}
                                    isClearable
                                    isSearchable
                                    isLoading={loadingCandidates}
                                    isDisabled={loadingCandidates}
                                    placeholder={loadingCandidates ? "Memuat kandidat..." : "Cari nama atau posisi kandidat..."}
                                    noOptionsMessage={({ inputValue }) => inputValue ? `Kandidat “${inputValue}” tidak ditemukan` : "Belum ada kandidat"}
                                    loadingMessage={() => "Memuat kandidat..."}
                                    menuPortalTarget={typeof document !== "undefined" ? document.body : null}
                                    menuPosition="fixed"
                                    classNamePrefix="ai-candidate-select"
                                    filterOption={(option, inputValue) => {
                                        const keyword = inputValue.toLowerCase();
                                        const data = option.data;
                                        return [data.label, data.position, data.company].some((value) => String(value || "").toLowerCase().includes(keyword));
                                    }}
                                    formatOptionLabel={(option, { context }) => context === "menu" ? (
                                        <div className="flex min-w-0 items-center gap-3 py-1">
                                            <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-xs font-black text-indigo-700">{option.label?.charAt(0)?.toUpperCase() || "K"}</span>
                                            <span className="min-w-0"><strong className="block truncate text-sm text-slate-900">{option.label}</strong><span className="mt-0.5 block truncate text-xs text-slate-500">{option.position} · {option.company}</span></span>
                                        </div>
                                    ) : <span className="block truncate text-sm font-bold text-slate-800">{option.label} — {option.position}</span>}
                                    styles={{
                                        control: (base, state) => ({ ...base, minHeight: 48, borderRadius: 12, borderColor: state.isFocused ? "#6366f1" : "#e2e8f0", boxShadow: state.isFocused ? "0 0 0 4px #eef2ff" : "none", cursor: "pointer", ":hover": { borderColor: "#818cf8" } }),
                                        valueContainer: (base) => ({ ...base, padding: "0 12px" }),
                                        placeholder: (base) => ({ ...base, color: "#94a3b8", fontSize: 13, fontWeight: 600 }),
                                        input: (base) => ({ ...base, color: "#0f172a", fontSize: 13 }),
                                        indicatorSeparator: () => ({ display: "none" }),
                                        dropdownIndicator: (base, state) => ({ ...base, color: state.isFocused ? "#4f46e5" : "#94a3b8" }),
                                        clearIndicator: (base) => ({ ...base, color: "#94a3b8" }),
                                        menuPortal: (base) => ({ ...base, zIndex: 99999 }),
                                        menu: (base) => ({ ...base, borderRadius: 16, overflow: "hidden", border: "1px solid #e2e8f0", boxShadow: "0 20px 50px -20px rgba(30, 41, 59, .35)" }),
                                        menuList: (base) => ({ ...base, padding: 8, maxHeight: 280 }),
                                        option: (base, state) => ({ ...base, borderRadius: 12, marginBottom: 3, backgroundColor: state.isSelected ? "#eef2ff" : state.isFocused ? "#f8fafc" : "white", color: "#0f172a", cursor: "pointer", ":active": { backgroundColor: "#e0e7ff" } }),
                                    }}
                                />
                            </div>
                            {selectedCandidate && <div className="mt-2.5 flex flex-wrap gap-2"><span className="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-600">{selectedCandidate.company}</span><span className="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold text-indigo-700">{selectedCandidate.position}</span></div>}

                            <fieldset className="mt-5 space-y-2">
                                <legend className="mb-2 text-xs font-black uppercase tracking-wide text-slate-600">Jenis analisis</legend>
                                {taskOptions.map((option) => (
                                    <label key={option.value} className={`group flex cursor-pointer gap-3 rounded-2xl border p-3.5 transition ${task === option.value ? "border-indigo-400 bg-indigo-50 shadow-sm shadow-indigo-100" : "border-slate-200 hover:border-indigo-200 hover:bg-slate-50"}`}>
                                        <span className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[10px] font-black ${task === option.value ? "bg-indigo-600 text-white" : "bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-indigo-700"}`}>{option.icon}</span>
                                        <input type="radio" name="ai-task" value={option.value} checked={task === option.value} onChange={(event) => { setTask(event.target.value); setResult(null); }} className="sr-only" />
                                        <span><strong className="block text-sm text-slate-900">{option.label}</strong><span className="mt-1 block text-xs leading-5 text-slate-500">{option.description}</span></span>
                                    </label>
                                ))}
                            </fieldset>

                            {error && <div role="alert" className="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-4"><div className="flex gap-3"><span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-xs font-black text-rose-700">!</span><div><p className="text-xs font-black text-rose-800">Analisis belum dapat diproses</p><p className="mt-1 text-xs leading-5 text-rose-700">{error}</p></div></div></div>}
                            <button type="button" onClick={runAnalysis} disabled={analyzing || loadingCandidates} className="mt-5 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-wait disabled:opacity-60">
                                {analyzing && <span className="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white" />}{analyzing ? "AI sedang menganalisis..." : <><span>Buat Insight AI</span><span aria-hidden="true">→</span></>}
                            </button>
                            <p className="mt-3 text-center text-[10px] leading-4 text-slate-400">Hasil AI bersifat rekomendasi dan wajib ditinjau HR.</p>
                        </div>
                    </div>

                    <div>
                        {!result && !analyzing && <div className="flex min-h-[520px] items-center justify-center rounded-[1.75rem] border border-slate-200 bg-white p-8 text-center shadow-sm shadow-slate-200/50"><div className="max-w-md"><div className="relative mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-indigo-50 to-violet-100 text-xl font-black text-indigo-600"><span className="absolute -right-1 -top-1 h-4 w-4 rounded-full border-4 border-white bg-violet-500" />AI</div><p className="mt-6 text-[10px] font-black uppercase tracking-[0.18em] text-indigo-600">Workspace siap digunakan</p><h2 className="mt-2 text-2xl font-black text-slate-950">Insight kandidat tampil di sini</h2><p className="mt-3 text-sm leading-6 text-slate-500">Pilih kandidat dan jenis analisis di panel sebelah kiri. AI hanya memakai data profesional yang sudah tersimpan.</p><div className="mt-6 grid grid-cols-3 gap-2 text-[10px] font-bold text-slate-500"><span className="rounded-xl bg-slate-50 px-2 py-3">Ringkasan</span><span className="rounded-xl bg-slate-50 px-2 py-3">Interview</span><span className="rounded-xl bg-slate-50 px-2 py-3">Review data</span></div></div></div>}
                        {analyzing && <div className="min-h-[520px] animate-pulse space-y-4 rounded-[1.75rem] border border-slate-200 bg-white p-6"><div className="h-7 w-1/3 rounded bg-slate-100" /><div className="h-24 rounded-2xl bg-slate-100" /><div className="grid gap-4 md:grid-cols-2"><div className="h-44 rounded-2xl bg-slate-100" /><div className="h-44 rounded-2xl bg-slate-100" /></div></div>}
                        {result && <div className="space-y-4"><section className="rounded-[1.75rem] border border-indigo-100 bg-gradient-to-br from-indigo-50 to-violet-50 p-6"><div className="flex flex-wrap items-center justify-between gap-3"><p className="text-[10px] font-black uppercase tracking-[0.17em] text-indigo-600">Ringkasan AI</p><span className="rounded-full bg-white px-3 py-1 text-[10px] font-bold text-slate-500">{result.model}</span></div><p className="mt-4 text-sm font-semibold leading-7 text-slate-700">{result.summary || "AI tidak memberikan ringkasan."}</p></section><div className="grid gap-4 md:grid-cols-2">{renderList("Kekuatan", result.strengths, "bg-indigo-500")}{renderList("Gap / Perhatian", result.gaps, "bg-violet-500")}</div>{renderList("Tindak Lanjut HR", result.follow_up, "bg-indigo-500")}<p className="rounded-2xl bg-slate-100 px-4 py-3 text-xs leading-5 text-slate-500">{result.disclaimer}</p></div>}
                    </div>
                </section>
            </div>
        </div>
    );
}
