/**
 * Type definitions for objects returned from the Hypothesis API.
 *
 * The canonical reference is the API documentation at
 * https://h.readthedocs.io/en/latest/api-reference/
 */

/**
 * An entry in the API index response (`/api`) describing an API route.
 *
 * @typedef RouteMetadata
 * @prop {string} method - HTTP method
 * @prop {string} url - URL template
 * @prop {string} desc - Description of API route
 */

/**
 * Structure of the `links` field of the API index response (`/api`) describing
 * available API routes.
 *
 * @typedef {{ [key: string]: RouteMap|RouteMetadata }} RouteMap
 */

/**
 * @typedef TextQuoteSelector
 * @prop {'TextQuoteSelector'} type
 * @prop {string} exact
 * @prop {string} [prefix]
 * @prop {string} [suffix]
 */

/**
 * @typedef TextPositionSelector
 * @prop {'TextPositionSelector'} type
 * @prop {number} start
 * @prop {number} end
 */

/**
 * @typedef RangeSelector
 * @prop {'RangeSelector'} type
 * @prop {string} startContainer
 * @prop {string} endContainer
 * @prop {number} startOffset
 * @prop {number} endOffset
 */

/**
 * @typedef {TextQuoteSelector | TextPositionSelector | RangeSelector} Selector
 */

/**
 * An entry in the `target` field of an annotation which identifies the document
 * and region of the document that it refers to.
 *
 * @typedef Target
 * @prop {string} source - URI of the document
 * @prop {Selector[]} [selector] - Region of the document
 */

/**
 * TODO - Fill out remaining properties
 *
 * @typedef Annotation
 * @prop {string} [id] -
 *   The server-assigned ID for the annotation. This is only set once the
 *   annotation has been saved to the backend.
 * @prop {string} $tag - A locally-generated unique identifier for annotations.
 *   This is set for all annotations, whether they have been saved to the backend
 *   or not.
 * @prop {string[]} [references]
 * @prop {string} created
 * @prop {boolean} [flagged]
 * @prop {string} group
 * @prop {string} updated
 * @prop {string[]} tags
 * @prop {string} text
 * @prop {string} uri
 * @prop {string} user
 * @prop {boolean} hidden
 *
 * @prop {Object} document
 *   @prop {string} document.title
 *
 * @prop {Object} permissions
 *   @prop {string[]} permissions.read
 *   @prop {string[]} permissions.update
 *   @prop {string[]} permissions.delete
 *
 * @prop {Target[]} target - Which document and region this annotation refers to.
 *   The Hypothesis API structure allows for multiple targets, but the current
 *   h server only allows for one target per annotation.
 *
 * @prop {Object} [moderation]
 *   @prop {number} moderation.flagCount
 *
 * @prop {Object} links
 *   @prop {string} [links.incontext] - A "bouncer" URL to the annotation in
 *     context on its target document
 *   @prop {string} [links.html] - An `h`-website URL to view the annotation
 *     by itself
 *
 * @prop {Object} [user_info]
 *   @prop {string|null} user_info.display_name
 *
 * // Properties not present on API objects, but added by utilities in the client.
 * @prop {boolean} [$highlight]
 * @prop {boolean} [$orphan]
 * @prop {boolean} [$anchorTimeout]
 */

/**
 * @typedef Profile
 * @prop {string|null} userid
 * @prop {Object} preferences
 *   @prop {boolean} [preferences.show_sidebar_tutorial]
 * @prop {Object.<string, boolean>} features
 * @prop {Object} [user_info]
 *   @prop {string|null} user_info.display_name
 *
 * @prop {unknown} [groups] - Deprecated.
 */

/**
 * TODO - Fill out remaining properties
 *
 * @typedef Organization
 * @prop {string} name
 * @prop {string} logo
 * @prop {string} id
 * @prop {boolean} [default]
 */

/**
 * @typedef GroupScopes
 * @prop {boolean} enforced
 * @prop {string[]} uri_patterns;
 */

/**
 * TODO - Fill out remaining properties
 *
 * @typedef Group
 * @prop {string} id
 * @prop {string} groupid
 * @prop {'private'|'open'} type
 * @prop {Organization} organization - nb. This field is nullable in the API, but
 *   we assign a default organization on the client.
 * @prop {GroupScopes|null} scopes
 * @prop {Object} links
 *   @prop {string} [links.html]
 *
 * // Properties not present on API objects, but added by utilities in the client.
 * @prop {string} logo
 * @prop {boolean} isMember
 * @prop {boolean} isScopedToUri
 * @prop {string} name
 * @prop {boolean} canLeave
 */

/**
 * Query parameters for an `/api/search` API call.
 *
 * This type currently includes params that we've actually used.
 *
 * See https://h.readthedocs.io/en/latest/api-reference/#tag/annotations/paths/~1search/get
 * for the complete list and usage of each.
 *
 * @typedef SearchQuery
 * @prop {number} [limit]
 * @prop {string[]} [uri]
 * @prop {string} [group]
 * @prop {string} [order]
 * @prop {string} [references]
 * @prop {string} [search_after]
 * @prop {string} [sort]
 * @prop {boolean} [_separate_replies] - Unofficial param that causes replies
 *   to be returned in a separate `replies` field
 */

/**
 * Response to an `/api/search` API call.
 *
 * See https://h.readthedocs.io/en/latest/api-reference/#tag/annotations/paths/~1search/get
 *
 * @typedef SearchResult
 * @prop {number} total
 * @prop {Annotation[]} rows
 * @prop {Annotation[]} [replies] - Unofficial property that is populated if
 *   `_separate_replies` query param was specified
 */

/**
 * This module defines the subset of the PDF.js interface that the client relies
 * on.
 *
 * PDF.js doesn't provide its own types. There are partial definitions available
 * from DefinitelyTyped but these don't include everything we use. The source of
 * truth is the pdf.js repo (https://github.com/mozilla/pdf.js/) on GitHub.
 * See in particular `src/display/api.js` in that repo.
 *
 * Note that the definitions here are not complete, they only include properties
 * that the client uses. The names of types should match the corresponding
 * JSDoc types or classes in the PDF.js source where possible.
 */

/**
 * Document metadata parsed from the PDF's _metadata stream_.
 *
 * See `Metadata` class from `display/metadata.js` in PDF.js.
 *
 * @typedef Metadata
 * @prop {(name: string) => string} get
 * @prop {(name: string) => boolean} has
 */

/**
 * Document metadata parsed from the PDF's _document info dictionary_.
 *
 * See `PDFDocument#documentInfo` in PDF.js.
 *
 * @typedef PDFDocumentInfo
 * @prop {string} [Title]
 */

/**
 * An object containing metadata about the PDF. This includes information from:
 *
 * - The PDF's document info dictionary
 * - The PDF's metadata stream
 * - The HTTP headers (eg. `Content-Disposition`) sent when the PDF file was
 *   served
 *
 * See the "Metadata" section (14.3) in the PDF 1.7 reference for details of
 * the _metadata stream_ and _document info dictionary_.
 *
 * @typedef PDFDocumentMetadata
 * @prop {Metadata|null} metadata
 * @prop {PDFDocumentInfo} [info]
 * @prop {string|null} contentDispositionFilename - The `filename` directive from
 *   the `Content-Disposition` header
 */

/**
 * @typedef PDFDocument
 * @prop {string} fingerprint
 * @prop {() => Promise<PDFDocumentMetadata>} getMetadata
 */

/**
 * @typedef GetTextContentParameters
 * @prop {boolean} normalizeWhitespace
 */

/**
 * @typedef TextContentItem
 * @prop {string} str
 */

/**
 * @typedef TextContent
 * @prop {TextContentItem[]} items
 */

/**
 * @typedef PDFPageProxy
 * @prop {(o?: GetTextContentParameters) => Promise<TextContent>} getTextContent
 */

/**
 * @typedef PDFPageView
 * @prop {HTMLElement} div - Container element for the PDF page
 * @prop {PDFPageProxy} pdfPage
 * @prop {TextLayer|null} textLayer
 * @prop {number} renderingState - See `RenderingStates` enum in src/annotator/anchoring/pdf.js
 */

/**
 * @typedef PDFViewer
 *
 * Defined in `web/pdf_viewer.js` in the PDF.js source.
 *
 * @prop {string} currentScaleValue - Zoom level/mode. This can be a string representation
 *   of a float or a special constant ("auto", "page-fit", "page-width" and more)
 * @prop {number} pagesCount
 * @prop {EventBus} eventBus -
 *   Reference to the global event bus. Added in PDF.js v1.6.210.
 * @prop {(page: number) => PDFPageView|null} getPageView
 * @prop {HTMLElement} viewer - DOM element containing the main content of the document
 * @prop {() => void} update - Re-render the current view
 */

/**
 * Defined in `web/ui_utils.js` in the PDF.js source.
 *
 * @typedef EventBus
 * @prop {(event: string, listener: Function) => void} on
 * @prop {(event: string, listener: Function) => void} off
 */

/**
 * Object containing references to various DOM elements that make up the PDF.js viewer UI,
 * as well as a few other global objects used by the viewer.
 *
 * @typedef AppConfig
 * @prop {HTMLElement} appContainer
 */

/**
 * The `PDFViewerApplication` global which is the entry-point for accessing PDF.js.
 *
 * Defined in `web/app.js` in the PDF.js source.
 *
 * @typedef PDFViewerApplication
 * @prop {AppConfig} [appConfig] - Viewer DOM elements. Since v1.5.188.
 * @prop {EventBus} [eventBus] -
 *   Global event bus. Since v1.6.210. This is not available until the PDF viewer
 *   has been initialized. See `initialized` and `initializedPromise` properties.
 * @prop {PDFDocument} pdfDocument
 * @prop {PDFViewer} pdfViewer
 * @prop {boolean} downloadComplete
 * @prop {PDFDocumentInfo} documentInfo
 * @prop {Metadata} metadata
 * @prop {boolean} initialized - Indicates that the PDF viewer is initialized.
 * @prop {Promise<void>} [initializedPromise] -
 *   Promise that resolves when PDF.js is initialized. Since v2.4.456.
 *   See https://github.com/mozilla/pdf.js/wiki/Third-party-viewer-usage#initialization-promise.
 * @prop {string} url - The URL of the loaded PDF file
 */

/**
 * @typedef TextLayer
 * @prop {boolean} renderingDone
 * @prop {HTMLElement} textLayerDiv
 */

// Make TypeScript treat this file as a module.
export const unused = {};
