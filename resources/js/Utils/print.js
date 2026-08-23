/**
 * Shared helper: opens a hidden iframe, writes the given HTML into it,
 * waits for it to actually finish loading/rendering, then prints and
 * cleans up. Using onload (instead of printing immediately) avoids the
 * classic "blank first page" bug where the browser prints before the
 * @page rule / fonts have been applied to the iframe document.
 *
 * Extracted from Components/custom/PosSuccessModel.vue, which had the
 * most robust of several near-duplicate print implementations in the
 * codebase (onload + requestAnimationFrame instead of a blind setTimeout).
 */
export function printHtmlInIframe(html) {
  const iframe = document.createElement("iframe");
  iframe.style.position = "fixed";
  iframe.style.right = "0";
  iframe.style.bottom = "0";
  iframe.style.width = "0";
  iframe.style.height = "0";
  iframe.style.border = "0";
  document.body.appendChild(iframe);

  const cleanup = () => {
    if (iframe.parentNode) {
      document.body.removeChild(iframe);
    }
  };

  let printed = false;
  const doPrint = () => {
    if (printed || !iframe.contentWindow) return;
    printed = true;
    requestAnimationFrame(() => {
      iframe.contentWindow.focus();
      iframe.contentWindow.print();
      setTimeout(cleanup, 500);
    });
  };

  iframe.onload = doPrint;

  iframe.contentDocument.open();
  iframe.contentDocument.write(html);
  iframe.contentDocument.close();

  // Fallback in case onload doesn't fire (already-loaded documents, etc.)
  if (iframe.contentDocument.readyState !== "loading") {
    setTimeout(doPrint, 100);
  }
}
