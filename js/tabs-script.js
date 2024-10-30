document.addEventListener('DOMContentLoaded', function () {
    // Default to the first tab
    const defaultTab = document.querySelector('.tab-links li:first-child a');
    if (defaultTab) {
        changeTab(defaultTab.getAttribute('href'));
    }

    // Handle tab clicks
    const tabLinks = document.querySelectorAll('.tab-links a');
    tabLinks.forEach(function (tabLink) {
        tabLink.addEventListener('click', function (event) {
            event.preventDefault();
            changeTab(this.getAttribute('href'));
            history.pushState(null, null, this.getAttribute('href'));
        });
    });

    // Function to switch tabs
    function changeTab(tabId) {
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(function (tab) {
            tab.classList.remove('active');
        });

        const tabLinks = document.querySelectorAll('.tab-links a');
        tabLinks.forEach(function (tabLink) {
            tabLink.classList.remove('active');
        });

        document.querySelector(tabId).classList.add('active');
        document.querySelector('.tab-links a[href="' + tabId + '"]').classList.add('active');
    }

    // Handle back/forward navigation
    window.addEventListener('popstate', function () {
        const activeTab = window.location.hash || '#setup-guide';
        changeTab(activeTab);
    });
    const loadActiveTab = window.location.hash || '#setup-guide';
    changeTab(loadActiveTab);


});
