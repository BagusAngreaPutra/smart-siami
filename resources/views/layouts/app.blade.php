<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SMART SIAMI')</title>
    <style>
        :root {
            --bg: #eef3f6;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --line: #d7e0e7;
            --line-strong: #c4d0da;
            --text: #17202c;
            --muted: #667085;
            --brand: #176b87;
            --brand-strong: #0f4d5f;
            --brand-soft: #e8f4f7;
            --accent: #b45309;
            --danger: #b42318;
            --success: #14804a;
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, .06);
            --shadow-md: 0 14px 34px rgba(15, 23, 42, .08);
            --shadow-lg: 0 24px 54px rgba(15, 23, 42, .14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--text);
            background: var(--bg);
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            line-height: 1.5;
            text-rendering: optimizeLegibility;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .app-shell {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            background: #122333;
            color: #edf6f9;
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .brand {
            padding: 0 8px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, .16);
        }

        .brand-title {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .brand-subtitle {
            margin: 4px 0 0;
            color: #b6c5d1;
            font-size: 13px;
        }

        .nav-list {
            display: grid;
            gap: 6px;
        }

        .nav-link {
            display: block;
            border-radius: 8px;
            padding: 11px 12px;
            color: #d9e5ec;
            font-size: 14px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #1f6f8b;
            color: #ffffff;
        }

        .sidebar-footer {
            margin-top: auto;
            display: grid;
            gap: 10px;
            padding: 14px 8px 0;
            border-top: 1px solid rgba(255, 255, 255, .16);
            font-size: 13px;
        }

        .user-name {
            font-weight: 700;
        }

        .user-meta {
            color: #b6c5d1;
        }

        .topbar {
            min-height: 72px;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 28px;
            gap: 18px;
        }

        .page-title {
            margin: 0;
            font-size: 24px;
            letter-spacing: 0;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .notification-menu {
            position: relative;
        }

        .notification-menu summary {
            list-style: none;
        }

        .notification-menu summary::-webkit-details-marker {
            display: none;
        }

        .topbar-icon-button {
            position: relative;
            display: inline-grid;
            place-items: center;
            width: 42px;
            height: 42px;
            min-width: 42px;
            padding: 0;
            border-radius: 14px;
            border: 1px solid rgba(14, 102, 86, .12);
            background: #ffffff;
            color: var(--brand-strong);
            box-shadow: 0 3px 10px rgba(14, 102, 86, .07);
            text-decoration: none;
            line-height: 1;
            cursor: pointer;
            transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease, border-color 160ms ease;
        }

        .topbar-icon-button:hover,
        .topbar-icon-button:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 9px 20px rgba(14, 102, 86, .12);
            outline: 0;
        }

        .topbar-icon-button svg {
            display: block;
            width: 19px;
            height: 19px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .topbar-icon-theme {
            background: #fff7e8;
            border-color: rgba(217, 164, 65, .24);
            color: #b7791f;
        }

        .topbar-icon-notification {
            background: #fff0ef;
            border-color: rgba(199, 100, 90, .22);
            color: var(--danger);
        }

        .topbar-icon-profile {
            background: #f2f4f7;
            border-color: rgba(100, 116, 139, .18);
            color: #475467;
        }

        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            min-height: 20px;
            padding: 0 6px;
            margin-left: 0;
            border-radius: 999px;
            background: var(--danger);
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            border: 2px solid #ffffff;
        }

        .notification-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            z-index: 20;
            width: min(380px, 92vw);
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .16);
            padding: 10px;
        }

        .notification-dropdown .list-item {
            display: block;
            margin-bottom: 8px;
        }

        .notification-dropdown .list-item:last-child {
            margin-bottom: 0;
        }

        .notification-list-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: start;
        }

        .notification-list-link {
            display: block;
            min-width: 0;
        }

        .notification-delete-button {
            min-height: 34px;
            padding: 7px 10px;
            color: var(--danger);
            border-color: rgba(180, 35, 24, .24);
        }

        .notification-delete-button:hover {
            background: rgba(180, 35, 24, .08);
            color: var(--danger);
        }

        .notification-dropdown .notification-list-row {
            gap: 8px;
            padding: 10px;
        }

        .notification-dropdown .notification-delete-button {
            min-width: 34px;
            width: 34px;
            height: 34px;
            padding: 0;
            border-radius: 10px;
            font-size: 18px;
            line-height: 1;
        }

        .danger-confirm-modal[hidden] {
            display: none;
        }

        .danger-confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 120;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .danger-confirm-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, .50);
            backdrop-filter: blur(3px);
        }

        .danger-confirm-card {
            position: relative;
            z-index: 1;
            width: min(460px, 100%);
            border: 1px solid rgba(180, 35, 24, .20);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: var(--shadow-lg);
            padding: 24px;
        }

        .danger-confirm-icon {
            display: inline-grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: rgba(180, 35, 24, .10);
            color: var(--danger);
            font-size: 24px;
            font-weight: 900;
        }

        .danger-confirm-card h3 {
            margin: 14px 0 8px;
            font-size: 22px;
        }

        .danger-confirm-card p {
            margin: 0;
            color: var(--muted);
        }

        .danger-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .danger-confirm-submit {
            background: var(--danger);
        }

        .danger-confirm-submit:hover {
            background: #8f1f16;
        }

        .danger-confirm-submit:disabled {
            cursor: wait;
            opacity: .58;
        }

        .advanced-settings-grid {
            display: grid;
            gap: 18px;
        }

        .danger-zone-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 16px;
            align-items: start;
            border: 1px solid rgba(180, 35, 24, .20);
            border-radius: 16px;
            padding: 22px;
            background:
                linear-gradient(135deg, rgba(180, 35, 24, .06), rgba(255, 255, 255, .92)),
                var(--surface);
        }

        .danger-zone-card h3 {
            margin: 0 0 6px;
            font-size: 22px;
        }

        .danger-zone-card form,
        .reset-impact-list {
            grid-column: 1 / -1;
        }

        .reset-impact-list {
            display: grid;
            gap: 8px;
            border-radius: 14px;
            padding: 14px;
            background: rgba(180, 35, 24, .06);
            color: var(--muted);
            font-size: 14px;
        }

        .content {
            padding: 28px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 24px;
        }

        .panel + .panel {
            margin-top: 18px;
        }

        .template-panel {
            margin: 18px 0;
            padding: 18px;
            background: color-mix(in srgb, var(--brand-soft) 32%, transparent);
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        .template-panel h3 {
            margin: 0 0 6px;
            font-size: 17px;
        }

        .template-actions {
            flex-wrap: wrap;
            gap: 10px;
        }

        .instrument-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(280px, .85fr);
            gap: 24px;
            align-items: center;
            margin-bottom: 18px;
            padding: 28px;
            border-radius: 16px;
            background:
                linear-gradient(135deg, rgba(14, 102, 86, .96), rgba(61, 156, 135, .92)),
                var(--brand);
            color: #ffffff;
            box-shadow: 0 18px 44px rgba(14, 102, 86, .18);
        }

        .instrument-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, .09) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .09) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: linear-gradient(90deg, transparent, #000 18%, #000 82%, transparent);
        }

        .instrument-hero > * {
            position: relative;
            z-index: 1;
        }

        .instrument-hero h2 {
            margin: 8px 0 8px;
            font-size: clamp(28px, 4vw, 44px);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .instrument-hero p {
            max-width: 720px;
            margin: 0;
            color: rgba(255, 255, 255, .82);
            font-size: 16px;
        }

        .instrument-eyebrow {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            min-height: 28px;
            border-radius: 999px;
            padding: 5px 10px;
            background: rgba(255, 255, 255, .14);
            color: inherit;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .instrument-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .instrument-stat {
            min-width: 0;
            border: 1px solid rgba(255, 255, 255, .20);
            border-radius: 14px;
            padding: 14px;
            background: rgba(255, 255, 255, .13);
            backdrop-filter: blur(8px);
        }

        .instrument-stat span {
            display: block;
            color: rgba(255, 255, 255, .76);
            font-size: 12px;
            font-weight: 700;
        }

        .instrument-stat strong {
            display: block;
            margin-top: 4px;
            color: #ffffff;
            font-size: 30px;
            line-height: 1;
        }

        .instrument-workflow {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .workflow-step,
        .instrument-section {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--surface);
            box-shadow: 0 8px 22px rgba(14, 102, 86, .06);
        }

        .workflow-step {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            column-gap: 12px;
            align-items: center;
            padding: 16px;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .workflow-step:hover {
            transform: translateY(-2px);
            border-color: rgba(14, 102, 86, .28);
            box-shadow: 0 14px 30px rgba(14, 102, 86, .10);
        }

        .workflow-step span {
            display: inline-grid;
            place-items: center;
            width: 34px;
            height: 34px;
            grid-row: span 2;
            border-radius: 10px;
            background: var(--brand-soft);
            color: var(--brand-strong);
            font-weight: 900;
        }

        .workflow-step strong {
            min-width: 0;
            font-size: 15px;
        }

        .workflow-step p {
            min-width: 0;
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .instrument-section {
            margin-bottom: 18px;
            padding: 22px;
        }

        .instrument-section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .instrument-section-header h3 {
            margin: 0 0 4px;
            font-size: 19px;
        }

        .instrument-section-header p {
            margin: 0;
        }

        .instrument-filter-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr)) auto;
            gap: 14px;
            align-items: end;
        }

        .instrument-filter-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
        }

        .standard-manager {
            padding: 0;
        }

        .standard-manager summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 20px 22px;
            cursor: pointer;
            list-style: none;
        }

        .standard-manager summary::-webkit-details-marker {
            display: none;
        }

        .standard-manager summary span {
            display: grid;
            gap: 2px;
        }

        .standard-manager summary small {
            color: var(--muted);
            font-weight: 600;
        }

        .standard-manager-caret {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            border-radius: 999px;
            padding: 6px 12px;
            background: var(--brand-soft);
            color: var(--brand-strong);
            font-size: 13px;
            font-weight: 800;
        }

        .standard-manager[open] .standard-manager-caret {
            background: rgba(232, 179, 106, .18);
            color: var(--accent);
        }

        .standard-manager[open] .standard-manager-caret::before {
            content: "Tutup";
        }

        .standard-manager[open] .standard-manager-caret {
            font-size: 0;
        }

        .standard-manager[open] .standard-manager-caret::before {
            font-size: 13px;
        }

        .standard-manager-actions {
            display: flex;
            justify-content: flex-end;
            margin: 0 22px 14px;
        }

        .standard-manager > .table-wrap,
        .standard-manager > .warning {
            margin: 0 22px 22px;
        }

        .standard-manager > .table-wrap {
            width: calc(100% - 44px);
            max-width: calc(100% - 44px);
        }

        .standard-manager > .table-wrap table {
            min-width: 760px;
        }

        .instrument-table-wrap th {
            background: var(--brand);
            color: #ffffff;
        }

        .instrument-master-toolbar {
            display: block;
        }

        .instrument-master-filters {
            display: grid;
            grid-template-columns: minmax(210px, 1.1fr) minmax(132px, .72fr) minmax(120px, .62fr) auto auto;
            align-items: end;
            gap: 10px;
            flex: 1 1 auto;
            min-width: 0;
        }

        .instrument-master-filters .form-field {
            min-width: 0;
        }

        .instrument-master-filters select {
            min-height: 40px;
            padding: 9px 10px;
        }

        .instrument-master-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex: 0 0 auto;
            flex-wrap: wrap;
        }

        .instrument-master-actions > .button,
        .instrument-master-actions > button {
            min-height: 40px;
            padding-inline: 12px;
            white-space: nowrap;
        }

        .instrument-master-actions .excel-action {
            padding-inline: 11px;
            white-space: nowrap;
        }

        .instrument-master-actions .excel-action-icon {
            width: 22px;
            height: 22px;
        }

        .instrument-select-cell {
            width: 44px;
            text-align: center;
        }

        .instrument-select-cell input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            margin: 0;
            accent-color: var(--brand);
            cursor: pointer;
            opacity: .42;
            transition: none;
            vertical-align: middle;
        }

        .instrument-select-cell input[type="checkbox"]:hover {
            opacity: .42;
            outline: none;
        }

        .instrument-select-cell input[type="checkbox"]:checked,
        .instrument-select-cell input[type="checkbox"]:indeterminate {
            opacity: 1;
        }

        .instrument-select-cell input[type="checkbox"]:focus-visible {
            outline: 2px solid rgba(14, 102, 86, .28);
            outline-offset: 2px;
        }

        .instrument-select-cell input[type="checkbox"]:disabled {
            cursor: not-allowed;
            opacity: .18;
            filter: grayscale(.25);
        }

        .bulk-action-bar {
            position: sticky;
            top: 12px;
            z-index: 25;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
            width: fit-content;
            margin: 0 0 14px auto;
            padding: 8px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--surface-soft);
        }

        .bulk-action-bar:not([hidden]) {
            box-shadow: 0 10px 24px rgba(14, 102, 86, .10);
        }

        .standard-bulk-action-bar {
            margin: 0 22px 14px auto;
        }

        .bulk-action-bar[hidden] {
            display: none;
        }

        .bulk-action-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 36px;
            border-radius: 999px;
            padding: 6px 10px;
            background: var(--brand-soft);
            color: var(--brand-strong);
            font-size: 13px;
            font-weight: 800;
        }

        .bulk-action-count span {
            display: inline-grid;
            place-items: center;
            min-width: 22px;
            min-height: 22px;
            border-radius: 999px;
            background: var(--brand);
            color: #ffffff;
            font-size: 12px;
        }

        .bulk-deactivate-button,
        .bulk-delete-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 36px;
            padding: 8px 12px;
        }

        .bulk-deactivate-button {
            color: var(--brand-strong);
            border-color: rgba(14, 102, 86, .24);
        }

        .bulk-deactivate-button:hover {
            background: rgba(14, 102, 86, .08);
            color: var(--brand-strong);
        }

        .bulk-delete-button {
            color: var(--danger);
            border-color: rgba(180, 35, 24, .24);
        }

        .bulk-delete-button:hover {
            background: rgba(180, 35, 24, .08);
            color: var(--danger);
        }

        .bulk-deactivate-button:disabled,
        .bulk-delete-button:disabled {
            cursor: not-allowed;
            opacity: .52;
        }

        .instrument-empty {
            display: grid;
            gap: 4px;
            padding: 18px;
            color: var(--muted);
            text-align: center;
        }

        .instrument-empty strong {
            color: var(--text);
            font-size: 16px;
        }

        .instrument-import-box {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) minmax(260px, 2fr) auto;
            gap: 12px;
            align-items: end;
            padding: 16px;
            border: 1px dashed rgba(14, 102, 86, .30);
            border-radius: 14px;
            background: color-mix(in srgb, var(--brand-soft) 42%, transparent);
        }

        .instrument-import-box label {
            font-weight: 800;
        }

        .excel-action-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .excel-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 9px 13px;
            border: 1px solid rgba(14, 102, 86, .14);
            border-radius: 12px;
            background: #ffffff;
            color: var(--brand-strong);
            box-shadow: 0 2px 8px rgba(14, 102, 86, .06);
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
            cursor: pointer;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, background 160ms ease;
        }

        .excel-action:hover,
        .excel-action:focus-visible {
            transform: translateY(-1px);
            border-color: rgba(14, 102, 86, .24);
            background: var(--brand-tint);
            box-shadow: 0 8px 18px rgba(14, 102, 86, .12);
            outline: 0;
        }

        .excel-action-import {
            background: #eef7ff;
            border-color: rgba(23, 107, 135, .20);
            color: #176b87;
        }

        .excel-action-export {
            background: #fff7e8;
            border-color: rgba(217, 164, 65, .24);
            color: #b7791f;
        }

        .excel-action-import .excel-action-icon {
            background: #dff1fb;
            color: #176b87;
        }

        .excel-action-export .excel-action-icon {
            background: #ffe8bb;
            color: #b7791f;
        }

        .excel-action-icon {
            display: inline-grid;
            place-items: center;
            width: 24px;
            height: 24px;
            flex: 0 0 auto;
            border-radius: 8px;
            background: #e8f5ee;
            color: #217346;
        }

        .excel-action-icon svg {
            display: block;
            width: 17px;
            height: 17px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .template-modal[hidden] {
            display: none;
        }

        .import-modal[hidden] {
            display: none;
        }

        .template-modal,
        .import-modal {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .template-modal-backdrop,
        .import-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, .46);
            backdrop-filter: blur(3px);
        }

        .template-modal-card,
        .import-modal-card {
            position: relative;
            z-index: 1;
            width: min(860px, 100%);
            max-height: min(720px, calc(100vh - 48px));
            overflow: auto;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: var(--shadow-lg);
            padding: 24px;
        }

        .import-modal-card {
            width: min(520px, 100%);
        }

        .template-modal-header,
        .import-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 16px;
        }

        .template-modal-header .instrument-eyebrow,
        .import-modal-header .instrument-eyebrow {
            background: var(--brand-soft);
            color: var(--brand-strong);
        }

        .template-modal-header h3,
        .import-modal-header h3 {
            margin: 8px 0 4px;
            font-size: 24px;
        }

        .template-modal-header p,
        .import-modal-header p {
            margin: 0;
        }

        .import-modal-form {
            display: grid;
            gap: 14px;
        }

        .import-file-drop {
            display: grid;
            place-items: center;
            gap: 8px;
            min-height: 150px;
            padding: 20px;
            border: 1px dashed rgba(14, 102, 86, .32);
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(228, 242, 238, .72), rgba(255, 255, 255, .78));
            text-align: center;
            cursor: pointer;
            transition: border-color 160ms ease, background 160ms ease, transform 160ms ease;
        }

        .import-file-drop:hover {
            transform: translateY(-1px);
            border-color: rgba(14, 102, 86, .52);
            background: var(--brand-tint);
        }

        .import-file-drop.is-dragging {
            transform: translateY(-1px) scale(1.01);
            border-color: rgba(14, 102, 86, .72);
            background: linear-gradient(180deg, rgba(228, 242, 238, .95), rgba(255, 255, 255, .92));
            box-shadow: inset 0 0 0 2px rgba(14, 102, 86, .10), 0 10px 24px rgba(14, 102, 86, .12);
        }

        .import-file-drop strong {
            font-size: 15px;
        }

        .import-file-drop small {
            color: var(--muted);
            font-weight: 700;
        }

        .import-file-name {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            padding: 7px 10px;
            border-radius: 999px;
            background: #ffffff;
            color: var(--brand-strong);
            font-size: 12px;
            font-weight: 800;
            box-shadow: 0 2px 8px rgba(14, 102, 86, .08);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .import-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .template-modal-close {
            display: inline-grid;
            place-items: center;
            width: 40px;
            height: 40px;
            flex: 0 0 auto;
            border-radius: 12px;
            padding: 0;
            background: var(--surface-soft);
            border-color: var(--line);
            color: var(--text);
            font-size: 28px;
            line-height: 1;
        }

        .template-modal-close:hover {
            background: var(--brand-soft);
            color: var(--brand-strong);
        }

        .template-option-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .template-option {
            display: grid;
            gap: 6px;
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
            background:
                linear-gradient(135deg, rgba(228, 242, 238, .72), rgba(255, 255, 255, .92));
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .template-option:hover {
            transform: translateY(-2px);
            border-color: rgba(14, 102, 86, .32);
            box-shadow: 0 12px 28px rgba(14, 102, 86, .10);
        }

        .template-option span {
            width: fit-content;
            border-radius: 999px;
            padding: 4px 9px;
            background: var(--brand);
            color: #ffffff;
            font-size: 12px;
            font-weight: 900;
        }

        .template-option strong {
            color: var(--text);
            line-height: 1.25;
        }

        .template-option small {
            color: var(--muted);
        }

        .template-option-main {
            margin-bottom: 12px;
            background:
                linear-gradient(135deg, rgba(14, 102, 86, .10), rgba(232, 179, 106, .12)),
                var(--surface);
        }

        body.modal-open {
            overflow: hidden;
        }

        .panel-title {
            margin: 0 0 6px;
            font-size: 18px;
        }

        .muted {
            color: var(--muted);
        }

        .button,
        button {
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 10px 14px;
            background: var(--brand);
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            line-height: 1.2;
        }

        .button:hover,
        button:hover {
            background: var(--brand-strong);
        }

        .button.secondary {
            background: #ffffff;
            color: var(--brand-strong);
            border-color: var(--line);
        }

        .button.secondary:hover {
            background: #f1f5f9;
        }

        .button.with-icon,
        button.with-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 41px;
            box-sizing: border-box;
            padding: 10px 14px;
            white-space: nowrap;
        }

        .button-icon {
            display: block;
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .button-template {
            background: #e8f5ee;
            border-color: rgba(14, 102, 86, .16);
            color: var(--brand-strong);
        }

        .button-template:hover {
            background: var(--brand-tint);
            color: var(--brand-strong);
        }

        .button-reset {
            background: #f8fafc;
            border-color: rgba(100, 116, 139, .20);
            color: #475467;
        }

        .button-reset:hover {
            background: #eef2f7;
            color: #344054;
        }

        .logout-button {
            width: 100%;
            background: transparent;
            color: #edf6f9;
            border-color: rgba(255, 255, 255, .24);
        }

        .logout-button:hover {
            background: rgba(255, 255, 255, .1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .sectioned-form {
            display: grid;
            gap: 18px;
        }

        .form-section {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(228, 242, 238, .34), rgba(255, 255, 255, .72));
        }

        .form-section-title {
            grid-column: 1 / -1;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .form-section-title span {
            display: inline-grid;
            place-items: center;
            width: 30px;
            height: 30px;
            flex: 0 0 auto;
            border-radius: 10px;
            background: var(--brand);
            color: #ffffff;
            font-size: 13px;
            font-weight: 900;
        }

        .form-section-title h3 {
            margin: 0 0 3px;
            font-size: 16px;
        }

        .form-section-title p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .form-actions-sticky {
            position: sticky;
            bottom: 12px;
            z-index: 18;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 12px 30px rgba(14, 102, 86, .12);
            backdrop-filter: blur(10px);
        }

        .form-field {
            display: grid;
            gap: 6px;
        }

        .form-field.full {
            grid-column: 1 / -1;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 14px;
        }

        label {
            font-size: 14px;
            font-weight: 700;
        }

        input,
        textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 11px 12px;
            color: var(--text);
            font: inherit;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: 3px solid rgba(23, 107, 135, .18);
            border-color: var(--brand);
        }

        .error {
            color: var(--danger);
            font-size: 13px;
        }

        .status {
            margin-bottom: 16px;
            border-left: 4px solid var(--brand);
            background: #edf7fa;
            padding: 10px 12px;
            color: var(--brand-strong);
        }

        .warning {
            margin-bottom: 16px;
            border-left: 4px solid var(--accent);
            background: #fff7ed;
            padding: 10px 12px;
            color: #9a3412;
        }

        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--line);
        }

        .tab-link {
            display: inline-flex;
            align-items: center;
            min-height: 42px;
            padding: 10px 14px;
            border: 1px solid transparent;
            border-bottom: 0;
            border-radius: 8px 8px 0 0;
            color: var(--muted);
            font-weight: 700;
        }

        .tab-link.active {
            background: var(--surface);
            border-color: var(--line);
            color: var(--brand-strong);
            margin-bottom: -1px;
        }

        .toolbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .filters {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filters .form-field {
            min-width: 170px;
        }

        .filters .form-field[hidden] {
            display: none !important;
        }

        .filter-toggle {
            min-height: 41px;
            padding-inline: 12px;
        }

        .filter-toggle::after {
            content: "";
            display: inline-block;
            width: 7px;
            height: 7px;
            margin-left: 8px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: translateY(-2px) rotate(45deg);
            transition: transform 160ms ease;
        }

        .filter-toggle[aria-expanded="true"]::after {
            transform: translateY(2px) rotate(225deg);
        }

        select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 11px 12px;
            color: var(--text);
            background: #ffffff;
            font: inherit;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 860px;
            background: #ffffff;
        }

        th,
        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            color: #475467;
            font-size: 13px;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            border-radius: 999px;
            padding: 3px 9px;
            background: #e7f6ee;
            color: #116239;
            font-size: 12px;
            font-weight: 700;
        }

        .badge.off {
            background: #f2f4f7;
            color: #667085;
        }

        .badge.success {
            background: #e7f6ee;
            color: #116239;
        }

        .badge.warning {
            background: #fff7ed;
            color: #9a3412;
        }

        .badge.danger {
            background: #fee4e2;
            color: var(--danger);
        }

        .badge.neutral {
            background: #f2f4f7;
            color: #667085;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .inline-form {
            display: inline-flex;
        }

        .table-actions {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
        }

        .action-icon {
            position: relative;
            display: inline-grid;
            place-items: center;
            width: 34px;
            height: 34px;
            padding: 0;
            border: 1px solid rgba(14, 102, 86, 0.12);
            border-radius: 10px;
            background: #fff;
            color: var(--muted);
            box-shadow: 0 2px 7px rgba(14, 102, 86, 0.06);
            cursor: pointer;
            text-decoration: none;
            line-height: 1;
            vertical-align: middle;
            appearance: none;
            -webkit-appearance: none;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, background 160ms ease, color 160ms ease;
        }

        .action-icon:hover,
        .action-icon:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 7px 16px rgba(14, 102, 86, 0.12);
            outline: 0;
        }

        .action-icon svg {
            display: block;
            width: 17px;
            height: 17px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .action-icon.tone-view {
            background: var(--brand-tint);
            border-color: rgba(14, 102, 86, 0.16);
            color: var(--brand-strong);
        }

        .action-icon.tone-edit {
            background: #eef7ff;
            border-color: rgba(23, 107, 135, 0.18);
            color: #176b87;
        }

        .action-icon.tone-success {
            background: #ecfdf5;
            border-color: rgba(59, 158, 124, 0.18);
            color: var(--success);
        }

        .action-icon.tone-warning {
            background: #fff7e8;
            border-color: rgba(217, 164, 65, 0.22);
            color: #b7791f;
        }

        .action-icon.tone-danger {
            background: #fff0ef;
            border-color: rgba(199, 100, 90, 0.22);
            color: var(--danger);
        }

        .action-icon.tone-neutral {
            background: #f7faf8;
            border-color: rgba(107, 123, 118, 0.18);
            color: var(--muted);
        }

        .action-tooltip {
            position: absolute;
            z-index: 30;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translate(-50%, 4px);
            padding: 6px 9px;
            border-radius: 8px;
            background: #1f2c29;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            box-shadow: 0 8px 18px rgba(31, 44, 41, 0.18);
            transition: opacity 140ms ease, transform 140ms ease;
        }

        .action-tooltip::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            width: 8px;
            height: 8px;
            background: #1f2c29;
            transform: translate(-50%, -4px) rotate(45deg);
        }

        .action-icon:hover .action-tooltip,
        .action-icon:focus-visible .action-tooltip {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        .link-button {
            border: 0;
            padding: 0;
            background: transparent;
            color: var(--brand-strong);
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .link-button:hover {
            background: transparent;
            text-decoration: underline;
        }

        .danger-link {
            color: var(--danger);
        }

        .import-box {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
        }

        .pagination {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .pagination nav {
            width: 100%;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .summary-item {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px;
            background: #fbfcfe;
        }

        .summary-label {
            color: var(--muted);
            font-size: 13px;
        }

        .summary-value {
            margin-top: 4px;
            font-size: 18px;
            font-weight: 700;
        }

        .stat-card {
            display: block;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px;
            background: #fbfcfe;
        }

        .stat-card:hover {
            border-color: var(--brand);
            background: #f5fbfd;
        }

        .stat-card.success {
            border-left: 4px solid #16a34a;
        }

        .stat-card.warning {
            border-left: 4px solid var(--accent);
        }

        .stat-card.danger {
            border-left: 4px solid var(--danger);
        }

        .stat-card.neutral {
            border-left: 4px solid #98a2b3;
        }

        .stat-value {
            margin-top: 8px;
            font-size: 28px;
            font-weight: 700;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: stretch;
            gap: 18px;
        }

        .dashboard-grid > .panel {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .dashboard-grid > .panel + .panel {
            margin-top: 0;
        }

        .panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
            text-align: left;
        }

        .panel-header .panel-title {
            margin-bottom: 6px;
        }

        .panel-header .muted {
            margin: 6px;
        }

        .dashboard-list {
            display: grid;
            gap: 12px;
        }

        .list-item {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px;
            background: #ffffff;
        }

        .conditional-field[hidden] {
            display: none;
        }

        .progress {
            width: 100%;
            height: 10px;
            overflow: hidden;
            border-radius: 999px;
            background: #e5e7eb;
        }

        .progress-bar {
            height: 100%;
            background: var(--brand);
        }

        .split-panel {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 18px;
        }

        .section-block {
            padding: 18px 0;
            border-top: 1px solid var(--line);
        }

        .section-block:first-child {
            border-top: 0;
            padding-top: 0;
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            background: #132635;
            border-right: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 16px 0 44px rgba(15, 23, 42, .12);
        }

        .brand {
            padding-bottom: 20px;
        }

        .brand-title {
            font-size: 26px;
            line-height: 1;
        }

        .brand-subtitle {
            max-width: 220px;
            line-height: 1.35;
        }

        .nav-list {
            gap: 7px;
        }

        .nav-link {
            position: relative;
            border: 1px solid transparent;
            padding: 11px 12px 11px 14px;
            transition: background .16s ease, border-color .16s ease, color .16s ease, transform .16s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, .08);
            border-color: rgba(255, 255, 255, .08);
            transform: translateX(2px);
        }

        .nav-link.active {
            background: #1f6f8b;
            border-color: rgba(255, 255, 255, .18);
            box-shadow: inset 3px 0 0 #f2c94c;
        }

        .sidebar-footer {
            border-top-color: rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .04);
            border-radius: 8px;
            padding: 14px;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 12;
            min-height: 76px;
            background: rgba(255, 255, 255, .96);
            box-shadow: var(--shadow-sm);
        }

        .page-title {
            font-size: 25px;
            font-weight: 800;
            color: #101828;
        }

        .content {
            padding: 30px;
        }

        .panel {
            border-color: rgba(196, 208, 218, .92);
            box-shadow: var(--shadow-sm);
        }

        .panel:hover {
            box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        }

        .panel-title {
            color: #101828;
            font-weight: 800;
        }

        .button,
        button {
            box-shadow: 0 1px 2px rgba(15, 23, 42, .08);
            transition: background .16s ease, border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }

        .button:hover,
        button:hover {
            box-shadow: 0 8px 18px rgba(23, 107, 135, .18);
            transform: translateY(-1px);
        }

        .button.secondary {
            background: var(--surface-soft);
        }

        .button.secondary:hover {
            border-color: var(--line-strong);
            color: var(--brand-strong);
        }

        .logout-button {
            box-shadow: none;
        }

        input,
        textarea,
        select {
            background: #ffffff;
            border-color: var(--line-strong);
            box-shadow: inset 0 1px 1px rgba(15, 23, 42, .03);
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: 3px solid rgba(23, 107, 135, .16);
            border-color: var(--brand);
            box-shadow: 0 0 0 1px rgba(23, 107, 135, .08);
        }

        input[type="checkbox"],
        input[type="radio"] {
            width: auto;
            accent-color: var(--brand);
            box-shadow: none;
        }

        input[type="file"] {
            background: var(--surface-soft);
        }

        .status,
        .warning {
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
        }

        .tabs {
            gap: 4px;
            overflow-x: auto;
            padding: 4px 4px 0;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        .tab-link {
            border-radius: 8px 8px 0 0;
            white-space: nowrap;
        }

        .tab-link.active {
            box-shadow: inset 0 3px 0 var(--brand);
        }

        .table-wrap {
            border-color: var(--line);
            box-shadow: var(--shadow-sm);
        }

        table {
            background: #ffffff;
        }

        th {
            background: #f3f7fa;
            color: #344054;
            font-weight: 800;
        }

        tbody tr {
            transition: background .14s ease;
        }

        tbody tr:hover {
            background: #f8fbfc;
        }

        .badge {
            border: 1px solid rgba(17, 98, 57, .12);
            line-height: 1.2;
        }

        .badge.warning {
            border-color: rgba(154, 52, 18, .14);
        }

        .badge.danger {
            border-color: rgba(180, 35, 24, .14);
        }

        .badge.neutral,
        .badge.off {
            border-color: rgba(102, 112, 133, .14);
        }

        .link-button {
            border-radius: 6px;
            padding: 2px 0;
            box-shadow: none;
        }

        .link-button:hover {
            box-shadow: none;
            transform: none;
        }

        .summary-item,
        .stat-card,
        .list-item {
            border-color: rgba(196, 208, 218, .92);
            box-shadow: var(--shadow-sm);
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }

        .stat-card:hover,
        .list-item:hover {
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
            transform: translateY(-1px);
        }

        .stat-value {
            color: #101828;
            line-height: 1.1;
        }

        .progress {
            height: 12px;
            background: #e4ebf0;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, .08);
        }

        .progress-bar {
            background: var(--brand);
        }

        .notification-dropdown {
            box-shadow: var(--shadow-lg);
        }

        /* Playful SIAMI redesign */
        :root {
            --bg: #f8fafc;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --line: #e2e8f0;
            --line-strong: #cbd5e1;
            --text: #1e293b;
            --muted: #64748b;
            --brand: #6366f1;
            --brand-strong: #4f46e5;
            --brand-soft: #eef2ff;
            --brand-purple: #a855f7;
            --secondary: #14b8a6;
            --accent: #fb7185;
            --highlight: #fbbf24;
            --danger: #f43f5e;
            --success: #10b981;
            --warning-color: #f59e0b;
            --neutral: #94a3b8;
            --shadow-sm: 0 4px 12px rgba(99, 102, 241, .08);
            --shadow-md: 0 14px 34px rgba(99, 102, 241, .13);
            --shadow-lg: 0 24px 54px rgba(99, 102, 241, .18);
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(99, 102, 241, .12), transparent 34vw),
                radial-gradient(circle at top right, rgba(20, 184, 166, .12), transparent 32vw),
                var(--bg);
            color: var(--text);
            font-family: Inter, Poppins, "Segoe UI", Arial, Helvetica, sans-serif;
        }

        .app-shell {
            grid-template-columns: 292px minmax(0, 1fr);
        }

        .sidebar {
            background: linear-gradient(180deg, #3730a3 0%, #4f46e5 48%, #7e22ce 100%);
            border-right: 0;
            box-shadow: 18px 0 42px rgba(79, 70, 229, .22);
        }

        .brand {
            position: relative;
            border-bottom-color: rgba(255, 255, 255, .2);
        }

        .brand::before {
            content: "AMI";
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            margin-bottom: 14px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .18);
            color: #ffffff;
            font-weight: 900;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .22);
        }

        .brand-title {
            color: #ffffff;
            font-family: Poppins, Inter, "Segoe UI", sans-serif;
            font-weight: 900;
        }

        .brand-subtitle,
        .user-meta {
            color: rgba(255, 255, 255, .76);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            border-radius: 14px;
            color: rgba(255, 255, 255, .88);
            font-weight: 750;
        }

        .nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 32px;
            height: 32px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .14);
            color: #ffffff;
        }

        .nav-icon svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, .14);
            border-color: rgba(255, 255, 255, .18);
            transform: translateX(3px) scale(1.01);
        }

        .nav-link.active {
            background: #ffffff;
            color: #4338ca;
            border-color: rgba(255, 255, 255, .72);
            box-shadow: 0 10px 24px rgba(30, 41, 59, .16);
        }

        .nav-link.active .nav-icon {
            background: linear-gradient(135deg, var(--brand), var(--brand-purple));
            color: #ffffff;
        }

        .sidebar-footer {
            background: rgba(255, 255, 255, .14);
            border-radius: 16px;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .16);
        }

        .topbar {
            background: linear-gradient(135deg, var(--brand), var(--brand-purple));
            border-bottom: 0;
            box-shadow: var(--shadow-md);
        }

        .page-title {
            color: #ffffff;
            font-family: Poppins, Inter, "Segoe UI", sans-serif;
            font-size: 26px;
        }

        .topbar .button.secondary,
        .notification-menu summary {
            background: rgba(255, 255, 255, .18);
            border-color: rgba(255, 255, 255, .24);
            color: #ffffff;
            box-shadow: none;
        }

        .topbar .button.secondary:hover,
        .notification-menu summary:hover {
            background: rgba(255, 255, 255, .28);
            color: #ffffff;
        }

        .notification-badge {
            background: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .18);
        }

        .content {
            animation: pageFade .22s ease-out;
        }

        .panel,
        .summary-item,
        .stat-card,
        .list-item,
        .table-wrap {
            border-color: rgba(148, 163, 184, .22);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
        }

        .panel {
            background: rgba(255, 255, 255, .94);
        }

        .panel:hover,
        .stat-card:hover,
        .list-item:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px) scale(1.01);
        }

        .panel,
        .stat-card,
        .list-item {
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
        }

        .panel-title {
            color: var(--text);
            font-family: Poppins, Inter, "Segoe UI", sans-serif;
        }

        .button,
        button {
            border-radius: 14px;
            background: linear-gradient(135deg, var(--brand), var(--brand-purple));
            box-shadow: 0 8px 18px rgba(99, 102, 241, .22);
        }

        .button:hover,
        button:hover {
            background: linear-gradient(135deg, #4f46e5, #9333ea);
            box-shadow: 0 12px 26px rgba(99, 102, 241, .28);
            transform: translateY(-1px) scale(1.02);
        }

        .button.secondary {
            background: #ffffff;
            border-color: rgba(99, 102, 241, .24);
            color: var(--brand-strong);
        }

        .button.secondary:hover {
            background: var(--brand-soft);
            color: var(--brand-strong);
        }

        .logout-button {
            background: rgba(255, 255, 255, .14);
            border-color: rgba(255, 255, 255, .22);
            border-radius: 14px;
        }

        input,
        textarea,
        select {
            border-radius: 14px;
            border-color: #dbe4ef;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline-color: rgba(99, 102, 241, .18);
            border-color: var(--brand);
            box-shadow: 0 0 0 1px rgba(99, 102, 241, .08);
        }

        input[type="checkbox"],
        input[type="radio"] {
            accent-color: var(--brand);
        }

        .status {
            border-left-color: var(--secondary);
            background: #ecfdf5;
            color: #047857;
        }

        .warning {
            border-left-color: var(--warning-color);
            background: #fffbeb;
            color: #92400e;
        }

        .tabs {
            border-radius: 16px;
            background: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .tab-link {
            border-radius: 14px 14px 0 0;
        }

        .tab-link.active {
            color: var(--brand-strong);
            box-shadow: inset 0 3px 0 var(--accent);
        }

        th {
            background: #f1f5ff;
            color: var(--text);
        }

        tbody tr:hover {
            background: #fbfdff;
        }

        .badge,
        .badge.success {
            background: #d1fae5;
            color: #047857;
            border-color: rgba(16, 185, 129, .18);
        }

        .badge.warning {
            background: #fef3c7;
            color: #92400e;
            border-color: rgba(245, 158, 11, .2);
        }

        .badge.danger {
            background: #ffe4e9;
            color: #be123c;
            border-color: rgba(244, 63, 94, .2);
        }

        .badge.neutral,
        .badge.off {
            background: #f1f5f9;
            color: #475569;
            border-color: rgba(148, 163, 184, .24);
        }

        .link-button {
            color: var(--brand-strong);
        }

        .danger-link {
            color: var(--danger);
        }

        .summary-grid {
            gap: 16px;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            border: 0;
            background: linear-gradient(135deg, #eef2ff, #ffffff);
        }

        .stat-card::after {
            content: "";
            position: absolute;
            right: -22px;
            top: -22px;
            width: 86px;
            height: 86px;
            border-radius: 999px;
            background: rgba(99, 102, 241, .16);
        }

        .stat-card:nth-child(4n+1),
        .stat-card.success {
            background: linear-gradient(135deg, #ecfdf5, #ffffff);
        }

        .stat-card:nth-child(4n+2),
        .stat-card.warning {
            background: linear-gradient(135deg, #fffbeb, #ffffff);
        }

        .stat-card:nth-child(4n+3),
        .stat-card.danger {
            background: linear-gradient(135deg, #fff1f2, #ffffff);
        }

        .stat-card:nth-child(4n+4),
        .stat-card.neutral {
            background: linear-gradient(135deg, #f0fdfa, #ffffff);
        }

        .summary-label {
            color: var(--muted);
            font-weight: 700;
        }

        .stat-value {
            color: var(--text);
            font-size: 30px;
            font-family: Poppins, Inter, "Segoe UI", sans-serif;
        }

        .progress {
            height: 13px;
            background: #e2e8f0;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--secondary), var(--brand), var(--brand-purple));
            background-size: 180% 100%;
            animation: progressGlow 1.5s ease-in-out infinite alternate;
        }

        .dashboard-grid > .panel:nth-child(4n+1) {
            border-top: 5px solid var(--brand);
        }

        .dashboard-grid > .panel:nth-child(4n+2) {
            border-top: 5px solid var(--secondary);
        }

        .dashboard-grid > .panel:nth-child(4n+3) {
            border-top: 5px solid var(--accent);
        }

        .dashboard-grid > .panel:nth-child(4n+4) {
            border-top: 5px solid var(--highlight);
        }

        @keyframes pageFade {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes progressGlow {
            from {
                background-position: 0% 50%;
            }
            to {
                background-position: 100% 50%;
            }
        }

        /* JDS Company futuristic teal identity */
        :root {
            --bg: #0b1f1b;
            --surface: rgba(18, 46, 40, .82);
            --surface-soft: rgba(24, 63, 55, .72);
            --line: rgba(34, 211, 238, .16);
            --line-strong: rgba(45, 212, 191, .28);
            --text: #e2f5f0;
            --muted: #9cc9bf;
            --brand: #0e6656;
            --brand-strong: #14967d;
            --brand-soft: rgba(45, 212, 191, .12);
            --brand-purple: #22d3ee;
            --secondary: #2dd4bf;
            --accent: #22d3ee;
            --highlight: #f59e0b;
            --danger: #ef4444;
            --success: #10b981;
            --warning-color: #f59e0b;
            --neutral: #64748b;
            --shadow-sm: 0 4px 12px rgba(14, 102, 86, .20);
            --shadow-md: 0 16px 38px rgba(0, 0, 0, .26), 0 0 24px rgba(34, 211, 238, .08);
            --shadow-lg: 0 26px 64px rgba(0, 0, 0, .36), 0 0 44px rgba(34, 211, 238, .13);
        }

        body {
            background:
                linear-gradient(rgba(34, 211, 238, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 211, 238, .035) 1px, transparent 1px),
                radial-gradient(circle at 16% 12%, rgba(45, 212, 191, .18), transparent 32vw),
                radial-gradient(circle at 84% 10%, rgba(34, 211, 238, .14), transparent 28vw),
                radial-gradient(circle at 70% 84%, rgba(20, 150, 125, .18), transparent 34vw),
                var(--bg);
            background-size: 42px 42px, 42px 42px, auto, auto, auto, auto;
            color: var(--text);
            font-family: Inter, "Segoe UI", Arial, Helvetica, sans-serif;
        }

        .sidebar {
            background:
                linear-gradient(rgba(34, 211, 238, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 211, 238, .035) 1px, transparent 1px),
                linear-gradient(180deg, #0a4a3f 0%, #0b1f1b 100%);
            background-size: 34px 34px, 34px 34px, auto;
            box-shadow: 18px 0 42px rgba(0, 0, 0, .34), inset -1px 0 0 rgba(34, 211, 238, .13);
        }

        .brand {
            border-bottom: 1px solid rgba(45, 212, 191, .18);
        }

        .brand::before {
            background: linear-gradient(135deg, rgba(45, 212, 191, .24), rgba(34, 211, 238, .14));
            color: #dffcf7;
            box-shadow: inset 0 0 0 1px rgba(34, 211, 238, .32), 0 0 24px rgba(34, 211, 238, .18);
        }

        .brand-title,
        .page-title,
        .panel-title,
        .stat-value {
            font-family: "Space Grotesk", Sora, Inter, "Segoe UI", sans-serif;
            letter-spacing: .01em;
        }

        .brand-title,
        .user-name {
            color: #f0fffb;
        }

        .brand-subtitle,
        .user-meta {
            color: rgba(226, 245, 240, .72);
        }

        .nav-link {
            color: rgba(226, 245, 240, .74);
            border-radius: 14px;
        }

        .nav-icon {
            background: rgba(226, 245, 240, .07);
            color: rgba(226, 245, 240, .72);
            box-shadow: inset 0 0 0 1px rgba(226, 245, 240, .08);
        }

        .nav-link:hover {
            background: rgba(45, 212, 191, .10);
            border-color: rgba(34, 211, 238, .18);
            box-shadow: 0 0 22px rgba(34, 211, 238, .08);
        }

        .nav-link.active {
            background: rgba(18, 46, 40, .82);
            color: #ecfffb;
            border-color: rgba(34, 211, 238, .28);
            box-shadow: inset 4px 0 0 #22d3ee, 0 0 26px rgba(34, 211, 238, .16);
        }

        .nav-link.active .nav-icon {
            background: rgba(34, 211, 238, .14);
            color: #67e8f9;
            box-shadow: inset 0 0 0 1px rgba(34, 211, 238, .28), 0 0 20px rgba(34, 211, 238, .20);
        }

        .sidebar-footer {
            background: rgba(18, 46, 40, .68);
            border: 1px solid rgba(34, 211, 238, .12);
            backdrop-filter: blur(12px);
        }

        .topbar {
            position: sticky;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(10, 74, 63, .96), rgba(14, 102, 86, .94), rgba(20, 150, 125, .94));
            border-bottom: 1px solid rgba(34, 211, 238, .20);
        }

        .topbar::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(34, 211, 238, .06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 211, 238, .05) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: linear-gradient(90deg, transparent, #000 14%, #000 86%, transparent);
        }

        .page-title,
        .topbar-actions {
            position: relative;
            z-index: 1;
        }

        .topbar .button.secondary,
        .notification-menu summary {
            background: rgba(226, 245, 240, .08);
            border-color: rgba(34, 211, 238, .22);
            color: #e2f5f0;
            backdrop-filter: blur(12px);
        }

        .topbar .button.secondary:hover,
        .notification-menu summary:hover {
            background: rgba(34, 211, 238, .12);
            border-color: rgba(34, 211, 238, .38);
            box-shadow: 0 0 24px rgba(34, 211, 238, .18);
        }

        .notification-dropdown {
            background: rgba(18, 46, 40, .94);
            border-color: rgba(34, 211, 238, .24);
            backdrop-filter: blur(12px);
        }

        .panel,
        .summary-item,
        .stat-card,
        .list-item,
        .table-wrap {
            background: var(--surface);
            border: 1px solid rgba(34, 211, 238, .16);
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(12px);
        }

        .panel:hover,
        .stat-card:hover,
        .list-item:hover {
            border-color: rgba(34, 211, 238, .38);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px) scale(1.02);
        }

        .panel-title,
        .stat-value,
        th {
            color: #e2f5f0;
        }

        .muted,
        .summary-label {
            color: var(--muted);
        }

        .button,
        button {
            background: linear-gradient(135deg, #0e6656, #14967d, #22d3ee);
            color: #ecfffb;
            border-color: rgba(34, 211, 238, .24);
            box-shadow: 0 0 0 1px rgba(34, 211, 238, .10), 0 10px 24px rgba(34, 211, 238, .14);
        }

        .button:hover,
        button:hover {
            background: linear-gradient(135deg, #14967d, #2dd4bf, #22d3ee);
            box-shadow: 0 0 0 1px rgba(34, 211, 238, .22), 0 14px 32px rgba(34, 211, 238, .30);
            transform: translateY(-1px) scale(1.02);
        }

        .button.secondary {
            background: rgba(226, 245, 240, .06);
            border-color: rgba(34, 211, 238, .18);
            color: #baf7ef;
        }

        .button.secondary:hover {
            background: rgba(34, 211, 238, .10);
            color: #ecfffb;
        }

        .logout-button {
            background: rgba(226, 245, 240, .06);
            color: #e2f5f0;
            border-color: rgba(34, 211, 238, .18);
        }

        input,
        textarea,
        select {
            background: rgba(11, 31, 27, .74);
            border-color: rgba(34, 211, 238, .18);
            color: #e2f5f0;
        }

        input::placeholder,
        textarea::placeholder {
            color: rgba(226, 245, 240, .42);
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline-color: rgba(34, 211, 238, .20);
            border-color: #22d3ee;
            box-shadow: 0 0 0 1px rgba(34, 211, 238, .16), 0 0 20px rgba(34, 211, 238, .10);
        }

        option {
            background: #122e28;
            color: #e2f5f0;
        }

        input[type="file"] {
            background: rgba(18, 46, 40, .72);
        }

        input[type="checkbox"],
        input[type="radio"] {
            accent-color: #22d3ee;
        }

        .status {
            background: rgba(16, 185, 129, .11);
            border-left-color: #10b981;
            color: #bbf7d0;
        }

        .warning {
            background: rgba(245, 158, 11, .11);
            border-left-color: #f59e0b;
            color: #fde68a;
        }

        .error {
            color: #fecdd3;
        }

        .tabs {
            background: rgba(18, 46, 40, .72);
            border-color: rgba(34, 211, 238, .16);
            backdrop-filter: blur(12px);
        }

        .tab-link {
            color: rgba(226, 245, 240, .68);
        }

        .tab-link.active {
            background: rgba(34, 211, 238, .09);
            border-color: rgba(34, 211, 238, .22);
            color: #67e8f9;
            box-shadow: inset 0 3px 0 #22d3ee, 0 0 18px rgba(34, 211, 238, .10);
        }

        table {
            background: transparent;
        }

        th {
            background: rgba(34, 211, 238, .07);
        }

        td,
        th {
            border-bottom-color: rgba(34, 211, 238, .12);
        }

        tbody tr:hover {
            background: rgba(34, 211, 238, .045);
        }

        tr[style*="background:#fff1f0"] {
            background: rgba(239, 68, 68, .12) !important;
        }

        .badge,
        .badge.success {
            background: rgba(16, 185, 129, .14);
            color: #86efac;
            border-color: rgba(16, 185, 129, .24);
        }

        .badge.warning {
            background: rgba(245, 158, 11, .14);
            color: #fcd34d;
            border-color: rgba(245, 158, 11, .24);
        }

        .badge.danger {
            background: rgba(239, 68, 68, .14);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, .24);
        }

        .badge.neutral,
        .badge.off {
            background: rgba(100, 116, 139, .16);
            color: #cbd5e1;
            border-color: rgba(100, 116, 139, .24);
        }

        .link-button {
            color: #67e8f9;
        }

        .danger-link {
            color: #fca5a5;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(18, 46, 40, .88), rgba(14, 102, 86, .22));
            border: 1px solid rgba(34, 211, 238, .16);
        }

        .stat-card::after {
            background: radial-gradient(circle, rgba(34, 211, 238, .24), transparent 68%);
        }

        .stat-card:nth-child(4n+1),
        .stat-card.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, .18), rgba(18, 46, 40, .88));
        }

        .stat-card:nth-child(4n+2),
        .stat-card.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, .18), rgba(18, 46, 40, .88));
        }

        .stat-card:nth-child(4n+3),
        .stat-card.danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, .18), rgba(18, 46, 40, .88));
        }

        .stat-card:nth-child(4n+4),
        .stat-card.neutral {
            background: linear-gradient(135deg, rgba(34, 211, 238, .18), rgba(18, 46, 40, .88));
        }

        .progress {
            background: rgba(100, 116, 139, .24);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, .22);
        }

        .progress-bar {
            background: linear-gradient(90deg, #0e6656, #2dd4bf, #22d3ee);
            box-shadow: 0 0 16px rgba(34, 211, 238, .24);
        }

        .dashboard-grid > .panel:nth-child(4n+1) {
            border-top-color: #22d3ee;
        }

        .dashboard-grid > .panel:nth-child(4n+2) {
            border-top-color: #2dd4bf;
        }

        .dashboard-grid > .panel:nth-child(4n+3) {
            border-top-color: #10b981;
        }

        .dashboard-grid > .panel:nth-child(4n+4) {
            border-top-color: #f59e0b;
        }

        /* JDS calm professional aesthetic */
        :root {
            --bg: #fafaf8;
            --surface: #ffffff;
            --surface-soft: #f4f8f6;
            --line: #e5e7e0;
            --line-strong: #d8ddd4;
            --text: #1f2c29;
            --muted: #6b7b76;
            --brand: #0e6656;
            --brand-strong: #0a4a3f;
            --brand-soft: #e4f2ee;
            --brand-purple: #3d9c87;
            --secondary: #3d9c87;
            --accent: #e8b36a;
            --highlight: #e8b36a;
            --danger: #c7645a;
            --success: #3b9e7c;
            --warning-color: #d9a441;
            --neutral: #a3b0ac;
            --shadow-sm: 0 2px 8px rgba(14, 102, 86, .06);
            --shadow-md: 0 8px 22px rgba(14, 102, 86, .10);
            --shadow-lg: 0 18px 42px rgba(14, 102, 86, .14);
        }

        body {
            background:
                linear-gradient(135deg, rgba(228, 242, 238, .70), transparent 34vw),
                linear-gradient(315deg, rgba(232, 179, 106, .10), transparent 30vw),
                var(--bg);
            background-size: auto;
            color: var(--text);
            font-family: Inter, Manrope, "Segoe UI", Arial, Helvetica, sans-serif;
        }

        .sidebar {
            background: #0e6656;
            background-image: linear-gradient(180deg, #0e6656 0%, #0a4a3f 100%);
            box-shadow: 10px 0 28px rgba(14, 102, 86, .14);
            border-right: 0;
        }

        .brand {
            border-bottom-color: rgba(255, 255, 255, .18);
        }

        .brand::before {
            background: rgba(255, 255, 255, .12);
            color: #ffffff;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18);
        }

        .brand-title,
        .page-title,
        .panel-title,
        .stat-value {
            font-family: "Plus Jakarta Sans", Manrope, Inter, "Segoe UI", sans-serif;
            letter-spacing: 0;
        }

        .brand-title,
        .user-name {
            color: #ffffff;
        }

        .brand-subtitle,
        .user-meta {
            color: rgba(255, 255, 255, .74);
        }

        .nav-link {
            color: rgba(255, 255, 255, .82);
            border-radius: 12px;
            font-weight: 650;
        }

        .nav-list {
            gap: 12px;
        }

        .nav-group {
            --group-accent: rgba(232, 179, 106, .88);
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 7px;
            min-height: 190px;
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 14px;
            padding: 10px;
            background: rgba(255, 255, 255, .045);
        }

        .nav-group.tone-setup {
            --group-accent: #9ed9c9;
        }

        .nav-group.tone-process {
            --group-accent: #e8b36a;
        }

        .nav-group.tone-finding {
            --group-accent: #e8827a;
        }

        .nav-group.tone-report {
            --group-accent: #b7d8cf;
        }

        .nav-group-title {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 22px;
            color: rgba(255, 255, 255, .62);
            font-size: 11px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .nav-group-title span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 20px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            color: rgba(255, 255, 255, .78);
            font-size: 10px;
        }

        .nav-group-title strong {
            color: rgba(255, 255, 255, .68);
            letter-spacing: .02em;
        }

        .nav-group-items {
            display: grid;
            align-content: start;
            gap: 5px;
        }

        .nav-icon {
            background: rgba(255, 255, 255, .10);
            color: rgba(255, 255, 255, .82);
            box-shadow: none;
        }

        .nav-step {
            display: none;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .10);
            color: rgba(255, 255, 255, .78);
            font-size: 11px;
            font-weight: 850;
        }

        .nav-label {
            min-width: 0;
            line-height: 1.25;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, .12);
            border-color: rgba(255, 255, 255, .14);
            box-shadow: none;
            transform: translateX(2px);
        }

        .nav-link.active {
            background: #ffffff;
            color: var(--brand-strong);
            border-color: rgba(255, 255, 255, .6);
            box-shadow: inset 4px 0 0 var(--accent), 0 8px 18px rgba(10, 74, 63, .18);
        }

        .nav-link.active .nav-step {
            background: var(--accent);
            color: #4b3511;
        }

        .nav-link.active .nav-icon {
            background: var(--brand-soft);
            color: var(--brand);
            box-shadow: none;
        }

        .sidebar-footer {
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .14);
            backdrop-filter: none;
        }

        .topbar {
            overflow: visible;
            background: linear-gradient(135deg, var(--brand-strong), var(--brand), var(--secondary));
            border-bottom: 0;
            box-shadow: var(--shadow-md);
        }

        .topbar::after {
            display: none;
        }

        .page-title {
            color: #ffffff;
            font-weight: 750;
        }

        .topbar .button.secondary,
        .notification-menu summary {
            background: rgba(255, 255, 255, .14);
            border-color: rgba(255, 255, 255, .24);
            color: #ffffff;
            backdrop-filter: none;
        }

        .topbar .button.secondary:hover,
        .notification-menu summary:hover {
            background: rgba(255, 255, 255, .22);
            border-color: rgba(255, 255, 255, .34);
            box-shadow: none;
        }

        .notification-badge {
            background: var(--danger);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .20);
        }

        .notification-dropdown {
            background: #ffffff;
            border-color: var(--line);
            backdrop-filter: none;
            box-shadow: var(--shadow-lg);
        }

        .panel,
        .summary-item,
        .stat-card,
        .list-item,
        .table-wrap {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: var(--shadow-sm);
            backdrop-filter: none;
        }

        .panel:hover,
        .stat-card:hover,
        .list-item:hover {
            border-color: #d8e5df;
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .panel-title,
        .stat-value,
        th {
            color: var(--text);
        }

        .muted,
        .summary-label {
            color: var(--muted);
        }

        .button,
        button {
            background: var(--brand);
            color: #ffffff;
            border-color: var(--brand);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(14, 102, 86, .12);
        }

        .button:hover,
        button:hover {
            background: var(--secondary);
            border-color: var(--secondary);
            box-shadow: 0 6px 16px rgba(14, 102, 86, .16);
            transform: translateY(-1px);
        }

        .button.secondary {
            background: #ffffff;
            border-color: var(--line);
            color: var(--brand);
        }

        .button.secondary:hover {
            background: var(--brand-soft);
            border-color: #cddfd8;
            color: var(--brand-strong);
        }

        .logout-button {
            background: rgba(255, 255, 255, .12);
            color: #ffffff;
            border-color: rgba(255, 255, 255, .24);
        }

        input,
        textarea,
        select {
            background: #ffffff;
            border-color: var(--line-strong);
            color: var(--text);
            border-radius: 12px;
        }

        input::placeholder,
        textarea::placeholder {
            color: #9aa8a3;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline-color: rgba(14, 102, 86, .14);
            border-color: var(--brand);
            box-shadow: 0 0 0 1px rgba(14, 102, 86, .08);
        }

        option {
            background: #ffffff;
            color: var(--text);
        }

        input[type="file"] {
            background: var(--surface-soft);
        }

        input[type="checkbox"],
        input[type="radio"] {
            accent-color: var(--brand);
        }

        .status {
            background: #edf8f4;
            border-left-color: var(--success);
            color: #246f58;
        }

        .warning {
            background: #fff7e8;
            border-left-color: var(--warning-color);
            color: #8a6420;
        }

        .error {
            color: var(--danger);
        }

        .tabs {
            background: #ffffff;
            border-color: var(--line);
            backdrop-filter: none;
            box-shadow: var(--shadow-sm);
        }

        .tab-link {
            color: var(--muted);
        }

        .tab-link.active {
            background: var(--brand-soft);
            border-color: #d2e7df;
            color: var(--brand);
            box-shadow: inset 0 3px 0 var(--brand);
        }

        table {
            background: #ffffff;
        }

        th {
            background: #f4f8f6;
        }

        td,
        th {
            border-bottom-color: var(--line);
        }

        tbody tr:hover {
            background: #fbfcfa;
        }

        tr[style*="background:#fff1f0"] {
            background: #fff1ef !important;
        }

        .badge,
        .badge.success {
            background: #e7f4ee;
            color: #2b7b61;
            border-color: #d4eadf;
        }

        .badge.warning {
            background: #fff4d9;
            color: #8b681c;
            border-color: #f3dfaa;
        }

        .badge.danger {
            background: #fbe8e6;
            color: #9d4a43;
            border-color: #f0d2cf;
        }

        .badge.neutral,
        .badge.off {
            background: #f0f3f1;
            color: #66736f;
            border-color: #e0e5e2;
        }

        .link-button {
            color: var(--brand);
        }

        .danger-link {
            color: var(--danger);
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid var(--line);
        }

        .stat-card::after {
            background: var(--brand-soft);
            opacity: .9;
        }

        .stat-card:nth-child(4n+1),
        .stat-card.success {
            background: linear-gradient(135deg, #f2faf7, #ffffff);
        }

        .stat-card:nth-child(4n+2),
        .stat-card.warning {
            background: linear-gradient(135deg, #fff8ea, #ffffff);
        }

        .stat-card:nth-child(4n+3),
        .stat-card.danger {
            background: linear-gradient(135deg, #fff2f0, #ffffff);
        }

        .stat-card:nth-child(4n+4),
        .stat-card.neutral {
            background: linear-gradient(135deg, #eef7f4, #ffffff);
        }

        .progress {
            background: #e7eee9;
            box-shadow: inset 0 1px 2px rgba(14, 102, 86, .08);
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--brand), var(--secondary));
            box-shadow: none;
            animation: none;
        }

        .dashboard-grid > .panel:nth-child(4n+1) {
            border-top-color: var(--brand);
        }

        .dashboard-grid > .panel:nth-child(4n+2) {
            border-top-color: var(--accent);
        }

        .dashboard-grid > .panel:nth-child(4n+3) {
            border-top-color: var(--secondary);
        }

        .dashboard-grid > .panel:nth-child(4n+4) {
            border-top-color: #e8827a;
        }

        .brand::before {
            display: none;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo img {
            width: 58px;
            height: 58px;
            border-radius: 0;
            object-fit: contain;
            background: transparent;
            padding: 0;
            box-shadow: none;
        }

        .brand-wordmark {
            display: block;
            width: min(206px, 100%);
            max-width: 100%;
            height: auto;
            overflow: visible;
        }

        .brand-title {
            font-size: 22px;
        }

        [data-theme="dark"] {
            --bg: #10211d;
            --surface: #18342e;
            --surface-soft: #203f38;
            --line: #2d5b50;
            --line-strong: #3d7568;
            --text: #effaf7;
            --muted: #aac6be;
            --brand-soft: #203f38;
            --shadow-md: 0 14px 34px rgba(0, 0, 0, .20);
            --shadow-lg: 0 24px 54px rgba(0, 0, 0, .30);
            color-scheme: dark;
        }

        [data-theme="dark"] .topbar,
        [data-theme="dark"] .notification-dropdown,
        [data-theme="dark"] .table-wrap,
        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background: var(--surface);
            color: var(--text);
        }

        [data-theme="dark"] body {
            background:
                linear-gradient(135deg, rgba(45, 116, 100, .22), transparent 36vw),
                linear-gradient(315deg, rgba(232, 179, 106, .08), transparent 32vw),
                var(--bg);
            color: var(--text);
        }

        [data-theme="dark"] main,
        [data-theme="dark"] .content {
            color: var(--text);
        }

        [data-theme="dark"] .sidebar {
            background: linear-gradient(180deg, #082f28 0%, #071f1b 100%);
            box-shadow: 10px 0 28px rgba(0, 0, 0, .26);
        }

        [data-theme="dark"] .brand {
            border-bottom-color: rgba(255, 255, 255, .12);
        }

        [data-theme="dark"] .brand-title,
        [data-theme="dark"] .user-name,
        [data-theme="dark"] .page-title {
            color: #ffffff;
        }

        [data-theme="dark"] .brand-subtitle,
        [data-theme="dark"] .user-meta {
            color: rgba(226, 245, 240, .76);
        }

        [data-theme="dark"] .nav-link {
            color: rgba(226, 245, 240, .82);
        }

        [data-theme="dark"] .nav-icon {
            background: rgba(255, 255, 255, .08);
            color: rgba(226, 245, 240, .82);
        }

        [data-theme="dark"] .nav-link:hover {
            background: rgba(45, 156, 135, .20);
            border-color: rgba(45, 156, 135, .28);
            color: #ffffff;
        }

        [data-theme="dark"] .nav-link.active {
            background: linear-gradient(135deg, rgba(61, 156, 135, .34), rgba(14, 102, 86, .22));
            border-color: rgba(170, 198, 190, .30);
            color: #ffffff;
            box-shadow: inset 4px 0 0 var(--accent), 0 8px 18px rgba(0, 0, 0, .20);
        }

        [data-theme="dark"] .nav-link.active .nav-icon {
            background: rgba(232, 179, 106, .18);
            color: #f7d9a5;
        }

        [data-theme="dark"] .nav-group {
            background: rgba(255, 255, 255, .045);
            border-color: rgba(255, 255, 255, .10);
        }

        [data-theme="dark"] .nav-group-title,
        [data-theme="dark"] .nav-group-title strong {
            color: rgba(226, 245, 240, .76);
        }

        [data-theme="dark"] .nav-step {
            background: rgba(255, 255, 255, .08);
            color: rgba(226, 245, 240, .78);
        }

        [data-theme="dark"] .nav-link.active .nav-step {
            background: rgba(232, 179, 106, .22);
            color: #f4d490;
        }

        [data-theme="dark"] .sidebar-footer {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .12);
        }

        [data-theme="dark"] .topbar {
            background: linear-gradient(135deg, #092f28, #0e6656, #145f52);
            color: #ffffff;
        }

        [data-theme="dark"] .panel,
        [data-theme="dark"] .summary-item,
        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .list-item,
        [data-theme="dark"] .smart-list-item,
        [data-theme="dark"] .quick-guide-panel,
        [data-theme="dark"] .kpi-card,
        [data-theme="dark"] .table-wrap,
        [data-theme="dark"] .tabs,
        [data-theme="dark"] .notification-dropdown {
            background: linear-gradient(180deg, rgba(24, 52, 46, .98), rgba(21, 45, 40, .98));
            border-color: var(--line);
            color: var(--text);
            box-shadow: 0 10px 28px rgba(0, 0, 0, .20);
        }

        [data-theme="dark"] .quick-guide-panel {
            background:
                linear-gradient(135deg, rgba(32, 63, 56, .96), rgba(24, 52, 46, .96) 52%, rgba(232, 179, 106, .10)),
                var(--surface);
        }

        [data-theme="dark"] .quick-guide-panel::after {
            background: rgba(232, 179, 106, .10);
        }

        [data-theme="dark"] .quick-guide-icon {
            background: #2f8f7a;
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(0, 0, 0, .22);
        }

        [data-theme="dark"] .quick-guide-eyebrow {
            background: rgba(61, 156, 135, .20);
            color: #a9e8d2;
        }

        [data-theme="dark"] .quick-guide-step {
            background: rgba(16, 43, 38, .72);
            border-color: rgba(170, 198, 190, .16);
            color: var(--text);
        }

        [data-theme="dark"] .quick-guide-step:hover {
            background: rgba(24, 63, 55, .92);
            border-color: rgba(170, 198, 190, .28);
        }

        [data-theme="dark"] .quick-guide-number {
            background: rgba(232, 179, 106, .22);
            color: #f4d490;
        }

        [data-theme="dark"] .quick-guide-action {
            background: rgba(16, 43, 38, .82);
            border-color: rgba(170, 198, 190, .18);
            color: #a9e8d2;
        }

        [data-theme="dark"] .quick-guide-action.primary {
            background: #2f8f7a;
            border-color: #2f8f7a;
            color: #ffffff;
        }

        [data-theme="dark"] .quick-guide-action:hover {
            background: rgba(61, 156, 135, .20);
            color: #ffffff;
        }

        [data-theme="dark"] .guide-hero {
            background: linear-gradient(135deg, #092f28, #0e6656, #145f52);
        }

        [data-theme="dark"] .guide-flow-step,
        [data-theme="dark"] .guide-index nav a,
        [data-theme="dark"] .guide-info-grid > div,
        [data-theme="dark"] .guide-steps {
            background: rgba(16, 43, 38, .72);
            border-color: rgba(170, 198, 190, .16);
            color: var(--text);
        }

        [data-theme="dark"] .guide-index nav a:hover {
            background: rgba(61, 156, 135, .20);
            color: #a9e8d2;
        }

        [data-theme="dark"] .guide-label {
            color: #a9e8d2;
        }

        [data-theme="dark"] .guide-note {
            background: rgba(217, 164, 65, .14);
            border-color: rgba(217, 164, 65, .26);
        }

        [data-theme="dark"] .guide-note span {
            background: rgba(217, 164, 65, .22);
            color: #f4d490;
        }

        [data-theme="dark"] .guide-note p {
            color: #f4d490;
        }

        [data-theme="dark"] .kpi-card {
            background:
                linear-gradient(180deg, rgba(24, 52, 46, .96), rgba(20, 42, 37, .96)),
                var(--surface);
        }

        [data-theme="dark"] .panel-title,
        [data-theme="dark"] .stat-value,
        [data-theme="dark"] .kpi-value,
        [data-theme="dark"] h1,
        [data-theme="dark"] h2,
        [data-theme="dark"] h3,
        [data-theme="dark"] h4,
        [data-theme="dark"] strong,
        [data-theme="dark"] th {
            color: var(--text);
        }

        [data-theme="dark"] .muted,
        [data-theme="dark"] .summary-label,
        [data-theme="dark"] .kpi-hint,
        [data-theme="dark"] .chart-legend,
        [data-theme="dark"] .radar-chart text {
            color: var(--muted);
            fill: var(--muted);
        }

        [data-theme="dark"] .button.secondary,
        [data-theme="dark"] .topbar .button.secondary,
        [data-theme="dark"] .notification-menu summary {
            background: rgba(255, 255, 255, .10);
            border-color: rgba(255, 255, 255, .18);
            color: #ffffff;
        }

        [data-theme="dark"] .button.secondary:hover,
        [data-theme="dark"] .topbar .button.secondary:hover,
        [data-theme="dark"] .notification-menu summary:hover {
            background: rgba(255, 255, 255, .16);
            border-color: rgba(255, 255, 255, .28);
            color: #ffffff;
        }

        [data-theme="dark"] input,
        [data-theme="dark"] textarea,
        [data-theme="dark"] select,
        [data-theme="dark"] input[type="file"] {
            background: #102b26;
            border-color: var(--line-strong);
            color: var(--text);
        }

        [data-theme="dark"] input::placeholder,
        [data-theme="dark"] textarea::placeholder {
            color: rgba(170, 198, 190, .72);
        }

        [data-theme="dark"] option {
            background: #102b26;
            color: var(--text);
        }

        [data-theme="dark"] table {
            background: transparent;
            color: var(--text);
        }

        [data-theme="dark"] th {
            background: #203f38;
        }

        [data-theme="dark"] td,
        [data-theme="dark"] th {
            border-bottom-color: var(--line);
        }

        [data-theme="dark"] tbody tr:hover {
            background: rgba(61, 156, 135, .12);
        }

        [data-theme="dark"] .tab-link {
            color: var(--muted);
        }

        [data-theme="dark"] .tab-link.active {
            background: rgba(61, 156, 135, .20);
            border-color: rgba(61, 156, 135, .30);
            color: #ffffff;
        }

        [data-theme="dark"] .topbar-icon-button {
            box-shadow: none;
        }

        [data-theme="dark"] .topbar-icon-theme {
            background: rgba(217, 164, 65, .16);
            border-color: rgba(217, 164, 65, .28);
            color: #f4d490;
        }

        [data-theme="dark"] .topbar-icon-notification {
            background: rgba(199, 100, 90, .18);
            border-color: rgba(199, 100, 90, .30);
            color: #ffaaa3;
        }

        [data-theme="dark"] .topbar-icon-profile {
            background: rgba(148, 163, 184, .14);
            border-color: rgba(148, 163, 184, .24);
            color: #e2e8f0;
        }

        [data-theme="dark"] .notification-badge {
            border-color: #102b26;
        }

        [data-theme="dark"] .form-section {
            background: linear-gradient(180deg, rgba(24, 52, 46, .90), rgba(21, 45, 40, .92));
            border-color: var(--line);
        }

        [data-theme="dark"] .form-actions-sticky {
            background: rgba(16, 43, 38, .92);
            border-color: var(--line);
            box-shadow: 0 12px 30px rgba(0, 0, 0, .22);
        }

        [data-theme="dark"] .action-icon {
            background: rgba(255, 255, 255, .08);
            border-color: rgba(170, 198, 190, .18);
            color: var(--muted);
            box-shadow: none;
        }

        [data-theme="dark"] .action-icon:hover,
        [data-theme="dark"] .action-icon:focus-visible {
            box-shadow: 0 9px 20px rgba(0, 0, 0, .22);
        }

        [data-theme="dark"] .action-icon.tone-view {
            background: rgba(61, 156, 135, .16);
            border-color: rgba(61, 156, 135, .24);
            color: #b7f0df;
        }

        [data-theme="dark"] .action-icon.tone-edit {
            background: rgba(34, 138, 170, .16);
            border-color: rgba(34, 138, 170, .26);
            color: #9bdcf0;
        }

        [data-theme="dark"] .action-icon.tone-success {
            background: rgba(59, 158, 124, .16);
            border-color: rgba(59, 158, 124, .26);
            color: #9ee6c5;
        }

        [data-theme="dark"] .action-icon.tone-warning {
            background: rgba(217, 164, 65, .16);
            border-color: rgba(217, 164, 65, .28);
            color: #f4d490;
        }

        [data-theme="dark"] .action-icon.tone-danger {
            background: rgba(199, 100, 90, .17);
            border-color: rgba(199, 100, 90, .30);
            color: #ffaaa3;
        }

        [data-theme="dark"] .action-tooltip,
        [data-theme="dark"] .action-tooltip::after {
            background: #061c17;
        }

        [data-theme="dark"] .progress {
            background: rgba(170, 198, 190, .18);
        }

        [data-theme="dark"] .section-block {
            border-top-color: var(--line);
        }

        [data-theme="dark"] .badge,
        [data-theme="dark"] .badge.neutral,
        [data-theme="dark"] .badge.off {
            background: rgba(170, 198, 190, .12);
            border-color: rgba(170, 198, 190, .20);
            color: #d5e8e2;
        }

        [data-theme="dark"] .badge.success {
            background: rgba(59, 158, 124, .18);
            border-color: rgba(59, 158, 124, .32);
            color: #a9e8d2;
        }

        [data-theme="dark"] .badge.warning {
            background: rgba(217, 164, 65, .18);
            border-color: rgba(217, 164, 65, .32);
            color: #f4d490;
        }

        [data-theme="dark"] .badge.danger {
            background: rgba(199, 100, 90, .18);
            border-color: rgba(199, 100, 90, .34);
            color: #f4b5ae;
        }

        [data-theme="dark"] .link-button {
            color: #8bd8c7;
        }

        [data-theme="dark"] .danger-link,
        [data-theme="dark"] .error {
            color: #f4b5ae;
        }

        [data-theme="dark"] .status {
            background: rgba(59, 158, 124, .14);
            color: #bcebdc;
        }

        [data-theme="dark"] .warning {
            background: rgba(217, 164, 65, .14);
            color: #f4d490;
        }

        [data-theme="dark"] .notification-dropdown .list-item {
            background: rgba(16, 43, 38, .82);
        }

        [data-theme="dark"] .dashboard-alert {
            background: rgba(217, 164, 65, .14);
            border-color: rgba(217, 164, 65, .26);
            border-left-color: var(--warning-color);
            color: #f4d490;
        }

        [data-theme="dark"] .instrument-hero {
            background:
                linear-gradient(135deg, rgba(10, 74, 63, .98), rgba(14, 102, 86, .92)),
                var(--brand);
            box-shadow: 0 18px 44px rgba(0, 0, 0, .24);
        }

        [data-theme="dark"] .workflow-step,
        [data-theme="dark"] .instrument-section,
        [data-theme="dark"] .template-modal-card,
        [data-theme="dark"] .import-modal-card,
        [data-theme="dark"] .template-option {
            background: linear-gradient(180deg, rgba(24, 52, 46, .98), rgba(21, 45, 40, .98));
            border-color: var(--line);
            color: var(--text);
        }

        [data-theme="dark"] .workflow-step span,
        [data-theme="dark"] .standard-manager-caret,
        [data-theme="dark"] .template-modal-header .instrument-eyebrow,
        [data-theme="dark"] .import-modal-header .instrument-eyebrow {
            background: rgba(61, 156, 135, .20);
            color: #a9e8d2;
        }

        [data-theme="dark"] .instrument-import-box {
            background: rgba(61, 156, 135, .10);
            border-color: rgba(61, 156, 135, .28);
        }

        [data-theme="dark"] .excel-action {
            background: rgba(255, 255, 255, .08);
            border-color: rgba(170, 198, 190, .18);
            color: #d8f4eb;
            box-shadow: none;
        }

        [data-theme="dark"] .excel-action:hover,
        [data-theme="dark"] .excel-action:focus-visible {
            background: rgba(61, 156, 135, .18);
            border-color: rgba(61, 156, 135, .30);
            box-shadow: 0 9px 20px rgba(0, 0, 0, .22);
        }

        [data-theme="dark"] .excel-action-icon {
            background: rgba(59, 158, 124, .18);
            color: #9ee6c5;
        }

        [data-theme="dark"] .excel-action-import {
            background: rgba(23, 107, 135, .18);
            border-color: rgba(23, 107, 135, .34);
            color: #9bdcf0;
        }

        [data-theme="dark"] .excel-action-import .excel-action-icon {
            background: rgba(23, 107, 135, .24);
            color: #9bdcf0;
        }

        [data-theme="dark"] .excel-action-export {
            background: rgba(217, 164, 65, .16);
            border-color: rgba(217, 164, 65, .30);
            color: #f4d490;
        }

        [data-theme="dark"] .excel-action-export .excel-action-icon {
            background: rgba(217, 164, 65, .22);
            color: #f4d490;
        }

        [data-theme="dark"] .button-template {
            background: rgba(61, 156, 135, .18);
            border-color: rgba(61, 156, 135, .30);
            color: #d8f4eb;
        }

        [data-theme="dark"] .button-reset {
            background: rgba(148, 163, 184, .14);
            border-color: rgba(148, 163, 184, .24);
            color: #e2e8f0;
        }

        [data-theme="dark"] .import-file-drop {
            background: rgba(61, 156, 135, .10);
            border-color: rgba(61, 156, 135, .28);
        }

        [data-theme="dark"] .import-file-drop:hover {
            background: rgba(61, 156, 135, .16);
            border-color: rgba(61, 156, 135, .42);
        }

        [data-theme="dark"] .import-file-drop.is-dragging {
            background: rgba(61, 156, 135, .22);
            border-color: rgba(61, 156, 135, .60);
            box-shadow: inset 0 0 0 2px rgba(61, 156, 135, .12), 0 10px 24px rgba(0, 0, 0, .22);
        }

        [data-theme="dark"] .import-file-name {
            background: rgba(255, 255, 255, .10);
            color: #d8f4eb;
            box-shadow: none;
        }

        [data-theme="dark"] .instrument-table-wrap th {
            background: #0e6656;
            color: #ffffff;
        }

        [data-theme="dark"] .template-modal-close {
            background: rgba(255, 255, 255, .08);
            border-color: rgba(255, 255, 255, .14);
            color: #ffffff;
        }

        [data-theme="dark"] .template-option-main {
            background: linear-gradient(135deg, rgba(61, 156, 135, .16), rgba(232, 179, 106, .10));
        }

        [data-theme="dark"] .danger-confirm-card {
            background: linear-gradient(180deg, rgba(24, 52, 46, .98), rgba(21, 45, 40, .98));
            border-color: rgba(199, 100, 90, .34);
            color: var(--text);
        }

        [data-theme="dark"] .danger-confirm-icon {
            background: rgba(199, 100, 90, .18);
            color: #f4b5ae;
        }

        [data-theme="dark"] .danger-zone-card {
            background: linear-gradient(135deg, rgba(199, 100, 90, .12), rgba(21, 45, 40, .98));
            border-color: rgba(199, 100, 90, .34);
        }

        [data-theme="dark"] .reset-impact-list {
            background: rgba(199, 100, 90, .12);
        }

        [data-theme="dark"] .dashboard-alert-icon {
            background: rgba(217, 164, 65, .18);
            color: #f4d490;
        }

        .theme-toggle {
            min-width: 42px;
            padding: 10px 12px;
        }

        .avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: hsl(var(--avatar-hue), 46%, 86%);
            color: hsl(var(--avatar-hue), 48%, 26%);
            font-size: 12px;
            font-weight: 800;
        }

        .avatar.md {
            width: 42px;
            height: 42px;
            font-size: 14px;
        }

        .avatar.has-photo {
            overflow: hidden;
            background: var(--brand-soft);
            background-image: var(--photo-url);
            background-position: var(--photo-x, 50%) var(--photo-y, 50%);
            background-repeat: no-repeat;
            background-size: cover;
        }

        .profile-photo-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 18px;
            align-items: start;
            margin: 18px 0;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--surface-soft);
        }

        .profile-photo-preview {
            width: 96px;
            height: 96px;
            border-radius: 999px;
            overflow: hidden;
            display: grid;
            place-items: center;
            background: var(--brand-soft);
            color: var(--brand);
            font-size: 26px;
            font-weight: 900;
        }

        .profile-photo-preview img {
            display: none;
        }

        .profile-photo-preview.has-photo {
            background-image: var(--photo-url);
            background-position: var(--photo-x, 50%) var(--photo-y, 50%);
            background-repeat: no-repeat;
            background-size: cover;
        }

        .photo-focus-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 12px;
        }

        .photo-focus-field {
            display: grid;
            gap: 6px;
        }

        .photo-focus-field input[type="range"] {
            padding: 0;
            accent-color: var(--brand);
        }

        .photo-focus-help {
            margin: 8px 0 0;
            font-size: 13px;
        }

        .photo-focus-modal {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: grid;
            place-items: center;
            padding: 24px;
            background: rgba(31, 44, 41, .56);
            backdrop-filter: blur(4px);
        }

        .photo-focus-modal[hidden] {
            display: none;
        }

        .photo-focus-dialog {
            width: min(520px, 100%);
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--surface);
            box-shadow: var(--shadow-lg);
            padding: 22px;
        }

        .photo-crop-stage {
            display: grid;
            place-items: center;
            padding: 26px;
            border-radius: 14px;
            background:
                linear-gradient(45deg, var(--brand-soft) 25%, transparent 25%),
                linear-gradient(-45deg, var(--brand-soft) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, var(--brand-soft) 75%),
                linear-gradient(-45deg, transparent 75%, var(--brand-soft) 75%);
            background-size: 22px 22px;
            background-position: 0 0, 0 11px, 11px -11px, -11px 0;
        }

        .photo-crop-frame {
            width: min(320px, 72vw);
            height: min(320px, 72vw);
            border-radius: 999px;
            overflow: hidden;
            display: grid;
            place-items: center;
            background: var(--brand-soft);
            color: var(--brand);
            font-size: 34px;
            font-weight: 900;
            cursor: grab;
            touch-action: none;
            box-shadow: 0 0 0 5px var(--surface), 0 0 0 6px var(--line-strong), var(--shadow-md);
            user-select: none;
        }

        .photo-crop-frame.is-dragging {
            cursor: grabbing;
        }

        .photo-crop-frame img {
            display: none;
        }

        .photo-crop-frame.has-photo {
            background-image: var(--photo-url);
            background-position: var(--photo-x, 50%) var(--photo-y, 50%);
            background-repeat: no-repeat;
            background-size: cover;
        }

        .photo-focus-actions {
            margin: 18px 0 0;
        }

        .toast-stack {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 60;
            display: grid;
            gap: 10px;
            width: min(360px, calc(100vw - 32px));
        }

        .toast {
            border: 1px solid var(--line);
            border-left: 5px solid var(--success);
            border-radius: 12px;
            background: var(--surface);
            box-shadow: var(--shadow-lg);
            padding: 14px 16px;
            animation: slideToast .28s ease-out both;
        }

        .toast.warning {
            border-left-color: var(--danger);
        }

        @keyframes slideToast {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .progress-bar,
        .timeline-segment i,
        .gauge-ring {
            transition: width .5s ease, background .2s ease;
        }

        .visual-grid {
            display: grid;
            grid-template-columns: minmax(220px, .7fr) minmax(280px, 1fr);
            gap: 18px;
        }

        .visual-gauge {
            display: grid;
            justify-items: center;
            gap: 10px;
        }

        .gauge-panel {
            text-align: left;
        }

        .gauge-chart-wrap {
            display: grid;
            justify-items: center;
            width: 100%;
            margin-top: auto;
            padding-top: 14px;
            text-align: center;
        }

        .admin-visual-panel {
            min-height: 420px;
        }

        .admin-visual-panel .panel-header {
            min-height: 76px;
        }

        .admin-visual-panel .gauge-chart-wrap {
            margin-top: 4px;
            align-content: center;
            min-height: 250px;
            padding-top: 0;
        }

        .radar-panel .visual-chart {
            min-height: 282px;
            align-content: center;
        }

        .gauge-chart-wrap .visual-gauge {
            width: 100%;
        }

        .gauge-footnote {
            display: grid;
            justify-items: center;
            gap: 2px;
            margin-top: 10px;
        }

        .gauge-score {
            color: var(--brand);
            font-size: 20px;
            font-weight: 800;
        }

        .gauge-ring {
            --tone: var(--brand);
            width: 156px;
            aspect-ratio: 1;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: conic-gradient(var(--tone) calc(var(--value) * 1%), var(--brand-soft) 0);
            box-shadow: inset 0 0 0 1px var(--line), var(--shadow-sm);
        }

        .visual-gauge.success .gauge-ring { --tone: var(--success); }
        .visual-gauge.warning .gauge-ring { --tone: #d9a441; }
        .visual-gauge.danger .gauge-ring { --tone: var(--danger); }

        .gauge-inner {
            display: grid;
            place-items: center;
            width: 108px;
            aspect-ratio: 1;
            border-radius: 999px;
            background: var(--surface);
            text-align: center;
            box-shadow: inset 0 0 0 1px var(--line);
        }

        .gauge-inner strong {
            font-size: 28px;
            line-height: 1;
        }

        .gauge-inner span {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .visual-chart {
            display: grid;
            gap: 12px;
        }

        .radar-chart {
            width: min(100%, 360px);
            min-height: 240px;
            justify-self: center;
        }

        .radar-chart circle,
        .radar-chart line {
            fill: none;
            stroke: var(--line);
            stroke-width: 1;
        }

        .radar-chart text {
            fill: var(--muted);
            font-size: 8px;
            font-weight: 800;
        }

        .radar-target {
            fill: rgba(232, 179, 106, .12);
            stroke: var(--accent);
            stroke-width: 1.4;
        }

        .radar-value {
            fill: rgba(14, 102, 86, .20);
            stroke: var(--brand);
            stroke-width: 2.4;
        }

        .radar-dot {
            fill: var(--brand);
        }

        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 14px;
            color: var(--muted);
            font-size: 13px;
        }

        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 6px;
            border-radius: 999px;
            background: var(--muted);
        }

        .legend-dot.success { background: var(--success); }
        .legend-dot.warning { background: #d9a441; }
        .legend-dot.danger { background: var(--danger); }

        .sr-table {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
        }

        .heatmap {
            display: grid;
            gap: 12px;
        }

        .heatmap-grid {
            display: grid;
            grid-template-columns: minmax(96px, 1.1fr) repeat(calc(var(--columns) - 1), minmax(72px, 1fr));
            gap: 8px;
            overflow-x: auto;
        }

        .heatmap-head,
        .heatmap-unit,
        .heatmap-cell {
            border-radius: 10px;
            padding: 10px;
            min-height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
        }

        .heatmap-head {
            background: var(--brand-soft);
            color: var(--brand);
        }

        .heatmap-unit {
            justify-content: flex-start;
            background: var(--surface-soft);
        }

        .heatmap-cell.success { background: #dff3eb; color: #1d6b54; }
        .heatmap-cell.warning { background: #fff1d6; color: #8b5d11; }
        .heatmap-cell.danger { background: #ffe4df; color: #9f3d34; }

        .heatmap-empty,
        .empty-compact {
            border: 1px dashed var(--line-strong);
            border-radius: 12px;
            padding: 18px;
            color: var(--muted);
            text-align: center;
        }

        .audit-timeline {
            display: grid;
            gap: 14px;
        }

        .timeline-markers {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(96px, 1fr));
            gap: 8px;
        }

        .timeline-markers span {
            border-radius: 10px;
            background: var(--surface-soft);
            padding: 10px;
            color: var(--muted);
            font-size: 12px;
        }

        .timeline-markers strong {
            display: block;
            color: var(--text);
        }

        .timeline-rows {
            display: grid;
            gap: 10px;
        }

        .timeline-row {
            display: grid;
            grid-template-columns: 86px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px;
            background: var(--surface);
        }

        .timeline-row.is-late {
            border-color: rgba(199, 100, 90, .42);
            background: linear-gradient(90deg, rgba(199, 100, 90, .08), var(--surface));
        }

        .timeline-segments {
            display: grid;
            grid-template-columns: repeat(4, minmax(100px, 1fr));
            gap: 8px;
        }

        .timeline-segment {
            position: relative;
            min-height: 34px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--text);
        }

        .timeline-segment i {
            position: absolute;
            inset: 0 auto 0 0;
            width: 0;
            background: var(--brand);
        }

        .timeline-segment.warning i { background: #d9a441; }
        .timeline-segment.danger i { background: var(--danger); }
        .timeline-segment.success i { background: var(--success); }

        .timeline-segment b {
            position: relative;
            z-index: 1;
            display: grid;
            place-items: center;
            height: 100%;
            padding: 0 10px;
            color: var(--text);
            font-size: 12px;
        }

        .kanban-board {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 14px;
            align-items: start;
        }

        .kanban-column {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--surface-soft);
            padding: 12px;
        }

        .kanban-column h3 {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 0 0 12px;
            font-size: 15px;
        }

        .kanban-card {
            display: grid;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--surface);
            padding: 12px;
            box-shadow: var(--shadow-sm);
            cursor: grab;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .kanban-card + .kanban-card {
            margin-top: 10px;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .kanban-card.dragging {
            opacity: .65;
        }

        .kanban-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .evidence-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }

        .evidence-card {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--surface);
            box-shadow: var(--shadow-sm);
        }

        .evidence-thumb {
            position: relative;
            aspect-ratio: 4 / 3;
            display: grid;
            place-items: center;
            background: var(--brand-soft);
            overflow: hidden;
        }

        .evidence-thumb img,
        .evidence-thumb iframe {
            width: 100%;
            height: 100%;
            border: 0;
            object-fit: cover;
        }

        .evidence-thumb .badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .file-glyph {
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            padding: 12px 14px;
            background: var(--surface);
            color: var(--brand);
            font-weight: 900;
        }

        .evidence-body {
            display: grid;
            gap: 8px;
            padding: 12px;
        }

        .empty-state {
            display: grid;
            justify-items: center;
            gap: 8px;
            border: 1px dashed var(--line-strong);
            border-radius: 14px;
            padding: 26px;
            text-align: center;
            background: var(--surface-soft);
        }

        .empty-illustration {
            width: 70px;
            height: 70px;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand);
            display: grid;
            place-items: center;
        }

        .empty-illustration svg {
            width: 42px;
            height: 42px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
        }

        .view-toggle {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .quick-chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 12px;
            background: var(--surface);
            color: var(--muted);
            font-weight: 700;
            font-size: 13px;
        }

        .quick-chip.active {
            background: var(--brand);
            color: #ffffff;
        }

        .quick-guide-panel {
            position: relative;
            display: grid;
            grid-template-columns: minmax(260px, .8fr) minmax(0, 1.2fr) auto;
            gap: 18px;
            align-items: center;
            margin-bottom: 18px;
            border: 1px solid rgba(14, 102, 86, .16);
            border-radius: 18px;
            padding: 18px;
            background:
                linear-gradient(135deg, rgba(228, 242, 238, .90), rgba(255, 255, 255, .96) 52%, rgba(232, 179, 106, .16)),
                var(--surface);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .quick-guide-panel::after {
            content: "";
            position: absolute;
            inset: auto 18px 14px auto;
            width: 86px;
            height: 86px;
            border-radius: 28px;
            background: rgba(14, 102, 86, .08);
            transform: rotate(14deg);
            pointer-events: none;
        }

        .quick-guide-intro {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            min-width: 0;
        }

        .quick-guide-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: var(--brand);
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(14, 102, 86, .16);
        }

        .quick-guide-icon svg {
            width: 25px;
            height: 25px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .quick-guide-eyebrow {
            display: inline-flex;
            width: fit-content;
            margin-bottom: 6px;
            border-radius: 999px;
            padding: 3px 9px;
            background: var(--brand-soft);
            color: var(--brand);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .quick-guide-panel h3 {
            margin: 0 0 6px;
            color: var(--text);
            font-size: 20px;
            line-height: 1.2;
        }

        .quick-guide-panel p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .quick-guide-steps {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .quick-guide-step {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 10px;
            align-items: flex-start;
            min-height: 92px;
            border: 1px solid rgba(14, 102, 86, .12);
            border-radius: 14px;
            padding: 12px;
            background: rgba(255, 255, 255, .72);
            color: var(--text);
            transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .quick-guide-step:hover {
            transform: translateY(-2px);
            border-color: rgba(14, 102, 86, .24);
            background: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .quick-guide-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 10px;
            background: var(--accent);
            color: #4b3511;
            font-size: 13px;
            font-weight: 900;
        }

        .quick-guide-step strong {
            display: block;
            color: var(--text);
            font-size: 13px;
            line-height: 1.25;
        }

        .quick-guide-step small {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.35;
        }

        .quick-guide-actions {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 8px;
            min-width: 170px;
        }

        .quick-guide-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 12px;
            background: #ffffff;
            color: var(--brand);
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            transition: transform .16s ease, background .16s ease, border-color .16s ease;
        }

        .quick-guide-action.primary {
            background: var(--brand);
            border-color: var(--brand);
            color: #ffffff;
        }

        .quick-guide-action:hover {
            transform: translateY(-1px);
            background: var(--brand-soft);
            border-color: rgba(14, 102, 86, .24);
        }

        .quick-guide-action.primary:hover {
            background: var(--secondary);
            color: #ffffff;
        }

        .guide-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(300px, 420px);
            gap: 26px;
            align-items: center;
            margin-bottom: 18px;
            border: 1px solid rgba(14, 102, 86, .18);
            border-radius: 24px;
            padding: 32px;
            background:
                radial-gradient(circle at 86% 18%, rgba(232, 179, 106, .28), transparent 26%),
                radial-gradient(circle at 10% 110%, rgba(255, 255, 255, .22), transparent 28%),
                linear-gradient(135deg, rgba(10, 74, 63, .98), rgba(14, 102, 86, .94), rgba(61, 156, 135, .88)),
                var(--brand);
            color: #ffffff;
            box-shadow: 0 18px 44px rgba(14, 102, 86, .18);
        }

        .guide-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(255, 255, 255, .10) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 34px 34px;
            mask-image: linear-gradient(90deg, transparent, #000 14%, #000 86%, transparent);
            pointer-events: none;
        }

        .smart-guide-hero::after {
            content: "";
            position: absolute;
            right: -58px;
            bottom: -66px;
            width: 210px;
            height: 210px;
            border-radius: 64px;
            background: rgba(255, 255, 255, .10);
            transform: rotate(22deg);
            pointer-events: none;
        }

        .guide-hero > * {
            position: relative;
            z-index: 1;
        }

        .guide-hero h3 {
            max-width: 880px;
            margin: 12px 0 10px;
            color: #ffffff;
            font-size: clamp(28px, 4vw, 44px);
            line-height: 1.06;
        }

        .guide-hero p {
            max-width: 860px;
            margin: 0;
            color: rgba(246, 255, 252, .84);
        }

        .guide-illustration {
            position: relative;
            display: grid;
            gap: 14px;
            justify-items: center;
        }

        .guide-illustration svg {
            width: min(100%, 380px);
            height: auto;
            filter: drop-shadow(0 18px 24px rgba(0, 0, 0, .16));
        }

        .guide-hero-card {
            display: grid;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 18px;
            padding: 18px;
            background: rgba(255, 255, 255, .14);
            color: #ffffff;
            backdrop-filter: blur(8px);
        }

        .guide-hero-card span {
            color: rgba(246, 255, 252, .82);
        }

        .guide-smart-row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .guide-smart-card {
            position: relative;
            overflow: hidden;
            display: grid;
            gap: 6px;
            min-height: 98px;
            border: 1px solid rgba(14, 102, 86, .14);
            border-radius: 18px;
            padding: 16px;
            background: linear-gradient(180deg, #ffffff, var(--surface-soft));
            box-shadow: var(--shadow-sm);
        }

        .guide-smart-card::after {
            content: "";
            position: absolute;
            right: 12px;
            bottom: 12px;
            width: 44px;
            height: 44px;
            border-radius: 16px;
            background: var(--brand-soft);
            transform: rotate(16deg);
        }

        .guide-smart-card span,
        .guide-smart-card strong {
            position: relative;
            z-index: 1;
        }

        .guide-smart-card span {
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .guide-smart-card strong {
            color: var(--text);
            font-size: 18px;
            line-height: 1.2;
        }

        .guide-flow {
            margin-bottom: 18px;
        }

        .smart-flow-panel {
            overflow: hidden;
        }

        .guide-flow-track {
            position: relative;
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 4px 2px 8px;
            scrollbar-width: thin;
        }

        .guide-flow-track::before {
            content: "";
            position: absolute;
            left: 18px;
            right: 18px;
            top: 28px;
            height: 3px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--brand), var(--accent), var(--secondary));
            opacity: .24;
        }

        .guide-flow-step {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 9px;
            flex: 0 0 156px;
            border: 1px solid var(--line);
            border-radius: 15px;
            padding: 13px;
            background: linear-gradient(180deg, var(--surface), var(--surface-soft));
            box-shadow: var(--shadow-sm);
        }

        .guide-flow-step span,
        .guide-card-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 12px;
            background: var(--brand);
            color: #ffffff;
            font-weight: 900;
        }

        .guide-card-number {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            box-shadow: 0 10px 20px rgba(14, 102, 86, .14);
        }

        .guide-card-number svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .guide-flow-step strong {
            color: var(--text);
            font-size: 13px;
            line-height: 1.25;
        }

        .guide-layout {
            display: grid;
            grid-template-columns: minmax(230px, 280px) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .guide-index {
            position: sticky;
            top: 96px;
            overflow: hidden;
        }

        .guide-mini-tip {
            display: grid;
            gap: 5px;
            margin-top: 14px;
            border: 1px solid rgba(232, 179, 106, .34);
            border-radius: 14px;
            padding: 13px;
            background: #fff8e8;
        }

        .guide-mini-tip strong {
            color: #674609;
            font-size: 13px;
        }

        .guide-mini-tip span {
            color: #7a5a1a;
            font-size: 12px;
            line-height: 1.4;
        }

        .guide-index nav {
            display: grid;
            gap: 8px;
            margin-top: 14px;
        }

        .guide-index nav a {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 12px;
            background: var(--surface-soft);
            color: var(--text);
            font-size: 13px;
            font-weight: 800;
            transition: background .16s ease, border-color .16s ease, transform .16s ease;
        }

        .guide-index nav a:hover {
            transform: translateX(2px);
            border-color: rgba(14, 102, 86, .24);
            background: var(--brand-soft);
            color: var(--brand);
        }

        .guide-sections {
            display: grid;
            gap: 18px;
            min-width: 0;
        }

        .guide-card {
            scroll-margin-top: 98px;
            position: relative;
            overflow: hidden;
            border-radius: 18px;
        }

        .guide-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 5px;
            background: linear-gradient(90deg, var(--brand), var(--accent));
        }

        .guide-card-header {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 14px;
            align-items: start;
            margin-bottom: 18px;
        }

        .guide-feature-kicker {
            display: inline-flex;
            width: fit-content;
            margin-bottom: 5px;
            border-radius: 999px;
            padding: 3px 8px;
            background: var(--brand-soft);
            color: var(--brand);
            font-size: 11px;
            font-weight: 900;
        }

        .guide-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .guide-info-grid > div,
        .guide-steps {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
            background: var(--surface-soft);
        }

        .guide-label {
            display: block;
            margin-bottom: 8px;
            color: var(--brand);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .guide-info-grid p {
            margin: 0;
            color: var(--text);
        }

        .guide-steps ol {
            display: grid;
            gap: 10px;
            margin: 0;
            padding-left: 22px;
        }

        .guide-steps li {
            color: var(--text);
            padding-left: 4px;
        }

        .guide-result-ribbon {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 14px;
            border: 1px solid rgba(14, 102, 86, .16);
            border-radius: 14px;
            padding: 12px 14px;
            background: linear-gradient(90deg, var(--brand-soft), rgba(232, 179, 106, .14));
        }

        .guide-result-ribbon span {
            color: var(--brand);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .guide-result-ribbon strong {
            color: var(--text);
            font-size: 14px;
        }

        [data-theme="dark"] .guide-smart-card,
        [data-theme="dark"] .guide-flow-step,
        [data-theme="dark"] .guide-index nav a,
        [data-theme="dark"] .guide-info-grid > div,
        [data-theme="dark"] .guide-steps {
            background: rgba(16, 43, 38, .78);
            border-color: rgba(170, 198, 190, .18);
            color: var(--text);
        }

        [data-theme="dark"] .guide-smart-card::after {
            background: rgba(61, 156, 135, .20);
        }

        [data-theme="dark"] .guide-mini-tip {
            background: rgba(217, 164, 65, .14);
            border-color: rgba(217, 164, 65, .28);
        }

        [data-theme="dark"] .guide-mini-tip strong,
        [data-theme="dark"] .guide-mini-tip span {
            color: #f4d490;
        }

        [data-theme="dark"] .guide-feature-kicker {
            background: rgba(61, 156, 135, .20);
            color: #a9e8d2;
        }

        [data-theme="dark"] .guide-result-ribbon {
            background: linear-gradient(90deg, rgba(61, 156, 135, .18), rgba(217, 164, 65, .12));
            border-color: rgba(170, 198, 190, .18);
        }

        [data-theme="dark"] .guide-result-ribbon span {
            color: #a9e8d2;
        }

        [data-theme="dark"] .guide-illustration svg {
            filter: drop-shadow(0 18px 24px rgba(0, 0, 0, .28));
        }

        .guide-notes {
            margin-top: 18px;
        }

        .guide-note-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .guide-note {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            border: 1px solid rgba(217, 164, 65, .30);
            border-radius: 14px;
            padding: 13px;
            background: #fff8e8;
        }

        .guide-note span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 26px;
            height: 26px;
            border-radius: 10px;
            background: var(--accent);
            color: #4b3511;
            font-weight: 900;
        }

        .guide-note p {
            margin: 0;
            color: #5f3f09;
        }

        .unit-profile-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(240px, 320px);
            gap: 20px;
            align-items: center;
            margin-bottom: 18px;
            border-radius: 22px;
            padding: 26px;
            background:
                radial-gradient(circle at 92% 18%, rgba(232, 179, 106, .24), transparent 28%),
                linear-gradient(135deg, var(--brand-strong), var(--brand), var(--secondary));
            color: #ffffff;
            box-shadow: 0 18px 44px rgba(14, 102, 86, .18);
        }

        .unit-profile-hero h3 {
            margin: 12px 0 6px;
            color: #ffffff;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.08;
        }

        .unit-profile-hero p {
            margin: 0;
            color: rgba(246, 255, 252, .82);
        }

        .unit-profile-stamp {
            display: grid;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, .20);
            border-radius: 18px;
            padding: 18px;
            background: rgba(255, 255, 255, .14);
            color: #ffffff;
        }

        .unit-profile-stamp span {
            color: rgba(246, 255, 252, .72);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .unit-profile-stamp strong {
            color: #ffffff;
            font-size: 26px;
        }

        .unit-profile-stamp small {
            color: rgba(246, 255, 252, .80);
        }

        .profile-info-list {
            display: grid;
            gap: 10px;
        }

        .profile-info-list > div {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            border: 1px solid var(--line);
            border-radius: 13px;
            padding: 12px;
            background: var(--surface-soft);
        }

        .profile-info-list span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .profile-info-list strong {
            color: var(--text);
            text-align: right;
        }

        .status-pill-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .status-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px;
            background: var(--surface-soft);
        }

        .status-pill span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .status-pill strong {
            color: var(--brand);
            font-size: 20px;
        }

        .topbar-title-block {
            display: grid;
            gap: 3px;
            min-width: 0;
        }

        .topbar-greeting {
            color: rgba(255, 255, 255, .82);
            font-size: 13px;
            font-weight: 700;
        }

        .topbar-clock {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 38px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 999px;
            padding: 7px 12px;
            background: rgba(255, 255, 255, .12);
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .topbar-clock::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--accent);
        }

        [data-theme="dark"] .topbar-greeting {
            color: rgba(226, 245, 240, .78);
        }

        [data-theme="dark"] .topbar-clock {
            background: rgba(255, 255, 255, .10);
            border-color: rgba(255, 255, 255, .18);
            color: #ffffff;
        }

        .task-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            margin-bottom: 18px;
            border-radius: 22px;
            padding: 24px;
            background:
                radial-gradient(circle at 92% 24%, rgba(232, 179, 106, .24), transparent 28%),
                linear-gradient(135deg, var(--brand-strong), var(--brand), var(--secondary));
            color: #ffffff;
            box-shadow: 0 18px 44px rgba(14, 102, 86, .18);
        }

        .task-hero h3 {
            margin: 12px 0 6px;
            color: #ffffff;
            font-size: clamp(28px, 4vw, 40px);
            line-height: 1.08;
        }

        .task-hero p {
            margin: 0;
            color: rgba(246, 255, 252, .82);
        }

        .task-hero-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }

        .task-board {
            display: grid;
            gap: 14px;
        }

        .task-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 180px;
            gap: 18px;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
            background: linear-gradient(180deg, var(--surface), var(--surface-soft));
            box-shadow: var(--shadow-sm);
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }

        .task-card:hover {
            transform: translateY(-2px);
            border-color: rgba(14, 102, 86, .24);
            box-shadow: var(--shadow-md);
        }

        .task-card-main {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 16px;
            min-width: 0;
        }

        .task-unit-mark {
            display: grid;
            place-items: center;
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: var(--brand);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(14, 102, 86, .14);
        }

        .task-card-title {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .task-card-title h3 {
            margin: 0 0 4px;
            color: var(--text);
            font-size: 20px;
            line-height: 1.2;
        }

        .task-card-title p {
            margin: 0;
            color: var(--muted);
        }

        .task-meta-grid,
        .task-progress-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .task-progress-grid {
            margin-top: 14px;
        }

        .task-meta-grid > div,
        .task-progress-grid > div,
        .task-status-box {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px;
            background: var(--surface);
        }

        .task-meta-grid span,
        .task-status-box span {
            display: block;
            margin-bottom: 5px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .task-meta-grid strong {
            color: var(--text);
            line-height: 1.25;
        }

        .task-card-side {
            display: grid;
            align-content: space-between;
            gap: 12px;
        }

        .task-status-box {
            text-align: center;
        }

        .task-status-box strong {
            display: block;
            color: var(--brand);
            font-size: 34px;
            line-height: 1;
        }

        .task-actions {
            display: grid;
            gap: 8px;
        }

        .task-actions .button {
            justify-content: center;
            text-align: center;
        }

        [data-theme="dark"] .profile-info-list > div,
        [data-theme="dark"] .status-pill,
        [data-theme="dark"] .task-card,
        [data-theme="dark"] .task-meta-grid > div,
        [data-theme="dark"] .task-progress-grid > div,
        [data-theme="dark"] .task-status-box {
            background: rgba(16, 43, 38, .78);
            border-color: rgba(170, 198, 190, .18);
        }

        .dashboard-hero {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(14, 102, 86, .18);
            border-radius: 18px;
            margin-bottom: 18px;
            padding: 24px;
            background:
                linear-gradient(135deg, rgba(14, 102, 86, .96), rgba(61, 156, 135, .92)),
                var(--brand);
            color: #f6fffc;
            box-shadow: 0 18px 44px rgba(14, 102, 86, .18);
        }

        .dashboard-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(255, 255, 255, .10) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: linear-gradient(90deg, transparent, #000 18%, #000 82%, transparent);
            pointer-events: none;
        }

        .dashboard-hero-content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 20px;
            align-items: center;
        }

        .dashboard-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 28px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 999px;
            padding: 4px 10px;
            background: rgba(255, 255, 255, .12);
            color: #eafff9;
            font-size: 12px;
            font-weight: 800;
        }

        .dashboard-hero h3 {
            margin: 12px 0 8px;
            color: #ffffff;
            font-size: clamp(26px, 3vw, 38px);
            line-height: 1.08;
            letter-spacing: 0;
        }

        .dashboard-hero p {
            max-width: 760px;
            margin: 0;
            color: rgba(246, 255, 252, .82);
        }

        .hero-filter {
            display: grid;
            gap: 10px;
            min-width: min(320px, 100%);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 16px;
            padding: 14px;
            background: rgba(255, 255, 255, .12);
            backdrop-filter: blur(8px);
        }

        .hero-filter label {
            color: #eafff9;
        }

        .hero-filter select {
            border-color: rgba(255, 255, 255, .30);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .hero-action {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            border-radius: 999px;
            padding: 8px 13px;
            background: rgba(255, 255, 255, .16);
            color: #ffffff;
            font-weight: 800;
            border: 1px solid rgba(255, 255, 255, .20);
            transition: transform .16s ease, background .16s ease;
        }

        .hero-action:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, .24);
        }

        .dashboard-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .admin-kpi-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            align-items: stretch;
        }

        .kpi-card {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 122px;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(248, 252, 250, .92)),
                var(--surface);
            box-shadow: var(--shadow-sm);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .admin-kpi-grid .kpi-card {
            min-width: 0;
            width: 100%;
            min-height: 132px;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: rgba(14, 102, 86, .30);
        }

        .kpi-card::after {
            content: "";
            position: absolute;
            inset: auto 12px 12px auto;
            width: 54px;
            height: 54px;
            border-radius: 18px;
            background: var(--brand-soft);
            opacity: .52;
            transform: rotate(18deg);
        }

        .kpi-icon {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--brand-soft);
            color: var(--brand);
        }

        .kpi-icon svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .kpi-body {
            position: relative;
            z-index: 1;
            display: grid;
            min-width: 0;
        }

        .kpi-hint {
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .kpi-card.success .kpi-icon,
        .kpi-card.success::after {
            background: #dff3eb;
            color: var(--success);
        }

        .kpi-card.warning .kpi-icon,
        .kpi-card.warning::after {
            background: #fff1d6;
            color: #9a6a12;
        }

        .kpi-card.danger .kpi-icon,
        .kpi-card.danger::after {
            background: #ffe4df;
            color: var(--danger);
        }

        .dashboard-panel-accent {
            border-radius: 18px;
            border-top: 4px solid var(--brand);
        }

        .dashboard-panel-accent.warning {
            border-top-color: #d9a441;
        }

        .dashboard-panel-accent.danger {
            border-top-color: var(--danger);
        }

        .smart-list {
            display: grid;
            gap: 12px;
        }

        .smart-list-item {
            display: grid;
            gap: 6px;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 13px;
            background: linear-gradient(180deg, var(--surface), var(--surface-soft));
            box-shadow: var(--shadow-sm);
        }

        .smart-list-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .dashboard-alert {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            border: 1px solid rgba(217, 164, 65, .32);
            border-left: 5px solid #d9a441;
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 14px;
            background: #fff8e8;
            color: #5f3f09;
        }

        .dashboard-alert-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: #fff1d6;
            color: #9a6a12;
            font-weight: 900;
        }

        .sidebar {
            overflow: visible;
            position: relative;
            z-index: 20;
            height: auto;
            min-height: 100vh;
            align-self: stretch;
        }

        .app-shell {
            grid-template-columns: var(--sidebar-width, 380px) minmax(0, 1fr);
            min-height: 100vh;
            align-items: stretch;
            transition: grid-template-columns .18s ease;
        }

        [data-sidebar="collapsed"] {
            --sidebar-width: 92px;
        }

        [data-sidebar-layout="vertical"] {
            --sidebar-width: 300px;
        }

        [data-sidebar="collapsed"] {
            --sidebar-width: 92px;
        }

        .sidebar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 36px;
            height: 36px;
            border-radius: 12px;
            padding: 0;
            background: rgba(255, 255, 255, .12);
            border-color: rgba(255, 255, 255, .18);
            color: #ffffff;
            box-shadow: none;
        }

        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, .18);
            border-color: rgba(255, 255, 255, .28);
            transform: none;
        }

        .sidebar-toggle svg {
            width: 19px;
            height: 19px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: transform .18s ease;
        }

        .brand-logo {
            justify-content: space-between;
        }

        .sidebar-controls {
            display: grid;
            justify-items: end;
            gap: 7px;
            flex: 0 0 auto;
        }

        .sidebar .brand,
        .sidebar .sidebar-footer {
            flex: 0 0 auto;
        }

        .sidebar .sidebar-nav {
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .32) transparent;
        }

        .sidebar .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar .sidebar-nav::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(255, 255, 255, .30);
        }

        .sidebar .sidebar-footer {
            margin-top: 0;
        }

        .sidebar .nav-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            align-items: stretch;
        }

        [data-sidebar-layout="vertical"] .sidebar .nav-list {
            grid-template-columns: 1fr;
        }

        .nav-group-items {
            grid-template-columns: 1fr;
        }

        .nav-group .nav-link {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: 7px;
            min-height: 46px;
            padding: 9px 8px;
            font-size: 12px;
            position: relative;
        }

        .nav-unread-dot,
        .new-info-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .16);
            flex: 0 0 auto;
        }

        .nav-unread-dot {
            position: absolute;
            top: 8px;
            right: 8px;
        }

        .new-info-dot {
            display: inline-flex;
            margin-left: 8px;
            vertical-align: middle;
        }

        .unread-row {
            background: rgba(239, 68, 68, .045);
        }

        .unread-row td:first-child {
            box-shadow: inset 3px 0 0 rgba(239, 68, 68, .70);
        }

        .nav-group .nav-icon {
            width: 28px;
            height: 28px;
            border-radius: 10px;
        }

        .nav-group .nav-icon svg {
            width: 16px;
            height: 16px;
        }

        :not([data-sidebar-layout="vertical"]):not([data-sidebar="collapsed"]) .sidebar .nav-group {
            align-self: stretch;
            height: 100%;
        }

        :not([data-sidebar-layout="vertical"]):not([data-sidebar="collapsed"]) .sidebar .nav-group.item-count-1 .nav-group-items {
            align-content: start;
        }

        :not([data-sidebar-layout="vertical"]):not([data-sidebar="collapsed"]) .sidebar .nav-group.item-count-1 .nav-link {
            align-content: initial;
            min-height: 46px;
        }

        :not([data-sidebar-layout="vertical"]):not([data-sidebar="collapsed"]) .sidebar .nav-group.item-count-1 .nav-icon {
            width: 28px;
            height: 28px;
        }

        :not([data-sidebar-layout="vertical"]):not([data-sidebar="collapsed"]) .sidebar .nav-group.item-count-1 .nav-icon svg {
            width: 16px;
            height: 16px;
        }

        [data-sidebar="collapsed"] .sidebar {
            padding-inline: 12px;
            gap: 14px;
        }

        [data-sidebar="collapsed"] .brand {
            padding-inline: 0;
        }

        [data-sidebar="collapsed"] .brand-logo {
            justify-content: center;
            gap: 0;
        }

        [data-sidebar="collapsed"] .brand-copy {
            display: none;
        }

        [data-sidebar="collapsed"] .brand-logo img {
            width: 54px;
            height: 54px;
        }

        [data-sidebar="collapsed"] .sidebar-controls {
            position: absolute;
            top: 27px;
            right: -18px;
            gap: 6px;
            z-index: 30;
            display: grid;
        }

        [data-sidebar="collapsed"] .sidebar-toggle {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            pointer-events: auto;
            background: var(--surface);
            border: 1px solid rgba(14, 102, 86, .20);
            color: var(--brand);
            box-shadow: 0 8px 20px rgba(14, 102, 86, .16);
        }

        [data-sidebar="collapsed"] .sidebar-toggle:hover {
            background: var(--brand);
            border-color: var(--brand);
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(14, 102, 86, .24);
        }

        [data-sidebar="collapsed"] .sidebar-toggle svg {
            transform: rotate(180deg);
        }

        [data-sidebar="collapsed"] .nav-list {
            grid-template-columns: 1fr;
            padding-right: 0;
        }

        [data-sidebar="collapsed"] .sidebar-layout-toggle {
            display: none;
        }

        [data-sidebar="collapsed"] .nav-group {
            border-left-width: 0;
            min-height: auto;
            padding: 8px;
        }

        [data-sidebar="collapsed"] .nav-group-title {
            justify-content: center;
        }

        [data-sidebar="collapsed"] .nav-group-title strong,
        [data-sidebar="collapsed"] .nav-label,
        [data-sidebar="collapsed"] .nav-step,
        [data-sidebar="collapsed"] .sidebar-footer .user-name,
        [data-sidebar="collapsed"] .sidebar-footer .user-meta {
            display: none;
        }

        [data-sidebar="collapsed"] .nav-group-title span {
            min-width: 30px;
        }

        [data-sidebar="collapsed"] .nav-group-items {
            grid-template-columns: 1fr;
        }

        [data-sidebar="collapsed"] .nav-group .nav-link {
            grid-template-columns: 1fr;
            justify-items: center;
            min-height: 44px;
            padding: 8px 6px;
        }

        [data-sidebar="collapsed"] .nav-group .nav-icon {
            width: 34px;
            height: 34px;
        }

        [data-sidebar="collapsed"] .sidebar-footer {
            padding: 10px 6px;
        }

        .sidebar-layout-toggle {
            display: inline-flex;
            width: 36px;
            height: 36px;
            justify-content: center;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 12px;
            padding: 0;
            background: rgba(255, 255, 255, .08);
        }

        .sidebar-layout-toggle button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            min-height: 36px;
            border-radius: 12px;
            padding: 0;
            background: transparent;
            border-color: transparent;
            color: rgba(255, 255, 255, .80);
            box-shadow: none;
        }

        .sidebar-layout-toggle button:hover {
            background: rgba(255, 255, 255, .12);
            box-shadow: none;
            transform: none;
        }

        .sidebar-layout-toggle button.is-active {
            background: rgba(255, 255, 255, .14);
            color: #ffffff;
        }

        .sidebar-layout-toggle svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .sidebar-layout-toggle .layout-icon-grid {
            display: none;
        }

        [data-sidebar-layout="vertical"] .sidebar-layout-toggle .layout-icon-grid {
            display: block;
        }

        [data-sidebar-layout="vertical"] .sidebar-layout-toggle .layout-icon-list {
            display: none;
        }

        [data-sidebar="collapsed"] .logout-button {
            min-height: 36px;
            padding: 8px 4px;
            font-size: 0;
        }

        [data-sidebar="collapsed"] .logout-button::before {
            content: "Keluar";
            font-size: 10px;
            font-weight: 800;
        }

        @media (max-width: 860px) {
            .app-shell {
                grid-template-columns: 1fr;
            }

            .instrument-hero,
            .instrument-filter-grid,
            .instrument-import-box {
                grid-template-columns: 1fr;
            }

            .instrument-workflow,
            .template-option-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .instrument-section-header,
            .standard-manager summary {
                flex-direction: column;
                align-items: stretch;
            }

            .instrument-section-header .actions,
            .instrument-filter-actions {
                justify-content: flex-start;
            }

            .instrument-master-filters {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .instrument-master-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .instrument-master-actions {
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .standard-manager-actions,
            .standard-manager > .table-wrap,
            .standard-manager > .warning {
                margin-left: 16px;
                margin-right: 16px;
            }

            .standard-manager > .table-wrap {
                width: calc(100% - 32px);
                max-width: calc(100% - 32px);
            }

            .excel-action-group,
            .excel-action {
                width: 100%;
            }

            .sidebar {
                min-height: auto;
                height: auto;
                position: static;
                overflow: visible;
            }

            .nav-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .sidebar .sidebar-nav {
                overflow: visible;
                padding-right: 0;
            }

            .sidebar-toggle {
                display: none;
            }

            [data-sidebar="collapsed"] .sidebar {
                padding: 24px 18px;
                gap: 22px;
            }

            [data-sidebar="collapsed"] .brand-logo {
                justify-content: space-between;
                gap: 12px;
            }

            [data-sidebar="collapsed"] .sidebar-controls {
                position: static;
            }

            [data-sidebar="collapsed"] .brand-copy,
            [data-sidebar="collapsed"] .nav-group-title strong,
            [data-sidebar="collapsed"] .nav-label,
            [data-sidebar="collapsed"] .sidebar-footer .user-name,
            [data-sidebar="collapsed"] .sidebar-footer .user-meta {
                display: block;
            }

            [data-sidebar="collapsed"] .brand-logo img {
                width: 58px;
                height: 58px;
            }

            [data-sidebar="collapsed"] .nav-group {
                border-left-width: 4px;
                min-height: 206px;
                padding: 10px;
            }

            [data-sidebar="collapsed"] .nav-group-title {
                justify-content: flex-start;
            }

            [data-sidebar="collapsed"] .nav-group .nav-link {
                grid-template-columns: auto minmax(0, 1fr);
                justify-items: stretch;
                min-height: 46px;
                padding: 9px 8px;
            }

            [data-sidebar="collapsed"] .logout-button {
                font-size: 14px;
                padding: 10px 14px;
            }

            [data-sidebar="collapsed"] .logout-button::before {
                content: none;
            }

            .nav-group-items {
                grid-template-columns: 1fr;
            }

            .nav-group {
                min-height: auto;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .topbar-actions {
                width: 100%;
            }

            .topbar-clock {
                order: -1;
            }

            .content {
                padding: 20px;
            }

            .form-grid,
            .form-section,
            .summary-grid,
            .dashboard-grid,
            .split-panel,
            .visual-grid,
            .timeline-row {
                grid-template-columns: 1fr;
            }

            .profile-photo-card {
                grid-template-columns: 1fr;
                justify-items: center;
                text-align: center;
            }

            .timeline-segments {
                grid-template-columns: 1fr;
            }

            .dashboard-hero-content {
                grid-template-columns: 1fr;
            }

            .quick-guide-panel {
                grid-template-columns: 1fr;
            }

            .quick-guide-steps {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .quick-guide-actions {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                min-width: 0;
            }

            .guide-hero,
            .unit-profile-hero,
            .task-hero,
            .task-card,
            .guide-layout,
            .guide-info-grid,
            .guide-note-grid {
                grid-template-columns: 1fr;
            }

            .task-hero-actions {
                justify-content: flex-start;
            }

            .task-card-side {
                align-content: stretch;
            }

            .task-actions {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .guide-smart-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .guide-index {
                position: static;
            }

            .admin-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .admin-visual-panel {
                min-height: auto;
            }

            .admin-visual-panel .panel-header,
            .admin-visual-panel .gauge-chart-wrap,
            .radar-panel .visual-chart {
                min-height: 0;
            }
        }

        @media (max-width: 560px) {
            .instrument-hero,
            .instrument-section,
            .template-modal-card,
            .import-modal-card {
                padding: 18px;
            }

            .instrument-stats,
            .instrument-workflow,
            .template-option-grid {
                grid-template-columns: 1fr;
            }

            .template-modal,
            .import-modal {
                padding: 12px;
                align-items: end;
            }

            .template-modal-card,
            .import-modal-card {
                max-height: calc(100vh - 24px);
                border-radius: 16px 16px 0 0;
            }

            .template-modal-header,
            .import-modal-header {
                gap: 12px;
            }

            .instrument-master-filters {
                grid-template-columns: 1fr;
            }

            .standard-manager-actions,
            .standard-manager > .table-wrap,
            .standard-manager > .warning {
                margin-left: 12px;
                margin-right: 12px;
            }

            .standard-manager > .table-wrap {
                width: calc(100% - 24px);
                max-width: calc(100% - 24px);
            }

            .nav-list {
                grid-template-columns: 1fr;
            }

            .quick-guide-steps,
            .quick-guide-actions {
                grid-template-columns: 1fr;
            }

            .guide-card-header {
                grid-template-columns: auto minmax(0, 1fr);
            }

            .guide-card-header .button {
                grid-column: 1 / -1;
                width: 100%;
                justify-content: center;
            }

            .guide-hero {
                padding: 22px;
            }

            .guide-smart-row {
                grid-template-columns: 1fr;
            }

            .guide-flow-step {
                flex-basis: 138px;
            }

            .task-card-main,
            .task-meta-grid,
            .task-progress-grid,
            .task-actions {
                grid-template-columns: 1fr;
            }

            .admin-kpi-grid {
                grid-template-columns: 1fr;
            }
        }

        [data-sidebar-layout="vertical"] .sidebar .nav-group {
            align-self: start;
            grid-template-rows: auto;
            min-height: auto;
        }

        [data-sidebar-layout="vertical"] .sidebar .nav-group-items {
            align-content: start;
        }

        .visual-grid {
            align-items: stretch;
            gap: 18px;
        }

        .visual-grid .section-block,
        .visual-chart,
        .visual-gauge {
            min-width: 0;
        }

        .visual-grid .section-block {
            display: grid;
            align-content: start;
            padding: 24px;
        }

        .visual-grid .section-block > .panel-title {
            margin: 0 0 18px;
            padding-inline: 4px;
            text-align: center;
        }

        .admin-visual-panel,
        .visual-grid .section-block {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(228, 242, 238, .74), rgba(255, 255, 255, .96) 42%, rgba(255, 248, 232, .66)),
                var(--surface);
        }

        .admin-visual-panel::before,
        .visual-grid .section-block::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(14, 102, 86, .055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(14, 102, 86, .045) 1px, transparent 1px);
            background-size: 26px 26px;
            mask-image: linear-gradient(130deg, #000, transparent 72%);
            pointer-events: none;
        }

        .admin-visual-panel > *,
        .visual-grid .section-block > * {
            position: relative;
            z-index: 1;
        }

        .gauge-chart-wrap {
            min-height: 285px;
            place-items: center;
        }

        .visual-grid .visual-gauge-panel {
            align-content: center;
            justify-items: center;
        }

        .visual-gauge {
            position: relative;
            isolation: isolate;
            width: 100%;
            justify-items: center;
            align-content: center;
            padding: 16px 0 6px;
        }

        .gauge-shell {
            display: none;
        }

        .gauge-ring {
            position: relative;
            z-index: 1;
            margin-inline: auto;
            width: clamp(164px, 18vw, 210px);
            background:
                radial-gradient(circle at center, var(--surface) 0 48%, transparent 49%),
                conic-gradient(var(--tone) calc(var(--value) * 1%), rgba(14, 102, 86, .10) 0);
            box-shadow:
                inset 0 0 0 1px rgba(14, 102, 86, .10),
                0 18px 42px rgba(14, 102, 86, .14);
        }

        .gauge-inner {
            width: 66%;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(250, 250, 248, .92));
            box-shadow:
                inset 0 0 0 1px rgba(14, 102, 86, .10),
                0 10px 24px rgba(14, 102, 86, .10);
        }

        .gauge-inner strong {
            color: var(--text);
            font-size: clamp(30px, 4vw, 42px);
        }

        .gauge-inner span {
            color: var(--brand);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .gauge-scale {
            display: flex;
            justify-content: space-between;
            width: min(210px, 100%);
            margin-top: 8px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
        }

        .visual-gauge > .muted {
            margin-top: 8px;
            text-align: center;
        }

        .gauge-footnote {
            border: 1px solid rgba(14, 102, 86, .12);
            border-radius: 14px;
            padding: 8px 14px;
            background: rgba(255, 255, 255, .68);
        }

        .radar-panel .visual-chart,
        .visual-chart {
            align-content: center;
            justify-items: center;
            width: 100%;
            padding-inline: 12px;
        }

        .radar-chart {
            width: min(100%, 390px);
            min-height: 282px;
            filter: drop-shadow(0 14px 24px rgba(14, 102, 86, .10));
        }

        .radar-chart circle {
            fill: rgba(255, 255, 255, .36);
            stroke: rgba(14, 102, 86, .13);
            stroke-width: 1;
        }

        .radar-chart line {
            stroke: rgba(14, 102, 86, .14);
            stroke-width: 1.1;
        }

        .radar-chart text {
            fill: var(--brand);
            font-size: 10px;
            font-weight: 900;
        }

        .radar-target {
            fill: rgba(232, 179, 106, .11);
            stroke: rgba(232, 179, 106, .66);
            stroke-dasharray: 5 4;
            stroke-width: 1.6;
        }

        .radar-value {
            stroke-width: 3;
            stroke-linejoin: round;
        }

        .radar-dot {
            fill: #ffffff;
            stroke: var(--brand);
            stroke-width: 2.4;
        }

        .score-orbit {
            display: grid;
            gap: 12px;
            width: 100%;
            max-width: 520px;
            padding: 4px 12px 8px;
        }

        .score-orbit-item {
            display: grid;
            gap: 10px;
            border: 1px solid rgba(14, 102, 86, .12);
            border-radius: 18px;
            padding: 14px;
            background:
                radial-gradient(circle at 92% 18%, rgba(232, 179, 106, .20), transparent 28%),
                linear-gradient(180deg, rgba(255, 255, 255, .88), rgba(248, 252, 250, .74));
            box-shadow: 0 12px 28px rgba(14, 102, 86, .08);
        }

        .score-orbit-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .score-orbit-head strong {
            color: var(--text);
            font-size: 16px;
        }

        .score-orbit-head span {
            color: var(--brand);
            font-size: 22px;
            font-weight: 900;
        }

        .score-track {
            height: 12px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(14, 102, 86, .10);
        }

        .score-track i {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--brand), var(--secondary), var(--accent));
            box-shadow: 0 0 0 1px rgba(255, 255, 255, .45) inset;
        }

        .score-orbit-item small {
            color: var(--muted);
            font-weight: 700;
        }

        .chart-legend {
            justify-content: center;
            border: 1px solid rgba(14, 102, 86, .10);
            border-radius: 999px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, .66);
        }

        .heatmap {
            border: 1px solid rgba(14, 102, 86, .10);
            border-radius: 18px;
            padding: 12px;
            background:
                radial-gradient(circle at 100% 0%, rgba(232, 179, 106, .14), transparent 30%),
                rgba(255, 255, 255, .68);
        }

        .heatmap-grid {
            gap: 10px;
            padding-bottom: 4px;
        }

        .heatmap-head,
        .heatmap-unit,
        .heatmap-cell {
            border-radius: 13px;
            min-height: 48px;
        }

        .heatmap-head {
            background: linear-gradient(135deg, var(--brand), var(--secondary));
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(14, 102, 86, .12);
        }

        .heatmap-unit {
            border: 1px solid rgba(14, 102, 86, .10);
            background: rgba(255, 255, 255, .76);
            color: var(--text);
        }

        .heatmap-cell {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .60);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .50), 0 8px 18px rgba(14, 102, 86, .07);
        }

        .heatmap-cell::after {
            content: "";
            position: absolute;
            inset: auto 8px 7px 8px;
            height: 3px;
            border-radius: 999px;
            background: currentColor;
            opacity: calc((var(--value, 0) / 100) + .18);
        }

        .progress {
            height: 13px;
            border: 1px solid rgba(14, 102, 86, .10);
            border-radius: 999px;
            background:
                linear-gradient(90deg, rgba(14, 102, 86, .08), rgba(232, 179, 106, .10)),
                var(--surface-soft);
            box-shadow: inset 0 1px 2px rgba(14, 102, 86, .08);
        }

        .progress-bar {
            border-radius: inherit;
            background:
                linear-gradient(90deg, var(--brand), var(--secondary) 58%, var(--accent)),
                var(--brand);
            box-shadow: 0 6px 16px rgba(14, 102, 86, .20);
        }

        [data-theme="dark"] .admin-visual-panel,
        [data-theme="dark"] .visual-grid .section-block,
        [data-theme="dark"] .score-orbit-item,
        [data-theme="dark"] .heatmap,
        [data-theme="dark"] .chart-legend,
        [data-theme="dark"] .gauge-footnote {
            background: rgba(16, 43, 38, .76);
            border-color: rgba(170, 198, 190, .16);
        }

        [data-theme="dark"] .gauge-inner,
        [data-theme="dark"] .heatmap-unit {
            background: rgba(11, 31, 27, .88);
        }

        [data-theme="dark"] .radar-chart circle {
            fill: rgba(18, 46, 40, .48);
            stroke: rgba(226, 245, 240, .14);
        }

        [data-theme="dark"] .radar-chart line {
            stroke: rgba(226, 245, 240, .12);
        }

        table thead th {
            background: var(--brand);
            color: #ffffff;
            border-bottom-color: rgba(255, 255, 255, .18);
        }

        table thead th:first-child {
            border-top-left-radius: 8px;
        }

        table thead th:last-child {
            border-top-right-radius: 8px;
        }

        [data-theme="dark"] table thead th {
            background: var(--brand-dark);
            color: #ffffff;
            border-bottom-color: rgba(255, 255, 255, .14);
        }

        [data-theme="dark"] .unread-row {
            background: rgba(239, 68, 68, .10);
        }

        /* Final header command-bar treatment */
        main {
            min-width: 0;
        }

        .topbar {
            position: relative;
            z-index: 60;
            min-height: 94px;
            margin: 0;
            padding: 18px 20px;
            border: 1px solid rgba(42, 190, 160, .22);
            border-left: 0;
            border-right: 0;
            border-top: 0;
            border-radius: 0;
            background:
                radial-gradient(circle at 10% 0%, rgba(45, 212, 191, .22), transparent 30%),
                radial-gradient(circle at 100% 18%, rgba(232, 179, 106, .16), transparent 28%),
                linear-gradient(135deg, rgba(7, 47, 42, .98), rgba(14, 102, 86, .96) 52%, rgba(29, 122, 107, .94));
            box-shadow: 0 18px 42px rgba(14, 102, 86, .16), inset 0 1px 0 rgba(255, 255, 255, .13);
            overflow: hidden;
        }

        .topbar::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(226, 245, 240, .055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(226, 245, 240, .045) 1px, transparent 1px);
            background-size: 30px 30px;
            mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent);
        }

        .topbar::after {
            content: "";
            position: absolute;
            inset: auto 22px 0;
            height: 2px;
            pointer-events: none;
            background: linear-gradient(90deg, transparent, rgba(45, 212, 191, .74), rgba(232, 179, 106, .56), transparent);
            opacity: .78;
        }

        .topbar-title-block,
        .topbar-actions {
            position: relative;
            z-index: 1;
        }

        .topbar-title-block {
            display: grid;
            gap: 5px;
            min-width: 0;
        }

        .topbar-title-row {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            flex-wrap: wrap;
        }

        .topbar-kicker {
            color: rgba(226, 245, 240, .72);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .topbar-system-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 24px;
            padding: 4px 9px;
            border: 1px solid rgba(45, 212, 191, .26);
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            color: #d8f4eb;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .topbar-system-chip::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #2dd4bf;
            box-shadow: 0 0 0 4px rgba(45, 212, 191, .12);
        }

        .page-title {
            color: #ffffff;
            font-size: clamp(22px, 2.2vw, 30px);
            font-weight: 900;
            letter-spacing: 0;
            line-height: 1.05;
            text-shadow: 0 8px 22px rgba(0, 0, 0, .18);
        }

        .topbar-greeting {
            color: rgba(226, 245, 240, .78);
            font-size: 13px;
            font-weight: 700;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            max-width: 100%;
            padding: 7px;
            border: 1px solid rgba(226, 245, 240, .16);
            border-radius: 22px;
            background: rgba(3, 32, 28, .28);
            backdrop-filter: blur(14px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08);
        }

        .topbar-clock {
            min-height: 42px;
            padding: 8px 13px;
            border: 1px solid rgba(226, 245, 240, .16);
            border-radius: 16px;
            background: rgba(255, 255, 255, .10);
            color: #ffffff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            letter-spacing: .02em;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .10);
        }

        .topbar-clock::before {
            width: 9px;
            height: 9px;
            background: #e8b36a;
            box-shadow: 0 0 0 4px rgba(232, 179, 106, .13);
        }

        .topbar-icon-button {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 16px;
            border: 1px solid rgba(226, 245, 240, .16);
            background: rgba(255, 255, 255, .10);
            color: #e2f5f0;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .10);
        }

        .topbar-icon-button:hover,
        .topbar-icon-button:focus-visible {
            transform: translateY(-1px);
            border-color: rgba(226, 245, 240, .34);
            box-shadow: 0 12px 24px rgba(0, 0, 0, .16), inset 0 1px 0 rgba(255, 255, 255, .14);
        }

        .topbar-icon-theme {
            background: rgba(232, 179, 106, .18);
            color: #ffe2a9;
        }

        .topbar-icon-notification {
            background: rgba(232, 130, 122, .18);
            color: #ffc0ba;
        }

        .topbar-icon-profile {
            background: rgba(148, 163, 184, .16);
            color: #eef2f7;
        }

        .topbar-actions .avatar {
            width: 42px;
            height: 42px;
            border: 2px solid rgba(226, 245, 240, .48);
            box-shadow: 0 0 0 4px rgba(226, 245, 240, .08);
        }

        .notification-badge {
            border-color: #0e6656;
            box-shadow: 0 4px 10px rgba(180, 35, 24, .22);
        }

        [data-theme="dark"] .topbar {
            border-color: rgba(45, 212, 191, .20);
            background:
                radial-gradient(circle at 10% 0%, rgba(45, 212, 191, .18), transparent 30%),
                radial-gradient(circle at 100% 18%, rgba(232, 179, 106, .12), transparent 28%),
                linear-gradient(135deg, rgba(6, 30, 26, .98), rgba(10, 74, 63, .96) 54%, rgba(14, 102, 86, .90));
            box-shadow: 0 18px 42px rgba(0, 0, 0, .28), inset 0 1px 0 rgba(255, 255, 255, .08);
        }

        [data-theme="dark"] .topbar-actions,
        [data-theme="dark"] .topbar-clock,
        [data-theme="dark"] .topbar-icon-button,
        [data-theme="dark"] .topbar-system-chip {
            border-color: rgba(226, 245, 240, .14);
            background-color: rgba(255, 255, 255, .08);
        }

        @media (max-width: 860px) {
            .topbar {
                margin: 0;
                padding: 16px;
                border-radius: 0;
            }

            .topbar-actions {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .topbar-clock {
                order: 0;
                flex: 1 1 220px;
                justify-content: center;
            }
        }

        @media (max-width: 560px) {
            .topbar-actions {
                display: grid;
                grid-template-columns: repeat(3, 42px) minmax(0, 1fr) 42px;
            }

            .topbar-clock {
                grid-column: 1 / -1;
                order: -1;
            }
        }
    </style>
</head>
<body>
    @php
        $currentUser = auth()->user();
        $currentRole = $currentUser->role;
        $navIcons = [
            'Dashboard' => 'dashboard',
            'Panduan' => 'help',
            'Periode Audit' => 'calendar',
            'Unit dan Pengguna' => 'users',
            'Standar dan Instrumen' => 'book',
            'Instrumen AMI' => 'book',
            'Penugasan Audit' => 'clipboard',
            'Monitoring' => 'activity',
            'Laporan' => 'file',
            'Pengaturan' => 'settings',
            'Tugas Audit' => 'list',
            'Desk Evaluation' => 'search',
            'Klarifikasi' => 'message',
            'Visitasi' => 'map',
            'Temuan' => 'alert',
            'Verifikasi Tindak Lanjut' => 'check',
            'Verifikasi Perbaikan' => 'check',
            'Laporan Saya' => 'file',
            'Profil Unit' => 'building',
            'Evaluasi Diri' => 'edit',
            'Bukti Dokumen' => 'paperclip',
            'Klarifikasi Auditor' => 'message',
            'Jadwal Visitasi' => 'calendar',
            'Temuan dan Tindak Lanjut' => 'refresh',
            'Tindak Lanjut Temuan' => 'refresh',
            'Laporan Unit' => 'file',
        ];
        \App\Models\Notification::archiveExpired();
        markViewedNotificationsForCurrentPage($currentUser);
        $unreadNotificationCount = \App\Models\Notification::query()
            ->where('user_id', $currentUser->id)
            ->active()
            ->unread()
            ->count();
        $latestNotifications = \App\Models\Notification::query()
            ->where('user_id', $currentUser->id)
            ->active()
            ->latest()
            ->limit(10)
            ->get();
    @endphp

    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo">
                    <img src="{{ route('brand.logo') }}" alt="Logo JDS">
                    <div class="brand-copy">
                        <x-brand-wordmark tone="sidebar" :width="206" />
                        <p class="brand-subtitle">Sistem Informasi Audit Mutu Internal</p>
                    </div>
                    <div class="sidebar-controls">
                        <button class="sidebar-toggle" type="button" data-sidebar-toggle title="Minimize sidebar" aria-label="Minimize sidebar">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M15 18l-6-6 6-6"></path>
                            </svg>
                        </button>
                        <div class="sidebar-layout-toggle" aria-label="Mode tampilan sidebar">
                            <button type="button" data-sidebar-layout-toggle title="Ganti bentuk sidebar" aria-label="Ganti bentuk sidebar">
                                <svg class="layout-icon-grid" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="4" y="4" width="7" height="7" rx="1"></rect>
                                    <rect x="13" y="4" width="7" height="7" rx="1"></rect>
                                    <rect x="4" y="13" width="7" height="7" rx="1"></rect>
                                    <rect x="13" y="13" width="7" height="7" rx="1"></rect>
                                </svg>
                                <svg class="layout-icon-list" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8 6h12"></path><path d="M8 12h12"></path><path d="M8 18h12"></path><path d="M4 6h.01"></path><path d="M4 12h.01"></path><path d="M4 18h.01"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <nav class="nav-list" aria-label="Navigasi utama">
                    @foreach ($currentRole->sidebarGroups() as $groupIndex => $group)
                        <div class="nav-group tone-{{ $group['tone'] ?? 'overview' }} item-count-{{ count($group['items']) }}">
                            <div class="nav-group-title">
                                <span>{{ chr(65 + $groupIndex) }}</span>
                                <strong>{{ $group['label'] }}</strong>
                            </div>
                            <div class="nav-group-items">
                                @foreach ($group['items'] as $itemIndex => $item)
                                    @php
                                        $icon = $navIcons[$item['label']] ?? 'circle';
                                        $isMenuActive = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*');
                                        $menuUnreadCount = $isMenuActive ? 0 : unreadNotificationCountForMenu($item['route'], $currentUser);
                                    @endphp
                                    <a class="nav-link @if ($isMenuActive) active @endif @if ($menuUnreadCount > 0) has-unread @endif" href="{{ route($item['route']) }}">
                                        <span class="nav-step">{{ $itemIndex + 1 }}</span>
                                        <span class="nav-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24">
                                            @switch($icon)
                                                @case('dashboard')
                                                    <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect>
                                                    @break
                                                @case('help')
                                                    <circle cx="12" cy="12" r="9"></circle><path d="M9.8 9a2.4 2.4 0 0 1 4.6 1c0 1.8-2.4 2.1-2.4 4"></path><path d="M12 17h.01"></path>
                                                    @break
                                                @case('calendar')
                                                    <rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>
                                                    @break
                                                @case('users')
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                    @break
                                                @case('book')
                                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"></path>
                                                    @break
                                                @case('clipboard')
                                                    <rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><path d="M8 12h8M8 16h6"></path>
                                                    @break
                                                @case('activity')
                                                    <path d="M22 12h-4l-3 8L9 4l-3 8H2"></path>
                                                    @break
                                                @case('file')
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6M8 13h8M8 17h6"></path>
                                                    @break
                                                @case('settings')
                                                    <circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-.4-1.1 1.7 1.7 0 0 0-1-.6 1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1.1-.4 1.7 1.7 0 0 0 .6-1 1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 .4 1.1 1.7 1.7 0 0 0 1 .6 1.7 1.7 0 0 0 1.88-.34l.06-.06A2 2 0 1 1 21 6.23l-.06.06A1.7 1.7 0 0 0 19.4 9c.2.3.4.6.6 1h1a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.1.4 1.7 1.7 0 0 0-.41.6z"></path>
                                                    @break
                                                @case('list')
                                                    <path d="M8 6h13M8 12h13M8 18h13"></path><path d="M3 6h.01M3 12h.01M3 18h.01"></path>
                                                    @break
                                                @case('search')
                                                    <circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3"></path>
                                                    @break
                                                @case('message')
                                                    <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
                                                    @break
                                                @case('map')
                                                    <path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"></path><circle cx="12" cy="10" r="2.5"></circle>
                                                    @break
                                                @case('alert')
                                                    <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path><path d="M12 9v4M12 17h.01"></path>
                                                    @break
                                                @case('check')
                                                    <path d="M9 12l2 2 4-5"></path><rect x="3" y="3" width="18" height="18" rx="4"></rect>
                                                    @break
                                                @case('building')
                                                    <path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path><path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"></path>
                                                    @break
                                                @case('edit')
                                                    <path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path>
                                                    @break
                                                @case('paperclip')
                                                    <path d="m21.4 11.6-8.5 8.5a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 1 1-2.8-2.8l8.5-8.5"></path>
                                                    @break
                                                @case('refresh')
                                                    <path d="M21 12a9 9 0 0 1-15.5 6.2L3 16"></path><path d="M3 21v-5h5"></path><path d="M3 12A9 9 0 0 1 18.5 5.8L21 8"></path><path d="M21 3v5h-5"></path>
                                                    @break
                                                @default
                                                    <circle cx="12" cy="12" r="8"></circle>
                                            @endswitch
                                            </svg>
                                        </span>
                                        <span class="nav-label">{{ $item['label'] }}</span>
                                        @if ($menuUnreadCount > 0)
                                            <span class="nav-unread-dot" title="{{ $menuUnreadCount }} informasi baru" aria-label="{{ $menuUnreadCount }} informasi baru"></span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>
            </div>

            <div class="sidebar-footer">
                <div>
                    <div class="user-name">{{ $currentUser->name }}</div>
                    <div class="user-meta">{{ $currentRole->label() }}</div>
                </div>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">Keluar</button>
                </form>
            </div>
        </aside>

        <main>
            <header class="topbar">
                <div class="topbar-title-block">
                    <div class="topbar-title-row">
                        <span class="topbar-kicker">SMART SIAMI Command</span>
                        <span class="topbar-system-chip">Online</span>
                    </div>
                    <h2 class="page-title">@yield('page_title', 'SMART SIAMI')</h2>
                    <span class="topbar-greeting">Halo, {{ $currentUser->name }}. Semoga harimu produktif.</span>
                </div>
                <div class="topbar-actions">
                    <button class="topbar-icon-button topbar-icon-theme theme-toggle" type="button" data-theme-toggle title="Ganti tema" aria-label="Ganti tema">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 3a6 6 0 0 0 9 7 9 9 0 1 1-9-7Z"></path>
                        </svg>
                    </button>
                    <span class="topbar-clock" data-live-clock>{{ now()->translatedFormat('l, d F Y H:i') }}</span>
                    <details class="notification-menu">
                        <summary class="topbar-icon-button topbar-icon-notification" title="Notifikasi" aria-label="Notifikasi">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            @if ($unreadNotificationCount > 0)
                                <span class="notification-badge" data-notification-badge>{{ $unreadNotificationCount }}</span>
                            @endif
                        </summary>
                        <div class="notification-dropdown">
                            <div class="dashboard-list" data-notification-list>
                                @forelse ($latestNotifications as $notification)
                                    <div class="list-item notification-list-row">
                                        <a class="notification-list-link" href="{{ route('notifications.open', $notification) }}">
                                            <strong>{{ $notification->judul }}</strong>
                                            <div class="muted">{{ str($notification->isi)->limit(90) }}</div>
                                            <span class="badge {{ $notification->is_read ? 'neutral' : 'danger' }}">{{ $notification->created_at->format('d/m H:i') }}</span>
                                        </a>
                                        <form method="post" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Hapus notifikasi ini?')">
                                            @csrf
                                            @method('delete')
                                            <button class="button secondary notification-delete-button" type="submit" aria-label="Hapus notifikasi">&times;</button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="list-item muted">Belum ada notifikasi.</div>
                                @endforelse
                                <a class="link-button" href="{{ route('notifications.index') }}">Lihat Semua Notifikasi</a>
                            </div>
                        </div>
                    </details>
                    <x-visual.avatar
                        :name="$currentUser->name"
                        size="md"
                        :photo-url="$currentUser->profile_photo_path ? route('profile.photo.show', $currentUser) : null"
                        :focus-x="$currentUser->profile_photo_focus_x ?? 50"
                        :focus-y="$currentUser->profile_photo_focus_y ?? 50"
                    />
                    <a class="topbar-icon-button topbar-icon-profile" href="{{ route('profile.edit') }}" title="Profil akun" aria-label="Profil akun">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20 21a8 8 0 0 0-16 0"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                </div>
            </header>

            <section class="content">
                @yield('content')
            </section>
        </main>
    </div>
    <div class="toast-stack" aria-live="polite">
        @if (session('status'))
            <div class="toast">{{ session('status') }}</div>
        @endif
        @if (session('warning'))
            <div class="toast warning">{{ session('warning') }}</div>
        @endif
    </div>
    <div class="danger-confirm-modal" data-danger-confirm-modal hidden>
        <div class="danger-confirm-backdrop" data-danger-confirm-cancel></div>
        <section class="danger-confirm-card" role="dialog" aria-modal="true" aria-labelledby="dangerConfirmTitle">
            <div class="danger-confirm-icon">!</div>
            <h3 id="dangerConfirmTitle" data-danger-confirm-title>Konfirmasi hapus</h3>
            <p data-danger-confirm-message>Tindakan ini perlu dikonfirmasi.</p>
            <div class="danger-confirm-actions">
                <button class="button secondary" type="button" data-danger-confirm-cancel>Batal</button>
                <button class="danger-confirm-submit" type="button" disabled data-danger-confirm-submit>Ya, Hapus</button>
            </div>
        </section>
    </div>
    <script>
        (() => {
            const storedTheme = localStorage.getItem('siami-theme');
            if (storedTheme === 'dark') {
                document.documentElement.dataset.theme = 'dark';
            }

            const storedSidebar = localStorage.getItem('siami-sidebar');
            if (storedSidebar === 'collapsed') {
                document.documentElement.dataset.sidebar = 'collapsed';
            }

            const storedSidebarLayout = localStorage.getItem('siami-sidebar-layout');
            if (storedSidebarLayout === 'vertical') {
                document.documentElement.dataset.sidebarLayout = 'vertical';
            } else {
                document.documentElement.dataset.sidebarLayout = 'grid';
            }

            document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
                const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
                document.documentElement.dataset.theme = next === 'dark' ? 'dark' : '';
                localStorage.setItem('siami-theme', next);
            });

            document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => {
                const collapsed = document.documentElement.dataset.sidebar === 'collapsed';
                document.documentElement.dataset.sidebar = collapsed ? '' : 'collapsed';
                localStorage.setItem('siami-sidebar', collapsed ? 'expanded' : 'collapsed');
            });

            document.querySelector('[data-sidebar-layout-toggle]')?.addEventListener('click', () => {
                const next = document.documentElement.dataset.sidebarLayout === 'vertical' ? 'grid' : 'vertical';
                document.documentElement.dataset.sidebarLayout = next;
                localStorage.setItem('siami-sidebar-layout', next);
            });

            const liveClock = document.querySelector('[data-live-clock]');
            if (liveClock) {
                const formatClock = () => {
                    const now = new Date();
                    const day = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long',
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                    }).format(now);
                    const time = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                    }).format(now);
                    liveClock.textContent = `${day} ${time}`;
                };

                formatClock();
                setInterval(formatClock, 1000);
            }

            const importOpenButtons = document.querySelectorAll('[data-import-modal-open]');
            const importModals = document.querySelectorAll('[data-import-modal]');
            let activeImportButton = null;
            const closeImportModal = (modal) => {
                if (! modal) {
                    return;
                }

                modal.hidden = true;
                document.body.classList.remove('modal-open');
                activeImportButton?.focus();
                activeImportButton = null;
            };

            importOpenButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const modal = document.querySelector(`[data-import-modal="${button.dataset.importModalOpen}"]`);
                    if (! modal) {
                        return;
                    }

                    activeImportButton = button;
                    modal.hidden = false;
                    document.body.classList.add('modal-open');
                    modal.querySelector('input[type="file"]')?.focus();
                });
            });

            importModals.forEach((modal) => {
                modal.querySelectorAll('[data-import-modal-close]').forEach((button) => {
                    button.addEventListener('click', () => closeImportModal(modal));
                });

                const dropZone = modal.querySelector('[data-import-drop]');
                const fileInput = modal.querySelector('[data-import-file-input]');
                const fileName = modal.querySelector('[data-import-file-name]');

                if (! dropZone || ! fileInput || ! fileName) {
                    return;
                }

                const updateFileName = () => {
                    fileName.textContent = fileInput.files?.[0]?.name ?? 'Belum ada file dipilih';
                };

                const stopDrag = (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                };

                ['dragenter', 'dragover'].forEach((eventName) => {
                    dropZone.addEventListener(eventName, (event) => {
                        stopDrag(event);
                        dropZone.classList.add('is-dragging');
                    });
                });

                ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
                    dropZone.addEventListener(eventName, (event) => {
                        stopDrag(event);
                        dropZone.classList.remove('is-dragging');
                    });
                });

                dropZone.addEventListener('drop', (event) => {
                    const files = event.dataTransfer?.files;
                    if (! files || files.length === 0) {
                        return;
                    }

                    fileInput.files = files;
                    updateFileName();
                });

                fileInput.addEventListener('change', updateFileName);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') {
                    return;
                }

                importModals.forEach((modal) => {
                    if (! modal.hidden) {
                        closeImportModal(modal);
                    }
                });
            });

            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    window.location.reload();
                }
            });

            let syncVersion = null;
            let formDirty = false;
            const syncUrl = @json(route('sync-state'));
            const refreshablePath = () => {
                const path = window.location.pathname;
                return !/(\/create|\/edit|\/profile|\/pengaturan|\/laporan.*\/preview)/.test(path);
            };
            const notifyUser = (message) => {
                const stack = document.querySelector('.toast-stack');
                if (! stack) {
                    return;
                }

                const toast = document.createElement('div');
                toast.className = 'toast warning';
                toast.textContent = message;
                stack.appendChild(toast);
                setTimeout(() => toast.remove(), 5200);
            };

            document.querySelectorAll('input, textarea, select').forEach((field) => {
                field.addEventListener('change', () => { formDirty = true; }, { once: true });
                field.addEventListener('input', () => { formDirty = true; }, { once: true });
            });

            const dangerConfirmModal = document.querySelector('[data-danger-confirm-modal]');
            const dangerConfirmTitle = document.querySelector('[data-danger-confirm-title]');
            const dangerConfirmMessage = document.querySelector('[data-danger-confirm-message]');
            const dangerConfirmSubmit = document.querySelector('[data-danger-confirm-submit]');
            const dangerConfirmCancel = document.querySelectorAll('[data-danger-confirm-cancel]');
            let dangerConfirmTimer = null;
            let pendingDangerForm = null;
            let pendingDangerSubmitter = null;

            const closeDangerConfirm = () => {
                if (! dangerConfirmModal) {
                    return;
                }

                dangerConfirmModal.hidden = true;
                pendingDangerForm = null;
                pendingDangerSubmitter = null;
                if (dangerConfirmTimer) {
                    clearInterval(dangerConfirmTimer);
                    dangerConfirmTimer = null;
                }
            };

            const openDangerConfirm = (form, submitter) => {
                if (! dangerConfirmModal || ! dangerConfirmSubmit || ! dangerConfirmTitle || ! dangerConfirmMessage) {
                    form.submit();
                    return;
                }

                pendingDangerForm = form;
                pendingDangerSubmitter = submitter;
                const source = submitter?.matches('[data-danger-confirm]') ? submitter : form;
                const seconds = Number(source.dataset.dangerCountdown ?? 5);
                let remaining = Number.isFinite(seconds) ? Math.max(3, Math.min(10, seconds)) : 5;
                const confirmLabel = source.dataset.dangerConfirmLabel ?? 'Ya, Lanjutkan';

                dangerConfirmTitle.textContent = source.dataset.dangerTitle ?? 'Konfirmasi tindakan';
                dangerConfirmMessage.textContent = source.dataset.dangerMessage ?? 'Tindakan ini tidak bisa dibatalkan setelah dikonfirmasi.';
                dangerConfirmSubmit.disabled = true;
                dangerConfirmSubmit.textContent = `${confirmLabel} (${remaining})`;
                dangerConfirmModal.hidden = false;

                dangerConfirmTimer = setInterval(() => {
                    remaining -= 1;
                    if (remaining <= 0) {
                        clearInterval(dangerConfirmTimer);
                        dangerConfirmTimer = null;
                        dangerConfirmSubmit.disabled = false;
                        dangerConfirmSubmit.textContent = confirmLabel;
                        dangerConfirmSubmit.focus();
                        return;
                    }

                    dangerConfirmSubmit.textContent = `${confirmLabel} (${remaining})`;
                }, 1000);
            };

            document.addEventListener('submit', (event) => {
                const form = event.target;
                const submitter = event.submitter;
                const needsConfirm = submitter?.matches?.('[data-danger-confirm]') || form.matches?.('[data-danger-confirm]');

                if (! needsConfirm) {
                    return;
                }

                if (form.dataset.dangerConfirmed === '1') {
                    delete form.dataset.dangerConfirmed;
                    return;
                }

                event.preventDefault();
                openDangerConfirm(form, submitter);
            });

            dangerConfirmCancel.forEach((button) => button.addEventListener('click', closeDangerConfirm));
            dangerConfirmSubmit?.addEventListener('click', () => {
                if (! pendingDangerForm) {
                    closeDangerConfirm();
                    return;
                }

                const form = pendingDangerForm;
                const submitter = pendingDangerSubmitter;
                form.dataset.dangerConfirmed = '1';
                closeDangerConfirm();

                if (submitter && typeof form.requestSubmit === 'function') {
                    form.requestSubmit(submitter);
                    return;
                }

                form.submit();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && dangerConfirmModal && ! dangerConfirmModal.hidden) {
                    closeDangerConfirm();
                }
            });

            document.querySelectorAll('.filters').forEach((filter) => {
                const fields = [...filter.querySelectorAll(':scope > .form-field')];
                if (fields.length <= 3 || filter.dataset.noCollapse === '1') {
                    return;
                }

                const extraFields = fields.slice(2);
                const hasActiveExtra = extraFields.some((field) => {
                    const control = field.querySelector('input, select, textarea');
                    if (! control) {
                        return false;
                    }

                    if (control.type === 'checkbox' || control.type === 'radio') {
                        return control.checked;
                    }

                    return String(control.value ?? '').trim() !== '';
                });

                const toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'button secondary filter-toggle';
                toggle.textContent = 'Filter Lanjutan';
                toggle.setAttribute('aria-expanded', hasActiveExtra ? 'true' : 'false');

                const syncExtraFilters = () => {
                    const expanded = toggle.getAttribute('aria-expanded') === 'true';
                    extraFields.forEach((field) => {
                        field.hidden = ! expanded;
                        field.style.display = expanded ? '' : 'none';
                    });
                    toggle.textContent = expanded ? 'Sembunyikan Filter' : 'Filter Lanjutan';
                };

                const firstButton = filter.querySelector('button[type="submit"], .button');
                filter.insertBefore(toggle, firstButton ?? null);
                toggle.addEventListener('click', () => {
                    const expanded = toggle.getAttribute('aria-expanded') === 'true';
                    toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    syncExtraFilters();
                });
                syncExtraFilters();
            });

            document.querySelectorAll('[data-bulk-action-bar]').forEach((bulkBar) => {
                const formId = bulkBar.id;
                if (! formId) {
                    return;
                }

                const scope = bulkBar.closest('.panel, .instrument-section, .standard-manager') ?? document;
                const checkboxes = [...scope.querySelectorAll(`[form="${formId}"][data-bulk-select]`)];
                const selectAll = scope.querySelector('[data-bulk-select-all]');
                const counter = bulkBar.querySelector('[data-bulk-selected-count]');
                const buttons = [...bulkBar.querySelectorAll('[data-bulk-action-button]')];

                if (checkboxes.length === 0 || ! counter) {
                    return;
                }

                const refreshBulkBar = () => {
                    const enabledCheckboxes = checkboxes.filter((checkbox) => ! checkbox.disabled);
                    const selected = enabledCheckboxes.filter((checkbox) => checkbox.checked).length;
                    bulkBar.hidden = selected === 0;
                    counter.textContent = selected;
                    buttons.forEach((button) => {
                        button.disabled = selected === 0;

                        const template = button.dataset.dangerMessageTemplate;
                        if (template) {
                            button.dataset.dangerMessage = template.replace('{count}', selected);
                        }
                    });

                    if (selectAll) {
                        selectAll.checked = enabledCheckboxes.length > 0 && selected === enabledCheckboxes.length;
                        selectAll.indeterminate = selected > 0 && selected < enabledCheckboxes.length;
                    }
                };

                selectAll?.addEventListener('change', () => {
                    checkboxes.forEach((checkbox) => {
                        if (! checkbox.disabled) {
                            checkbox.checked = selectAll.checked;
                        }
                    });
                    refreshBulkBar();
                });

                checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshBulkBar));
                refreshBulkBar();
            });

            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            const renderNotifications = (payload) => {
                const summary = document.querySelector('.notification-menu summary');
                const list = document.querySelector('[data-notification-list]');
                if (! summary || ! list) {
                    return;
                }

                let badge = document.querySelector('[data-notification-badge]');
                if (payload.unread_count > 0) {
                    if (! badge) {
                        badge = document.createElement('span');
                        badge.className = 'notification-badge';
                        badge.dataset.notificationBadge = '';
                        summary.appendChild(badge);
                    }
                    badge.textContent = payload.unread_count;
                } else if (badge) {
                    badge.remove();
                }

                const items = payload.notifications.length
                    ? payload.notifications.map((notification) => `
                        <div class="list-item notification-list-row">
                            <a class="notification-list-link" href="${escapeHtml(notification.url)}">
                                <strong>${escapeHtml(notification.title)}</strong>
                                <div class="muted">${escapeHtml(notification.body)}</div>
                                <span class="badge ${notification.is_read ? 'neutral' : 'danger'}">${notification.time}</span>
                            </a>
                            <form method="post" action="${escapeHtml(notification.delete_url)}" onsubmit="return confirm('Hapus notifikasi ini?')">
                                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                                <input type="hidden" name="_method" value="delete">
                                <button class="button secondary notification-delete-button" type="submit" aria-label="Hapus notifikasi">&times;</button>
                            </form>
                        </div>
                    `).join('')
                    : '<div class="list-item muted">Belum ada notifikasi.</div>';

                list.innerHTML = `${items}<a class="link-button" href="{{ route('notifications.index') }}">Lihat Semua Notifikasi</a>`;
            };

            const syncState = async (allowReload = false) => {
                try {
                    const response = await fetch(syncUrl, {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store',
                    });
                    if (! response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    renderNotifications(payload);

                    if (syncVersion === null) {
                        syncVersion = payload.version;
                        return;
                    }

                    if (payload.version !== syncVersion) {
                        syncVersion = payload.version;
                        if (allowReload && refreshablePath() && ! formDirty) {
                            window.location.reload();
                        } else {
                            notifyUser('Ada pembaruan data. Muat ulang halaman untuk melihat data terbaru.');
                        }
                    }
                } catch (error) {
                    // Sinkronisasi bersifat pelengkap; kegagalan jaringan tidak perlu mengganggu kerja utama.
                }
            };

            syncState(false);
            setInterval(() => syncState(true), 15000);
            window.addEventListener('focus', () => syncState(true));
            document.addEventListener('visibilitychange', () => {
                if (! document.hidden) {
                    syncState(true);
                }
            });

            document.querySelectorAll('[data-kanban-card]').forEach((card) => {
                card.addEventListener('dragstart', () => card.classList.add('dragging'));
                card.addEventListener('dragend', () => card.classList.remove('dragging'));
            });

            document.querySelectorAll('[data-kanban-column]').forEach((column) => {
                column.addEventListener('dragover', (event) => event.preventDefault());
                column.addEventListener('drop', (event) => {
                    event.preventDefault();
                    const toast = document.createElement('div');
                    toast.className = 'toast warning';
                    toast.textContent = 'Perubahan status temuan tetap mengikuti proses detail, tindak lanjut, dan verifikasi.';
                    document.querySelector('.toast-stack')?.appendChild(toast);
                    setTimeout(() => toast.remove(), 4200);
                });
            });

            setTimeout(() => {
                document.querySelectorAll('.toast').forEach((toast) => toast.remove());
            }, 5200);
        })();
    </script>
    @stack('scripts')
</body>
</html>
