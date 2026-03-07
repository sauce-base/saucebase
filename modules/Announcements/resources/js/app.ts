import '../css/style.css';

/**
 * Announcements module setup
 * Called during app initialization before mounting
 */
export function setup() {
    console.log('Announcements module loaded');
}

/**
 * Announcements module after mount logic
 * Called after the app has been mounted
 */
export function afterMount() {
    console.log('Announcements module after mount logic executed');
}
