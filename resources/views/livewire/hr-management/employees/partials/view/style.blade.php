<style>
    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-on-scroll {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Hover Effects */
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }

    .employee-image {
        transition: transform 0.3s ease;
    }

    /* Info Item Styling */
    .info-item {
        padding: 0.5rem 0;
    }

    .info-item label {
        font-size: 0.85rem;
    }

    .info-item p {
        font-size: 0.95rem;
    }

    /* Print Styles */
    @media print {
        .btn, .employee-view-container .btn {
            display: none !important;
        }
        
        .card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        
        .animate-on-scroll {
            animation: none;
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .employee-avatar img {
            width: 60px !important;
            height: 60px !important;
        }
        
        .employee-image {
            width: 150px !important;
            height: 150px !important;
        }
    }

    /* Badge Colors */
    .bg-pink {
        background-color: #e91e63 !important;
    }
    
    /* Active tabs styling - Theme color */
    .nav-tabs .nav-link.active {
        background-color: var(--color-primary-100, #b3f0e0) !important;
        color: var(--color-primary-800, #15674b) !important;
        border-color: var(--color-primary-200, #80e6cb) !important;
        font-weight: 700;
    }
    
    /* Hover effect for active tabs */
    .nav-tabs .nav-link.active:hover {
        background-color: var(--color-primary-200, #80e6cb) !important;
        color: var(--color-primary-900, #0e4c35) !important;
    }
</style>