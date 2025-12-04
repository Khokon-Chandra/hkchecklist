// resources/js/room-tasks-editor.js

export default () => ({
    tasks: [],
    selectedTaskIds: [],
    search: '',
    typeFilter: '',

    init() {
        // Read JSON data from data-* attributes on the form
        this.tasks = JSON.parse(this.$el.dataset.tasks || '[]')
        this.selectedTaskIds = JSON.parse(
            this.$el.dataset.selectedTaskIds || '[]'
        )
    },

    filteredTasks() {
        const q = this.search.toLowerCase().trim()
        const type = this.typeFilter

        return this.tasks.filter((task) => {
            const matchesSearch =
                !q || task.name.toLowerCase().includes(q)

            const matchesType =
                !type || task.type === type

            return matchesSearch && matchesType
        })
    },

    toggleSelectAllVisible() {
        const visibleIds = this.filteredTasks().map((t) => t.id)
        const allSelected = visibleIds.every((id) =>
            this.selectedTaskIds.includes(id)
        )

        if (allSelected) {
            // unselect visible
            this.selectedTaskIds = this.selectedTaskIds.filter(
                (id) => !visibleIds.includes(id)
            )
        } else {
            // add visible (dedupe)
            this.selectedTaskIds = Array.from(
                new Set([...this.selectedTaskIds, ...visibleIds])
            )
        }
    },
})
    