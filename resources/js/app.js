import './bootstrap';
import emailEditor from './email-editor';

// Register Alpine component globally when Alpine is available (loaded via Livewire).
document.addEventListener('alpine:init', () => {
    window.Alpine.data('emailEditor', emailEditor);
});
