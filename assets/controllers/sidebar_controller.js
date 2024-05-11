import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        // Toggle the side navigation
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            // KB, 11.05.2024: Dieses Speichern macht die Sidebar deutlich unzuverlässiger, daher temporär deaktiviert.
            // if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            //     document.body.classList.toggle('sidenav-toggled');
            // }
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('sidenav-toggled');
                // localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sidenav-toggled'));
            });
        }

        // Close side navigation when width < LG
        const sidenavContent = document.body.querySelector('#layoutSidenav_content');
        if (sidenavContent) {
            sidenavContent.addEventListener('click', event => {
                const BOOTSTRAP_LG_WIDTH = 992;
                if (window.innerWidth >= BOOTSTRAP_LG_WIDTH) {
                    return;
                }
                if (document.body.classList.contains("sidenav-toggled")) {
                    document.body.classList.toggle("sidenav-toggled");
                }
            });
        }
    }
}