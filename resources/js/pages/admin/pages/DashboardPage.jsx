import React, { useEffect, useMemo, useState } from "react";

const STAGE_COLORS = {
    administrasi: "bg-slate-100 text-slate-700 ring-slate-200",
    jadwal_test_zoom: "bg-blue-50 text-blue-700 ring-blue-100",
    hasil_test_zoom_lolos: "bg-emerald-50 text-emerald-700 ring-emerald-100",
    hasil_test_zoom_gagal: "bg-rose-50 text-rose-700 ring-rose-100",
    jadwal_test_mmpi: "bg-indigo-50 text-indigo-700 ring-indigo-100",
    hasil_test_mmpi_lolos: "bg-emerald-50 text-emerald-700 ring-emerald-100",
    hasil_test_mmpi_gagal: "bg-rose-50 text-rose-700 ring-rose-100",
    jadwal_interview: "bg-cyan-50 text-cyan-700 ring-cyan-100",
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
    jadwal_interview: "from-cyan-400 to-cyan-600",
    interview_reschedule: "from-sky-400 to-sky-600",
    interview_lolos: "from-emerald-400 to-emerald-600",
    interview_tidak_lolos: "from-rose-400 to-rose-600",
    interview_dipertimbangkan: "from-amber-400 to-amber-600",
};

export default function DashboardPage() {
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(false);
    const [errorMessage, setErrorMessage] = useState("");

    const fetchSummary = async () => {
        setLoading(true);
        setErrorMessage("");

        try {
            const response = await fetch("/admin/dashboard/summary", {
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

    useEffect(() => {
        fetchSummary();
    }, []);

    const stages = useMemo(() => summary?.stages || [], [summary]);
    const monthlyApplicants = useMemo(() => summary?.monthly_applicants || [], [summary]);

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
                    onRefresh={fetchSummary}
                    highestStage={highestStage}
                />

                {errorMessage && (
                    <div className="rounded-3xl border border-rose-100 bg-rose-50 px-5 py-4 shadow-sm">
                        <p className="text-sm font-bold text-rose-700">{errorMessage}</p>
                    </div>
                )}

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <SummaryCard
                        label="Total Pelamar"
                        value={summary?.total_pelamar || 0}
                        description="Semua data pelamar"
                        icon="▤"
                        accent="from-teal-500 to-cyan-500"
                    />
                    <SummaryCard
                        label="Jadwal Test Zoom"
                        value={summary?.total_jadwal_test_zoom || 0}
                        description="Pelamar dijadwalkan Zoom"
                        icon="◎"
                        accent="from-blue-500 to-cyan-500"
                    />
                    <SummaryCard
                        label="Jadwal Test MMPI"
                        value={summary?.total_jadwal_test_mmpi || 0}
                        description="Pelamar dijadwalkan MMPI"
                        icon="◉"
                        accent="from-indigo-500 to-violet-500"
                    />
                    <SummaryCard
                        label="Jadwal Interview"
                        value={summary?.total_jadwal_interview || 0}
                        description="Pelamar masuk interview"
                        icon="◷"
                        accent="from-amber-500 to-orange-500"
                    />
                </div>

                {loading && !summary ? (
                    <LoadingState />
                ) : (
                    <>
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

function HeroSection({ summary, loading, onRefresh, highestStage }) {
    return (
        <div className="relative overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-xl shadow-slate-200">
            <div className="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl" />
            <div className="absolute -bottom-28 left-12 h-72 w-72 rounded-full bg-teal-400/20 blur-3xl" />
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(34,211,238,0.22),transparent_35%),linear-gradient(135deg,rgba(15,23,42,1),rgba(15,118,110,0.85))]" />

            <div className="relative grid gap-8 px-6 py-8 lg:grid-cols-[1.2fr_0.8fr] lg:px-8 lg:py-10">
                <div>
                    <div className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-black uppercase tracking-[0.2em] text-cyan-100 backdrop-blur">
                        <span className="h-2 w-2 rounded-full bg-emerald-300" />
                        Dashboard Rekrutmen
                    </div>

                    <h1 className="mt-5 max-w-3xl text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">
                        Ringkasan Pelamar yang Lebih Cepat Dibaca
                    </h1>

                    <p className="mt-4 max-w-2xl text-sm font-semibold leading-6 text-slate-200 sm:text-base">
                        Pantau pelamar masuk, progres seleksi, dan hasil akhir kandidat dalam satu tampilan yang lebih rapi.
                    </p>

                    <div className="mt-6 flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            onClick={onRefresh}
                            disabled={loading}
                            className="rounded-2xl bg-white px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-cyan-50 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {loading ? "Memuat Data..." : "Refresh Data"}
                        </button>

                        {highestStage && (
                            <div className="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-bold text-cyan-50 backdrop-blur">
                                Tahapan terbanyak: <span className="font-black">{highestStage.label}</span>
                            </div>
                        )}
                    </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <HeroMetric label="Total Pelamar" value={summary?.total_pelamar || 0} />
                    <HeroMetric label="Interview" value={summary?.total_jadwal_interview || 0} />
                    <HeroMetric label="Lolos" value={summary?.stage_counts?.interview_lolos || 0} />
                </div>
            </div>
        </div>
    );
}

function HeroMetric({ label, value }) {
    return (
        <div className="rounded-3xl border border-white/15 bg-white/10 p-5 backdrop-blur">
            <p className="text-xs font-black uppercase tracking-[0.18em] text-cyan-100">{label}</p>
            <p className="mt-2 text-4xl font-black text-white">{formatNumber(value)}</p>
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
                        <p className="text-xs font-black uppercase tracking-[0.18em] text-teal-600">Tren Bulanan</p>
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
                        <div className="mb-5 rounded-3xl bg-gradient-to-r from-teal-50 to-cyan-50 px-4 py-3 text-sm font-bold text-teal-800 ring-1 ring-teal-100">
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
                                                    isHighest ? "from-cyan-500 to-teal-400" : "from-slate-300 to-slate-400"
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
    return (
        <SectionCard>
            <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p className="text-xs font-black uppercase tracking-[0.18em] text-teal-600">Pipeline Seleksi</p>
                    <h2 className="mt-2 text-2xl font-black text-slate-950">Kandidat per Tahapan</h2>
                    <p className="mt-1 text-sm font-semibold text-slate-500">Dihitung berdasarkan tahap terakhir kandidat.</p>
                </div>

                {highestStage && (
                    <span className="w-fit rounded-full bg-teal-50 px-3 py-1 text-xs font-black text-teal-700 ring-1 ring-teal-100">
                        Terbanyak: {highestStage.label}
                    </span>
                )}
            </div>

            <div className="space-y-4">
                {stages.length > 0 ? (
                    stages.map((stage) => (
                        <StageProgress key={stage.key} stage={stage} totalPelamar={totalPelamar} />
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
                <p className="text-xs font-black uppercase tracking-[0.18em] text-teal-600">Hasil Akhir</p>
                <h2 className="mt-2 text-2xl font-black text-slate-950">Ringkasan Interview</h2>
                <p className="mt-1 text-sm font-semibold text-slate-500">Status akhir kandidat setelah proses interview.</p>
            </div>

            <div className="rounded-[2rem] bg-gradient-to-br from-slate-950 to-teal-900 p-5 text-white shadow-lg shadow-slate-200">
                <p className="text-xs font-black uppercase tracking-[0.18em] text-cyan-100">Total Keputusan</p>
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

function StageProgress({ stage, totalPelamar }) {
    const percentage = Number(stage.percentage || 0);
    const color = STAGE_COLORS[stage.key] || "bg-slate-100 text-slate-700 ring-slate-200";
    const barColor = STAGE_BAR_COLORS[stage.key] || "from-teal-400 to-teal-600";

    return (
        <div className="rounded-3xl border border-slate-100 bg-slate-50/70 p-4 transition hover:bg-white hover:shadow-sm">
            <div className="mb-3 flex items-center justify-between gap-3">
                <span className={`rounded-full px-3 py-1 text-xs font-black ring-1 ${color}`}>{stage.label}</span>
                <span className="whitespace-nowrap text-sm font-black text-slate-700">
                    {formatNumber(stage.total)} / {formatNumber(totalPelamar)}
                </span>
            </div>
            <div className="h-3 overflow-hidden rounded-full bg-white ring-1 ring-slate-100">
                <div
                    className={`h-full rounded-full bg-gradient-to-r ${barColor} transition-all duration-500`}
                    style={{ width: `${Math.min(percentage, 100)}%` }}
                />
            </div>
            <p className="mt-2 text-xs font-bold text-slate-400">{percentage}% dari total pelamar</p>
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
            <div className="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-slate-100 border-t-teal-600" />
            <p className="mt-4 text-sm font-black text-slate-500">Memuat data dashboard...</p>
        </div>
    );
}

function EmptyState({ text }) {
    return (
        <div className="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl shadow-sm">▤</div>
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
