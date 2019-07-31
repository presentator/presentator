<template>
    <div class="dropdown" :class="{'active': isActive}">
        <template v-for="(item, i) in filteredList">
            <div ref="mentions"
                class="dropdown-item"
                :class="{'highlight': focusIndex == i}"
                @click.prevent="select(i)"
            >{{ item.label }}</div>
            <hr v-if="i+1 != filteredList.length">
        </template>
    </div>
</template>

<script>
import CommonHelper from '@/utils/CommonHelper';

export default {
    name: 'mentions-list',
    props: {
        list: {
            type:     Array, // eg. [{value: 'my_searchable_keyword', label: 'My label'}, ...]
            required: true,
        },
        triggers: {
            type: Array,
            default() {
                return ['+', '@'];
            }
        },
        input: {}, // selector or dom element (default to sibling textarea/input)
    },
    data() {
        return {
            isActive:     false,
            focusIndex:   0,
            filteredList: [],
        }
    },
    computed: {
        inputElem() {
            if (this.input instanceof Node) {
                return this.input;
            }

            if (
                this.input &&
                CommonHelper.isString(this.input) &&
                document.querySelector(this.input)
            ) {
                return document.querySelector(this.input);
            }

            return this.$el.parentNode.querySelector('textarea, input');
        },
    },
    mounted() {
        if (!this.inputElem) {
            console.warn('MentionsList: input element is invalid or missing.');
            return;
        }

        this.inputElem.addEventListener('input', this.onInputChange);
        this.inputElem.addEventListener('keydown', this.onInputKeydown);
    },
    beforeDestroy() {
        if (!this.inputElem) {
            return;
        }

        this.inputElem.removeEventListener('input', this.onInputChange);
        this.inputElem.removeEventListener('keydown', this.onInputKeydown);
    },
    methods: {
        hide() {
            if (!this.isActive) {
                return; // already hidden
            }

            this.isActive = false;

            this.focusIndex = 0;

            this.filteredList = [];

            this.$emit('hide');
        },
        show() {
            if (this.isActive) {
                return; // already visible
            }

            this.isActive = true;

            this.focusIndex = 0;

            this.$emit('show');

            this.positionFocusedMendtionWithinView();
        },
        focusPrev() {
            if (this.focusIndex <= 0) {
                this.focusIndex = this.filteredList.length - 1;
            } else {
                this.focusIndex--;
            }

            this.positionFocusedMendtionWithinView();
        },
        focusNext() {
            if (this.focusIndex >= this.filteredList.length - 1) {
                this.focusIndex = 0;
            } else {
                this.focusIndex++;
            }

            this.positionFocusedMendtionWithinView();
        },
        positionFocusedMendtionWithinView() {
            if (this.$refs.mentions && this.$refs.mentions[this.focusIndex]) {
                this.$refs.mentions[this.focusIndex].scrollIntoView({
                    block: 'nearest',
                });
            }
        },
        getCurrentWordInfo() {
            var cursorPos = typeof this.inputElem.selectionStart !== 'undefined' ? this.inputElem.selectionStart : this.inputElem.value.length;
            var wordInfo = CommonHelper.getWordInfoAt(this.inputElem.value, cursorPos - 1);

            return wordInfo;
        },
        select(index) {
            index = typeof index !== 'undefined' ? index : this.focusIndex;
            var wordInfo = this.getCurrentWordInfo();

            if (
                !wordInfo.word ||
                !this.inputElem ||
                !this.filteredList[index]
            ) {
                return;
            }

            var val = this.inputElem.value;

            this.inputElem.value = (
                val.substring(0, wordInfo.start) +
                this.triggers[0] +
                this.filteredList[index].value +
                val.substring(wordInfo.end + 1) +
                ' '
            );

            // trigger v-model update
            this.inputElem.dispatchEvent(new Event('input'));

            this.$nextTick(() => {
                this.inputElem.focus();
                this.hide();
            });
        },
        filterList(needle) {
            // normalize search string
            needle = (needle || '').toLowerCase().replace(/\s+/g, '');

            if (!needle) {
                return this.list;
            }

            var result = [];

            for (let i = this.list.length - 1; i >= 0; i--) {
                let haystack = ((this.list[i].value + this.list[i].label) || '')
                        .toLowerCase()
                        .replace(/\s+/g, '');

                if (haystack.indexOf(needle) >= 0) {
                    result.push(this.list[i]);
                }
            }

            return result;
        },

        // Input events handlers
        onInputChange(e) {
            var wordInfo = this.getCurrentWordInfo();

            // reset
            this.hide();

            if (
                wordInfo.word &&
                // wordInfo.word.length > 1 &&
                this.triggers.indexOf(wordInfo.word[0]) >= 0
            ) {
                this.filteredList = this.filterList(wordInfo.word.substring(1));
                if (this.filteredList.length) {
                    this.show();
                }
            }
        },
        onInputKeydown(e) {
            if (!this.isActive) {
                return;
            }

            var code = e.which || e.keyCode;

            if (code == 13) { // enter
                e.preventDefault();

                this.select();
            } else if (code == 38) { // up
                e.preventDefault();

                this.focusPrev();
            } else if (code == 40) { // down
                e.preventDefault();

                this.focusNext();
            }
        },
    },
}
</script>
