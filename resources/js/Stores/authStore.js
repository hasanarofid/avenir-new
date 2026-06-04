import { reactive } from 'vue';

export const authStore = reactive({
    modalOpen: false,
    activeTab: 'login', // 'login', 'register'
    open(tab = 'login') {
        this.activeTab = tab;
        this.modalOpen = true;
    },
    close() {
        this.modalOpen = false;
    }
});
