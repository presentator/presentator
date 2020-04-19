import moment from 'moment';

const cachedLoadedImages = {};

/**
 * Commonly used generic static helper methods.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class CommonHelper {
    /**
     * @param  {Mixed} value
     * @return {Boolean}
     */
    static isObject (value) {
        return value !== null && typeof value === 'object' && value.constructor !== Array;
    }

    /**
     * @param  {Mixed} value
     * @return {Boolean}
     */
    static isArray (value) {
        return value !== null && typeof value === 'object' && value.constructor === Array;
    }

    /**
     * @param  {Mixed} value
     * @return {Boolean}
     */
    static isFunction (value) {
        return value !== null && typeof value === 'function';
    }

    /**
     * @param  {Mixed} value
     * @return {Boolean}
     */
    static isBoolean (value) {
        return typeof value === 'boolean';
    }

    /**
     * @param  {Mixed value
     * @return {Boolean}
     */
    static isString (value) {
        return typeof value === 'string';
    }

    /**
     * Checks whether a value is empty. The following values are considered as empty:
     * - null
     * - undefined
     * - empty string
     * - empty array
     * - empty object
     *
     * @param  {Mixed} value
     * @return {Boolean}
     */
    static isEmpty (value) {
        return (
            (value === '') ||
            (value === null) ||
            (typeof value === 'undefined') ||
            (this.isArray(value) && value.length === 0) ||
            (this.isObject(value) && Object.keys(value).length === 0)
        );
    }

    /**
     * Loosely checks if value exists in an array.
     *
     * @param  {Array}  arr
     * @param  {String} value
     * @return {Boolean}
     */
    static inArray (arr, value) {
        for (let i = arr.length - 1; i >= 0; i--) {
            if (arr[i] == value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Group objects array by a specific key.
     *
     * @param  {Array}  objectArr
     * @param  {String} key
     * @return {Object}
     */
    static groupByKey(objectArr, key) {
        var result = {};

        for (let i in objectArr) {
            result[objectArr[i][key]] = result[objectArr[i][key]] || [];

            result[objectArr[i][key]].push(objectArr[i]);
        }

        return result;
    }

    /**
     * Removes single element from objects array by matching a property value.
     *
     * @param {Array}  objectArr
     * @param {String} key
     * @param {Mixed}  value
     */
    static removeByKey(objectArr, key, value) {
        for (let i in objectArr) {
            if (objectArr[i][key] == value) {
                objectArr.splice(i, 1);

                break;
            }
        }
    }

    /**
     * Returns single element from objects array by matching a property value.
     *
     * @param  {Array} objectArr
     * @param  {Mixed} key
     * @param  {Mixed} value
     * @return {Object}
     */
    static findByKey(objectArr, key, value) {
        for (let i in objectArr) {
            if (objectArr[i][key] == value) {
                return objectArr[i];
            }
        }

        return null;
    }

    /**
     * Moves a single element objects array at the beginning.
     *
     * @param {Array} objectArr
     * @param {Mixed} key
     * @param {Mixed} value
     */
    static unshiftByKey(objectArr, key, value) {
        for (let i = objectArr.length - 1; i >= 0; i--) {
            if (objectArr[i][key] == value) {
                if (i != 0) {
                    let splicedElems = objectArr.splice(i, 1);
                    objectArr.unshift(splicedElems[0]);
                }

                break;
            }
        }
    }

    /**
     * Adds an object to an array (if not existing already).
     *
     * @param  {Array}  objectArr
     * @param  {Object} item
     * @param  {Mixed}  [key]
     * @return {Array}
     */
    static pushUnique(objectArr, item, key = 'id') {
        for (let i = objectArr.length - 1; i >= 0; i--) {
            if (objectArr[i][key] == item[key]) {
                return; // already exist
            }
        }

        objectArr.push(item);
    }

    /**
     * Safely access nested object/array key with dot-notation.
     *
     * @example
     * ```javascript
     * var myObj = {a: {b: {c: 3}}}
     * this.getNestedVal(myObj, 'a.b.c');       // returns 3
     * this.getNestedVal(myObj, 'a.b.c.d');     // returns null
     * this.getNestedVal(myObj, 'a.b.c.d', -1); // returns -1
     * ```
     *
     * @param  {Object|Array} obj
     * @param  {Mixed}        key
     * @param  {Mixed}        [defaultVal]
     * @param  {String}       [delimiter]
     * @return {Mixed}
     */
    static getNestedVal (obj, key, defaultVal = null, delimiter = '.') {
        var result = obj || {};
        var parts  = key.split(delimiter);

        for (let i = 0; i < parts.length; i++) {
            if (typeof result[parts[i]] === 'undefined') {
                return defaultVal;
            }

            result = result[parts[i]];
        }

        return result;
    }

    /**
     * Generates random string (suitable for elements id and Vue keys).
     *
     * @param  {Number} [length] Results string length (default 10)
     * @return {String}
     */
    static randomString (length) {
        length = length || 10;

        var result   = '';
        var alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        for (let i = 0; i < length; i++) {
            result += alphabet.charAt(Math.floor(Math.random() * alphabet.length));
        }

        return result;
    }

    /**
     * Converts utc datetime string to local one.
     *
     * @param  {String} utcDate
     * @param  {String} [format]
     * @return {String}
     */
    static utcToLocal(utcDate, format = 'YYYY-MM-DD HH:mm:ss') {
        return moment.utc(utcDate).local().format(format);
    }

    /**
     * Returns human readable string for a time related to the current local date.
     *
     * @param  {String} utcDate
     * @return {String}
     */
    static getTimeFromNow(utcDate) {
        return moment.utc(utcDate).local().fromNow();
    }

    /**
     * Copies text to the user clipboard.
     *
     * @param {String} text
     * @param {Boolean}
     */
    static copyToClipboard(text) {
        if (!text.length) {
            return false;
        }

        try {
            // create a dummy textarea
            var textarea = document.createElement('textarea');
            document.body.appendChild(textarea);

            // copy `text` to clipboard
            textarea.value = text;
            textarea.select();
            document.execCommand('copy');

            // remove dummy textarea from dom
            document.body.removeChild(textarea);

            return true;
        } catch (err) {
            console.warn('Failed to copy.');
        }

        return false;
    }

    /**
     * Returns constrast '#ffffff' or '#000000' color based on the provided hex.
     * Based on the answer from https://stackoverflow.com/questions/24226085/changing-text-depending-on-background
     *
     * @param  {String} hex
     * @return {String}
     */
    static getContrastHex(hex) {
        if (!hex) {
            return '#ffffff'
        }

        // normalize hex format
        if (hex.startsWith('#')) {
            hex = hex.substr(1)
        }

        return (parseInt(hex, 16) > 0xffffff/2) ? '#000000': '#ffffff';
    }

    /**
     * Converts hex string to rgb object.
     * Based on the answer from https://stackoverflow.com/questions/5623838/rgb-to-hex-and-hex-to-rgb
     *
     * @param  {String} hex
     * @return {Object}
     */
    static hexToRgb(hex) {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

        if (result) {
            return {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            };
        }

        return {};
    }

    /**
     * Sets the carrent position for a content editable div.
     *
     * @param {Object} contentEditableElement
     * @param {Number} [position]
     */
    static setCaretPosition(contentEditableElement, position = 0) {
        if (typeof document.createRange !== 'function') {
            return;
        }

        var range = document.createRange();
        range.selectNodeContents(contentEditableElement);
        range.setStart(contentEditableElement.firstChild, position);
        range.setEnd(contentEditableElement.firstChild, position);

        var selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
    }

    /**
     * Checks whether the provided dom element is a form field (input, textarea, select or button).
     *
     * @param  {Node} element
     * @return {Boolean}
     */
    static isFormField(element) {
        var tagName = element && element.tagName ? element.tagName.toLowerCase() : '';

        if (
            tagName === 'input' ||
            tagName === 'button' ||
            tagName === 'select' ||
            tagName === 'textarea'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns a promise whith loaded image url dimensions.
     *
     * @param  {String} imageUrl
     * @return {Promise}
     */
    static loadImage(imageUrl) {
        return new Promise((resolve, reject) => {
            // load from cache
            if (cachedLoadedImages[imageUrl]) {
                return resolve({
                    success: true,
                    url:     imageUrl,
                    width:   cachedLoadedImages[imageUrl].width,
                    height:  cachedLoadedImages[imageUrl].height,
                });
            }

            let img = new Image();

            // successfully loaded
            img.onload = () => {
                cachedLoadedImages[imageUrl] = {
                    width:  img.naturalWidth,
                    height: img.naturalHeight,
                }

                return resolve({
                    success: true,
                    url:     imageUrl,
                    width:   img.naturalWidth,
                    height:  img.naturalHeight,
                });
            };

            // fail gracefully
            img.onerror = () => resolve({
                success: false,
                url:     imageUrl,
                width:   0,
                height:  0,
            });

            img.src = imageUrl;
        });
    }

    /**
     * Returns the rect coordinates of the closest feature (eg. button) within an image area.
     * Note: for more advanced feature/edge detection consider using sobel filter or similar.
     *
     * @param  {Image}  imgElem     Image object to process.
     * @param  {Object} [crop]      Set if you want to process only part of the image.
     * @param  {Number} [threshold] Pixel "diversity/edge" thresshold.
     * @return {Object} Object with `x`, `y`, `w` and `h` properties of the found feature.
     */
    static closestFeatureEdge(imgElem, crop = {}, threshold = 5) {
        crop.x = crop.x || 0;
        crop.y = crop.y || 0;
        crop.w = crop.w || imgElem.naturalWidth;
        crop.h = crop.h || imgElem.naturalHeight;

        // create in memory canvas to get image data
        var canvas    = document.createElement('canvas');
        canvas.width  = crop.w;
        canvas.height = crop.h;

        // draw image on white canvas
        var context = canvas.getContext('2d');
        context.fillStyle = 'white';
        context.fillRect(0, 0, crop.w, crop.h);
        context.drawImage(imgElem, crop.x, crop.y, crop.w, crop.h, 0, 0, crop.w, crop.h);

        // get image data
        var imageData = context.getImageData(0, 0, crop.w, crop.h);

        // convert to greyscale (for single channel comparision)
        for (let y = 0; y < imageData.height; y++){
            for (let x = 0; x < imageData.width; x++){
                let i = (x + y * imageData.width) * 4;
                let avg = (imageData.data[i] + imageData.data[i + 1] + imageData.data[i + 2]) / 3;

                imageData.data[i] = avg;
                imageData.data[i + 1] = avg;
                imageData.data[i + 2] = avg;
            }
        }

        var minX = crop.w;
        var maxX = 0;
        var minY = crop.h;
        var maxY = 0;

        // fill edge points
        for (let y = 0; y < imageData.height; y++) {
            for (let x = 0; x < imageData.width; x++) {
                // we are checking only a single channel (red)
                // since the image is greyscale and therefore all channels are the same
                let i     = (x + y * imageData.width) * 4;
                let pixel = imageData.data[i];

                // get surrounding pixels
                let left   = imageData.data[i-4];
                let right  = imageData.data[i];
                let top    = imageData.data[i-(crop.w*4)];
                let bottom = imageData.data[i+(crop.w*4)];

                // compare pixels
                if (
                    (pixel > left + threshold) ||
                    (pixel < left - threshold) ||
                    (pixel > right + threshold) ||
                    (pixel < right - threshold) ||
                    (pixel > top + threshold) ||
                    (pixel < top - threshold) ||
                    (pixel > bottom + threshold) ||
                    (pixel < bottom - threshold)
                ) {
                    if (x < minX) {
                        minX = x;
                    }

                    if (x > maxX) {
                        maxX = x;
                    }

                    if (y < minY) {
                        minY = y;
                    }

                    if (y > maxY) {
                        maxY = y;
                    }
                }
            }
        }

        // normalize points
        minX = minX < crop.w ? minX : 0;
        minY = minY < crop.h ? minY : 0;
        maxX = maxX || crop.w;
        maxY = maxY || crop.h;

        return {
            x: minX,
            y: minY,
            w: maxX - minX,
            h: maxY - minY,
        }
    }

    /**
     * Returns JWT token's payload data.
     *
     * @param  {String} token
     * @return {Object}
     */
    static getJwtPayload(token) {
        try {
            let base64 = decodeURIComponent(atob(token.split('.')[1]).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));

            return JSON.parse(base64) || {};
        } catch (e) {
        }

        return {};
    }

    /**
     * Checks whether a jwt token is expired or not.
     *
     * @param  {String} token
     * @param  {Number} [expirationThreshold] Time in seconds that will be substracted from the token `exp` property.
     * @return {Boolean}
     */
    static isJwtExpired(token, expirationThreshold = 0) {
        let payload = CommonHelper.getJwtPayload(token);

        if (
            !CommonHelper.isEmpty(payload) &&
            (!payload.exp || (payload.exp - expirationThreshold) > moment().format('X'))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Opens url address within a new popup window.
     *
     * @param  {String} url
     * @param  {Number} [width]  Popup window width (Default: 600).
     * @param  {Number} [height] Popup window height (Default: 480).
     * @param  {String} [name]   The name of the created popup window (default to 'popup').
     * @return {Object} Reference to the newly created window.
     */
    static openInWindow(url, width, height, name) {
        width  = width  || 1024;
        height = height || 768;
        name   = name   || 'popup';

        var windowWidth  = window.innerWidth;
        var windowHeight = window.innerHeight;

        // normalize window size
        width  = width > windowWidth ? windowWidth : width;
        height = height > windowHeight ? windowHeight : height;

        var left = (windowWidth / 2) - (width / 2);
        var top  = (windowHeight / 2) - (height / 2);

        return window.open(
            url,
            name,
            'width='+width+',height='+height+',top='+top+',left='+left+',resizable,menubar=no'
        );
    }

    /**
     * Very simple and robust query params parser.
     *
     * @param  {String} url
     * @return {Object}
     */
    static getQueryParams(url) {
        var result        = {};
        var hashStartPos  = url.indexOf('#');
        var queryStartPos = url.indexOf('?');
        var params        = url.substring(queryStartPos + 1, (hashStartPos > queryStartPos ? hashStartPos : url.length)).split('&');

        for (let i in params) {
            let parts = params[i].split('=');
            if (parts.length === 2) {
                result[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1]);
            }
        }

        return result;
    }

    /**
     * Returns a word info object at specific position.
     *
     * @param  {String} str
     * @param  {Number} index
     * @return {Object}
     */
    static getWordInfoAt(str, index) {
        var words           = str.replace(/(\r\n|\n|\r)/gm, ' ').split(' ');
        var prevWordsLength = 0;
        var startIndex      = 0;
        var endIndex        = 0;

        for (let i = 0; i < words.length; i++) {
            startIndex = prevWordsLength + i;
            endIndex   = (startIndex + words[i].length - 1);

            if (index >= startIndex && index <= endIndex) {
                return {
                    'start': startIndex,
                    'end':   endIndex,
                    'word':  words[i],
                };
            }

            prevWordsLength += words[i].length;
        }

        return {};
    }

    /**
     * Helper factory method that adds reset functionality to a vuex store definition.
     * This method sets additional `reset` mutation and action to the definition and returns the modified object.
     *
     * NB! The only requirement is the definition object to have a function `initialState()` that returns the initial store state.
     *
     * @param  {Object} definition
     * @return {Object}
     */
    static createResettableStore(definition) {
        if (CommonHelper.isFunction(definition.initialState)) {
            definition.state = definition.initialState();
        }

        if (!definition.mutations || !definition.mutations.reset) {
            definition.mutations = definition.mutations || {};
            definition.mutations.reset = (state, items) => {
                if (!CommonHelper.isFunction(definition.initialState)) {
                    return;
                }

                const initial = definition.initialState();

                for (let key in initial) {
                    if (!items || !items.length || items.indexOf(key) >=0) {
                        state[key] = initial[key];
                    }
                }
            };
        }

        if (!definition.actions || !definition.actions.reset) {
            definition.actions = definition.actions || {};
            definition.actions.reset = (context, items) => {
                context.commit('reset', items);
            };
        }

        return definition;
    }
}
