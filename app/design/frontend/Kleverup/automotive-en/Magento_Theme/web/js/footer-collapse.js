window.addEventListener('alpine:init', () => {
    Alpine.data('footerCollapse', (initialOpen, collapseId) => ({
        open: initialOpen || false,
        isMobile: false,
        id: collapseId || '',
        height: 0,
        init() {
            this.checkIsMobileResolution();
            this.$nextTick(() => this.setHeight());
            window.addEventListener('resize', () => {
                this.checkIsMobileResolution();
                this.setHeight();
            });
            window.addEventListener('visibilitychange', () => {
                this.checkIsMobileResolution();
            });
            window.addEventListener('accordion-close-all', (e) => {
                if (e.detail.except !== this.id) {
                    this.open = false;
                    this.$nextTick(() => this.setHeight());
                }
            });
            this.$watch('open', () => this.$nextTick(() => this.setHeight()));
        },
        checkIsMobileResolution() {
            this.isMobile = window.innerWidth < 1024;
        },
        toggle() {
            if (this.isMobile) {
                if (this.open) {
                    this.open = false;
                } else {
                    window.dispatchEvent(new CustomEvent('accordion-close-all', {
                        detail: { except: this.id }
                    }));
                    this.open = true;
                }
            }
        },
        setHeight() {
            if (this.isMobile) {
                this.height = this.open ? this.$refs.content.scrollHeight : 0;
            } else {
                this.height = 'auto';
            }
        },
        getContentStyle() {
            if (this.isMobile) {
                return 'height: ' + this.height + 'px';
            }
            return 'height: auto;';
        }
    }));
});
