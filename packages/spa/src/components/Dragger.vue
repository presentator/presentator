<template>
    <div @mousedown.left="dragInit" @touchstart="dragInit">
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: 'dragger',
    props: {
        wrapperSelector: {
            type: String
        },
        tolerance: {
            type:    Number,
            default: 10,
        },
        enable: {
            type:    Boolean,
            default: true,
        },
    },
    data() {
        return {
            startX:      0,
            startY:      0,
            shiftX:      0,
            shiftY:      0,
            lockX:       false,
            lockY:       false,
            dragStarted: false,
        }
    },
    computed: {
        wrapper() {
            return this.$el.closest(this.wrapperSelector) || this.$el.parentNode;
        },
    },
    methods: {
        dragInit(e) {
            if (!this.enable) {
                return;
            }

            e.stopPropagation();

            this.startX = e.clientX;
            this.startY = e.clientY;
            this.shiftX = e.clientX - this.$el.offsetLeft;
            this.shiftY = e.clientY - this.$el.offsetTop;

            document.addEventListener('touchmove', this.onMove);
            document.addEventListener('mousemove', this.onMove);
            document.addEventListener('touchend', this.onDrop);
            document.addEventListener('mouseup', this.onDrop);
        },
        onMove(e) {
            let diffX = e.clientX - this.startX;
            let diffY = e.clientY - this.startY;
            let left  = e.clientX - this.shiftX;
            let top   = e.clientY - this.shiftY;

            if (
                !this.dragStarted &&
                (Math.abs(left - this.$el.offsetLeft) < this.tolerance) &&
                (Math.abs(top - this.$el.offsetTop) < this.tolerance)
            ) {
                return;
            }

            e.preventDefault();

            if (!this.dragStarted) {
                this.dragStarted = true;
                this.$el.classList.add('no-pointer-events');
                this.wrapper.classList.add('dragging');
                this.$emit('dragStarted', e);
            }

            this.$emit('dragging', e);

            // lock/release axis
            if (e.shiftKey) {
                if (!this.lockX && !this.lockY) {
                    if (Math.abs(diffX) > Math.abs(diffY)) {
                        this.lockX = false;
                        this.lockY = true;
                    } else {
                        this.lockX = true;
                        this.lockY = false;
                    }
                }
            } else {
                this.lockX = false;
                this.lockY = false;
            }

            // Horizontal
            // ---
            if (!this.lockX) {
                // right side boundary
                let wrapperWidth = this.wrapper.scrollWidth || this.wrapper.offsetWidth;
                if (left + this.$el.offsetWidth > wrapperWidth) {
                    left = wrapperWidth - this.$el.offsetWidth;
                }

                // left side boundary
                left = left < 0 ? 0 : left;

                this.$el.style.left = left + 'px';
            }

            // Vertical
            // ---
            if (!this.lockY) {
                // bottom side boundary
                let wrapperHeight = this.wrapper.scrollHeight || this.wrapper.offsetHeight;
                if (top + this.$el.offsetHeight > wrapperHeight) {
                    top = wrapperHeight - this.$el.offsetHeight;
                }

                // top side boundary
                top = top < 0 ? 0 : top;

                this.$el.style.top  = top + 'px';
            }
        },
        onDrop(e) {
            if (this.dragStarted) {
                e.preventDefault();

                this.dragStarted = false;
                this.lockX       = false;
                this.lockY       = false;

                this.$el.classList.remove('no-pointer-events');
                this.wrapper.classList.remove('dragging');

                this.$emit('dragStopped', e);
            }

            document.removeEventListener('touchmove', this.onMove);
            document.removeEventListener('mousemove', this.onMove);
            document.removeEventListener('touchend', this.onDrop);
            document.removeEventListener('mouseup', this.onDrop);
        },
    },
}
</script>
