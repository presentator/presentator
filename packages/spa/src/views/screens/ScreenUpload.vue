<template>
    <div ref="uploadContainer"
        class="box box-placeholder dz-clickable"
        :class="{'dz-uploading': isUploading}"
    >
        <div class="content">
            <template v-if="!isUploading">
                <div class="icon"><i class="fe fe-upload-cloud"></i></div>
                <h3 class="title">{{ $t('Upload screens') }}</h3>
                <p class="txt-small txt-hint">{{ $t('Drop or click here to upload new screens') }}</p>
            </template>

            <template v-else>
                <div class="progress-bar"></div>
                <p class="txt-hint">{{ (uploadProgress < 100) ? $t('Uploading...') : $t('Upload completed.') }}</p>
            </template>
        </div>
    </div>
</template>

<script>
import Dropzone     from 'dropzone';
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';

export default {
    name: 'screen-upload',
    props: {
        prototypeId: {
            required: true,
        },
    },
    data() {
        return {
            dropzone:        null,
            isUploading:     false,
            uploadProgress:  0,
            successUploaded: 0,
        }
    },
    mounted() {
        Dropzone.autoDiscover = false;

        this.initDropzone();
    },
    destroyed() {
        if (this.dropzone) {
            this.dropzone.destroy();
        }
    },
    methods: {
        initDropzone() {
            this.dropzone = new Dropzone(this.$refs.uploadContainer, {
                url: ApiClient.$baseUrl + '/screens',
                method: 'post',
                paramName: 'file',
                timeout: 0,
                parallelUploads: 1, // limit parallel uploads to keep seletection files order
                uploadMultiple: false,
                thumbnailWidth: null,
                thumbnailHeight: null,
                addRemoveLinks: false,
                createImageThumbnails: false,
                previewTemplate: '<div style="display: none"></div>',
            });

            this.dropzone.on('addedfile', (file) => {
                // update the authorization header each time when a new file is added
                this.dropzone.options.headers = Object.assign(this.dropzone.options.headers || {}, {
                    'Authorization': ('Bearer ' + ApiClient.$token),
                });
            });

            this.dropzone.on('sending', (file, xhr, formData) => {
                formData.append('prototypeId', this.prototypeId);

                if (!this.isUploading) {
                    this.isUploading     = true;
                    this.uploadProgress  = 0;
                    this.successUploaded = 0;
                }
            });

            this.dropzone.on('totaluploadprogress', (uploadProgress, totalBytes, totalBytesSent) => {
                this.uploadProgress = uploadProgress;
            });

            this.dropzone.on('queuecomplete', () => {
                this.uploadProgress = 100;

                setTimeout(() => {
                    this.isUploading = false;

                    this.$emit('screensQueueComplete', this.successUploaded);
                }, 200); // animation delay to prevent "flickering"

                if (this.successUploaded > 0) {
                    this.$toast(this.$tc('Successfully uploaded 1 screen. | Successfully uploaded {count} screens.', this.successUploaded));
                }

                this.dropzone.removeAllFiles(true); // clear queue
            });

            this.dropzone.on('error', (file, response, xhr) => {
                var message = file.name + ': ' + CommonHelper.getNestedVal(response, 'errors.file', this.$t('An error occurred while uploading the screen.'));

                this.$toast(message, 'danger');
            });

            this.dropzone.on('success', (file, response) => {
                this.successUploaded++;

                this.$emit('screenUploaded', response);
            });
        },
    }
}
</script>
