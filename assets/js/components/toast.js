/**
 * File: toast.js
 * Path: /wilayah-indonesia/assets/js/components/toast.js
 * Description: Handles toast notifications for the plugin
 * Version: 1.0.0
 * Last modified: 2024-11-26
 * Dependencies: jQuery
 * 
 * Features:
 * - Shows success/error notifications
 * - Auto-dismisses after configurable duration
 * - Supports animations and position customization
 * - Ensures single toast instance
 * 
 * Usage:
 * wilayahToast.success('Success message');
 * wilayahToast.error('Error message');
 */
// File: assets/js/components/toast.js

const irToast = {
    container: null,
    
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'ir-toast-container';
            this.container.style.cssText = `
                position: fixed;
                top: 32px;
                right: 20px;
                z-index: 999999;
            `;
            document.body.appendChild(this.container);
        }
    },
    
    show(message, type = 'success', duration = 3000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `ir-toast ir-toast-${type}`;
        toast.style.cssText = `
            margin-bottom: 10px;
            padding: 12px 24px;
            border-radius: 4px;
            color: #fff;
            font-size: 14px;
            min-width: 250px;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        // Set background color based on type
        switch (type) {
            case 'success':
                toast.style.backgroundColor = '#4CAF50';
                break;
            case 'error':
                toast.style.backgroundColor = '#f44336';
                break;
            case 'warning':
                toast.style.backgroundColor = '#ff9800';
                break;
            case 'info':
                toast.style.backgroundColor = '#2196F3';
                break;
        }
        
        toast.textContent = message;
        this.container.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => {
            toast.style.opacity = '1';
        }, 10);
        
        // Remove toast after duration
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            }, 300);
        }, duration);
    },
    
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

// Expose to global scope
window.irToast = irToast;
