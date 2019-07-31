<template>
    <div @mousedown.left="dragInit" @touchstart="dragInit">
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: 'resizer',
    props: {
        containerSelector: {
            type: String,
        },
        wrapperSelector: {
            type: String,
        },
        tolerance: {
            type:    Number,
            default: 5,
        },
    },
    data() {
        return {
            resizeStarted:          false,
            lockX:                  false,
            lockY:                  false,
            startX:                 0,
            startY:                 0,
            initialContainerX:      0,
            initialContainerY:      0,
            initialContainerWidth:  0,
            initialContainerHeight: 0,
            initialWrapperWidth:    0,
            initialWrapperHeight:   0,
        }
    },
    computed: {
        container() {
            return this.$el.closest(this.containerSelector) || this.$el.parentNode;
        },
        wrapper() {
            return this.container.closest(this.wrapperSelector) || this.container.parentNode;
        },
    },
    methods: {
        dragInit(e) {
            e.stopPropagation();

            this.startX                 = e.clientX;
            this.startY                 = e.clientY;
            this.initialContainerX      = this.container.offsetLeft;
            this.initialContainerY      = this.container.offsetTop;
            this.initialContainerWidth  = this.container.offsetWidth;
            this.initialContainerHeight = this.container.offsetHeight;
            this.initialWrapperWidth    = this.wrapper.scrollWidth  || this.wrapper.offsetWidth;
            this.initialWrapperHeight   = this.wrapper.scrollHeight || this.wrapper.offsetHeight;

            document.addEventListener('touchmove', this.onMove);
            document.addEventListener('mousemove', this.onMove);
            document.addEventListener('touchend', this.onDrop);
            document.addEventListener('mouseup', this.onDrop);
        },
        onMove(e) {
            let moveDiffX = e.clientX - this.startX;
            let moveDiffY = e.clientY - this.startY;
            let left      = null;
            let top       = null;
            let width     = null;
            let height    = null;

            if (
                !this.resizeStarted &&
                (Math.abs(moveDiffX) < this.tolerance) &&
                (Math.abs(moveDiffY) < this.tolerance)
            ) {
                return;
            }

            e.preventDefault();

            if (!this.resizeStarted) {
                this.resizeStarted = true;

                this.container.classList.add('no-pointer-events');
                this.wrapper.classList.add('resizing');
                document.body.classList.add('resizer-active');

                this.$emit('resizeStarted', e);
            }

            this.$emit('resizing', e);

            // lock/release axis
            if (e.shiftKey) {
                if (!this.lockX && !this.lockY) {
                    if (Math.abs(moveDiffX) > Math.abs(moveDiffY)) {
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
                if (moveDiffX < 0 && moveDiffX < -this.initialContainerWidth) {
                    // backwards resizing
                    width = -(this.initialContainerWidth + moveDiffX);
                    left  = this.initialContainerX - width;

                    // reset on left side boundary reach
                    if (left < 0) {
                        left  = null;
                        width = null;
                    }
                } else {
                    width = this.initialContainerWidth + moveDiffX;

                    // reset on right side boundary reach
                    if (width + this.initialContainerX > this.initialWrapperWidth) {
                        width = null;
                        left  = null;
                    }
                }
            }

            // Vertical
            // ---
            if (!this.lockY) {
                if (moveDiffY < 0 && moveDiffY < -this.initialContainerHeight) {
                    // backwards resizing
                    height = -(this.initialContainerHeight + moveDiffY);
                    top    = this.initialContainerY - height;

                    // reset on top side boundary reach
                    if (top < 0) {
                        top    = null;
                        height = null;
                    }
                } else {
                    height = this.initialContainerHeight + moveDiffY;

                    // reset on bottom side boundary reach
                    if (height + this.initialContainerY > this.initialWrapperHeight) {
                        height = null;
                        top    = null;
                    }
                }
            }

            if (left !== null) {
                this.container.style.left = left + 'px';
            }

            if (width !== null) {
                this.container.style.width = width + 'px';
            }

            if (top !== null) {
                this.container.style.top = top + 'px';
            }

            if (height !== null) {
                this.container.style.height = height + 'px';
            }
        },
        onDrop(e) {
            if (this.resizeStarted) {
                e.preventDefault();

                this.resizeStarted = false;
                this.lockX         = false;
                this.lockY         = false;

                this.container.classList.remove('no-pointer-events');
                this.wrapper.classList.remove('resizing');
                document.body.classList.remove('resizer-active');

                this.$emit('resizeStopped', e);
            }

            document.removeEventListener('touchmove', this.onMove);
            document.removeEventListener('mousemove', this.onMove);
            document.removeEventListener('touchend', this.onDrop);
            document.removeEventListener('mouseup', this.onDrop);
        },
    },
}
</script>
