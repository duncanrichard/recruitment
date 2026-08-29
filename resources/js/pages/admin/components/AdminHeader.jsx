import React, { useEffect, useMemo, useRef, useState } from "react";

export default function AdminHeader({
    activeMenuData,
    showActionButton,
    onOpenSidebar,
    onHeaderAction,
    currentUser = null,
}) {
    const [logoutLoading, setLogoutLoading] = useState(false);
    const [profileMenuOpen, setProfileMenuOpen] = useState(false);
    const [profileModalOpen, setProfileModalOpen] = useState(false);
    const [profileLoading, setProfileLoading] = useState(false);
    const [authUser, setAuthUser] = useState(currentUser || null);

    const profileMenuRef = useRef(null);

    const userName =
        authUser?.name ||
        authUser?.nama ||
        authUser?.username ||
        authUser?.email ||
        (profileLoading ? "Memuat..." : "User");

    const userEmail = authUser?.email || "-";

    const userRole = useMemo(() => {
        if (authUser?.role?.name) return authUser.role.name;
        if (authUser?.role?.nama) return authUser.role.nama;
        if (authUser?.role_name) return authUser.role_name;
        if (authUser?.nama_role) return authUser.nama_role;

        if (Array.isArray(authUser?.roles) && authUser.roles.length > 0) {
            return authUser.roles
                .map((role) => {
                    if (typeof role === "string") return role;

                    return role?.name || role?.nama || role?.label || null;
                })
                .filter(Boolean)
                .join(", ");
        }

        return "-";
    }, [authUser]);

    const userPerusahaan = useMemo(() => {
        if (Array.isArray(authUser?.perusahaans)) {
            return authUser.perusahaans;
        }

        if (Array.isArray(authUser?.perusahaan)) {
            return authUser.perusahaan;
        }

        if (Array.isArray(authUser?.data_perusahaan)) {
            return authUser.data_perusahaan;
        }

        if (authUser?.perusahaan) {
            return [authUser.perusahaan];
        }

        if (authUser?.data_perusahaan) {
            return [authUser.data_perusahaan];
        }

        if (authUser?.perusahaan_id || authUser?.nama_perusahaan) {
            return [
                {
                    id: authUser?.perusahaan_id,
                    kode: authUser?.kode_perusahaan,
                    nama_perusahaan: authUser?.nama_perusahaan,
                },
            ];
        }

        return [];
    }, [authUser]);

    const getInitials = (value) => {
        const text = String(value || "U").trim();

        if (!text) return "U";

        const parts = text.split(" ").filter(Boolean);

        if (parts.length === 1) {
            return parts[0].charAt(0).toUpperCase();
        }

        return `${parts[0].charAt(0)}${parts[1].charAt(0)}`.toUpperCase();
    };

    const getPerusahaanLabel = (item) => {
        if (!item) return "-";

        const kode = item.kode || item.kode_perusahaan || null;
        const nama =
            item.nama_perusahaan ||
            item.perusahaan ||
            item.nama ||
            item.name ||
            item.label ||
            null;

        if (kode && nama) return `${kode} - ${nama}`;

        return nama || kode || "-";
    };

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const parseJsonResponse = async (response) => {
        const text = await response.text();

        if (!text) return {};

        try {
            return JSON.parse(text);
        } catch (error) {
            console.error("Response bukan JSON:", text);

            return {
                success: false,
                message: "Response server tidak valid.",
                raw: text,
            };
        }
    };

    const fetchAuthUser = async () => {
        setProfileLoading(true);

        try {
            const response = await fetch("/admin/auth-user", {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await parseJsonResponse(response);

            if (response.ok && result.success) {
                setAuthUser(result.data || null);
                return;
            }

            console.error("Gagal mengambil data user login:", result);
        } catch (error) {
            console.error("Gagal mengambil data user login:", error);
        } finally {
            setProfileLoading(false);
        }
    };

    useEffect(() => {
        fetchAuthUser();
    }, []);


    useEffect(() => {
        if (currentUser) {
            setAuthUser(currentUser);
        }
    }, [currentUser]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                profileMenuRef.current &&
                !profileMenuRef.current.contains(event.target)
            ) {
                setProfileMenuOpen(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);

        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, []);

    const openProfileModal = async () => {
        setProfileMenuOpen(false);
        setProfileModalOpen(true);
        await fetchAuthUser();
    };

    const closeProfileModal = () => {
        setProfileModalOpen(false);
    };

    const handleLogout = async () => {
        const confirmLogout = confirm("Yakin ingin logout?");

        if (!confirmLogout) return;

        setProfileMenuOpen(false);
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

            const result = await parseJsonResponse(response);

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
        <>
            <header className="sticky top-0 z-30 border-b border-indigo-100 bg-white/95 backdrop-blur-xl">
                <div className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-600 via-violet-500 to-indigo-600" />
                <div className="relative grid min-h-[86px] grid-cols-[minmax(0,1fr)_auto] items-center gap-4 overflow-hidden px-5 pt-1 sm:px-8">
                    <div className="pointer-events-none absolute -left-20 -top-24 h-52 w-52 rounded-full bg-indigo-100/60 blur-3xl" />

                    <div className="relative flex min-w-0 items-center gap-4">
                        <button
                            type="button"
                            onClick={onOpenSidebar}
                            className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-xl font-black text-indigo-700 transition hover:bg-indigo-100 lg:hidden"
                            aria-label="Buka menu"
                        >
                            ☰
                        </button>

                        <div className="hidden min-w-0 items-center gap-3 lg:flex">
                            <div className="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-sm font-black text-white shadow-lg shadow-indigo-200">
                                HR
                                <span className="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-[3px] border-white bg-violet-400" />
                            </div>
                            <div className="min-w-0">
                                <p className="truncate text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600">Sistem Recruitment</p>
                                <p className="mt-1 truncate text-xl font-black tracking-tight text-slate-950">SIREKRUT</p>
                                <p className="truncate text-[11px] font-semibold text-slate-400">Recruitment workspace</p>
                            </div>
                        </div>

                        <div className="min-w-0 lg:hidden"><p className="text-[10px] font-black uppercase tracking-[0.18em] text-indigo-600">{activeMenuData?.parentLabel || "Sistem Recruitment"}</p><h1 className="mt-1 truncate text-xl font-black text-slate-950">{activeMenuData?.label || "Dashboard"}</h1></div>
                    </div>

                    <div className="relative flex shrink-0 items-center justify-end gap-3">
                        {(showActionButton && activeMenuData?.action) ? (
                            <button
                                type="button"
                                onClick={onHeaderAction}
                                className="inline-flex h-11 items-center justify-center whitespace-nowrap rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60 sm:px-6"
                            >
                                <span className="hidden sm:inline">
                                    {activeMenuData.action.label}
                                </span>

                                <span className="sm:hidden">Tambah</span>
                            </button>
                        ) : null}

                        <div ref={profileMenuRef} className="relative">
                            <button
                                type="button"
                                onClick={() =>
                                    setProfileMenuOpen((prev) => !prev)
                                }
                                className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white/90 px-3 py-2 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:shadow-lg hover:shadow-indigo-100"
                            >
                                <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-black text-white shadow-md shadow-indigo-100">
                                    {getInitials(userName)}
                                </div>

                                <div className="hidden min-w-0 text-left md:block">
                                    <div className="max-w-[160px] truncate text-sm font-black text-slate-900">
                                        {userName}
                                    </div>
                                    <div className="max-w-[160px] truncate text-xs font-bold text-slate-500">
                                        {userEmail}
                                    </div>
                                </div>

                                <svg
                                    className={`h-4 w-4 text-slate-500 transition ${
                                        profileMenuOpen ? "rotate-180" : ""
                                    }`}
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="2.5"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </button>

                            {profileMenuOpen && (
                                <div className="absolute right-0 mt-3 w-72 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-200/70">
                                    <div className="border-b border-slate-100 bg-gradient-to-br from-indigo-50 to-violet-50 p-4">
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 text-base font-black text-white shadow-lg shadow-indigo-100">
                                                {getInitials(userName)}
                                            </div>

                                            <div className="min-w-0">
                                                <div className="truncate text-sm font-black text-slate-950">
                                                    {userName}
                                                </div>
                                                <div className="truncate text-xs font-bold text-slate-500">
                                                    {userEmail}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="p-2">
                                        <button
                                            type="button"
                                            onClick={openProfileModal}
                                            className="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-sm font-black text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
                                        >
                                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                                                👤
                                            </span>
                                            <span>Lihat Profil</span>
                                        </button>

                                        <button
    type="button"
    onClick={handleLogout}
    disabled={logoutLoading}
    className="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-sm font-black text-rose-600 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-60"
>
    <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
        {logoutLoading ? (
            <svg
                className="h-4 w-4 animate-spin"
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
                strokeWidth="2.2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
            >
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <path d="M16 17l5-5-5-5" />
                <path d="M21 12H9" />
            </svg>
        )}
    </span>

    <span>{logoutLoading ? "Logout..." : "Logout"}</span>
</button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </header>

            {profileModalOpen && (
                <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <div className="bg-gradient-to-br from-indigo-600 to-violet-600 px-6 py-6 text-white">
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex items-center gap-4">
                                    <div className="flex h-16 w-16 items-center justify-center rounded-3xl bg-white/20 text-xl font-black shadow-lg backdrop-blur">
                                        {getInitials(userName)}
                                    </div>

                                    <div>
                                        <div className="text-xs font-black uppercase tracking-[0.18em] text-white/70">
                                            Profil Login
                                        </div>
                                        <h2 className="mt-1 text-2xl font-black">
                                            {userName}
                                        </h2>
                                        <p className="mt-1 text-sm font-bold text-white/80">
                                            {userEmail}
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    onClick={closeProfileModal}
                                    className="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/15 text-xl font-black text-white transition hover:bg-white/25"
                                    aria-label="Tutup modal"
                                >
                                    ×
                                </button>
                            </div>
                        </div>

                        <div className="space-y-4 p-6">
                            {profileLoading && (
                                <div className="rounded-3xl border border-violet-100 bg-violet-50 px-4 py-3 text-sm font-black text-violet-700">
                                    Memuat data profil...
                                </div>
                            )}

                            <ProfileInfoRow label="Nama" value={userName} />
                            <ProfileInfoRow label="Email" value={userEmail} />
                            <ProfileInfoRow label="Role" value={userRole} />

                            <div className="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <div className="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                                    Perusahaan
                                </div>

                                {userPerusahaan.length > 0 ? (
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        {userPerusahaan.map((item, index) => (
                                            <span
                                                key={item?.id || index}
                                                className="inline-flex rounded-2xl bg-indigo-50 px-3 py-2 text-xs font-black text-indigo-700"
                                            >
                                                {getPerusahaanLabel(item)}
                                            </span>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="mt-3 rounded-2xl bg-white px-4 py-3 text-sm font-bold text-slate-500">
                                        Data perusahaan belum tersedia.
                                    </div>
                                )}
                            </div>

                            <div className="flex justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    onClick={closeProfileModal}
                                    className="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50"
                                >
                                    Tutup
                                </button>

                                <button
                                    type="button"
                                    onClick={handleLogout}
                                    disabled={logoutLoading}
                                    className="rounded-2xl bg-rose-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-rose-100 transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {logoutLoading ? "Logout..." : "Logout"}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}

function ProfileInfoRow({ label, value }) {
    return (
        <div className="rounded-3xl border border-slate-200 bg-white p-4">
            <div className="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                {label}
            </div>
            <div className="mt-1 break-words text-sm font-black text-slate-800">
                {value || "-"}
            </div>
        </div>
    );
}
