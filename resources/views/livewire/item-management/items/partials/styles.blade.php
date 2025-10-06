<style>
    .varibal-grid-table {
        border-collapse: separate;
        border-spacing: 2px;
        background-color: #e3f2fd;
    }

    .varibal-grid-table th {
        background-color: #007bff !important;
        color: white !important;
        font-weight: bold;
        padding: 12px 8px;
        border: 1px solid #0056b3;
        min-width: 80px;
        text-align: center;
    }

    .varibal-grid-table td {
        border: 1px solid #dee2e6;
        padding: 8px;
        vertical-align: middle;
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
        text-align: center;
    }

    .varibal-grid-table td:hover {
        background-color: #e9ecef;
    }

    .varibal-checkbox {
        transform: scale(1.3);
        cursor: pointer;
    }

    .varibal-checkbox:checked {
        background-color: #007bff;
        border-color: #007bff;
    }

    /* RTL Support */
    .varibal-grid-table {
        direction: rtl;
    }

    /* Combination Cards */
    .combination-card {
        transition: all 0.2s ease;
        border: 1px solid #dee2e6;
        min-height: 60px;
    }

    .combination-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .combination-card .btn {
        min-width: 30px;
        height: 30px;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .combination-card .btn i {
        font-size: 0.75rem;
    }

    .combination-card .badge {
        word-break: break-word;
        max-width: 100%;
        display: inline-block;
    }

    /* Barcode Container Styles */
    .barcode-container {
        min-width: 200px;
        max-width: 250px;
    }

    .barcode-input-group {
        transition: all 0.2s ease;
    }

    .barcode-input-group:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .barcode-input {
        border-left: none;
        border-right: none;
        transition: all 0.2s ease;
    }

    .barcode-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .barcode-input-group .input-group-text {
        border-color: #dee2e6;
        background-color: #f8f9fa;
        font-size: 0.875rem;
    }

    .barcode-input-group .input-group-text i {
        font-size: 1rem;
    }

    /* Barcode Add Button */
    .barcode-container .btn-outline-success {
        border-style: dashed;
        border-width: 2px;
        transition: all 0.2s ease;
    }

    .barcode-container .btn-outline-success:hover {
        border-style: solid;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(25, 135, 84, 0.25);
    }

    /* Barcode Remove Button */
    .barcode-input-group .btn-outline-danger {
        transition: all 0.2s ease;
    }

    .barcode-input-group .btn-outline-danger:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.25);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .varibal-grid-table {
            font-size: 12px;
        }

        .varibal-grid-table th,
        .varibal-grid-table td {
            padding: 6px 4px;
            min-width: 60px;
        }

/* Always show vertical scrollbar on this page to prevent layout shift and ensure scrolling */
html { overflow-y: scroll; }
body { overflow-y: auto !important; }
/* Even when Bootstrap adds modal-open, keep page scroll available */
body.modal-open { overflow-y: auto !important; }

        .varibal-checkbox {
            transform: scale(1.1);
        }

        .combination-card {
            margin-bottom: 10px;
        }
    }
</style>