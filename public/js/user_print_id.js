/**
 * Search Household IDs - External JavaScript
 * Handles QR code generation, printing, and UI interactions
 */

// Global functions
function printAllIDs() {
    window.print();
}

// QR Code Generator
class QRCodeGenerator {
    constructor() {
        this.initialized = false;
    }

    init() {
        if (this.initialized) return;
        
        if (typeof displayData !== 'undefined' && displayData.length > 0) {
            this.generateAllQRCodes();
        }
        this.initialized = true;
    }

    generateAllQRCodes() {
        displayData.forEach(item => {
            this.generateQRCode(item.index, item.hh_id);
        });
    }

generateQRCode(index, hhId) 
                {
                    const container = document.getElementById(`qrcode-${index}`);
                    if (container && typeof QRCode !== 'undefined') {
                        // Clear existing QR code
                        container.innerHTML = '';
                        
                        // Check if we're in print mode or screen mode
                        const isPrintMode = window.matchMedia('print').matches;
                        
                        new QRCode(container, {
                            text: hhId,
                            width: isPrintMode ? 30 : 70, // Smaller for print, larger for screen
                            height: isPrintMode ? 30 : 70,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    }
                }
}

// UI Controller
class UIController {
    constructor() {
        this.init();
    }

    init() {
        this.autoFocusInput();
        this.setupEventListeners();
    }

    autoFocusInput() {
        const input = document.querySelector('input[name="hh_ids"]');
        if (input) {
            input.focus();
            input.select();
        }
    }

    setupEventListeners() {
        // Add any additional event listeners here
        document.addEventListener('keydown', (e) => {
            // Ctrl + P for printing
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                printAllIDs();
            }
        });
    }
}

// Main Application
class SearchIDsApp {
    constructor() {
        this.qrGenerator = new QRCodeGenerator();
        this.uiController = new UIController();
        this.init();
    }

    init() {
        // Initialize when DOM is fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.start();
            });
        } else {
            this.start();
        }
    }

    start() {
        this.qrGenerator.init();
        console.log('Search IDs application initialized');
    }
}

// Initialize the application
const searchIDsApp = new SearchIDsApp();

// Export functions for global access (if needed in HTML onclick attributes)
window.printAllIDs = printAllIDs;