<li class="nav-item dropdown">
    <a class="nav-link d-flex align-items-center dropdown-toggle px-3 py-2 text-dark fw-medium" href="#"
        id="langDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"
        style="transition: all 0.2s ease; border-radius: 6px;">
        @if (app()->getLocale() === 'ar')
            <img src="https://flagcdn.com/w20/eg.png" class="me-2"
                style="width: 20px; height: 15px; border-radius: 2px; object-fit: cover;" alt="العربية">
            <span class="d-none d-md-inline">العربية</span>
        @else
            <img src="https://flagcdn.com/w20/gb.png" class="me-2"
                style="width: 20px; height: 15px; border-radius: 2px; object-fit: cover;" alt="English">
            <span class="d-none d-md-inline">English</span>
        @endif
        <i class="fas fa-chevron-down ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i>
    </a>

    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2"
        style="border-radius: 8px; min-width: 140px; padding: 0.5rem 0;" aria-labelledby="langDropdown">
        <li>
            <a class="dropdown-item d-flex align-items-center px-3 py-2 text-dark"
                href="{{ route('locale.switch', ['locale' => 'ar']) }}"
                style="transition: all 0.2s ease; border-radius: 4px; margin: 0 0.25rem;">
                <img src="https://flagcdn.com/w20/eg.png" class="me-2"
                    style="width: 18px; height: 13px; border-radius: 2px; object-fit: cover;" alt="العربية">
                <span class="fw-medium">العربية</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center px-3 py-2 text-dark"
                href="{{ route('locale.switch', ['locale' => 'en']) }}"
                style="transition: all 0.2s ease; border-radius: 4px; margin: 0 0.25rem;">
                <img src="https://flagcdn.com/w20/gb.png" class="me-2"
                    style="width: 18px; height: 13px; border-radius: 2px; object-fit: cover;" alt="English">
                <span class="fw-medium">English</span>
            </a>
        </li>
    </ul>
</li>

<style>
    /* Professional hover effects */
    #langDropdown:hover {
        background-color: rgba(0, 0, 0, 0.05) !important;
        color: #0066cc !important;
    }

    .dropdown-item:hover {
        background-color: rgba(0, 102, 204, 0.1) !important;
        color: #0066cc !important;
        transform: translateX(2px);
    }

    .dropdown-item:active {
        background-color: rgba(0, 102, 204, 0.15) !important;
    }

    /* Active language indicator */
    .dropdown-item[href*="{{ app()->getLocale() }}"] {
        background-color: rgba(0, 102, 204, 0.08);
        font-weight: 600;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dropdown-menu {
            min-width: 120px !important;
        }
    }
</style>
