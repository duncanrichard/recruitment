import React, { useState } from "react";

export default function AdminHeader({
    activeMenuData,
    showActionButton,
    onOpenSidebar,
    onHeaderAction,
}) {
    const [logoutLoading, setLogoutLoading] = useState(false);

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const handleLogout = async () => {
        const confirmLogout = confirm("Yakin ingin logout?");

        if (!confirmLogout) return;

        setLogoutLoading(true);

        try {
            const response = await fetch("/logout", {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });

            let result = {};

            try {
                result = await response.json();
            } catch (error) {
                result = {};
            }

            if (response.ok) {
                window.location.href = result.redirect || "/login";
                return;
            }

            alert(result.message || "Logout gagal.");
        } catch (error) {
            console.error("Gagal logout:", error);
            alert("Terjadi kesalahan saat logout.");
        } finally {
            setLogoutLoading(false);
        }
    };

    return (
        <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur-xl">
            <div className="flex min-h-[78px] items-center justify-between gap-4 px-5 sm:px-8">
                <div className="flex min-w-0 items-center gap-4">
                    <button
                        type="button"
                        onClick={onOpenSidebar}
                        className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-700 transition hover:bg-slate-200 lg:hidden"
                        aria-label="Buka menu"
                    >
                        ☰
                    </button>

                    <div className="min-w-0">
                        {activeMenuData?.parentLabel && (
                            <div className="text-xs font-black uppercase tracking-[0.18em] text-teal-700">
                                {activeMenuData.parentLabel}
                            </div>
                        )}

                        {!activeMenuData?.parentLabel && (
                            <div className="text-xs font-black uppercase tracking-[0.18em] text-teal-700">
                                {activeMenuData?.key === "dashboard"
                                    ? "Sistem Rekrutmen"
                                    : "Sistem Rekrutmen"}
                            </div>
                        )}

                        <h1 className="mt-1 truncate text-xl font-black text-slate-950 sm:text-2xl">
                            {activeMenuData?.label || "Dashboard"}
                        </h1>

                        {activeMenuData?.description && (
                            <p className="mt-1 hidden truncate text-sm font-semibold text-slate-500 sm:block">
                                {activeMenuData.description}
                            </p>
                        )}
                    </div>
                </div>

                <div className="flex shrink-0 items-center gap-3">
                    {showActionButton && activeMenuData?.action && (
                        <button
                            type="button"
                            onClick={onHeaderAction}
                            className="rounded-2xl bg-teal-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-teal-100 transition hover:-translate-y-0.5 hover:bg-teal-700 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60 sm:px-6"
                        >
                            <span className="hidden sm:inline">
                                {activeMenuData.action.label}
                            </span>

                            <span className="sm:hidden">Tambah</span>
                        </button>
                    )}

                    <button
                        type="button"
                        onClick={handleLogout}
                        disabled={logoutLoading}
                        title="Logout"
                        aria-label="Logout"
                        className="group flex h-12 w-12 items-center justify-center rounded-2xl border border-rose-100 bg-white text-rose-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-600 hover:text-white hover:shadow-lg hover:shadow-rose-100 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {logoutLoading ? (
                            <svg
                                className="h-5 w-5 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <circle
                                    className="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    strokeWidth="4"
                                />
                                <path
                                    className="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                />
                            </svg>
                        ) : (
                            <svg
                                className="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="2.4"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M12 2v10" />
                                <path d="M18.4 6.6a9 9 0 1 1-12.8 0" />
                            </svg>
                        )}
                    </button>
                </div>
            </div>
        </header>
    );
}