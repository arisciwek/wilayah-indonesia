/**
 * Toast Notification Component
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/assets/js/components/toast.js
 * 
 * Description: Komponen notifikasi toast untuk feedback UI.
 *              Menampilkan pesan sukses, error, warning, dan info.
 *              Support multiple toasts dengan queue system.
 *              Includes animasi dan auto dismiss.
 * 
 * Changelog:
 * 1.0.0 - 2024-12-02 17:00:00
 * - Initial release
 * - Added toast types (success, error, warning, info)
 * - Added animation support
 * - Added queue system
 * - Added responsive design
 * 
 * Dependencies:
 * - None (Vanilla JavaScript)
 */

const wilayahToast = {
    container: null,
    queue: [],
    isProcessing: false,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'wilayah-toast-container';
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

    show(message, type = 'info', duration = 3000) {
        this.init();
        
        // Add to queue
        this.queue.push({ message, type, duration });
        
        // Process queue if not already processing
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
        toast.className = `wilayah-toast wilayah-toast-${type}`;
        toast.style.cssText = this.getToastStyles(type);
        
        // Support multi-line messages
        if (Array.isArray(message)) {
            message.forEach(msg => {
                const p = document.createElement('p');
                p.style.margin = '5px 0';
                p.textContent = msg;
                toast.appendChild(p);
            });
        } else {
            toast.textContent = message;
        }

        // Add to container
        this.container.appendChild(toast);
        
        // Trigger animation
        await new Promise(resolve => setTimeout(resolve, 50));
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
        
        // Remove after duration
        await new Promise(resolve => setTimeout(resolve, duration));
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        
        // Remove from DOM after animation
        await new Promise(resolve => setTimeout(resolve, 300));
        if (toast.parentElement) {
            toast.parentElement.removeChild(toast);
        }
        
        // Process next in queue
        this.processQueue();
    },

    getToastStyles(type) {
        const baseStyles = `
            padding: 12px 24px;
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
            success: '#4CAF50',
            error: '#f44336',
            warning: '#ff9800',
            info: '#2196F3'
        };

        return `${baseStyles}background-color: ${colors[type] || colors.info};`;
    },

    // Convenience methods
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
    }
};

// Expose for global scope
window.wilayahToast = wilayahToast;
