import './bootstrap'

import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'
import PerfectScrollbar from 'perfect-scrollbar'
import Sortable from 'sortablejs'

window.Sortable = Sortable

window.randomUID = function randomUID(prefix = 'id') {
    const rand = Math.random().toString(36).substring(2, 10)
    const time = Date.now().toString(36)
    return `${prefix}-${time}-${rand}`
}

import roomsList from './room-list'
import roomAutocomplete from './room-autocomplete'
import taskList from './task-list'
import taskAutocomplete from './task-autocomplete'
import mediaDropzone from './media-dropzone'
import roomPicker from './room-picker'
import taskPicker from './task-picker'
import dropdown from './dropdown'

Alpine.data('mediaDropzone', mediaDropzone)
Alpine.data('taskList', taskList)
Alpine.data('taskAutocomplete', taskAutocomplete)

Alpine.data('roomsList', roomsList)
Alpine.data('roomAutocomplete', roomAutocomplete)
Alpine.data('roomPicker', roomPicker)
Alpine.data('taskPicker', taskPicker)
Alpine.data('dropdown', dropdown)

window.PerfectScrollbar = PerfectScrollbar

document.addEventListener('alpine:init', () => {
    Alpine.data('mainState', () => {
        let lastScrollTop = 0
        const init = function () {
            window.addEventListener('scroll', () => {
                let st =
                    window.pageYOffset || document.documentElement.scrollTop
                if (st > lastScrollTop) {
                    // downscroll
                    this.scrollingDown = true
                    this.scrollingUp = false
                } else {
                    // upscroll
                    this.scrollingDown = false
                    this.scrollingUp = true
                    if (st == 0) {
                        //  reset
                        this.scrollingDown = false
                        this.scrollingUp = false
                    }
                }
                lastScrollTop = st <= 0 ? 0 : st // For Mobile or negative scrolling
            })
        }

        const getTheme = () => {
            if (window.localStorage.getItem('dark')) {
                return JSON.parse(window.localStorage.getItem('dark'))
            }
            return (
                !!window.matchMedia &&
                window.matchMedia('(prefers-color-scheme: dark)').matches
            )
        }
        const setTheme = (value) => {
            window.localStorage.setItem('dark', value)
        }
        return {
            init,
            isDarkMode: getTheme(),
            toggleTheme() {
                this.isDarkMode = !this.isDarkMode
                setTheme(this.isDarkMode)
            },
            isSidebarOpen: window.innerWidth > 1024,
            isSidebarHovered: false,
            handleSidebarHover(value) {
                if (window.innerWidth < 1024) {
                    return
                }
                this.isSidebarHovered = value
            },
            handleWindowResize() {
                if (window.innerWidth <= 1024) {
                    this.isSidebarOpen = false
                } else {
                    this.isSidebarOpen = true
                }
            },
            scrollingDown: false,
            scrollingUp: false,
        }
    })
})








document.addEventListener('alpine:init', () => {
    // ... your existing mainState Alpine.data ...

    Alpine.data('roomsIndex', () => ({
        allRoomIds: [],
        selectedRoomIds: [],
        selectAll: false,

        tasks: [],
        taskSearch: '',
        taskTypeFilter: '', // '', 'room', 'inventory', etc.
        selectedTaskIds: [],

        isSubmittingBulk: false,
        bulkUrl: '',
        csrfToken: '',

        init() {
            // Load data from data-* attributes on the root element
            this.allRoomIds = JSON.parse(this.$el.dataset.rooms || '[]')
            this.tasks = JSON.parse(this.$el.dataset.tasks || '[]')
            this.bulkUrl = this.$el.dataset.bulkUrl || ''
            this.csrfToken = this.$el.dataset.csrf || ''
        },

        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedRoomIds = [...this.allRoomIds]
            } else {
                this.selectedRoomIds = []
            }
        },

        filteredTasks() {
            const q = this.taskSearch.toLowerCase().trim()
            const type = this.taskTypeFilter

            return this.tasks.filter((task) => {
                const matchesSearch =
                    !q || task.name.toLowerCase().includes(q)

                const matchesType =
                    !type || task.type === type

                return matchesSearch && matchesType
            })
        },

        async submitBulkAssign() {
            if (!this.selectedRoomIds.length || !this.selectedTaskIds.length) {
                return
            }

            this.isSubmittingBulk = true

            try {
                const res = await fetch(this.bulkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        room_ids: this.selectedRoomIds,
                        task_ids: this.selectedTaskIds,
                    }),
                })

                if (!res.ok) {
                    throw new Error('Request failed')
                }

                window.location.reload()
            } catch (error) {
                console.error('Bulk assign failed', error)
                alert(
                    'Something went wrong while assigning tasks. Please try again.'
                )
            } finally {
                this.isSubmittingBulk = false
            }
        },
    }))
})

Alpine.plugin(collapse)

Alpine.start()
