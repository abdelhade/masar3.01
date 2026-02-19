/**
 * Import Bootstrap
 */
import 'bootstrap';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import "./echo";
import "./components/currency-converter";

// ✅ Alpine.js components for invoices are now embedded directly in Blade templates
// This ensures they are registered before Alpine tries to use them
// See: resources/views/livewire/invoices/create-invoice-form.blade.php

(function attachTableExportActions() {
    function downloadBlob(content, mimeType, filename) {
        const blob = new Blob([content], {
            type: mimeType + ";charset=utf-8;",
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function tableToCsv(table, skipLastColumn) {
        const rows = Array.from(table.querySelectorAll("tr"));
        return rows
            .map((row) => {
                const cells = Array.from(row.querySelectorAll("th,td"));
                const usedCells =
                    skipLastColumn && cells.length ? cells.slice(0, -1) : cells;
                return usedCells
                    .map((cell) => {
                        let text = (cell.innerText || "")
                            .replace(/\r?\n|\r/g, " ")
                            .trim();
                        if (
                            text.includes('"') ||
                            text.includes(",") ||
                            text.includes("\n")
                        ) {
                            text = '"' + text.replace(/"/g, '""') + '"';
                        }
                        return text;
                    })
                    .join(",");
            })
            .join("\n");
    }

    function cloneTableWithoutLastColumn(table) {
        const clone = table.cloneNode(true);
        const rows = Array.from(clone.querySelectorAll("tr"));
        rows.forEach((row) => {
            if (row.cells && row.cells.length) {
                row.deleteCell(row.cells.length - 1);
            }
        });
        return clone;
    }

    function openPrintWindowFromTable(table, filename, skipLastColumn) {
        const win = window.open("", "_blank");
        if (!win) {
            alert("Popup blocked");
            return;
        }
        const tableToPrint = skipLastColumn
            ? cloneTableWithoutLastColumn(table)
            : table;
        const html =
            "<!doctype html>" +
            "<html>" +
            "<head>" +
            '<meta charset="utf-8" />' +
            "<title>" +
            filename +
            "</title>" +
            "<style>" +
            'body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, "Apple Color Emoji", "Segoe UI Emoji"; padding: 16px; }' +
            "table { width: 100%; border-collapse: collapse; }" +
            "th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }" +
            "th { background: #f3f4f6; }" +
            "</style>" +
            "</head>" +
            "<body>" +
            tableToPrint.outerHTML +
            "</body>" +
            "</html>";
        win.document.open();
        win.document.write(html);
        win.document.close();
        win.focus();
        setTimeout(function () {
            try {
                win.print();
            } catch (e) {
                console.error("[table-export-actions] print error", e);
            }
            setTimeout(function () {
                try {
                    win.close();
                } catch (e) {}
            }, 400);
        }, 100);
    }

    function handleClick(e) {
        const btn =
            e.target && e.target.closest
                ? e.target.closest("button[data-action]")
                : null;
        if (!btn) {
            return;
        }
        const container = btn.closest("[data-export-actions]");
        if (!container) {
            return;
        }

        try {
            const tableId = container.getAttribute("data-table-id");
            const filenameBase =
                container.getAttribute("data-filename") || "export";
            const skipLast =
                (container.getAttribute("data-skip-last") || "true") === "true";
            const table = document.getElementById(tableId);
            if (!table) {
                console.warn(
                    "[table-export-actions] Table not found:",
                    tableId
                );
                return;
            }
            const action = btn.getAttribute("data-action");
            if (action === "export-excel") {
                const csv = tableToCsv(table, skipLast);
                downloadBlob(csv, "text/csv", filenameBase + ".csv");
            } else if (action === "export-pdf" || action === "print") {
                openPrintWindowFromTable(table, filenameBase, skipLast);
            }
        } catch (err) {
            console.error("[table-export-actions] click error", err);
        }
    }

    function bind() {
        document.addEventListener("click", handleClick);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bind);
    } else {
        bind();
    }
})();

// Sidebar Toggle Functionality
(function initSidebarToggle() {
    function initLeftMenuCollapse() {
        // Left menu collapse - works with both jQuery and vanilla JS
        const menuButtons = document.querySelectorAll(".button-menu-mobile");

        menuButtons.forEach((button) => {
            button.addEventListener("click", function (event) {
                event.preventDefault();
                document.body.classList.toggle("enlarge-menu");
            });
        });
    }

    function initEnlarge() {
        // Auto-enlarge menu on small screens
        if (window.innerWidth < 1300) {
            document.body.classList.add("enlarge-menu", "enlarge-menu-all");
        } else {
            document.body.classList.remove("enlarge-menu", "enlarge-menu-all");
        }
    }

    // Initialize on DOM ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            initLeftMenuCollapse();
            initEnlarge();
        });
    } else {
        initLeftMenuCollapse();
        initEnlarge();
    }

    // Re-initialize on window resize
    window.addEventListener("resize", function () {
        initEnlarge();
    });

    // Also support jQuery if available (for backward compatibility)
    if (typeof jQuery !== "undefined") {
        jQuery(document).ready(function ($) {
            $(".button-menu-mobile").on("click", function (event) {
                event.preventDefault();
                $("body").toggleClass("enlarge-menu");
            });

            function initEnlargeJQ() {
                if ($(window).width() < 1300) {
                    $("body").addClass("enlarge-menu enlarge-menu-all");
                } else {
                    $("body").removeClass("enlarge-menu enlarge-menu-all");
                }
            }

            initEnlargeJQ();
            $(window).on("resize", initEnlargeJQ);
        });
    }
})();
