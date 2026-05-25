import React, { useEffect, useMemo, useState } from "react";
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
        </main>
    );
}
