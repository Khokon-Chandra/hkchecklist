// resources/js/app.js

import './bootstrap'

import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'
import PerfectScrollbar from 'perfect-scrollbar'
import Sortable from 'sortablejs'

window.Sortable = Sortable
window.PerfectScrollbar = PerfectScrollbar

// Simple UID helper
window.randomUID = function randomUID(prefix = 'id') {
    const rand = Math.random().toString(36).substring(2, 10)
    const time = Date.now().toString(36)
    return `${prefix}-${time}-${rand}`
}

// Existing local components
import roomsList from './room-list'
import roomAutocomplete from './room-autocomplete'
import taskList from './task-list'
import taskAutocomplete from './task-autocomplete'
import mediaDropzone from './media-dropzone'
import roomPicker from './room-picker'
import taskPicker from './task-picker'
import dropdown from './dropdown'

// New components
import roomsIndex from './rooms-index'
import roomTasksEditor from './room-tasks-editor'
import taskCreateForm from './task-create-form'

// ⛔️ DO NOT MODIFY — main app interaction (kept exactly as you sent)
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

// Second alpine:init for all other components
document.addEventListener('alpine:init', () => {
    // Existing components
    Alpine.data('mediaDropzone', mediaDropzone)
    Alpine.data('taskList', taskList)
    Alpine.data('taskAutocomplete', taskAutocomplete)

    Alpine.data('roomsList', roomsList)
    Alpine.data('roomAutocomplete', roomAutocomplete)
    Alpine.data('roomPicker', roomPicker)
    Alpine.data('taskPicker', taskPicker)
    Alpine.data('dropdown', dropdown)

    // New: rooms index (bulk assign tasks)
    Alpine.data('roomsIndex', roomsIndex)

    // New: edit room + tasks on same page
    Alpine.data('roomTasksEditor', roomTasksEditor)

    // New: task create form
    Alpine.data('taskCreateForm', taskCreateForm)
})

Alpine.plugin(collapse)
Alpine.start()
