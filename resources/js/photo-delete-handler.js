/**
 * Photo Delete Handler Component
 * Handles photo deletion with confirmation
 */

export default function photoDeleteHandler(photoId, sessionId) {
    return {
        fullscreen: false,
        deleting: false,
        photoId: photoId,
        sessionId: sessionId,

        handleDeletePhoto() {
            if (confirm('Are you sure you want to delete this photo?')) {
                this.deleting = true;
                window.api.delete(`/api/sessions/${this.sessionId}/photos/${this.photoId}`)
                    .then(data => {
                        if (data.success) {
                            // Find and remove the photo element
                            const photoElement = document.querySelector(`[data-photo-id="${this.photoId}"]`);
                            if (photoElement) {
                                photoElement.remove();
                            }
                            // Refresh session data
                            const renderer = document.querySelector('[x-data*="checklistRenderer"]')?._x_dataStack?.[0];
                            if (renderer) {
                                renderer.refresh();
                            }
                        } else {
                            alert('Failed to delete photo');
                            this.deleting = false;
                        }
                    })
                    .catch(() => {
                        alert('Failed to delete photo');
                        this.deleting = false;
                    });
            }
        },
    };
}
