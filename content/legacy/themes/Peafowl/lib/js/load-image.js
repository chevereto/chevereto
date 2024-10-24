/*
 * JavaScript Load Image
 * https://github.com/blueimp/JavaScript-Load-Image
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, module, Promise */

;(function ($) {
    'use strict'

    var urlAPI = $.URL || $.webkitURL

    /**
     * Creates an object URL for a given File object.
     *
     * @param {Blob} blob Blob object
     * @returns {string|boolean} Returns object URL if API exists, else false.
     */
    function createObjectURL(blob) {
      return urlAPI ? urlAPI.createObjectURL(blob) : false
    }

    /**
     * Revokes a given object URL.
     *
     * @param {string} url Blob object URL
     * @returns {undefined|boolean} Returns undefined if API exists, else false.
     */
    function revokeObjectURL(url) {
      return urlAPI ? urlAPI.revokeObjectURL(url) : false
    }

    /**
     * Helper function to revoke an object URL
     *
     * @param {string} url Blob Object URL
     * @param {object} [options] Options object
     */
    function revokeHelper(url, options) {
      if (url && url.slice(0, 5) === 'blob:' && !(options && options.noRevoke)) {
        revokeObjectURL(url)
      }
    }

    /**
     * Loads a given File object via FileReader interface.
     *
     * @param {Blob} file Blob object
     * @param {Function} onload Load event callback
     * @param {Function} [onerror] Error/Abort event callback
     * @param {string} [method=readAsDataURL] FileReader method
     * @returns {FileReader|boolean} Returns FileReader if API exists, else false.
     */
    function readFile(file, onload, onerror, method) {
      if (!$.FileReader) return false
      var reader = new FileReader()
      reader.onload = function () {
        onload.call(reader, this.result)
      }
      if (onerror) {
        reader.onabort = reader.onerror = function () {
          onerror.call(reader, this.error)
        }
      }
      var readerMethod = reader[method || 'readAsDataURL']
      if (readerMethod) {
        readerMethod.call(reader, file)
        return reader
      }
    }

    /**
     * Cross-frame instanceof check.
     *
     * @param {string} type Instance type
     * @param {object} obj Object instance
     * @returns {boolean} Returns true if the object is of the given instance.
     */
    function isInstanceOf(type, obj) {
      // Cross-frame instanceof check
      return Object.prototype.toString.call(obj) === '[object ' + type + ']'
    }

    /**
     * @typedef { HTMLImageElement|HTMLCanvasElement } Result
     */

    /**
     * Loads an image for a given File object.
     *
     * @param {Blob|string} file Blob object or image URL
     * @param {Function|object} [callback] Image load event callback or options
     * @param {object} [options] Options object
     * @returns {HTMLImageElement|FileReader|Promise<Result>} Object
     */
    function loadImage(file, callback, options) {
      /**
       * Promise executor
       *
       * @param {Function} resolve Resolution function
       * @param {Function} reject Rejection function
       * @returns {HTMLImageElement|FileReader} Object
       */
      function executor(resolve, reject) {
        var img = document.createElement('img')
        var url
        /**
         * Callback for the fetchBlob call.
         *
         * @param {HTMLImageElement|HTMLCanvasElement} img Error object
         * @param {object} data Data object
         * @returns {undefined} Undefined
         */
        function resolveWrapper(img, data) {
          if (resolve === reject) {
            // Not using Promises
            if (resolve) resolve(img, data)
            return
          } else if (img instanceof Error) {
            reject(img)
            return
          }
          data = data || {} // eslint-disable-line no-param-reassign
          data.image = img
          resolve(data)
        }
        /**
         * Callback for the fetchBlob call.
         *
         * @param {Blob} blob Blob object
         * @param {Error} err Error object
         */
        function fetchBlobCallback(blob, err) {
          if (err && $.console) console.log(err) // eslint-disable-line no-console
          if (blob && isInstanceOf('Blob', blob)) {
            file = blob // eslint-disable-line no-param-reassign
            url = createObjectURL(file)
          } else {
            url = file
            if (options && options.crossOrigin) {
              img.crossOrigin = options.crossOrigin
            }
          }
          img.src = url
        }
        img.onerror = function (event) {
          revokeHelper(url, options)
          if (reject) reject.call(img, event)
        }
        img.onload = function () {
          revokeHelper(url, options)
          var data = {
            originalWidth: img.naturalWidth || img.width,
            originalHeight: img.naturalHeight || img.height
          }
          try {
            loadImage.transform(img, options, resolveWrapper, file, data)
          } catch (error) {
            if (reject) reject(error)
          }
        }
        if (typeof file === 'string') {
          if (loadImage.requiresMetaData(options)) {
            loadImage.fetchBlob(file, fetchBlobCallback, options)
          } else {
            fetchBlobCallback()
          }
          return img
        } else if (isInstanceOf('Blob', file) || isInstanceOf('File', file)) {
          url = createObjectURL(file)
          if (url) {
            img.src = url
            return img
          }
          return readFile(
            file,
            function (url) {
              img.src = url
            },
            reject
          )
        }
      }
      if ($.Promise && typeof callback !== 'function') {
        options = callback // eslint-disable-line no-param-reassign
        return new Promise(executor)
      }
      return executor(callback, callback)
    }

    // Determines if metadata should be loaded automatically.
    // Requires the load image meta extension to load metadata.
    loadImage.requiresMetaData = function (options) {
      return options && options.meta
    }

    // If the callback given to this function returns a blob, it is used as image
    // source instead of the original url and overrides the file argument used in
    // the onload and onerror event callbacks:
    loadImage.fetchBlob = function (url, callback) {
      callback()
    }

    loadImage.transform = function (img, options, callback, file, data) {
      callback(img, data)
    }

    loadImage.global = $
    loadImage.readFile = readFile
    loadImage.isInstanceOf = isInstanceOf
    loadImage.createObjectURL = createObjectURL
    loadImage.revokeObjectURL = revokeObjectURL

    if (typeof define === 'function' && define.amd) {
      define(function () {
        return loadImage
      })
    } else if (typeof module === 'object' && module.exports) {
      module.exports = loadImage
    } else {
      $.loadImage = loadImage
    }
  })((typeof window !== 'undefined' && window) || this);

/*
 * JavaScript Load Image Meta
 * https://github.com/blueimp/JavaScript-Load-Image
 *
 * Copyright 2013, Sebastian Tschan
 * https://blueimp.net
 *
 * Image metadata handling implementation
 * based on the help and contribution of
 * Achim Stöhr.
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, module, require, Promise, DataView, Uint8Array, ArrayBuffer */

;(function (factory) {
    'use strict'
    if (typeof define === 'function' && define.amd) {
      // Register as an anonymous AMD module:
      define(['./load-image'], factory)
    } else if (typeof module === 'object' && module.exports) {
      factory(require('./load-image'))
    } else {
      // Browser globals:
      factory(window.loadImage)
    }
  })(function (loadImage) {
    'use strict'

    var global = loadImage.global
    var originalTransform = loadImage.transform

    var blobSlice =
      global.Blob &&
      (Blob.prototype.slice ||
        Blob.prototype.webkitSlice ||
        Blob.prototype.mozSlice)

    var bufferSlice =
      (global.ArrayBuffer && ArrayBuffer.prototype.slice) ||
      function (begin, end) {
        // Polyfill for IE10, which does not support ArrayBuffer.slice
        // eslint-disable-next-line no-param-reassign
        end = end || this.byteLength - begin
        var arr1 = new Uint8Array(this, begin, end)
        var arr2 = new Uint8Array(end)
        arr2.set(arr1)
        return arr2.buffer
      }

    var metaDataParsers = {
      jpeg: {
        0xffe1: [], // APP1 marker
        0xffed: [] // APP13 marker
      }
    }

    /**
     * Parses image metadata and calls the callback with an object argument
     * with the following property:
     * - imageHead: The complete image head as ArrayBuffer
     * The options argument accepts an object and supports the following
     * properties:
     * - maxMetaDataSize: Defines the maximum number of bytes to parse.
     * - disableImageHead: Disables creating the imageHead property.
     *
     * @param {Blob} file Blob object
     * @param {Function} [callback] Callback function
     * @param {object} [options] Parsing options
     * @param {object} [data] Result data object
     * @returns {Promise<object>|undefined} Returns Promise if no callback given.
     */
    function parseMetaData(file, callback, options, data) {
      var that = this
      /**
       * Promise executor
       *
       * @param {Function} resolve Resolution function
       * @param {Function} reject Rejection function
       * @returns {undefined} Undefined
       */
      function executor(resolve, reject) {
        if (
          !(
            global.DataView &&
            blobSlice &&
            file &&
            file.size >= 12 &&
            file.type === 'image/jpeg'
          )
        ) {
          // Nothing to parse
          return resolve(data)
        }
        // 256 KiB should contain all EXIF/ICC/IPTC segments:
        var maxMetaDataSize = options.maxMetaDataSize || 262144
        if (
          !loadImage.readFile(
            blobSlice.call(file, 0, maxMetaDataSize),
            function (buffer) {
              // Note on endianness:
              // Since the marker and length bytes in JPEG files are always
              // stored in big endian order, we can leave the endian parameter
              // of the DataView methods undefined, defaulting to big endian.
              var dataView = new DataView(buffer)
              // Check for the JPEG marker (0xffd8):
              if (dataView.getUint16(0) !== 0xffd8) {
                return reject(
                  new Error('Invalid JPEG file: Missing JPEG marker.')
                )
              }
              var offset = 2
              var maxOffset = dataView.byteLength - 4
              var headLength = offset
              var markerBytes
              var markerLength
              var parsers
              var i
              while (offset < maxOffset) {
                markerBytes = dataView.getUint16(offset)
                // Search for APPn (0xffeN) and COM (0xfffe) markers,
                // which contain application-specific metadata like
                // Exif, ICC and IPTC data and text comments:
                if (
                  (markerBytes >= 0xffe0 && markerBytes <= 0xffef) ||
                  markerBytes === 0xfffe
                ) {
                  // The marker bytes (2) are always followed by
                  // the length bytes (2), indicating the length of the
                  // marker segment, which includes the length bytes,
                  // but not the marker bytes, so we add 2:
                  markerLength = dataView.getUint16(offset + 2) + 2
                  if (offset + markerLength > dataView.byteLength) {
                    // eslint-disable-next-line no-console
                    console.log('Invalid JPEG metadata: Invalid segment size.')
                    break
                  }
                  parsers = metaDataParsers.jpeg[markerBytes]
                  if (parsers && !options.disableMetaDataParsers) {
                    for (i = 0; i < parsers.length; i += 1) {
                      parsers[i].call(
                        that,
                        dataView,
                        offset,
                        markerLength,
                        data,
                        options
                      )
                    }
                  }
                  offset += markerLength
                  headLength = offset
                } else {
                  // Not an APPn or COM marker, probably safe to
                  // assume that this is the end of the metadata
                  break
                }
              }
              // Meta length must be longer than JPEG marker (2)
              // plus APPn marker (2), followed by length bytes (2):
              if (!options.disableImageHead && headLength > 6) {
                data.imageHead = bufferSlice.call(buffer, 0, headLength)
              }
              resolve(data)
            },
            reject,
            'readAsArrayBuffer'
          )
        ) {
          // No support for the FileReader interface, nothing to parse
          resolve(data)
        }
      }
      options = options || {} // eslint-disable-line no-param-reassign
      if (global.Promise && typeof callback !== 'function') {
        options = callback || {} // eslint-disable-line no-param-reassign
        data = options // eslint-disable-line no-param-reassign
        return new Promise(executor)
      }
      data = data || {} // eslint-disable-line no-param-reassign
      return executor(callback, callback)
    }

    /**
     * Replaces the head of a JPEG Blob
     *
     * @param {Blob} blob Blob object
     * @param {ArrayBuffer} oldHead Old JPEG head
     * @param {ArrayBuffer} newHead New JPEG head
     * @returns {Blob} Combined Blob
     */
    function replaceJPEGHead(blob, oldHead, newHead) {
      if (!blob || !oldHead || !newHead) return null
      return new Blob([newHead, blobSlice.call(blob, oldHead.byteLength)], {
        type: 'image/jpeg'
      })
    }

    /**
     * Replaces the image head of a JPEG blob with the given one.
     * Returns a Promise or calls the callback with the new Blob.
     *
     * @param {Blob} blob Blob object
     * @param {ArrayBuffer} head New JPEG head
     * @param {Function} [callback] Callback function
     * @returns {Promise<Blob|null>|undefined} Combined Blob
     */
    function replaceHead(blob, head, callback) {
      var options = { maxMetaDataSize: 1024, disableMetaDataParsers: true }
      if (!callback && global.Promise) {
        return parseMetaData(blob, options).then(function (data) {
          return replaceJPEGHead(blob, data.imageHead, head)
        })
      }
      parseMetaData(
        blob,
        function (data) {
          callback(replaceJPEGHead(blob, data.imageHead, head))
        },
        options
      )
    }

    loadImage.transform = function (img, options, callback, file, data) {
      if (loadImage.requiresMetaData(options)) {
        data = data || {} // eslint-disable-line no-param-reassign
        parseMetaData(
          file,
          function (result) {
            if (result !== data) {
              // eslint-disable-next-line no-console
              if (global.console) console.log(result)
              result = data // eslint-disable-line no-param-reassign
            }
            originalTransform.call(
              loadImage,
              img,
              options,
              callback,
              file,
              result
            )
          },
          options,
          data
        )
      } else {
        originalTransform.apply(loadImage, arguments)
      }
    }

    loadImage.blobSlice = blobSlice
    loadImage.bufferSlice = bufferSlice
    loadImage.replaceHead = replaceHead
    loadImage.parseMetaData = parseMetaData
    loadImage.metaDataParsers = metaDataParsers
  });

/*
 * JavaScript Load Image Scaling
 * https://github.com/blueimp/JavaScript-Load-Image
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, module, require */

;(function (factory) {
    'use strict'
    if (typeof define === 'function' && define.amd) {
      // Register as an anonymous AMD module:
      define(['./load-image'], factory)
    } else if (typeof module === 'object' && module.exports) {
      factory(require('./load-image'))
    } else {
      // Browser globals:
      factory(window.loadImage)
    }
  })(function (loadImage) {
    'use strict'

    var originalTransform = loadImage.transform

    loadImage.createCanvas = function (width, height, offscreen) {
      if (offscreen && loadImage.global.OffscreenCanvas) {
        return new OffscreenCanvas(width, height)
      }
      var canvas = document.createElement('canvas')
      canvas.width = width
      canvas.height = height
      return canvas
    }

    loadImage.transform = function (img, options, callback, file, data) {
      originalTransform.call(
        loadImage,
        loadImage.scale(img, options, data),
        options,
        callback,
        file,
        data
      )
    }

    // Transform image coordinates, allows to override e.g.
    // the canvas orientation based on the orientation option,
    // gets canvas, options and data passed as arguments:
    loadImage.transformCoordinates = function () {}

    // Returns transformed options, allows to override e.g.
    // maxWidth, maxHeight and crop options based on the aspectRatio.
    // gets img, options, data passed as arguments:
    loadImage.getTransformedOptions = function (img, options) {
      var aspectRatio = options.aspectRatio
      var newOptions
      var i
      var width
      var height
      if (!aspectRatio) {
        return options
      }
      newOptions = {}
      for (i in options) {
        if (Object.prototype.hasOwnProperty.call(options, i)) {
          newOptions[i] = options[i]
        }
      }
      newOptions.crop = true
      width = img.naturalWidth || img.width
      height = img.naturalHeight || img.height
      if (width / height > aspectRatio) {
        newOptions.maxWidth = height * aspectRatio
        newOptions.maxHeight = height
      } else {
        newOptions.maxWidth = width
        newOptions.maxHeight = width / aspectRatio
      }
      return newOptions
    }

    // Canvas render method, allows to implement a different rendering algorithm:
    loadImage.drawImage = function (
      img,
      canvas,
      sourceX,
      sourceY,
      sourceWidth,
      sourceHeight,
      destWidth,
      destHeight,
      options
    ) {
      var ctx = canvas.getContext('2d')
      if (options.imageSmoothingEnabled === false) {
        ctx.msImageSmoothingEnabled = false
        ctx.imageSmoothingEnabled = false
      } else if (options.imageSmoothingQuality) {
        ctx.imageSmoothingQuality = options.imageSmoothingQuality
      }
      ctx.drawImage(
        img,
        sourceX,
        sourceY,
        sourceWidth,
        sourceHeight,
        0,
        0,
        destWidth,
        destHeight
      )
      return ctx
    }

    // Determines if the target image should be a canvas element:
    loadImage.requiresCanvas = function (options) {
      return options.canvas || options.crop || !!options.aspectRatio
    }

    // Scales and/or crops the given image (img or canvas HTML element)
    // using the given options:
    loadImage.scale = function (img, options, data) {
      // eslint-disable-next-line no-param-reassign
      options = options || {}
      // eslint-disable-next-line no-param-reassign
      data = data || {}
      var useCanvas =
        img.getContext ||
        (loadImage.requiresCanvas(options) &&
          !!loadImage.global.HTMLCanvasElement)
      var width = img.naturalWidth || img.width
      var height = img.naturalHeight || img.height
      var destWidth = width
      var destHeight = height
      var maxWidth
      var maxHeight
      var minWidth
      var minHeight
      var sourceWidth
      var sourceHeight
      var sourceX
      var sourceY
      var pixelRatio
      var downsamplingRatio
      var tmp
      var canvas
      /**
       * Scales up image dimensions
       */
      function scaleUp() {
        var scale = Math.max(
          (minWidth || destWidth) / destWidth,
          (minHeight || destHeight) / destHeight
        )
        if (scale > 1) {
          destWidth *= scale
          destHeight *= scale
        }
      }
      /**
       * Scales down image dimensions
       */
      function scaleDown() {
        var scale = Math.min(
          (maxWidth || destWidth) / destWidth,
          (maxHeight || destHeight) / destHeight
        )
        if (scale < 1) {
          destWidth *= scale
          destHeight *= scale
        }
      }
      if (useCanvas) {
        // eslint-disable-next-line no-param-reassign
        options = loadImage.getTransformedOptions(img, options, data)
        sourceX = options.left || 0
        sourceY = options.top || 0
        if (options.sourceWidth) {
          sourceWidth = options.sourceWidth
          if (options.right !== undefined && options.left === undefined) {
            sourceX = width - sourceWidth - options.right
          }
        } else {
          sourceWidth = width - sourceX - (options.right || 0)
        }
        if (options.sourceHeight) {
          sourceHeight = options.sourceHeight
          if (options.bottom !== undefined && options.top === undefined) {
            sourceY = height - sourceHeight - options.bottom
          }
        } else {
          sourceHeight = height - sourceY - (options.bottom || 0)
        }
        destWidth = sourceWidth
        destHeight = sourceHeight
      }
      maxWidth = options.maxWidth
      maxHeight = options.maxHeight
      minWidth = options.minWidth
      minHeight = options.minHeight
      if (useCanvas && maxWidth && maxHeight && options.crop) {
        destWidth = maxWidth
        destHeight = maxHeight
        tmp = sourceWidth / sourceHeight - maxWidth / maxHeight
        if (tmp < 0) {
          sourceHeight = (maxHeight * sourceWidth) / maxWidth
          if (options.top === undefined && options.bottom === undefined) {
            sourceY = (height - sourceHeight) / 2
          }
        } else if (tmp > 0) {
          sourceWidth = (maxWidth * sourceHeight) / maxHeight
          if (options.left === undefined && options.right === undefined) {
            sourceX = (width - sourceWidth) / 2
          }
        }
      } else {
        if (options.contain || options.cover) {
          minWidth = maxWidth = maxWidth || minWidth
          minHeight = maxHeight = maxHeight || minHeight
        }
        if (options.cover) {
          scaleDown()
          scaleUp()
        } else {
          scaleUp()
          scaleDown()
        }
      }
      if (useCanvas) {
        pixelRatio = options.pixelRatio
        if (
          pixelRatio > 1 &&
          // Check if the image has not yet had the device pixel ratio applied:
          !(
            img.style.width &&
            Math.floor(parseFloat(img.style.width, 10)) ===
              Math.floor(width / pixelRatio)
          )
        ) {
          destWidth *= pixelRatio
          destHeight *= pixelRatio
        }
        // Check if workaround for Chromium orientation crop bug is required:
        // https://bugs.chromium.org/p/chromium/issues/detail?id=1074354
        if (
          loadImage.orientationCropBug &&
          !img.getContext &&
          (sourceX || sourceY || sourceWidth !== width || sourceHeight !== height)
        ) {
          // Write the complete source image to an intermediate canvas first:
          tmp = img
          // eslint-disable-next-line no-param-reassign
          img = loadImage.createCanvas(width, height, true)
          loadImage.drawImage(
            tmp,
            img,
            0,
            0,
            width,
            height,
            width,
            height,
            options
          )
        }
        downsamplingRatio = options.downsamplingRatio
        if (
          downsamplingRatio > 0 &&
          downsamplingRatio < 1 &&
          destWidth < sourceWidth &&
          destHeight < sourceHeight
        ) {
          while (sourceWidth * downsamplingRatio > destWidth) {
            canvas = loadImage.createCanvas(
              sourceWidth * downsamplingRatio,
              sourceHeight * downsamplingRatio,
              true
            )
            loadImage.drawImage(
              img,
              canvas,
              sourceX,
              sourceY,
              sourceWidth,
              sourceHeight,
              canvas.width,
              canvas.height,
              options
            )
            sourceX = 0
            sourceY = 0
            sourceWidth = canvas.width
            sourceHeight = canvas.height
            // eslint-disable-next-line no-param-reassign
            img = canvas
          }
        }
        canvas = loadImage.createCanvas(destWidth, destHeight)
        loadImage.transformCoordinates(canvas, options, data)
        if (pixelRatio > 1) {
          canvas.style.width = canvas.width / pixelRatio + 'px'
        }
        loadImage
          .drawImage(
            img,
            canvas,
            sourceX,
            sourceY,
            sourceWidth,
            sourceHeight,
            destWidth,
            destHeight,
            options
          )
          .setTransform(1, 0, 0, 1, 0, 0) // reset to the identity matrix
        return canvas
      }
      img.width = destWidth
      img.height = destHeight
      return img
    }
  });
