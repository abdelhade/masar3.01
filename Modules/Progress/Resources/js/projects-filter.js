/**
 * Real-time Projects Filter
 * Filters projects list in real-time without page reload
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get all project cards
    const projectCards = document.querySelectorAll('.project-card');
    const projectsContainer = document.querySelector('.row.g-4.m-2');
    
    // Filter elements
    const searchInput = document.getElementById('projectSearch');
    const statusFilter = document.getElementById('projectStatusFilter');
    const typeFilter = document.getElementById('projectTypeFilter');
    const clientFilter = document.getElementById('projectClientFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    // Results counter
    const resultsCounter = document.getElementById('filterResults');
    
    // Initialize filters
    if (!projectCards.length) return;
    
    // Filter function
    function filterProjects() {
        let visibleCount = 0;
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const statusValue = statusFilter ? statusFilter.value : 'all';
        const typeValue = typeFilter ? typeFilter.value : 'all';
        const clientValue = clientFilter ? clientFilter.value : 'all';
        
        projectCards.forEach(card => {
            // Get project data from data attributes
            const projectName = (card.getAttribute('data-project-name') || '').toLowerCase();
            const projectStatus = card.getAttribute('data-project-status') || '';
            const projectType = card.getAttribute('data-project-type') || '';
            const projectClient = (card.getAttribute('data-project-client') || '').toLowerCase();
            
            // Apply filters
            let shouldShow = true;
            
            // Search filter
            if (searchTerm) {
                shouldShow = shouldShow && (
                    projectName.includes(searchTerm) ||
                    projectClient.includes(searchTerm)
                );
            }
            
            // Status filter
            if (statusValue !== 'all') {
                shouldShow = shouldShow && (projectStatus === statusValue);
            }
            
            // Type filter
            if (typeValue !== 'all') {
                shouldShow = shouldShow && (projectType.toLowerCase() === typeValue.toLowerCase());
            }
            
            // Client filter
            if (clientValue !== 'all') {
                shouldShow = shouldShow && (projectClient.includes(clientValue.toLowerCase()));
            }
            
            // Show/hide card with animation
            const cardWrapper = card.closest('.col-lg-6, .col-md-6, .col-sm-12');
            if (!cardWrapper) return;
            
            if (shouldShow) {
                if (cardWrapper.style.display === 'none') {
                    cardWrapper.style.display = '';
                    cardWrapper.style.opacity = '0';
                    cardWrapper.style.transform = 'scale(0.95)';
                    
                    // Animate in
                    requestAnimationFrame(() => {
                        cardWrapper.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        cardWrapper.style.opacity = '1';
                        cardWrapper.style.transform = 'scale(1)';
                    });
                }
                visibleCount++;
            } else {
                if (cardWrapper.style.display !== 'none') {
                    cardWrapper.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                    cardWrapper.style.opacity = '0';
                    cardWrapper.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        cardWrapper.style.display = 'none';
                    }, 300);
                }
            }
        });
        
        // Update results counter
        if (resultsCounter) {
            resultsCounter.textContent = visibleCount;
            
            // Show no results message
            let noResultsMsg = document.getElementById('noResultsMessage');
            if (visibleCount === 0) {
                if (!noResultsMsg && projectsContainer) {
                    const msgDiv = document.createElement('div');
                    msgDiv.id = 'noResultsMessage';
                    msgDiv.className = 'col-12 text-center py-5';
                    msgDiv.style.opacity = '0';
                    msgDiv.innerHTML = `
                        <div class="alert alert-info border-0 shadow-sm" style="border-radius: 20px; padding: 2rem;">
                            <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                            <h5 class="text-muted mb-2">${getTranslation('no_projects_found')}</h5>
                            <p class="text-muted mb-0">${getTranslation('try_different_filters')}</p>
                        </div>
                    `;
                    projectsContainer.appendChild(msgDiv);
                    
                    // Animate in
                    requestAnimationFrame(() => {
                        msgDiv.style.transition = 'opacity 0.3s ease';
                        msgDiv.style.opacity = '1';
                    });
                }
            } else {
                if (noResultsMsg) {
                    noResultsMsg.style.transition = 'opacity 0.3s ease';
                    noResultsMsg.style.opacity = '0';
                    setTimeout(() => {
                        if (noResultsMsg && noResultsMsg.parentNode) {
                            noResultsMsg.remove();
                        }
                    }, 300);
                }
            }
        }
    }
    
    // Get translation helper
    function getTranslation(key) {
        const translations = {
            'no_projects_found': 'لا توجد مشاريع مطابقة للبحث',
            'try_different_filters': 'جرب البحث بكلمات مختلفة أو غير الفلاتر'
        };
        return translations[key];
    }
    
    // Event listeners
    if (searchInput) {
        searchInput.addEventListener('input', filterProjects);
        searchInput.addEventListener('keyup', filterProjects);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterProjects);
    }
    
    if (typeFilter) {
        typeFilter.addEventListener('change', filterProjects);
    }
    
    if (clientFilter) {
        clientFilter.addEventListener('change', filterProjects);
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = 'all';
            if (typeFilter) typeFilter.value = 'all';
            if (clientFilter) clientFilter.value = 'all';
            
            filterProjects();
            
            // Focus on search input
            if (searchInput) {
                searchInput.focus();
            }
        });
    }
    
    // Initialize - filter on load if any filters are active
    const hasActiveFilters = 
        (searchInput && searchInput.value) ||
        (statusFilter && statusFilter.value !== 'all') ||
        (typeFilter && typeFilter.value !== 'all') ||
        (clientFilter && clientFilter.value !== 'all');
    
    if (hasActiveFilters) {
        filterProjects();
    }
    
    // Store filter state in URL without reloading
    function updateURLParams() {
        const params = new URLSearchParams();
        
        if (searchInput && searchInput.value) {
            params.set('search', searchInput.value);
        }
        if (statusFilter && statusFilter.value !== 'all') {
            params.set('status', statusFilter.value);
        }
        if (typeFilter && typeFilter.value !== 'all') {
            params.set('type', typeFilter.value);
        }
        if (clientFilter && clientFilter.value !== 'all') {
            params.set('client', clientFilter.value);
        }
        
        const newURL = params.toString() 
            ? `${window.location.pathname}?${params.toString()}`
            : window.location.pathname;
        
        window.history.pushState({}, '', newURL);
    }
    
    // Update URL when filters change
    if (searchInput) searchInput.addEventListener('input', updateURLParams);
    if (statusFilter) statusFilter.addEventListener('change', updateURLParams);
    if (typeFilter) typeFilter.addEventListener('change', updateURLParams);
    if (clientFilter) clientFilter.addEventListener('change', updateURLParams);
    
    // Load filters from URL on page load
    function loadFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('search') && searchInput) {
            searchInput.value = urlParams.get('search');
        }
        if (urlParams.has('status') && statusFilter) {
            statusFilter.value = urlParams.get('status');
        }
        if (urlParams.has('type') && typeFilter) {
            typeFilter.value = urlParams.get('type');
        }
        if (urlParams.has('client') && clientFilter) {
            clientFilter.value = urlParams.get('client');
        }
        
        // Apply filters from URL
        const hasURLParams = urlParams.has('search') || 
                           urlParams.has('status') || 
                           urlParams.has('type') || 
                           urlParams.has('client');
        
        if (hasURLParams) {
            setTimeout(filterProjects, 100);
        }
    }
    
    // Load filters from URL after initialization
    loadFiltersFromURL();
});

