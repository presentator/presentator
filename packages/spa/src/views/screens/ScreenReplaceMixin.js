import Dropzone     from 'dropzone';
import CommonHelper from '@/utils/CommonHelper';
import ApiClient    from '@/utils/ApiClient';

export default {
    data() {
        return {
            dropzone: null,
            isReplacing: false,
            isReplaceHandleClickable: true,
        }
    },
    mounted() {
        Dropzone.autoDiscover = false;
    },
    destroyed() {
        this.destroyReplace();
    },
    methods: {
        destroyReplace() {
            this.isReplacing = false;

            if (this.dropzone) {
                this.dropzone.destroy();
            }
        },
        initReplace(screen, replaceHandle) {
            replaceHandle = replaceHandle || this.$refs.replaceHandle;

            // reset
            this.destroyReplace();

            if (!replaceHandle || !screen || !screen.id) {
                return;
            }

            this.dropzone = new Dropzone(replaceHandle, {
                url: (ApiClient.$baseUrl + '/screens/' + encodeURIComponent(screen.id)),
                clickable: this.isReplaceHandleClickable,
                method: 'put',
                paramName: 'file',
                maxFiles: 1,
                parallelUploads: 1,
                uploadMultiple: false,
                thumbnailWidth: null,
                thumbnailHeight: null,
                addRemoveLinks: false,
                createImageThumbnails: false,
                previewTemplate: '<div style="display: none"></div>',
                accept: async (file, done) => {
                    // get replace image dimension
                    const replaceObjectUrl  = window.URL.createObjectURL(file);
                    const replaceDimensions = await CommonHelper.loadImage(replaceObjectUrl);
                    window.URL.revokeObjectURL(replaceObjectUrl); // cleanup

                    // get old image dimensions
                    const oldDimensions = await CommonHelper.loadImage(screen.getImage('original'));

                    const needConfirmation = replaceDimensions.width < oldDimensions.width || replaceDimensions.height < oldDimensions.height;

                    if (
                        needConfirmation &&
                        !window.confirm(
                            this.$t('Replacing could result in hotspots and comments misplacement if the new screen image has different dimensions from the original.') +
                            '\n' +
                            this.$t('Do you still want to proceed?')
                        )
                    ) {
                        this.dropzone.removeAllFiles(true);

                        return;
                    }

                    done();
                }
            });

            this.dropzone.on('addedfile', async (file) => {
                this.dropzone.options.headers = Object.assign(this.dropzone.options.headers || {}, {
                    'Authorization': ('Bearer ' + ApiClient.$token),
                });
            });

            this.dropzone.on('sending', (file, xhr, formData) => {
                this.isReplacing = true;
            });

            this.dropzone.on('queuecomplete', () => {
                this.isReplacing = false;
                this.dropzone.removeAllFiles(true); // reset queue
            });

            this.dropzone.on('success', (file, response) => {
                screen.load(response);

                this.replaceSucceeded();
            });

            this.dropzone.on('error', (file, response, xhr) => {
                const message = CommonHelper.getNestedVal(response, 'errors.file', this.$t('An error occurred while uploading the screen.'));

                this.$toast(message, 'danger');
                this.dropzone.removeAllFiles(true); // reset queue
            });
        },
        // overwrite in the host component if you need additional
        // handling after screen replace
        replaceSucceeded() {},
    },
}
