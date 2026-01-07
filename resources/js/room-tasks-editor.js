// resources/js/room-tasks-editor.js

export default function roomTasksEditor({
    orderUrl,
    attachUrl,
    suggestUrl,
    detachBaseUrl,
    csrf,
    roomTasks = [],
    availableTasks = []
}) {
    return {
        orderUrl,
        attachUrl,
        suggestUrl,
        detachBaseUrl,
        csrf,
        roomTasks: roomTasks.map(t => ({ ...t, key: `task-${t.id}` })),
        availableTasks: availableTasks,
        searchQuery: '',
        openSuggestions: false,
        suggestions: [],
        loading: false,
        highlighted: -1,
        saving: false,
        savedAt: null,
        status: 'idle',
        _saveTimer: null,
        _hideTimer: null,
        _pending: false,

        init() {
            // Wait for DOM to be ready
            this.$nextTick(() => {
                // Initialize Sortable for drag and drop
                if (this.$refs?.taskList) {
                    new window.Sortable(this.$refs.taskList, {
                        animation: 160,
                        handle: '.drag-handle',
                        draggable: '[data-task-id]',
                        onStart: () => {
                            if (this.$refs.taskList) {
                                this.$refs.taskList.classList.add('dragging');
                            }
                        },
                        onEnd: () => {
                            if (this.$refs.taskList) {
                                this.$refs.taskList.classList.remove('dragging');
                            }
                            this.updateOrderFromDOM();
                            this.queueSave();
                        },
                    });
                }
            });
        },

        updateOrderFromDOM() {
            if (!this.$refs?.taskList) return;
            const items = Array.from(this.$refs.taskList.querySelectorAll('[data-task-id]'));
            const orderedIds = items.map(item => parseInt(item.dataset.taskId));

            // Reorder roomTasks array based on DOM order
            const taskMap = new Map(this.roomTasks.map(t => [t.id, t]));
            this.roomTasks = orderedIds.map(id => taskMap.get(id)).filter(Boolean);
        },

        get taskIds() {
            return this.roomTasks.map(t => t.id);
        },

        queueSave() {
            clearTimeout(this._saveTimer);
            this._saveTimer = setTimeout(() => this.saveOrder(), 150);
        },

        async saveOrder() {
            if (this.saving) {
                this._pending = true;
                return;
            }

            this.saving = true;
            this.status = 'saving';
            clearTimeout(this._hideTimer);

            try {
                const res = await fetch(this.orderUrl, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order: this.taskIds }),
                });

                if (!res.ok) {
                    throw new Error('Failed to save order');
                }

                this.savedAt = new Date().toLocaleTimeString();
                this.status = 'saved';

                this._hideTimer = setTimeout(() => {
                    if (this.status === 'saved') this.status = 'idle';
                }, 2500);
            } catch (e) {
                this.status = 'error';
                console.error(e);
            } finally {
                this.saving = false;
                if (this._pending) {
                    this._pending = false;
                    this.queueSave();
                }
            }
        },

        async searchTasks() {
            const q = this.searchQuery.trim();
            if (!q) {
                this.suggestions = [];
                this.openSuggestions = false;
                return;
            }

            this.loading = true;
            try {
                const url = new URL(this.suggestUrl, window.location.origin);
                url.searchParams.set('q', q);
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                const arr = Array.isArray(json) ? json : [];

                // Filter out tasks already in roomTasks
                const existingIds = new Set(this.roomTasks.map(t => t.id));
                this.suggestions = arr.filter(t => !existingIds.has(t.id));
                this.openSuggestions = this.suggestions.length > 0;
                this.highlighted = this.openSuggestions ? 0 : -1;
            } catch (e) {
                console.error(e);
                this.suggestions = [];
            } finally {
                this.loading = false;
            }
        },

        addTask(task) {
            // Check if task already exists
            if (this.roomTasks.some(t => t.id === task.id)) {
                return;
            }

            // Add to roomTasks
            const newTask = {
                ...task,
                key: `task-${task.id}`,
            };
            this.roomTasks.push(newTask);

            // Remove from availableTasks if it was there
            this.availableTasks = this.availableTasks.filter(t => t.id !== task.id);

            // Reset search
            this.searchQuery = '';
            this.suggestions = [];
            this.openSuggestions = false;

            // Attach task to room via API
            this.attachTask(task.id);
        },

        async attachTask(taskId) {
            try {
                const res = await fetch(this.attachUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ task_ids: [taskId] }),
                });

                if (!res.ok) {
                    throw new Error('Failed to attach task');
                }

                // Update order after attaching
                this.queueSave();
            } catch (e) {
                console.error('Failed to attach task:', e);
            }
        },

        async removeTask(taskId) {
            const task = this.roomTasks.find(t => t.id === taskId);
            if (!task) return;

            // Optimistically remove from UI
            this.roomTasks = this.roomTasks.filter(t => t.id !== taskId);

            try {
                // Call API to detach task
                const detachUrl = `${this.detachBaseUrl}/${taskId}`;
                const res = await fetch(detachUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                if (!res.ok) {
                    throw new Error('Failed to detach task');
                }

                // Update order after detaching
                this.queueSave();
            } catch (e) {
                console.error('Failed to detach task:', e);
                // Re-add task on error
                this.roomTasks.push(task);
                this.roomTasks.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
            }
        },

        handleKeyDown(e) {
            if (!this.openSuggestions) return;

            const max = this.suggestions.length;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.highlighted = (this.highlighted + 1) % max;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.highlighted = (this.highlighted - 1 + max) % max;
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.highlighted >= 0 && this.highlighted < max) {
                    this.addTask(this.suggestions[this.highlighted]);
                }
            } else if (e.key === 'Escape') {
                this.openSuggestions = false;
            }
        },

        hoverIndex(i) {
            this.highlighted = i;
        },
    };
}
