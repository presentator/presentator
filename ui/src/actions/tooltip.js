// Simple Svelte tooltip action.
// ===================================================================
//
// ### Example usage
//
// Default (position bottom):
// ```html
// <span use:tooltip={"My tooltip"}>Lorem Ipsum</span>
// ```
//
// Custom options (valid positions: top, right, bottom, left, bottom-left, bottom-right, top-left, top-right):
// ```html
// <span use:tooltip={{
//     text:        "My tooltip",
//     sub:         "Some additional helper text",
//     position:    "top-left",
//     class:       "...",
//     delay:       300,
//     hideOnClick: false,
//     noAria:      false
// }}>Lorem Ipsum</span>
// ```
// ===================================================================

import utils from "@/utils";

const defaultTooltipClass = "app-tooltip";

let showTimeoutId;
let tooltipContainer;

function normalize(rawData) {
    if (typeof rawData == "string") {
        return {
            text: rawData,
            position: "bottom",
            hideOnClick: null, // auto
        };
    }

    return rawData || {};
}

function getTooltip() {
    tooltipContainer = tooltipContainer || document.querySelector("." + defaultTooltipClass);

    if (!tooltipContainer) {
        // create
        tooltipContainer = document.createElement("div");
        tooltipContainer.classList.add(defaultTooltipClass);
        document.body.appendChild(tooltipContainer);
    }

    return tooltipContainer;
}

function refreshTooltip(node, data, mouseEvent = null) {
    let tooltip = getTooltip();
    if (!tooltip.classList.contains("active") || !data?.text) {
        hideTooltip();
        return; // no need to update since it is not active or there is no text to display
    }

    // set tooltip content
    tooltip.textContent = data.text;
    if (data.sub) {
        tooltip.dataset.sub = data.sub;
    } else {
        delete tooltip.dataset.sub;
    }

    // reset tooltip styling
    tooltip.className = defaultTooltipClass + " active";
    if (data.class) {
        tooltip.classList.add(data.class);
    }
    if (data.position) {
        tooltip.classList.add(data.position);
    }

    // reset tooltip position
    tooltip.style.top = "0px";
    tooltip.style.left = "0px";

    // note: doesn"t use getBoundingClientRect() here because the
    // tooltip could be animated/scaled/transformed and we need the real size
    let tooltipHeight = tooltip.offsetHeight;
    let tooltipWidth = tooltip.offsetWidth;

    let nodeRect = node.getBoundingClientRect();
    let top = 0;
    let left = 0;
    let tolerance = 5;

    // calculate tooltip position position
    if (data.position == "follow" && mouseEvent) {
        top = mouseEvent.clientY + tolerance;
        left = mouseEvent.clientX + tolerance;
    } else if (data.position == "left") {
        top = nodeRect.top + nodeRect.height / 2 - tooltipHeight / 2;
        left = nodeRect.left - tooltipWidth - tolerance;
    } else if (data.position == "right") {
        top = nodeRect.top + nodeRect.height / 2 - tooltipHeight / 2;
        left = nodeRect.right + tolerance;
    } else if (data.position == "top") {
        top = nodeRect.top - tooltipHeight - tolerance;
        left = nodeRect.left + nodeRect.width / 2 - tooltipWidth / 2;
    } else if (data.position == "top-left") {
        top = nodeRect.top - tooltipHeight - tolerance;
        left = nodeRect.left;
    } else if (data.position == "top-right") {
        top = nodeRect.top - tooltipHeight - tolerance;
        left = nodeRect.right - tooltipWidth;
    } else if (data.position == "bottom-left") {
        top = nodeRect.top + nodeRect.height + tolerance;
        left = nodeRect.left;
    } else if (data.position == "bottom-right") {
        top = nodeRect.top + nodeRect.height + tolerance;
        left = nodeRect.right - tooltipWidth;
    } else {
        // bottom
        top = nodeRect.top + nodeRect.height + tolerance;
        left = nodeRect.left + nodeRect.width / 2 - tooltipWidth / 2;
    }

    // right edge boundary
    if (left + tooltipWidth > document.documentElement.clientWidth) {
        left = document.documentElement.clientWidth - tooltipWidth;
    }

    // left edge boundary
    left = left >= 0 ? left : 0;

    // bottom edge boundary
    if (top + tooltipHeight > document.documentElement.clientHeight) {
        top = document.documentElement.clientHeight - tooltipHeight;
    }

    // top edge boundary
    top = top >= 0 ? top : 0;

    // apply new tooltip position
    tooltip.style.top = top + "px";
    tooltip.style.left = left + "px";
}

function hideTooltip() {
    clearTimeout(showTimeoutId);
    getTooltip().classList.remove("active");
    getTooltip().activeNode = undefined;
}

function showTooltip(node, data, e) {
    getTooltip().activeNode = node;

    clearTimeout(showTimeoutId);

    function show() {
        getTooltip().classList.add("active");
        refreshTooltip(node, data, e);
    }

    if (!data.delay) {
        show();
    } else {
        showTimeoutId = setTimeout(show, data.delay);
    }
}

export default function tooltip(node, tooltipData) {
    let data = normalize(tooltipData);

    function showEventHandler(e) {
        showTooltip(node, data, e);
    }

    function hideEventHandler(e) {
        hideTooltip();
    }

    if (!data.noAria && data.text) {
        node.setAttribute("aria-description", data.text);
    }

    if (data.position == "follow") {
        node.addEventListener("mousemove", showEventHandler);
    }

    node.addEventListener("mouseenter", showEventHandler);
    node.addEventListener("mouseleave", hideEventHandler);
    node.addEventListener("focusin", showEventHandler);
    node.addEventListener("blur", hideEventHandler);
    if (data.hideOnClick === true || (data.hideOnClick === null && utils.isFocusable(node))) {
        node.addEventListener("click", hideEventHandler);
    }

    // trigger tooltip container creation (if not inserted already)
    getTooltip();

    return {
        update(newTooltipData) {
            data = normalize(newTooltipData);

            if (!data.noAria && data.text) {
                node.setAttribute("aria-description", data.text);
            } else {
                node.removeAttribute("aria-description");
            }

            if (data.position == "follow") {
                node.addEventListener("mousemove", showEventHandler);
            } else {
                node.removeEventListener("mousemove", showEventHandler);
            }

            if (getTooltip()?.activeNode?.contains(node)) {
                refreshTooltip(node, data);
            }
        },
        destroy() {
            if (getTooltip()?.activeNode?.contains(node)) {
                hideTooltip();
            }

            node.removeEventListener("mousemove", showEventHandler);
            node.removeEventListener("mouseenter", showEventHandler);
            node.removeEventListener("focusin", showEventHandler);
            node.removeEventListener("mouseleave", hideEventHandler);
            node.removeEventListener("blur", hideEventHandler);
            node.removeEventListener("click", hideEventHandler);
        },
    };
}
