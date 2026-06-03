import React, {
    useEffect,
    useLayoutEffect,
    useMemo,
    useRef,
    useState,
} from "react";
import AdminHeader from "./AdminHeader";
import AdminSidebar from "./AdminSidebar";
import {
    defaultMenuKey,
    getActiveMenuData,
    initialActionSignals,
} from "./adminMenuConfig";

export default function AdminLayout() {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [activeMenu, setActiveMenu] = useState(defaultMenuKey);

    const [detailPelamarId, setDetailPelamarId] = useState(null);
    const [detailDaftarHadirZoomTanggal, setDetailDaftarHadirZoomTanggal] =
        useState(null);
    const [detailDaftarHadirMmpiTanggal, setDetailDaftarHadirMmpiTanggal] =
        useState(null);

    const [actionSignals, setActionSignals] = useState(initialActionSignals);
    const [openMenus, setOpenMenus] = useState({});

    const originalAlertRef = useRef(null);
    const originalConfirmRef = useRef(null);

    const [alertState, setAlertState] = useState({
        open: false,
        title: "Informasi",
        message: "",
        type: "info",
        redirectToDashboard: false,
    });

    const isDetailPelamarPage = activeMenu === "data-pelamar-detail";
    const isDetailDaftarHadirZoomPage =
        activeMenu === "daftar-hadir-zoom-detail";
    const isDetailDaftarHadirMmpiPage =
        activeMenu === "daftar-hadir-mmpi-detail";

    const activeMenuData = useMemo(
        () => getActiveMenuData(activeMenu),
        [activeMenu]
    );

    const ActiveComponent = activeMenuData.component;

    const closeSidebar = () => setSidebarOpen(false);

    const resetDetails = () => {
        setDetailPelamarId(null);
        setDetailDaftarHadirZoomTanggal(null);
        setDetailDaftarHadirMmpiTanggal(null);
    };

    const openOnlyMenu = (menuKey) => {
        setOpenMenus(menuKey ? { [menuKey]: true } : {});
    };

    const redirectToDashboard = () => {
        resetDetails();
        setActiveMenu(defaultMenuKey);
        setOpenMenus({});
        setSidebarOpen(false);

        window.history.replaceState(null, "", "/dashboard");
    };

    const detectAlertMeta = (message) => {
        const originalMessage = String(message || "");
        const text = originalMessage.toLowerCase();

        /*
         * Success harus dicek lebih dulu.
         * Karena pesan seperti "Permission role berhasil disimpan"
         * mengandung kata permission, tapi itu pesan sukses.
         */
        if (
            text.includes("berhasil") ||
            text.includes("success") ||
            text.includes("sukses") ||
            text.includes("tersimpan") ||
            text.includes("disimpan") ||
            text.includes("ditambahkan") ||
            text.includes("diperbarui") ||
            text.includes("dihapus") ||
            text.includes("saved") ||
            text.includes("updated") ||
            text.includes("deleted")
        ) {
            return {
                type: "success",
                title: "Berhasil",
                message: originalMessage,
                redirectToDashboard: false,
            };
        }

        /*
         * Permission denied hanya untuk kalimat penolakan akses.
         * Jangan pakai text.includes("permission") saja,
         * karena halaman setting permission pasti banyak memakai kata permission.
         */
        if (
            text.includes("user does not have the right permissions") ||
            text.includes("does not have the right permissions") ||
            text.includes("this action is unauthorized") ||
            text.includes("unauthorized") ||
            text.includes("forbidden") ||
            text.includes("403") ||
            text.includes("akses ditolak") ||
            text.includes("tidak memiliki permission") ||
            text.includes("tidak memiliki hak akses") ||
            text.includes("anda tidak memiliki permission") ||
            text.includes("anda tidak memiliki hak akses")
        ) {
            return {
                type: "permission",
                title: "Akses Ditolak",
                message:
                    "Anda tidak memiliki permission untuk mengakses fitur ini. Anda akan diarahkan kembali ke Dashboard.",
                redirectToDashboard: true,
            };
        }

        if (
            text.includes("gagal") ||
            text.includes("error") ||
            text.includes("kesalahan") ||
            text.includes("failed") ||
            text.includes("internal server") ||
            text.includes("server error")
        ) {
            return {
                type: "error",
                title: "Terjadi Kesalahan",
                message: originalMessage,
                redirectToDashboard: false,
            };
        }

        return {
            type: "info",
            title: "Informasi",
            message: originalMessage,
            redirectToDashboard: false,
        };
    };

    const showCustomAlert = (message, options = {}) => {
        const detected = detectAlertMeta(message);

        setAlertState({
            open: true,
            title: options.title || detected.title,
            message: options.message || detected.message,
            type: options.type || detected.type,
            redirectToDashboard:
                typeof options.redirectToDashboard === "boolean"
                    ? options.redirectToDashboard
                    : detected.redirectToDashboard,
        });
    };

    const closeCustomAlert = () => {
        const shouldRedirect = alertState.redirectToDashboard;

        setAlertState((prev) => ({
            ...prev,
            open: false,
        }));

        if (shouldRedirect) {
            redirectToDashboard();
        }
    };

    useLayoutEffect(() => {
        if (!originalAlertRef.current) {
            originalAlertRef.current = window.alert;
        }

        if (!originalConfirmRef.current) {
            originalConfirmRef.current = window.confirm;
        }

        window.alert = (message) => {
            showCustomAlert(message);
        };

        window.adminAlert = (message, options = {}) => {
            showCustomAlert(message, options);
        };

        return () => {
            if (originalAlertRef.current) {
                window.alert = originalAlertRef.current;
            }

            if (originalConfirmRef.current) {
                window.confirm = originalConfirmRef.current;
            }

            delete window.adminAlert;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const handleMenuClick = (menu) => {
        const hasChildren = Array.isArray(menu.children);

        resetDetails();

        if (hasChildren) {
            const isCurrentlyOpen = Boolean(openMenus[menu.key]);

            setOpenMenus(isCurrentlyOpen ? {} : { [menu.key]: true });
            closeSidebar();
            return;
        }

        setActiveMenu(menu.key);
        openOnlyMenu(null);
        closeSidebar();
    };

    const handleSubMenuClick = (parentKey, childKey) => {
        resetDetails();
        setActiveMenu(childKey);
        openOnlyMenu(parentKey);
        closeSidebar();
    };

    const handleHeaderAction = () => {
        const action = activeMenuData.action;

        if (!action) return;

        setActionSignals((prev) => ({
            ...prev,
            [action.signalKey]: (prev[action.signalKey] || 0) + 1,
        }));
    };

    const handleOpenDetailPelamar = (id) => {
        setDetailPelamarId(id);
        setDetailDaftarHadirZoomTanggal(null);
        setDetailDaftarHadirMmpiTanggal(null);
        setActiveMenu("data-pelamar-detail");
        openOnlyMenu(null);
        closeSidebar();
    };

    const handleBackToDataPelamar = () => {
        setDetailPelamarId(null);
        setActiveMenu("data-pelamar");
        openOnlyMenu(null);
        closeSidebar();
    };

    const handleOpenDetailDaftarHadirZoom = (tanggal) => {
        setDetailDaftarHadirZoomTanggal(tanggal);
        setDetailDaftarHadirMmpiTanggal(null);
        setDetailPelamarId(null);
        setActiveMenu("daftar-hadir-zoom-detail");
        openOnlyMenu("daftar-hadir");
        closeSidebar();
    };

    const handleBackToDaftarHadirZoom = () => {
        setDetailDaftarHadirZoomTanggal(null);
        setActiveMenu("daftar-hadir-zoom");
        openOnlyMenu("daftar-hadir");
        closeSidebar();
    };

    const handleOpenDetailDaftarHadirMmpi = (tanggal) => {
        setDetailDaftarHadirMmpiTanggal(tanggal);
        setDetailDaftarHadirZoomTanggal(null);
        setDetailPelamarId(null);
        setActiveMenu("daftar-hadir-mmpi-detail");
        openOnlyMenu("daftar-hadir");
        closeSidebar();
    };

    const handleBackToDaftarHadirMmpi = () => {
        setDetailDaftarHadirMmpiTanggal(null);
        setActiveMenu("daftar-hadir-mmpi");
        openOnlyMenu("daftar-hadir");
        closeSidebar();
    };

    const handleNavigate = (key, props = {}) => {
        if (!key) return;

        if (key === "daftar-hadir-mmpi-detail") {
            handleOpenDetailDaftarHadirMmpi(props.tanggal);
            return;
        }

        if (key === "daftar-hadir-zoom-detail") {
            handleOpenDetailDaftarHadirZoom(props.tanggal);
            return;
        }

        if (key === "data-pelamar-detail") {
            handleOpenDetailPelamar(props.id);
            return;
        }

        resetDetails();
        setActiveMenu(key);
        openOnlyMenu(key.startsWith("daftar-hadir") ? "daftar-hadir" : null);
        closeSidebar();
    };

    useEffect(() => {
        const listener = (event) => {
            const detail = event?.detail || {};
            handleNavigate(detail.key, detail.props || {});
        };

        window.addEventListener("admin:navigate", listener);

        return () => {
            window.removeEventListener("admin:navigate", listener);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const showActionButton =
        Boolean(activeMenuData.action) &&
        !isDetailPelamarPage &&
        !isDetailDaftarHadirZoomPage &&
        !isDetailDaftarHadirMmpiPage;

    return (
        <main className="min-h-screen bg-slate-100 text-slate-900">
            <div className="flex min-h-screen">
                <AdminSidebar
                    sidebarOpen={sidebarOpen}
                    activeMenu={activeMenu}
                    openMenus={openMenus}
                    onCloseSidebar={closeSidebar}
                    onMenuClick={handleMenuClick}
                    onSubMenuClick={handleSubMenuClick}
                />

                <section className="flex min-w-0 flex-1 flex-col">
                    <AdminHeader
                        activeMenuData={activeMenuData}
                        showActionButton={showActionButton}
                        onOpenSidebar={() => setSidebarOpen(true)}
                        onHeaderAction={handleHeaderAction}
                    />

                    <div className="flex-1 p-5 sm:p-8">
                        {isDetailPelamarPage ? (
                            <ActiveComponent
                                id={detailPelamarId}
                                onBack={handleBackToDataPelamar}
                            />
                        ) : isDetailDaftarHadirZoomPage ? (
                            <ActiveComponent
                                tanggal={detailDaftarHadirZoomTanggal}
                                onBack={handleBackToDaftarHadirZoom}
                            />
                        ) : isDetailDaftarHadirMmpiPage ? (
                            <ActiveComponent
                                tanggal={detailDaftarHadirMmpiTanggal}
                                onBack={handleBackToDaftarHadirMmpi}
                            />
                        ) : (
                            <ActiveComponent
                                actionSignals={actionSignals}
                                onNavigate={handleNavigate}
                                onOpenDetailPelamar={handleOpenDetailPelamar}
                                onOpenDetailDaftarHadirZoom={
                                    handleOpenDetailDaftarHadirZoom
                                }
                                onOpenDetailDaftarHadirMmpi={
                                    handleOpenDetailDaftarHadirMmpi
                                }
                            />
                        )}
                    </div>
                </section>
            </div>

            <AdminAlertModal
                open={alertState.open}
                title={alertState.title}
                message={alertState.message}
                type={alertState.type}
                onClose={closeCustomAlert}
            />
        </main>
    );
}

function AdminAlertModal({ open, title, message, type, onClose }) {
    if (!open) return null;

    const style = getAlertStyle(type);

    return (
        <div className="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div className="w-full max-w-md overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div className={`h-2 w-full ${style.topBar}`} />

                <div className="px-6 pt-6">
                    <div className="flex items-start gap-4">
                        <div
                            className={`flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl text-3xl font-black shadow-lg ${style.iconBox}`}
                        >
                            {style.icon}
                        </div>

                        <div className="min-w-0 flex-1">
                            <div
                                className={`mb-2 inline-flex rounded-full border px-3 py-1 text-xs font-black uppercase tracking-wide ${style.badge}`}
                            >
                                {style.badgeText}
                            </div>

                            <h2 className="text-2xl font-black text-slate-950">
                                {title}
                            </h2>

                            <p className="mt-2 text-sm font-semibold leading-6 text-slate-500">
                                {message}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="mt-6 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <div className="flex justify-end gap-3">
                        <button
                            type="button"
                            onClick={onClose}
                            className={`rounded-2xl px-6 py-3 text-sm font-black text-white shadow-lg transition ${style.button}`}
                        >
                            Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

function getAlertStyle(type) {
    const styles = {
        permission: {
            icon: "🔒",
            badgeText: "Permission Denied",
            topBar: "bg-amber-500",
            badge: "border-amber-100 bg-amber-50 text-amber-700",
            iconBox: "bg-amber-500 text-white shadow-amber-100",
            button: "bg-amber-500 hover:bg-amber-600 shadow-amber-100",
        },
        success: {
            icon: "✓",
            badgeText: "Success",
            topBar: "bg-teal-600",
            badge: "border-teal-100 bg-teal-50 text-teal-700",
            iconBox: "bg-teal-600 text-white shadow-teal-100",
            button: "bg-teal-600 hover:bg-teal-700 shadow-teal-100",
        },
        error: {
            icon: "!",
            badgeText: "Error",
            topBar: "bg-rose-600",
            badge: "border-rose-100 bg-rose-50 text-rose-700",
            iconBox: "bg-rose-600 text-white shadow-rose-100",
            button: "bg-rose-600 hover:bg-rose-700 shadow-rose-100",
        },
        info: {
            icon: "i",
            badgeText: "Info",
            topBar: "bg-slate-900",
            badge: "border-slate-100 bg-slate-50 text-slate-700",
            iconBox: "bg-slate-900 text-white shadow-slate-100",
            button: "bg-slate-900 hover:bg-slate-800 shadow-slate-100",
        },
    };

    return styles[type] || styles.info;
}