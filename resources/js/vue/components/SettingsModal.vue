<script setup lang="ts">
import { useSettingsModal } from '@/composables/useSettingsModal';
import { visitModal, type ModalInstance } from '@inertiaui/modal-vue';
import { watch } from 'vue';

/**
 * Opens and closes the settings modal in step with the `#settings` fragment.
 *
 * Rendered once at the app root so settings can be opened from anywhere without
 * the surrounding page having to know about it.
 */
const { isOpen, section, close } = useSettingsModal();

let modal: ModalInstance | null = null;
let opening = false;

async function openModal(activeSection: string | null) {
    if (modal || opening) {
        return;
    }

    opening = true;

    try {
        modal = await visitModal(
            route('settings', activeSection ? { section: activeSection } : {}),
            {
                // Closing from inside the modal (escape, backdrop, close button)
                // has to clear the fragment too, or it could not be reopened.
                onClose: () => {
                    modal = null;
                    close();
                },
            },
        );
    } finally {
        opening = false;
    }
}

watch(
    isOpen,
    (open) => {
        if (open) {
            openModal(section.value);
        } else {
            modal?.close();
            modal = null;
        }
    },
    { immediate: true },
);
</script>
