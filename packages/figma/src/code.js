import types from '@/utils/types';

const storageKey            = 'presentator_storage';
const defaultWidth          = 450;
const defaultHeight         = 390;
const defaultExportSettings = {
    format: 'PNG',
    contentsOnly: true,
    constraint: {
        type: 'SCALE',
        value: 1,
    },
};

// loads storage data and initializes the ui
async function initUI() {
    let storageData;
    try {
        storageData = await figma.clientStorage.getAsync(storageKey);
    } catch (e) {
        console.log('Storage init error:', e);
    }

    figma.showUI(__html__, { width: defaultWidth, height: defaultHeight });

    figma.ui.postMessage({
        type: types.MESSAGE_INIT_APP,
        data: storageData || {},
    });
}

// returns visible frame nodes
function getFrames(fromSelection = false) {
    let result = [];
    let nodes  = (fromSelection ? figma.currentPage.selection : figma.currentPage.children) || [];

    for (let i = 0; i < nodes.length; i++) {
        if (nodes[i].type === 'FRAME' && nodes[i].visible) {
            result.push({
                node:   nodes[i],
                id:     nodes[i].id,
                name:   nodes[i].name,
                width:  nodes[i].width,
                height: nodes[i].height,
            });
        }
    }

    return result;
}

// returns single frame by its node id
function getFrameById(id) {
    const frames = getFrames();

    for (let i = frames.length - 1; i >= 0; i--) {
        if (frames[i].id == id) {
            return frames[i];
        }
    }

    return null;
}

// exports frame node data
async function exportFrame(id, additionalSettings) {
    try {
        const frame = getFrameById(id);

        if (frame && frame.node) {
            return await frame.node.exportAsync(Object.assign({}, defaultExportSettings, additionalSettings || {}));
        }
    } catch (e) {
        console.log('Export frame error:', e);
    }

    return null;
}

initUI();

figma.ui.onmessage = async (message) => {
    if (typeof message !== 'object' || message === null || !message.type) {
        return;
    }

    if (message.type === types.MESSAGE_SAVE_STORAGE) {
        figma.clientStorage.setAsync(storageKey, message.data || {});
    } else if (message.type === types.MESSAGE_CLOSE) {
        figma.closePlugin();
    } else if (message.type === types.MESSAGE_NOTIFY) {
        figma.notify(message.data.message, {
            timeout: (message.data.timeout || 4000) << 0,
        });
    } else if (message.type === types.MESSAGE_RESIZE_UI) {
        figma.ui.resize(
            (message.data.width || defaultWidth) << 0,
            (message.data.height || defaultHeight) << 0,
        );
    } else if (message.type === types.MESSAGE_GET_FRAMES) {
        let frames = getFrames(message.data.onlySelected);

        // send the result to the ui
        figma.ui.postMessage({
            state: message.state,
            type:  types.MESSAGE_GET_FRAMES_RESPONSE,
            data:  frames,
        });
    } else if (message.type === types.MESSAGE_EXPORT_FRAME) {
        let data = await exportFrame(message.data.id, message.data.settings);

        // send the result to the ui
        figma.ui.postMessage({
            state: message.state,
            type:  types.MESSAGE_EXPORT_FRAME_RESPONSE,
            data:  data,
        });
    }
}
