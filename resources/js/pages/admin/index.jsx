import "../../../css/app.css";

import React from "react";
import { createRoot } from "react-dom/client";
import AdminLayout from "./components/AdminLayout";

const rootElement = document.getElementById("admin-root");

if (rootElement) {
    createRoot(rootElement).render(<AdminLayout />);
}
