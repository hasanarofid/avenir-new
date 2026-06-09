import { reactive } from 'vue';

export const authStore = reactive({
    modalOpen: false,
    activeTab: 'login', // 'login', 'register'
    redirectUrl: null,
    open(tab = 'login', redirectUrl = null) {
        this.activeTab = tab;
        this.redirectUrl = redirectUrl;
        this.modalOpen = true;
    },
    close() {
        this.modalOpen = false;
        this.redirectUrl = null;
    }
});
