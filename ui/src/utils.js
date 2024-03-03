import { DateTime } from "luxon";

const cachedLoadedImages = [];

const defaultRandomAlphabet = "abcdefghijklmnopqrstuvwxyz0123456789";

export default class utils {
    /**
     * Checks whether value is plain object.
     *
     * @param  {Mixed} value
     * @return {Boolean}
     */
    static isObject(value) {
        return value !== null && typeof value === "object" && value.constructor === Object;
    }

    /**
     * Checks whether a value is empty. The following values are considered as empty:
     * - null
     * - undefined
     * - empty string
     * - empty array
     * - empty object
     * - zero uuid, time and dates
     *
     * @param  {Mixed} value
     * @return {Boolean}
     */
    static isEmpty(value) {
        return (
            typeof value === "undefined" ||
            value === null ||
            value === "" ||
            value === "00000000-0000-0000-0000-000000000000" || // zero uuid
            value === "0001-01-01 00:00:00.000Z" || // zero datetime
            value === "0001-01-01" || // zero date
            (Array.isArray(value) && value.length === 0) ||
            (utils.isObject(value) && Object.keys(value).length === 0)
        );
    }

    /**
     * Checks whether the provided dom element is a form field (input, textarea, select).
     *
     * @param  {Node} element
     * @return {Boolean}
     */
    static isInput(element) {
        let tagName = element && element.tagName ? element.tagName.toLowerCase() : "";

        return (
            tagName === "input" || tagName === "select" || tagName === "textarea" || element.isContentEditable
        );
    }

    /**
     * Checks if an element is a common focusable one.
     *
     * @param  {Node} element
     * @return {Boolean}
     */
    static isFocusable(element) {
        let tagName = element && element.tagName ? element.tagName.toLowerCase() : "";

        return (
            utils.isInput(element) ||
            tagName === "button" ||
            tagName === "a" ||
            tagName === "details" ||
            element.tabIndex >= 0
        );
    }

    /**
     * Normalizes and returns arr as a new array instance.
     *
     * @param  {Array}   arr
     * @param  {Boolean} [allowEmpty]
     * @return {Array}
     */
    static toArray(arr, allowEmpty = false) {
        if (Array.isArray(arr)) {
            return arr.slice();
        }

        return (allowEmpty || !utils.isEmpty(arr)) && typeof arr !== "undefined" ? [arr] : [];
    }

    /**
     * Loosely checks if value exists in an array.
     *
     * @param  {Array}  arr
     * @param  {String} value
     * @return {Boolean}
     */
    static inArray(arr, value) {
        arr = Array.isArray(arr) ? arr : [];

        for (let i = arr.length - 1; i >= 0; i--) {
            if (arr[i] == value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes single element from array by loosely comparying values.
     *
     * @param {Array} arr
     * @param {Mixed} value
     */
    static removeByValue(arr, value) {
        arr = Array.isArray(arr) ? arr : [];

        for (let i = arr.length - 1; i >= 0; i--) {
            if (arr[i] == value) {
                arr.splice(i, 1);
                break;
            }
        }
    }

    /**
     * Adds `value` in `arr` only if it's not added already.
     *
     * @param {Array} arr
     * @param {Mixed} value
     */
    static pushUnique(arr, value) {
        if (!utils.inArray(arr, value)) {
            arr.push(value);
        }
    }

    /**
     * Returns single element from objects array by matching its key value.
     *
     * @param  {Array} objectsArr
     * @param  {Mixed} key
     * @param  {Mixed} value
     * @return {Object}
     */
    static findByKey(objectsArr, key, value) {
        objectsArr = Array.isArray(objectsArr) ? objectsArr : [];

        for (let i in objectsArr) {
            if (objectsArr[i][key] == value) {
                return objectsArr[i];
            }
        }

        return null;
    }

    /**
     * Removes all elements from an objects array by matching a property value.
     *
     * @param {Array}  objectsArr
     * @param {String} key
     * @param {Mixed}  value
     */
    static removeByKey(objectsArr, key, value) {
        for (let i in objectsArr) {
            if (objectsArr[i][key] == value) {
                objectsArr.splice(i, 1);
            }
        }
    }

    /**
     * Adds or replace an object array element by comparing its key value.
     *
     * @param {Array}  objectsArr
     * @param {Object} item
     * @param {String} [key]
     */
    static pushOrReplaceObject(objectsArr, item, key = "id") {
        for (let i = objectsArr.length - 1; i >= 0; i--) {
            if (objectsArr[i][key] == item[key]) {
                objectsArr[i] = item; // replace
                return;
            }
        }

        objectsArr.push(item);
    }

    /**
     * Filters and returns a new objects array with duplicated elements removed.
     *
     * @param  {Array} objectsArr
     * @param  {String} key
     * @return {Array}
     */
    static filterDuplicatesByKey(objectsArr, key = "id") {
        objectsArr = Array.isArray(objectsArr) ? objectsArr : [];

        const uniqueMap = {};

        for (const item of objectsArr) {
            uniqueMap[item[key]] = item;
        }

        return Object.values(uniqueMap);
    }

    /**
     * Safely access nested object/array key with dot-notation.
     *
     * @example
     * ```javascript
     * let myObj = {a: {b: {c: 3}}}
     * this.getNestedVal(myObj, "a.b.c");       // returns 3
     * this.getNestedVal(myObj, "a.b.c.d");     // returns null
     * this.getNestedVal(myObj, "a.b.c.d", -1); // returns -1
     * ```
     *
     * @param  {Object|Array} data
     * @param  {string}       path
     * @param  {Mixed}        [defaultVal]
     * @param  {String}       [delimiter]
     * @return {Mixed}
     */
    static getNestedVal(data, path, defaultVal = null, delimiter = ".") {
        let result = data || {};
        let parts = (path || "").split(delimiter);

        for (const part of parts) {
            if ((!utils.isObject(result) && !Array.isArray(result)) || typeof result[part] === "undefined") {
                return defaultVal;
            }

            result = result[part];
        }

        return result;
    }

    /**
     * Sets a new value to an object (or array) by its key path.
     *
     * @example
     * ```javascript
     * this.setByPath({}, "a.b.c", 1);             // results in {a: b: {c: 1}}
     * this.setByPath({a: {b: {c: 3}}}, "a.b", 4); // results in {a: {b: 4}}
     * ```
     *
     * @param  {Array|Object} data
     * @param  {string}       path
     * @param  {String}       delimiter
     */
    static setByPath(data, path, newValue, delimiter = ".") {
        if (data === null || typeof data !== "object") {
            console.warn("setByPath: data not an object or array.");
            return;
        }

        let result = data;
        let parts = path.split(delimiter);
        let lastPart = parts.pop();

        for (const part of parts) {
            if (
                (!utils.isObject(result) && !Array.isArray(result)) ||
                (!utils.isObject(result[part]) && !Array.isArray(result[part]))
            ) {
                result[part] = {};
            }

            result = result[part];
        }

        result[lastPart] = newValue;
    }

    /**
     * Recursively delete element from an object (or array) by its key path.
     * Empty array or object elements from the parents chain will be also removed.
     *
     * @example
     * ```javascript
     * this.deleteByPath({a: {b: {c: 3}}}, "a.b.c");       // results in {}
     * this.deleteByPath({a: {b: {c: 3, d: 4}}}, "a.b.c"); // results in {a: {b: {d: 4}}}
     * ```
     *
     * @param  {Array|Object} data
     * @param  {string}       path
     * @param  {String}       delimiter
     */
    static deleteByPath(data, path, delimiter = ".") {
        let result = data || {};
        let parts = (path || "").split(delimiter);
        let lastPart = parts.pop();

        for (const part of parts) {
            if (
                (!utils.isObject(result) && !Array.isArray(result)) ||
                (!utils.isObject(result[part]) && !Array.isArray(result[part]))
            ) {
                result[part] = {};
            }

            result = result[part];
        }

        if (Array.isArray(result)) {
            result.splice(lastPart, 1);
        } else if (utils.isObject(result)) {
            delete result[lastPart];
        }

        // cleanup the parents chain
        if (
            parts.length > 0 &&
            ((Array.isArray(result) && !result.length) ||
                (utils.isObject(result) && !Object.keys(result).length)) &&
            ((Array.isArray(data) && data.length > 0) ||
                (utils.isObject(data) && Object.keys(data).length > 0))
        ) {
            utils.deleteByPath(data, parts.join(delimiter), delimiter);
        }
    }

    /**
     * Generates pseudo-random string (suitable for ids and keys).
     *
     * @param  {Number} [length] Results string length (default 8)
     * @return {String}
     */
    static randomString(length = 8, alphabet = defaultRandomAlphabet) {
        let result = "";

        for (let i = 0; i < length; i++) {
            result += alphabet.charAt(Math.floor(Math.random() * alphabet.length));
        }

        return result;
    }

    /**
     * Converts and normalizes string into a sentence.
     *
     * @param  {String}  str
     * @param  {Boolean} [stopCheck]
     * @return {String}
     */
    static sentenize(str, stopCheck = true) {
        if (typeof str !== "string") {
            return "";
        }

        str = str.trim().split("_").join(" ");
        if (str === "") {
            return str;
        }

        str = str[0].toUpperCase() + str.substring(1);

        if (stopCheck) {
            let lastChar = str[str.length - 1];
            if (lastChar !== "." && lastChar !== "?" && lastChar !== "!") {
                str += ".";
            }
        }

        return str;
    }

    /**
     * Splits `str` and returns its non empty parts as an array.
     *
     * @param  {String} str
     * @param  {String} [separator]
     * @return {Array}
     */
    static splitNonEmpty(str, separator = ",") {
        const items = (str || "").split(separator);
        const result = [];

        for (let item of items) {
            item = item.trim();
            if (!utils.isEmpty(item)) {
                result.push(item);
            }
        }

        return result;
    }

    /**
     * Returns a concatenated `items` string.
     *
     * @param  {String} items
     * @param  {String} [separator]
     * @return {Array}
     */
    static joinNonEmpty(items, separator = ", ") {
        const result = [];

        for (let item of items) {
            item = typeof item === "string" ? item.trim() : "";
            if (!utils.isEmpty(item)) {
                result.push(item);
            }
        }

        return result.join(separator);
    }

    /**
     * Extract the user initials from the provided username or email address
     * (eg. converts "john.doe@example.com" to "JD").
     *
     * @param  {String} str
     * @return {String}
     */
    static getInitials(str) {
        str = (str || "").split("@")[0].trim();

        if (str.length <= 2) {
            return str.toUpperCase();
        }

        const parts = str.split(/[\.\_\-\ ]/);

        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }

        return str[0].toUpperCase();
    }

    /**
     * Returns a word info object at specific position.
     *
     * @param  {String} str
     * @param  {Number} index
     * @return {Object}
     */
    static getWordInfoAt(str, index) {
        let words = str.replace(/(\r\n|\n|\r)/gm, " ").split(" ");
        let prevWordsLength = 0;
        let startIndex = 0;
        let endIndex = 0;

        for (let i = 0; i < words.length; i++) {
            startIndex = prevWordsLength + i;
            endIndex = startIndex + words[i].length - 1;

            if (index >= startIndex && index <= endIndex) {
                return {
                    start: startIndex,
                    end: endIndex,
                    word: words[i],
                };
            }

            prevWordsLength += words[i].length;
        }

        return {};
    }

    /**
     * Returns a DateTime instance from a date object/string.
     *
     * @param  {String|Date} date
     * @return {DateTime}
     */
    static getDateTime(date) {
        if (typeof date === "string") {
            const formats = {
                19: "yyyy-MM-dd HH:mm:ss",
                23: "yyyy-MM-dd HH:mm:ss.SSS",
                20: "yyyy-MM-dd HH:mm:ss'Z'",
                24: "yyyy-MM-dd HH:mm:ss.SSS'Z'",
            };
            const format = formats[date.length] || formats[19];
            return DateTime.fromFormat(date, format, { zone: "UTC" });
        }

        return DateTime.fromJSDate(date);
    }

    /**
     * Returns formatted datetime string in the UTC timezone.
     *
     * @param  {String|Date} date
     * @param  {String}      [format] The result format (see https://moment.github.io/luxon/#/parsing?id=table-of-tokens)
     * @return {String}
     */
    static formatToUTCDate(date, format = "yyyy-MM-dd HH:mm:ss") {
        return utils.getDateTime(date).toUTC().toFormat(format);
    }

    /**
     * Returns formatted datetime string in the local timezone.
     *
     * @param  {String|Date} date
     * @param  {String}      [format] The result format (see https://moment.github.io/luxon/#/parsing?id=table-of-tokens)
     * @return {String}
     */
    static formatToLocalDate(date, format = "yyyy-MM-dd HH:mm:ss") {
        return utils.getDateTime(date).toLocal().toFormat(format);
    }

    /**
     * Returns a string representation of a this time relative to now, such as "in two days".
     *
     * @param  {String|Date} date
     * @param  {String}      [format] The result format (see https://moment.github.io/luxon/#/parsing?id=table-of-tokens)
     * @return {String}
     */
    static relativeDate(date) {
        return utils.getDateTime(date).toRelative();
    }

    /**
     * Copies text to the user clipboard.
     *
     * @param  {String} text
     * @return {Promise}
     */
    static async copyToClipboard(text) {
        text = "" + text; // ensure that text is string

        if (!text.length || !window?.navigator?.clipboard) {
            return;
        }

        return window.navigator.clipboard.writeText(text).catch((err) => {
            console.warn("Failed to copy.", err);
        });
    }

    /**
     * "Yield" to the main thread to break long runing task into smaller ones.
     *
     * (see https://web.dev/optimize-long-tasks/)
     */
    static yieldToMain() {
        return new Promise((resolve) => {
            setTimeout(resolve, 0);
        });
    }

    /**
     * Checks if the provided prototype is for desktop screens.
     *
     * @param  {Object}  prototype
     * @return {Boolean}
     */
    static isDesktopPrototype(prototype) {
        return prototype.size == "";
    }

    /**
     * Returns an icon class based on the provided prototype type.
     *
     * @param  {Object} prototype
     * @return {String}
     */
    static getPrototypeIcon(prototype) {
        return utils.isDesktopPrototype(prototype) ? "iconoir-modern-tv" : "iconoir-smartphone-device";
    }

    /**
     * Returns the first available user display name identifier.
     *
     * @param  {Object} user
     * @return {String}
     */
    static getUserDisplayName(user) {
        return user?.name || user?.email || (user?.username ? "@" + user?.username : user?.id) || "";
    }

    /**
     * Returns the first available comment's author identifier.
     *
     * @param  {Object} comment
     * @return {String}
     */
    static getCommentAuthor(comment) {
        return utils.getUserDisplayName(comment?.expand?.user) || comment?.guestEmail || "";
    }

    /**
     * Returns a new `items` array based on the ids order.
     *
     * If an item is not found in the ids list, it will be appended
     * to the resulting array.
     *
     * @param  {Array<Object>} items
     * @param  {Array<mixed>} ids
     * @return {Array<Object>}
     */
    static sortItemsByIds(items, ids) {
        items = items || [];
        ids = ids || [];

        const orderedItems = [];
        for (const id of ids) {
            const i = items.findIndex((item) => item.id == id);
            if (i < 0) {
                continue;
            }

            orderedItems.push(items[i]);
            items.splice(i, 1);
        }

        // append any remaining items
        for (let item of items) {
            orderedItems.push(item);
        }

        return orderedItems;
    }

    /**
     * Triggers a window event.
     *
     * @param {String} eventName The event name to trigger.
     */
    static triggerEvent(eventName) {
        window.dispatchEvent(new Event(eventName));
    }

    /**
     * Extracts the hash query parameters from the current url and
     * returns them as plain object.
     *
     * @return {Object}
     */
    static getHashQueryParams() {
        let query = "";

        const queryStart = window.location.hash.indexOf("?");
        if (queryStart > -1) {
            query = window.location.hash.substring(queryStart + 1);
        }

        return Object.fromEntries(new URLSearchParams(query));
    }

    /**
     * Replaces the current hash query parameters with the provided `params`
     * without adding new state to the browser history.
     *
     * @param {Object} params
     */
    static replaceHashQueryParams(params) {
        params = params || {};

        let query = "";

        let hash = window.location.hash;

        const queryStart = hash.indexOf("?");
        if (queryStart > -1) {
            query = hash.substring(queryStart + 1);
            hash = hash.substring(0, queryStart);
        }

        const parsed = new URLSearchParams(query);

        for (let key in params) {
            const val = params[key];

            if (val === null) {
                parsed.delete(key);
            } else {
                parsed.set(key, val);
            }
        }

        query = parsed.toString();
        if (query != "") {
            hash += "?" + query;
        }

        // replace the hash/fragment part with the updated one
        let href = window.location.href;
        const hashIndex = href.indexOf("#");
        if (hashIndex > -1) {
            href = href.substring(0, hashIndex);
        }
        window.location.replace(href + hash);
    }

    /**
     * Returns an absolute url for a project link resource.
     *
     * @param  {Object} link
     * @return {String}
     */
    static getProjectLinkUrl(link) {
        let path = window.location.pathname.replace(/\/$/, "");
        const pathParts = path.split("/");
        // trim the admin path
        if (pathParts[pathParts.length - 1] == "_") {
            path = pathParts.slice(0, pathParts.length - 1).join("/");
        }

        return window.location.origin + path + "/#/" + (link?.username || "");
    }

    /**
     * Creates a thumbnail from `File` with the specified `width` and `height` params.
     * Returns a `Promise` with the generated base64 url.
     *
     * @param  {File}   file
     * @param  {Number} [width]
     * @param  {Number} [height]
     * @return {Promise}
     */
    static generateThumb(file, width = 100, height = 100) {
        return new Promise((resolve) => {
            let reader = new FileReader();

            reader.onload = function (e) {
                let img = new Image();

                img.onload = function () {
                    let canvas = document.createElement("canvas");
                    let ctx = canvas.getContext("2d");
                    let imgWidth = img.width;
                    let imgHeight = img.height;

                    canvas.width = width;
                    canvas.height = height;

                    ctx.drawImage(
                        img,
                        imgWidth > imgHeight ? (imgWidth - imgHeight) / 2 : 0,
                        0, // top aligned
                        // imgHeight > imgWidth ? (imgHeight - imgWidth) / 2 : 0,
                        imgWidth > imgHeight ? imgHeight : imgWidth,
                        imgWidth > imgHeight ? imgHeight : imgWidth,
                        0,
                        0,
                        width,
                        height,
                    );

                    return resolve(canvas.toDataURL(file.type));
                };

                img.src = e.target.result;
            };

            reader.readAsDataURL(file);
        });
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
                    url: imageUrl,
                    width: cachedLoadedImages[imageUrl].width,
                    height: cachedLoadedImages[imageUrl].height,
                });
            }

            let img = new Image();

            // successfully loaded
            img.onload = () => {
                cachedLoadedImages[imageUrl] = {
                    width: img.naturalWidth,
                    height: img.naturalHeight,
                };

                return resolve({
                    success: true,
                    url: imageUrl,
                    width: img.naturalWidth,
                    height: img.naturalHeight,
                });
            };

            // fail gracefully
            img.onerror = () =>
                resolve({
                    success: false,
                    url: imageUrl,
                    width: 0,
                    height: 0,
                });

            img.crossOrigin = "Anonymous";
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
        // note: trim floats to reduce the number of iterations
        crop.x = (crop.x || 0) << 0;
        crop.y = (crop.y || 0) << 0;
        crop.w = (crop.w || imgElem.naturalWidth) << 0;
        crop.h = (crop.h || imgElem.naturalHeight) << 0;

        // create in memory canvas to get image data
        let canvas = document.createElement("canvas");
        canvas.width = crop.w;
        canvas.height = crop.h;

        // draw image on white canvas
        let context = canvas.getContext("2d");
        context.fillStyle = "white";
        context.fillRect(0, 0, crop.w, crop.h);
        context.drawImage(imgElem, crop.x, crop.y, crop.w, crop.h, 0, 0, crop.w, crop.h);

        // get image data
        let imageData = context.getImageData(0, 0, crop.w, crop.h);

        // convert to greyscale (for single channel comparision)
        for (let y = 0; y < imageData.height; y++) {
            for (let x = 0; x < imageData.width; x++) {
                let i = (x + y * imageData.width) * 4;
                let avg = (imageData.data[i] + imageData.data[i + 1] + imageData.data[i + 2]) / 3;

                imageData.data[i] = avg;
                imageData.data[i + 1] = avg;
                imageData.data[i + 2] = avg;
            }
        }

        let minX = crop.w;
        let maxX = 0;
        let minY = crop.h;
        let maxY = 0;

        // fill edge points
        for (let y = 0; y < imageData.height; y++) {
            for (let x = 0; x < imageData.width; x++) {
                // we are checking only a single channel (red)
                // since the image is greyscale and therefore all channels are the same
                let i = (x + y * imageData.width) * 4;
                let pixel = imageData.data[i];

                // get surrounding pixels
                let left = imageData.data[i - 4];
                let right = imageData.data[i];
                let top = imageData.data[i - crop.w * 4];
                let bottom = imageData.data[i + crop.w * 4];

                // compare pixels
                if (
                    pixel > left + threshold ||
                    pixel < left - threshold ||
                    pixel > right + threshold ||
                    pixel < right - threshold ||
                    pixel > top + threshold ||
                    pixel < top - threshold ||
                    pixel > bottom + threshold ||
                    pixel < bottom - threshold
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
        };
    }

    /**
     * "Standardize" the access to common mouse and touch event props.
     *
     * @param {Event} e
     */
    static normalizePointerEvent(e) {
        if (typeof e.clientX == "undefined" && typeof e.touches?.[0]?.clientX != "undefined") {
            e.clientX = e.touches[0].clientX;
        }

        if (typeof e.clientY == "undefined" && typeof e.touches?.[0]?.clientY != "undefined") {
            e.clientY = e.touches[0].clientY;
        }

        if (typeof e.offsetX == "undefined" && typeof e.touches?.[0]?.pageX != "undefined" && e.target) {
            e.offsetX = e.touches[0].pageX - e.target.getBoundingClientRect().left;
        }

        if (typeof e.offsetY == "undefined" && typeof e.touches?.[0]?.pageY != "undefined" && e.target) {
            e.offsetY = e.touches[0].pageY - e.target.getBoundingClientRect().top || 0;
        }
    }
}
