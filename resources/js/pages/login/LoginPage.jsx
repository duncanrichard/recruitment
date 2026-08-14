import React, { useState } from "react";

const Icon = ({ children, className = "h-5 w-5" }) => (
    <svg
        aria-hidden="true"
        className={className}
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="1.8"
        strokeLinecap="round"
        strokeLinejoin="round"
    >
        {children}
    </svg>
);

const LoginPage = () => {
    const [formData, setFormData] = useState({
        email: "",
        password: "",
        remember: false,
    });
    const [showPassword, setShowPassword] = useState(false);
    const [loading, setLoading] = useState(false);
    const [errorMessage, setErrorMessage] = useState("");

    const getCsrfToken = () =>
        document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

    const getFirstErrorMessage = (errors) => {
        const firstError = errors ? Object.values(errors)[0] : null;
        return Array.isArray(firstError) ? firstError[0] : firstError;
    };

    const handleChange = ({ target }) => {
        const { name, value, type, checked } = target;
        setFormData((current) => ({
            ...current,
            [name]: type === "checkbox" ? checked : value,
        }));
        setErrorMessage("");
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        if (!formData.email.trim() || !formData.password) {
            setErrorMessage("Email dan password wajib diisi.");
            return;
        }

        setLoading(true);
        setErrorMessage("");

        try {
            const response = await fetch("/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                body: JSON.stringify(formData),
            });
            const result = await response.json();

            if (!response.ok) {
                setErrorMessage(
                    getFirstErrorMessage(result.errors) ||
                        result.message ||
                        "Login gagal. Periksa kembali akun Anda.",
                );
                return;
            }

            window.location.href = result.redirect || "/dashboard";
        } catch (error) {
            console.error("Gagal login:", error);
            setErrorMessage("Sistem tidak dapat dihubungi. Silakan coba kembali.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <main className="min-h-screen bg-[#ececf5] p-0 text-slate-950 lg:p-5">
            <div className="mx-auto grid min-h-screen max-w-[1500px] overflow-hidden bg-white shadow-[0_30px_100px_rgba(30,27,75,0.18)] lg:min-h-[calc(100vh-2.5rem)] lg:grid-cols-[1.16fr_0.84fr] lg:rounded-[32px]">
                <section className="relative hidden overflow-hidden bg-[#17133f] px-12 py-10 text-white lg:flex lg:flex-col xl:px-16">
                    <div className="absolute inset-0 opacity-20 [background-image:linear-gradient(rgba(255,255,255,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.08)_1px,transparent_1px)] [background-size:48px_48px]" />
                    <div className="absolute -left-40 -top-48 h-[520px] w-[520px] rounded-full bg-indigo-500/35 blur-[100px]" />
                    <div className="absolute -bottom-56 right-[-80px] h-[520px] w-[520px] rounded-full bg-violet-600/35 blur-[110px]" />

                    <div className="relative flex items-center gap-3">
                        <div className="relative flex h-12 w-12 items-center justify-center rounded-[15px] bg-gradient-to-br from-indigo-400 to-violet-500 font-black text-white shadow-[0_10px_30px_rgba(99,102,241,.35)]">
                            SR
                            <span className="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-[3px] border-[#17133f] bg-emerald-400" />
                        </div>
                        <div>
                            <p className="text-[11px] font-extrabold uppercase tracking-[0.26em] text-indigo-200">
                                Sistem Recruitment
                            </p>
                            <p className="mt-0.5 text-lg font-black">Sirekrut</p>
                        </div>
                    </div>

                    <div className="relative my-auto grid items-center gap-8 py-8 xl:grid-cols-[0.9fr_1.1fr]">
                        <div>
                            <p className="text-xs font-black uppercase tracking-[0.28em] text-violet-300">Recruitment workspace</p>
                            <h1 className="mt-5 text-[46px] font-black leading-[1.02] tracking-[-0.045em] xl:text-[58px]">
                                Satu tempat untuk setiap
                                <span className="mt-2 block bg-gradient-to-r from-indigo-300 via-violet-300 to-fuchsia-300 bg-clip-text text-transparent">perjalanan kandidat.</span>
                            </h1>
                            <p className="mt-6 max-w-md text-[15px] leading-7 text-indigo-100/70">Dari data masuk hingga offering, semua proses terpantau dalam alur kerja tim HR yang jelas.</p>
                        </div>

                        <div className="relative mx-auto w-full max-w-[380px] xl:translate-x-4">
                            <div className="absolute -inset-7 rounded-full bg-indigo-500/20 blur-3xl" />
                            <div className="relative rotate-[2deg] rounded-[28px] border border-white/15 bg-white/[0.09] p-4 shadow-[0_30px_70px_rgba(0,0,0,.3)] backdrop-blur-xl">
                                <div className="mb-4 flex items-center justify-between px-1">
                                    <div><p className="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200">Candidate journey</p><p className="mt-1 text-sm font-black">Pipeline hari ini</p></div>
                                    <span className="rounded-full bg-emerald-400/15 px-2.5 py-1 text-[9px] font-black text-emerald-300">LIVE</span>
                                </div>
                                <div className="rounded-[22px] bg-white p-4 text-slate-900 shadow-xl">
                                    <div className="flex items-center gap-3">
                                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-100 text-sm font-black text-indigo-700">AR</div>
                                        <div className="min-w-0 flex-1"><p className="truncate text-sm font-black">Aulia Rahma</p><p className="mt-0.5 text-[10px] font-semibold text-slate-400">UI/UX Designer · Kandidat aktif</p></div>
                                        <span className="h-2.5 w-2.5 rounded-full bg-emerald-400 ring-4 ring-emerald-50" />
                                    </div>
                                    <div className="relative mt-5 flex justify-between">
                                        <div className="absolute left-4 right-4 top-3 h-0.5 bg-slate-100" />
                                        {["Data", "Test", "Interview", "Offer"].map((stage, index) => <div key={stage} className="relative z-10 flex flex-col items-center gap-2"><span className={`flex h-6 w-6 items-center justify-center rounded-full text-[9px] font-black ${index < 3 ? "bg-indigo-600 text-white ring-4 ring-indigo-50" : "border-2 border-slate-200 bg-white text-slate-400"}`}>{index < 2 ? "✓" : index + 1}</span><span className={`text-[9px] font-bold ${index < 3 ? "text-indigo-700" : "text-slate-400"}`}>{stage}</span></div>)}
                                    </div>
                                </div>
                                <div className="mt-3 grid grid-cols-3 gap-2">
                                    {[["24", "Pelamar"], ["06", "Interview"], ["03", "Offering"]].map(([total, label]) => <div key={label} className="rounded-2xl border border-white/10 bg-black/10 px-3 py-3"><p className="text-lg font-black">{total}</p><p className="mt-0.5 text-[9px] font-bold uppercase tracking-wider text-indigo-200/70">{label}</p></div>)}
                                </div>
                            </div>
                            <div className="absolute -bottom-5 -left-7 -rotate-[5deg] rounded-2xl border border-white/15 bg-[#302879] px-4 py-3 shadow-xl"><p className="text-[9px] font-bold text-indigo-200">STATUS TERBARU</p><p className="mt-1 text-xs font-black">✓ Interview terjadwal</p></div>
                        </div>
                    </div>

                    <div className="relative flex items-center justify-between border-t border-white/10 pt-5 text-[11px] font-semibold text-indigo-200/60"><span>Monitor · Collaborate · Decide</span><span>HR workspace secured</span></div>
                </section>

                <section className="relative flex items-center justify-center px-5 py-8 sm:px-10 lg:px-14 xl:px-20">
                    <div className="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-500 lg:hidden" />
                    <div className="w-full max-w-[460px]">
                        <div className="mb-10 flex items-center gap-3 lg:hidden">
                            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 font-black text-white shadow-lg shadow-indigo-200">
                                HR
                            </div>
                            <div>
                                <p className="text-[10px] font-extrabold uppercase tracking-[0.22em] text-indigo-600">Sistem Recruitment</p>
                                <p className="font-black text-slate-950">Sirekrut</p>
                            </div>
                        </div>

                        <div className="mb-8">
                            <div className="mb-5 inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] text-indigo-700"><span className="h-1.5 w-1.5 rounded-full bg-indigo-500" /> HR access portal</div>
                            <h2 className="text-3xl font-black tracking-[-0.035em] text-slate-950 sm:text-4xl">Lanjutkan proses recruitment Anda.</h2>
                            <p className="mt-3 text-sm leading-6 text-slate-500">Masuk untuk melihat kandidat dan pekerjaan HR yang membutuhkan perhatian hari ini.</p>
                        </div>

                        {errorMessage && (
                            <div role="alert" aria-live="polite" className="mb-5 flex gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3.5 text-sm text-rose-700">
                                <Icon className="mt-0.5 h-5 w-5 shrink-0">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 8v4M12 16h.01" />
                                </Icon>
                                <span className="font-semibold leading-5">{errorMessage}</span>
                            </div>
                        )}

                        <form onSubmit={handleSubmit} className="space-y-5" noValidate>
                            <div>
                                <label htmlFor="email" className="mb-2 block text-sm font-extrabold text-slate-700">Email perusahaan</label>
                                <div className="relative">
                                    <Icon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400">
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <path d="m3 7 9 6 9-6" />
                                    </Icon>
                                    <input id="email" type="email" name="email" value={formData.email} onChange={handleChange} placeholder="nama@perusahaan.com" autoComplete="email" autoFocus aria-invalid={Boolean(errorMessage)} className="h-14 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-medium placeholder:text-slate-400 hover:border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" />
                                </div>
                            </div>

                            <div>
                                <label htmlFor="password" className="mb-2 block text-sm font-extrabold text-slate-700">Password</label>
                                <div className="relative">
                                    <Icon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400">
                                        <rect x="4" y="10" width="16" height="10" rx="2" />
                                        <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                    </Icon>
                                    <input id="password" type={showPassword ? "text" : "password"} name="password" value={formData.password} onChange={handleChange} placeholder="Masukkan password" autoComplete="current-password" aria-invalid={Boolean(errorMessage)} className="h-14 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-16 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-medium placeholder:text-slate-400 hover:border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" />
                                    <button type="button" onClick={() => setShowPassword((value) => !value)} aria-label={showPassword ? "Sembunyikan password" : "Tampilkan password"} className="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                        <Icon className="h-5 w-5">
                                            {showPassword ? <><path d="M3 3l18 18" /><path d="M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 4.2A10.6 10.6 0 0 1 12 4c5 0 8.5 4.5 9 6-.2.7-.8 1.7-1.6 2.7M6.6 6.6C4.6 8 3.4 9.7 3 11c.5 1.5 4 6 9 6 1.1 0 2.1-.2 3-.6" /></> : <><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></>}
                                        </Icon>
                                    </button>
                                </div>
                            </div>

                            <label className="inline-flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                                <input type="checkbox" name="remember" checked={formData.remember} onChange={handleChange} className="h-4.5 w-4.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                                Ingat saya di perangkat ini
                            </label>

                            <button type="submit" disabled={loading} className="flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-extrabold text-white shadow-[0_12px_28px_rgba(79,70,229,0.24)] transition hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(79,70,229,0.32)] focus:outline-none focus:ring-4 focus:ring-indigo-200 active:translate-y-0 disabled:cursor-wait disabled:opacity-70">
                                {loading && <span className="h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white" />}
                                {loading ? "Memverifikasi akun..." : "Masuk ke Dashboard"}
                                {!loading && <Icon className="h-4 w-4"><path d="M5 12h14M13 6l6 6-6 6" /></Icon>}
                            </button>
                        </form>

                        <div className="mt-8 flex items-start gap-3 rounded-2xl bg-slate-50 px-4 py-3.5 text-xs leading-5 text-slate-500">
                            <Icon className="mt-0.5 h-4 w-4 shrink-0 text-indigo-500"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" /><path d="m9 12 2 2 4-4" /></Icon>
                            <p>Akses terbatas untuk tim recruitment. Hubungi administrator apabila Anda mengalami kendala akun.</p>
                        </div>

                        <p className="mt-8 text-center text-xs text-slate-400">© {new Date().getFullYear()} Sirekrut · Sistem Recruitment Terintegrasi</p>
                    </div>
                </section>
            </div>
        </main>
    );
};

export default LoginPage;
