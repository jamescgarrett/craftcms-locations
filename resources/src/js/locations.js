(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/*!
 * mustache.js - Logic-less {{mustache}} templates with JavaScript
 * http://github.com/janl/mustache.js
 */

/*global define: false Mustache: true*/

(function defineMustache (global, factory) {
  if (typeof exports === 'object' && exports && typeof exports.nodeName !== 'string') {
    factory(exports); // CommonJS
  } else if (typeof define === 'function' && define.amd) {
    define(['exports'], factory); // AMD
  } else {
    global.Mustache = {};
    factory(global.Mustache); // script, wsh, asp
  }
}(this, function mustacheFactory (mustache) {

  var objectToString = Object.prototype.toString;
  var isArray = Array.isArray || function isArrayPolyfill (object) {
    return objectToString.call(object) === '[object Array]';
  };

  function isFunction (object) {
    return typeof object === 'function';
  }

  /**
   * More correct typeof string handling array
   * which normally returns typeof 'object'
   */
  function typeStr (obj) {
    return isArray(obj) ? 'array' : typeof obj;
  }

  function escapeRegExp (string) {
    return string.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
  }

  /**
   * Null safe way of checking whether or not an object,
   * including its prototype, has a given property
   */
  function hasProperty (obj, propName) {
    return obj != null && typeof obj === 'object' && (propName in obj);
  }

  // Workaround for https://issues.apache.org/jira/browse/COUCHDB-577
  // See https://github.com/janl/mustache.js/issues/189
  var regExpTest = RegExp.prototype.test;
  function testRegExp (re, string) {
    return regExpTest.call(re, string);
  }

  var nonSpaceRe = /\S/;
  function isWhitespace (string) {
    return !testRegExp(nonSpaceRe, string);
  }

  var entityMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
    '/': '&#x2F;',
    '`': '&#x60;',
    '=': '&#x3D;'
  };

  function escapeHtml (string) {
    return String(string).replace(/[&<>"'`=\/]/g, function fromEntityMap (s) {
      return entityMap[s];
    });
  }

  var whiteRe = /\s*/;
  var spaceRe = /\s+/;
  var equalsRe = /\s*=/;
  var curlyRe = /\s*\}/;
  var tagRe = /#|\^|\/|>|\{|&|=|!/;

  /**
   * Breaks up the given `template` string into a tree of tokens. If the `tags`
   * argument is given here it must be an array with two string values: the
   * opening and closing tags used in the template (e.g. [ "<%", "%>" ]). Of
   * course, the default is to use mustaches (i.e. mustache.tags).
   *
   * A token is an array with at least 4 elements. The first element is the
   * mustache symbol that was used inside the tag, e.g. "#" or "&". If the tag
   * did not contain a symbol (i.e. {{myValue}}) this element is "name". For
   * all text that appears outside a symbol this element is "text".
   *
   * The second element of a token is its "value". For mustache tags this is
   * whatever else was inside the tag besides the opening symbol. For text tokens
   * this is the text itself.
   *
   * The third and fourth elements of the token are the start and end indices,
   * respectively, of the token in the original template.
   *
   * Tokens that are the root node of a subtree contain two more elements: 1) an
   * array of tokens in the subtree and 2) the index in the original template at
   * which the closing tag for that section begins.
   */
  function parseTemplate (template, tags) {
    if (!template)
      return [];

    var sections = [];     // Stack to hold section tokens
    var tokens = [];       // Buffer to hold the tokens
    var spaces = [];       // Indices of whitespace tokens on the current line
    var hasTag = false;    // Is there a {{tag}} on the current line?
    var nonSpace = false;  // Is there a non-space char on the current line?

    // Strips all whitespace tokens array for the current line
    // if there was a {{#tag}} on it and otherwise only space.
    function stripSpace () {
      if (hasTag && !nonSpace) {
        while (spaces.length)
          delete tokens[spaces.pop()];
      } else {
        spaces = [];
      }

      hasTag = false;
      nonSpace = false;
    }

    var openingTagRe, closingTagRe, closingCurlyRe;
    function compileTags (tagsToCompile) {
      if (typeof tagsToCompile === 'string')
        tagsToCompile = tagsToCompile.split(spaceRe, 2);

      if (!isArray(tagsToCompile) || tagsToCompile.length !== 2)
        throw new Error('Invalid tags: ' + tagsToCompile);

      openingTagRe = new RegExp(escapeRegExp(tagsToCompile[0]) + '\\s*');
      closingTagRe = new RegExp('\\s*' + escapeRegExp(tagsToCompile[1]));
      closingCurlyRe = new RegExp('\\s*' + escapeRegExp('}' + tagsToCompile[1]));
    }

    compileTags(tags || mustache.tags);

    var scanner = new Scanner(template);

    var start, type, value, chr, token, openSection;
    while (!scanner.eos()) {
      start = scanner.pos;

      // Match any text between tags.
      value = scanner.scanUntil(openingTagRe);

      if (value) {
        for (var i = 0, valueLength = value.length; i < valueLength; ++i) {
          chr = value.charAt(i);

          if (isWhitespace(chr)) {
            spaces.push(tokens.length);
          } else {
            nonSpace = true;
          }

          tokens.push([ 'text', chr, start, start + 1 ]);
          start += 1;

          // Check for whitespace on the current line.
          if (chr === '\n')
            stripSpace();
        }
      }

      // Match the opening tag.
      if (!scanner.scan(openingTagRe))
        break;

      hasTag = true;

      // Get the tag type.
      type = scanner.scan(tagRe) || 'name';
      scanner.scan(whiteRe);

      // Get the tag value.
      if (type === '=') {
        value = scanner.scanUntil(equalsRe);
        scanner.scan(equalsRe);
        scanner.scanUntil(closingTagRe);
      } else if (type === '{') {
        value = scanner.scanUntil(closingCurlyRe);
        scanner.scan(curlyRe);
        scanner.scanUntil(closingTagRe);
        type = '&';
      } else {
        value = scanner.scanUntil(closingTagRe);
      }

      // Match the closing tag.
      if (!scanner.scan(closingTagRe))
        throw new Error('Unclosed tag at ' + scanner.pos);

      token = [ type, value, start, scanner.pos ];
      tokens.push(token);

      if (type === '#' || type === '^') {
        sections.push(token);
      } else if (type === '/') {
        // Check section nesting.
        openSection = sections.pop();

        if (!openSection)
          throw new Error('Unopened section "' + value + '" at ' + start);

        if (openSection[1] !== value)
          throw new Error('Unclosed section "' + openSection[1] + '" at ' + start);
      } else if (type === 'name' || type === '{' || type === '&') {
        nonSpace = true;
      } else if (type === '=') {
        // Set the tags for the next time around.
        compileTags(value);
      }
    }

    // Make sure there are no open sections when we're done.
    openSection = sections.pop();

    if (openSection)
      throw new Error('Unclosed section "' + openSection[1] + '" at ' + scanner.pos);

    return nestTokens(squashTokens(tokens));
  }

  /**
   * Combines the values of consecutive text tokens in the given `tokens` array
   * to a single token.
   */
  function squashTokens (tokens) {
    var squashedTokens = [];

    var token, lastToken;
    for (var i = 0, numTokens = tokens.length; i < numTokens; ++i) {
      token = tokens[i];

      if (token) {
        if (token[0] === 'text' && lastToken && lastToken[0] === 'text') {
          lastToken[1] += token[1];
          lastToken[3] = token[3];
        } else {
          squashedTokens.push(token);
          lastToken = token;
        }
      }
    }

    return squashedTokens;
  }

  /**
   * Forms the given array of `tokens` into a nested tree structure where
   * tokens that represent a section have two additional items: 1) an array of
   * all tokens that appear in that section and 2) the index in the original
   * template that represents the end of that section.
   */
  function nestTokens (tokens) {
    var nestedTokens = [];
    var collector = nestedTokens;
    var sections = [];

    var token, section;
    for (var i = 0, numTokens = tokens.length; i < numTokens; ++i) {
      token = tokens[i];

      switch (token[0]) {
        case '#':
        case '^':
          collector.push(token);
          sections.push(token);
          collector = token[4] = [];
          break;
        case '/':
          section = sections.pop();
          section[5] = token[2];
          collector = sections.length > 0 ? sections[sections.length - 1][4] : nestedTokens;
          break;
        default:
          collector.push(token);
      }
    }

    return nestedTokens;
  }

  /**
   * A simple string scanner that is used by the template parser to find
   * tokens in template strings.
   */
  function Scanner (string) {
    this.string = string;
    this.tail = string;
    this.pos = 0;
  }

  /**
   * Returns `true` if the tail is empty (end of string).
   */
  Scanner.prototype.eos = function eos () {
    return this.tail === '';
  };

  /**
   * Tries to match the given regular expression at the current position.
   * Returns the matched text if it can match, the empty string otherwise.
   */
  Scanner.prototype.scan = function scan (re) {
    var match = this.tail.match(re);

    if (!match || match.index !== 0)
      return '';

    var string = match[0];

    this.tail = this.tail.substring(string.length);
    this.pos += string.length;

    return string;
  };

  /**
   * Skips all text until the given regular expression can be matched. Returns
   * the skipped string, which is the entire tail if no match can be made.
   */
  Scanner.prototype.scanUntil = function scanUntil (re) {
    var index = this.tail.search(re), match;

    switch (index) {
      case -1:
        match = this.tail;
        this.tail = '';
        break;
      case 0:
        match = '';
        break;
      default:
        match = this.tail.substring(0, index);
        this.tail = this.tail.substring(index);
    }

    this.pos += match.length;

    return match;
  };

  /**
   * Represents a rendering context by wrapping a view object and
   * maintaining a reference to the parent context.
   */
  function Context (view, parentContext) {
    this.view = view;
    this.cache = { '.': this.view };
    this.parent = parentContext;
  }

  /**
   * Creates a new context using the given view with this context
   * as the parent.
   */
  Context.prototype.push = function push (view) {
    return new Context(view, this);
  };

  /**
   * Returns the value of the given name in this context, traversing
   * up the context hierarchy if the value is absent in this context's view.
   */
  Context.prototype.lookup = function lookup (name) {
    var cache = this.cache;

    var value;
    if (cache.hasOwnProperty(name)) {
      value = cache[name];
    } else {
      var context = this, names, index, lookupHit = false;

      while (context) {
        if (name.indexOf('.') > 0) {
          value = context.view;
          names = name.split('.');
          index = 0;

          /**
           * Using the dot notion path in `name`, we descend through the
           * nested objects.
           *
           * To be certain that the lookup has been successful, we have to
           * check if the last object in the path actually has the property
           * we are looking for. We store the result in `lookupHit`.
           *
           * This is specially necessary for when the value has been set to
           * `undefined` and we want to avoid looking up parent contexts.
           **/
          while (value != null && index < names.length) {
            if (index === names.length - 1)
              lookupHit = hasProperty(value, names[index]);

            value = value[names[index++]];
          }
        } else {
          value = context.view[name];
          lookupHit = hasProperty(context.view, name);
        }

        if (lookupHit)
          break;

        context = context.parent;
      }

      cache[name] = value;
    }

    if (isFunction(value))
      value = value.call(this.view);

    return value;
  };

  /**
   * A Writer knows how to take a stream of tokens and render them to a
   * string, given a context. It also maintains a cache of templates to
   * avoid the need to parse the same template twice.
   */
  function Writer () {
    this.cache = {};
  }

  /**
   * Clears all cached templates in this writer.
   */
  Writer.prototype.clearCache = function clearCache () {
    this.cache = {};
  };

  /**
   * Parses and caches the given `template` and returns the array of tokens
   * that is generated from the parse.
   */
  Writer.prototype.parse = function parse (template, tags) {
    var cache = this.cache;
    var tokens = cache[template];

    if (tokens == null)
      tokens = cache[template] = parseTemplate(template, tags);

    return tokens;
  };

  /**
   * High-level method that is used to render the given `template` with
   * the given `view`.
   *
   * The optional `partials` argument may be an object that contains the
   * names and templates of partials that are used in the template. It may
   * also be a function that is used to load partial templates on the fly
   * that takes a single argument: the name of the partial.
   */
  Writer.prototype.render = function render (template, view, partials) {
    var tokens = this.parse(template);
    var context = (view instanceof Context) ? view : new Context(view);
    return this.renderTokens(tokens, context, partials, template);
  };

  /**
   * Low-level method that renders the given array of `tokens` using
   * the given `context` and `partials`.
   *
   * Note: The `originalTemplate` is only ever used to extract the portion
   * of the original template that was contained in a higher-order section.
   * If the template doesn't use higher-order sections, this argument may
   * be omitted.
   */
  Writer.prototype.renderTokens = function renderTokens (tokens, context, partials, originalTemplate) {
    var buffer = '';

    var token, symbol, value;
    for (var i = 0, numTokens = tokens.length; i < numTokens; ++i) {
      value = undefined;
      token = tokens[i];
      symbol = token[0];

      if (symbol === '#') value = this.renderSection(token, context, partials, originalTemplate);
      else if (symbol === '^') value = this.renderInverted(token, context, partials, originalTemplate);
      else if (symbol === '>') value = this.renderPartial(token, context, partials, originalTemplate);
      else if (symbol === '&') value = this.unescapedValue(token, context);
      else if (symbol === 'name') value = this.escapedValue(token, context);
      else if (symbol === 'text') value = this.rawValue(token);

      if (value !== undefined)
        buffer += value;
    }

    return buffer;
  };

  Writer.prototype.renderSection = function renderSection (token, context, partials, originalTemplate) {
    var self = this;
    var buffer = '';
    var value = context.lookup(token[1]);

    // This function is used to render an arbitrary template
    // in the current context by higher-order sections.
    function subRender (template) {
      return self.render(template, context, partials);
    }

    if (!value) return;

    if (isArray(value)) {
      for (var j = 0, valueLength = value.length; j < valueLength; ++j) {
        buffer += this.renderTokens(token[4], context.push(value[j]), partials, originalTemplate);
      }
    } else if (typeof value === 'object' || typeof value === 'string' || typeof value === 'number') {
      buffer += this.renderTokens(token[4], context.push(value), partials, originalTemplate);
    } else if (isFunction(value)) {
      if (typeof originalTemplate !== 'string')
        throw new Error('Cannot use higher-order sections without the original template');

      // Extract the portion of the original template that the section contains.
      value = value.call(context.view, originalTemplate.slice(token[3], token[5]), subRender);

      if (value != null)
        buffer += value;
    } else {
      buffer += this.renderTokens(token[4], context, partials, originalTemplate);
    }
    return buffer;
  };

  Writer.prototype.renderInverted = function renderInverted (token, context, partials, originalTemplate) {
    var value = context.lookup(token[1]);

    // Use JavaScript's definition of falsy. Include empty arrays.
    // See https://github.com/janl/mustache.js/issues/186
    if (!value || (isArray(value) && value.length === 0))
      return this.renderTokens(token[4], context, partials, originalTemplate);
  };

  Writer.prototype.renderPartial = function renderPartial (token, context, partials) {
    if (!partials) return;

    var value = isFunction(partials) ? partials(token[1]) : partials[token[1]];
    if (value != null)
      return this.renderTokens(this.parse(value), context, partials, value);
  };

  Writer.prototype.unescapedValue = function unescapedValue (token, context) {
    var value = context.lookup(token[1]);
    if (value != null)
      return value;
  };

  Writer.prototype.escapedValue = function escapedValue (token, context) {
    var value = context.lookup(token[1]);
    if (value != null)
      return mustache.escape(value);
  };

  Writer.prototype.rawValue = function rawValue (token) {
    return token[1];
  };

  mustache.name = 'mustache.js';
  mustache.version = '2.2.1';
  mustache.tags = [ '{{', '}}' ];

  // All high-level mustache.* functions use this writer.
  var defaultWriter = new Writer();

  /**
   * Clears all cached templates in the default writer.
   */
  mustache.clearCache = function clearCache () {
    return defaultWriter.clearCache();
  };

  /**
   * Parses and caches the given template in the default writer and returns the
   * array of tokens it contains. Doing this ahead of time avoids the need to
   * parse templates on the fly as they are rendered.
   */
  mustache.parse = function parse (template, tags) {
    return defaultWriter.parse(template, tags);
  };

  /**
   * Renders the `template` with the given `view` and `partials` using the
   * default writer.
   */
  mustache.render = function render (template, view, partials) {
    if (typeof template !== 'string') {
      throw new TypeError('Invalid template! Template should be a "string" ' +
                          'but "' + typeStr(template) + '" was given as the first ' +
                          'argument for mustache#render(template, view, partials)');
    }

    return defaultWriter.render(template, view, partials);
  };

  // This is here for backwards compatibility with 0.4.x.,
  /*eslint-disable */ // eslint wants camel cased function name
  mustache.to_html = function to_html (template, view, partials, send) {
    /*eslint-enable*/

    var result = mustache.render(template, view, partials);

    if (isFunction(send)) {
      send(result);
    } else {
      return result;
    }
  };

  // Export the escaping function so that the user may override it.
  // See https://github.com/janl/mustache.js/issues/244
  mustache.escape = escapeHtml;

  // Export these mainly for testing, but also for advanced usage.
  mustache.Scanner = Scanner;
  mustache.Context = Context;
  mustache.Writer = Writer;

}));

},{}],2:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _mustache = require('mustache');

var _mustache2 = _interopRequireDefault(_mustache);

var _Utils = require('./utils/Utils');

var _Utils2 = _interopRequireDefault(_Utils);

var _ApiUtils = require('./utils/ApiUtils');

var _ApiUtils2 = _interopRequireDefault(_ApiUtils);

var _GoogleMapsUtils = require('./utils/GoogleMapsUtils');

var _GoogleMapsUtils2 = _interopRequireDefault(_GoogleMapsUtils);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

(function (window) {

    'use strict';

    var LocationLocator = function LocationLocator() {
        destroy.call(this);

        this.geocoder = new google.maps.Geocoder();
        this.map = null;
        this.data = [];
        this.locations = [];
        this.settings = null;
        this.filteredLocations = [];

        this.form = null;
        this.searchInput = null;
        this.radiusInput = null;
        this.resultsContainer = null;
        this.mapContainer = null;
        this.result = null;
        this.emptyContainer = null;
        this.errorsContainer = null;
        this.loadingContainer = null;

        var defaults = {
            data: null,
            formClass: 'locations-form',
            searchInputClass: 'locations-form-search',
            radiusInputClass: 'locations-form-radius',
            resultsContainerClass: 'locations-results',
            mapContainerClass: 'locations-map',
            emptyContainerClass: 'locations-empty',
            errorsContainerClass: 'locations-errors',
            resultsTemplateId: 'location-template',
            loadingContainerClass: 'locations-loading',
            loadingHtml: 'Loading...'
        };

        if (arguments[0] && _typeof(arguments[0]) === 'object') {
            this.options = _Utils2.default.extend(defaults, arguments[0]);
        }

        init.call(this);
    };

    /**
     * Destory instance of plugin
     * @private
     */
    var destroy = function destroy() {
        if (!this.options) return;

        document.removeEventListener('submit', handleSearchSubmit, false);

        this.options = null;
    };

    /**
     * Init Plugin
     * @private
     */
    var init = function init(options) {
        this.form = document.querySelector('.' + this.options.formClass);
        this.searchInput = document.querySelector('.' + this.options.searchInputClass);
        this.radiusInput = document.querySelector('.' + this.options.radiusInputClass);
        this.resultsContainer = document.querySelector('.' + this.options.resultsContainerClass);
        this.mapContainer = document.querySelector('.' + this.options.mapContainerClass);
        this.emptyContainer = document.querySelector('.' + this.options.emptyContainerClass);
        this.errorsContainer = document.querySelector('.' + this.options.errorsContainerClass);
        this.loadingContainer = document.querySelector('.' + this.options.loadingContainerClass);

        this.form.addEventListener('submit', handleSearchSubmit.bind(this), false);

        this.loadingContainer.innerHTML = this.options.loadingHtml;

        var self = this;
        _ApiUtils2.default.loadData(this.options.data, function (results) {
            self.locations = results.locations;
            self.settings = results.settings;
            self.geocoder = new google.maps.Geocoder();
            if (results.settings.showMap == '1') {

                _GoogleMapsUtils2.default.setupMap(self.geocoder, results.settings.defaultZip, self.mapContainer, function (result) {
                    self.map = result;
                    getInitialView.call(self);
                });
            } else {
                getInitialView.call(self);
            }
        });
    };

    /**
     * Handle Form Submit
     * @private
     */
    var handleSearchSubmit = function handleSearchSubmit(event) {
        if (this.searchInput.value === '') {
            getFormErrors.call(this, 'empty');
        } else if (!_Utils2.default.validate('zip', this.searchInput.value)) {
            getFormErrors.call(this, 'zipformat');
        } else if (this.radiusInput.value !== '' && !_Utils2.default.validate('radius', this.radiusInput.value)) {
            getFormErrors.call(this, 'radiusformat');
        } else {
            getSearchView.call(this, this.searchInput.value, this.radiusInput.value);
        }
        event.preventDefault();
    };

    /**
     * Get Form Errors
     * @private
     */
    var getFormErrors = function getFormErrors(type) {
        if (this.errorsContainer.innerHTML !== '') {
            this.errorsContainer.innerHTML = '';
        }
        var message;
        switch (type) {
            case 'empty':
                message = '<p>You must enter a zip code.</p>';
                break;
            case 'zipformat':
                message = '<p>Your zip code doesn\'t seem correct... Please check it again.</p>';
                break;
            case 'radiusformat':
                message = '<p>Please enter a radius that consists of only numbers.</p>';
                break;
            default:
                alert('Something unquestionably bad just happened!');
                break;
        }
        this.errorsContainer.innerHTML = message;
    };

    /**
     * Get Initial View
     * @private
     */
    var getInitialView = function getInitialView() {
        var self = this;
        _GoogleMapsUtils2.default.findCurrentLocation(this.geocoder, this.settings, this.locations, function (results) {
            for (var i = 0; i < results.length; i++) {
                self.filteredLocations.push(self.locations[results[i]]);
            }
            self.filteredLocations.sort(function (a, b) {
                return a.latitude - b.latitude;
            });
            updateView.call(self);
        });
    };

    /**
     * Get Search View
     * @private
     */
    var getSearchView = function getSearchView(zip, radius) {
        var searchRadius;
        if (radius === '') {
            searchRadius = '24860';
        } else {
            searchRadius = radius;
        }

        // Clear exsiting results
        this.filteredLocations.length = 0;
        this.resultsContainer.innerHTML = '';

        var self = this;
        _GoogleMapsUtils2.default.findLocation(this.geocoder, zip, searchRadius, this.locations, function (results) {
            for (var i = 0; i < results.length; i++) {
                self.filteredLocations.push(self.locations[results[i]]);
            }
            self.filteredLocations.sort(function (a, b) {
                return a.latitude - b.latitude;
            });
            updateView.call(self);
        });
    };

    /**
     * Update View
     * @private
     */
    var updateView = function updateView() {
        if (this.filteredLocations.length > 1) {
            this.loadingContainer.innerHTML = '';
            this.errorsContainer.innerHTML = '';
            this.emptyContainer.innerHTML = '';
        } else {
            this.loadingContainer.innerHTML = '';
            this.errorsContainer.innerHTML = '';
            this.emptyContainer.innerHTML = this.settings.notFoundText;
        }

        if (this.settings.showMap) {
            _GoogleMapsUtils2.default.addMapMarkers(this.map, this.filteredLocations);
        }

        var template = document.getElementById(this.options.resultsTemplateId).innerHTML;
        _mustache2.default.parse(template);
        var compiledTemplate = _mustache2.default.render(template, { locations: this.filteredLocations });
        this.resultsContainer.innerHTML = compiledTemplate;
    };

    // load it
    window.LocationLocator = LocationLocator;
})(window);

},{"./utils/ApiUtils":3,"./utils/GoogleMapsUtils":4,"./utils/Utils":5,"mustache":1}],3:[function(require,module,exports){
'use strict';

var ApiUtils = {};

ApiUtils.loadData = function (url, sendback) {
    var request = new XMLHttpRequest();
    request.open('GET', url, true);

    request.onload = function () {
        if (request.status >= 200 && request.status < 400) {
            sendback(JSON.parse(request.responseText));
        } else {
            // error
        }
    };
    request.onerror = function () {
        // error
    };
    request.send();
};

module.exports = ApiUtils;

},{}],4:[function(require,module,exports){
'use strict';

var GoogleMapsUtils = {};

GoogleMapsUtils.findCurrentLocation = function (geocoder, settings, locations, sendResults) {

    if (!!navigator.geolocation && settings.useGeoLocation !== '0') {
        navigator.geolocation.getCurrentPosition(function (position) {
            var geolocate = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            sendResults(findResults(geolocate, parseInt(settings.defaultRadius, 10), locations));
        });
    } else {
        geocoder.geocode({ 'address': settings.defaultZip }, function (results, status) {
            switch (status) {
                case google.maps.GeocoderStatus.OVER_QUERY_LIMIT:
                    alert('The daily query limit has been reached. Sorry. Try again Tomorrow!');
                    break;

                case google.maps.GeocoderStatus.OK:
                    sendResults(findResults(results[0].geometry.location, parseInt(settings.defaultRadius, 10), locations));
                    break;

                case google.maps.GeocoderStatus.ZERO_RESULTS:
                    alert('Address Can\'t be Found!');
                    break;

                default:
                    alert('Geocode error:' + status);
                    break;
            }
        });
    }
};

GoogleMapsUtils.findLocation = function (geocoder, zip, radius, locations, sendResults) {
    geocoder.geocode({ 'address': zip }, function (results, status) {
        switch (status) {
            case google.maps.GeocoderStatus.OVER_QUERY_LIMIT:
                alert('The daily query limit has been reached. Sorry. Try again Tomorrow!');
                break;

            case google.maps.GeocoderStatus.OK:
                sendResults(findResults(results[0].geometry.location, radius, locations));
                break;

            case google.maps.GeocoderStatus.ZERO_RESULTS:
                alert('Address Can\'t be Found!');
                break;

            default:
                alert('Geocode error:' + status);
                break;
        }
    });
};

GoogleMapsUtils.setupMap = function (geocoder, zip, mapContainer, callback) {
    findLngLat(geocoder, zip, function (results) {
        var center = new google.maps.LatLng(results.lat, results.lng);
        var mapSettings = {
            center: center,
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
            }
        };
        callback(new google.maps.Map(mapContainer, mapSettings));
    });
};

GoogleMapsUtils.addMapMarkers = function (map, locations) {
    var bounds = new google.maps.LatLngBounds();
    for (var d = 0; d < locations.length; d++) {
        var position = new google.maps.LatLng(locations[d].latitude, locations[d].longitude);
        bounds.extend(position);
        var marker = new google.maps.Marker({
            position: position,
            title: locations[d].name,
            map: map,
            url: 'http://maps.google.com/?q=' + locations[d].address1 + '+' + locations[d].city + '+' + locations[d].state + '+' + locations[d].zipCode
        });
        var infoWindow = new google.maps.InfoWindow();
        google.maps.event.addListener(marker, 'click', function (marker, d, locations) {
            var address2 = locations[d].address2.length ? '<br>' + locations[d].address2 : '';
            return function () {
                infoWindow.setContent('<div class="info_content"><h3>' + locations[d].name + '</h3><p>' + locations[d].address1 + '<br>' + address2 + locations[d].city + ',' + locations[d].state + locations[d].zipCode + '<br><a href="' + locations[d].websiteLink + '" target="_blank">' + locations[d].website + '</a></p>');
                infoWindow.open(map, marker);
            };
        }(marker, d, locations));
        map.fitBounds(bounds);
    }
    var boundsListener = google.maps.event.addListener(map, 'bounds_changed', function (event) {
        google.maps.event.removeListener(boundsListener);
    });
};

var findLngLat = function findLngLat(geocoder, zip, callback) {
    var lat = '';
    var lng = '';
    geocoder.geocode({ 'address': zip }, function (results, status) {
        switch (status) {
            case google.maps.GeocoderStatus.OVER_QUERY_LIMIT:
                alert('The daily query limit has been reached. Sorry. Try again Tomorrow!');
                break;
            case google.maps.GeocoderStatus.OK:
                callback({ lat: results[0].geometry.location.lat(), lng: results[0].geometry.location.lng() });
                break;
            case google.maps.GeocoderStatus.ZERO_RESULTS:
                alert('Address Can\'t be Found!');
                break;
            default:
                alert('Geocode error:' + status);
                break;
        }
    });
};

var findResults = function findResults(location, radius, locations) {
    var latSearched = location.lat();
    var lngSearched = location.lng();
    var nearBy = [];
    for (var j = 0; j < locations.length; j++) {
        var locLat = locations[j].latitude;
        var locLng = locations[j].longitude;
        var distance = 3959 * Math.acos(Math.cos(toRadian(latSearched)) * Math.cos(toRadian(locLat)) * Math.cos(toRadian(locLng) - toRadian(lngSearched)) + Math.sin(toRadian(latSearched)) * Math.sin(toRadian(locLat)));
        if (distance < radius) {
            nearBy.push(j);
        }
    }
    return nearBy;
};

var toRadian = function toRadian(degree) {
    return degree * Math.PI / 180;
};

module.exports = GoogleMapsUtils;

},{}],5:[function(require,module,exports){
'use strict';

var Utils = {};

Utils.extend = function (source, properties) {
    var property;
    for (property in properties) {
        if (properties.hasOwnProperty(property)) {
            source[property] = properties[property];
        }
    }
    return source;
};

Utils.validate = function (type, value) {

    switch (type) {
        case 'zip':
            if (/^\s*\d{5}\s*$/.test(value) || /[a-zA-Z][0-9][a-zA-Z](-| |)[0-9][a-zA-Z][0-9]/.test(value)) {
                return true;
            }
            break;
        case 'radius':
            if (/^\d+$/.test(value)) {
                return true;
            }
            break;
        default:
            alert('Something bad happened!');
            break;
    }

    return false;
};

module.exports = Utils;

},{}]},{},[2])


//# sourceMappingURL=locations.js.map
