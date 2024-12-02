/**
 * Province Toast Component
 * 
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: assets/js/components/province-toast.js
 * 
 * Description: Komponen toast notification khusus untuk manajemen provinsi.
 *              Menangani feedback untuk operasi CRUD provinsi.
 *              Support queue system untuk multiple notifications.
 *              Includes custom styling dan animations.
 */

const ProvinceToast = {
    container: null,
    queue: [],
    isProcessing: false,
    defaultDuration: 3000,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'province-toast-container';
            this.container.style.cssText = `
                position: fixed;
                top: 32px;
                right: 20px;
                z-index: 160000;
                display: flex;
                flex-direction: column;
                gap: 10px;
            `;
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'info', duration = this.defaultDuration) {
        this.init();
        this.queue.push({ message, type, duration });
        
        if (!this.isProcessing) {
            this.processQueue();
        }
    },

    async processQueue() {
        if (this.queue.length === 0) {
            this.isProcessing = false;
            return;
        }

        this.isProcessing = true;
        const { message, type, duration } = this.queue.shift();
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `province-toast province-toast-${type}`;
        toast.style.cssText = this.getToastStyles(type);
        
        // Support both string and array messages
        if (Array.isArray(message)) {
            message.forEach(msg => {
                const p = document.createElement('p');
                p.textContent = msg;
                p.style.margin = '5px 0';
                toast.appendChild(p);
            });
        } else {
            toast.textContent = message;
        }

        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            position: absolute;
            right: 8px;
            top: 8px;
            background: none;
            border: none;
            color: inherit;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.7;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        closeBtn.onclick = () => this.removeToast(toast);
        toast.appendChild(closeBtn);
        
        // Add to container with animation
        this.container.appendChild(toast);
        await new Promise(resolve => setTimeout(resolve, 50));
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
        
        // Auto remove after duration
        setTimeout(() => this.removeToast(toast), duration);
    },

    async removeToast(toast) {
        if (!toast.isRemoving) {
            toast.isRemoving = true;
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            
            await new Promise(resolve => setTimeout(resolve, 300));
            if (toast.parentElement) {
                toast.parentElement.removeChild(toast);
            }
            
            this.processQueue();
        }
    },

    getToastStyles(type) {
        const baseStyles = `
            position: relative;
            padding: 12px 35px 12px 15px;
            border-radius: 4px;
            color: #fff;
            font-size: 14px;
            min-width: 250px;
            max-width: 400px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            margin: 0;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;

        const colors = {
            success: '#00a32a', // WordPress success green
            error: '#d63638',   // WordPress error red
            warning: '#dba617', // WordPress warning yellow
            info: '#72aee6'     // WordPress info blue
        };

        return `${baseStyles}background-color: ${colors[type] || colors.info};`;
    },

    // Convenience methods with WordPress-style messages
    success(message, duration) {
        this.show(message, 'success', duration);
    },

    error(message, duration) {
        this.show(message, 'error', duration);
    },

    warning(message, duration) {
        this.show(message, 'warning', duration);
    },

    info(message, duration) {
        this.show(message, 'info', duration);
    },

    // Province-specific message shortcuts
    created() {
        this.success('Provinsi berhasil ditambahkan');
    },

    updated() {
        this.success('Provinsi berhasil diperbarui');
    },

    deleted() {
        this.success('Provinsi berhasil dihapus');
    },

    ajaxError() {
        this.error('Terjadi kesalahan saat menghubungi server. Silakan coba lagi.');
    }
};

// Expose for global use
window.ProvinceToast = ProvinceToast;

export default ProvinceToast;