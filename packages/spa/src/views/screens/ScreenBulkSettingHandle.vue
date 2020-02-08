<template>
  <span class="m-l-5"
      :class="isProcessing ? 'txt-fade' : 'link-primary'"
      v-tooltip.top="$t('Apply to all screens')"
      @click.prevent="bulkApply()"
  >
      <i class="fe fe-copy"></i>
  </span>
</template>

<script>
import { mapState } from 'vuex';
import ApiClient    from '@/utils/ApiClient';
import Screen       from '@/models/Screen';

export default {
    name: 'screen-bulk-setting-handle',
    props: {
        screen: {
            type:     Screen,
            required: true,
        },
        setting: {
            type:     String,
            required: true,
        },
    },
    data() {
        return {
            isProcessing: false,
        }
    },
    computed: {
        ...mapState({
            screens: state => state.screens.screens,
        }),
    },
    methods: {
        bulkApply() {
            if (this.isProcessing || typeof this.screen[this.setting] == 'undefined') {
                return;
            }

            this.isProcessing = true;

            const data = {'prototypeId': this.screen.prototypeId};
            data[this.setting] = this.screen[this.setting];

            ApiClient.enableAutoCancellation(false);

            ApiClient.Screens.bulkUpdate(data).then((response) => {
                // update all screens
                for (let i = this.screens.length - 1; i >= 0; i--) {
                    this.$set(this.screens[i], this.setting, this.screen[this.setting]);
                }

                this.$toast('Successfully updated all screens.');
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
